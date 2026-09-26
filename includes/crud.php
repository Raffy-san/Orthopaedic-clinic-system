<?php
require_once __DIR__ . '/notifications.php';
require_once __DIR__ . '/audit.php';

/**
 * @param int|null $actorUserId The staff user performing this action (e.g. $_SESSION['user_id']).
 */
function addPatient(PDO $pdo, array $data, ?int $actorUserId = null): array
{
    try {
        $pdo->beginTransaction();

        $patientCode = '';
        do {
            $patientCode = 'PT-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(2)));
            $check = $pdo->prepare('SELECT COUNT(*) FROM patients WHERE PatientCode = ?');
            $check->execute([$patientCode]);
        } while ($check->fetchColumn() > 0);

        $stmt = $pdo->prepare(
            'INSERT INTO users (Username, PasswordHash, FirstName, LastName, Role, Phone)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $patientCode,
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['firstName'],
            $data['lastName'],
            'Patient',
            $data['phone'] ?: null
        ]);
        $userId = $pdo->lastInsertId();

        $stmt = $pdo->prepare(
            'INSERT INTO patients
     (PatientCode, UserID, FirstName, MiddleName, LastName, BirthDate, Gender, Phone, PatientType, Address, Province, City, Barangay, Allergies)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $patientCode,
            $userId,
            $data['firstName'],
            $data['middleName'] ?? null,
            $data['lastName'],
            $data['birthDate'],
            $data['gender'],
            $data['phone'] ?: null,
            $data['patientType'] ?: 'Regular',
            $data['address'] ?: null,
            $data['province'] ?: null,
            $data['city'] ?: null,
            $data['barangay'] ?: null,
            $data['allergies'] ?: null
        ]);
        $patientId = (int) $pdo->lastInsertId();

        // AUDIT: patient created
        logAudit(
            $pdo,
            $actorUserId,
            'CREATE',
            'patients',
            $patientId,
            null,
            null,
            json_encode([
                'PatientCode' => $patientCode,
                'FirstName' => $data['firstName'],
                'MiddleName' => $data['middleName'] ?? null,
                'LastName' => $data['lastName'],
                'BirthDate' => $data['birthDate'],
                'Gender' => $data['gender'],
                'PatientType' => $data['patientType'] ?: 'Regular',
            ])
        );

        $pdo->commit();
        return [
            'status' => 'success',
            'message' => 'Patient registered successfully.',
            'patient_code' => $patientCode,
            'username' => $patientCode
        ];

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('AddPatient failed: ' . $e->getMessage());
        return ['status' => 'error', 'message' => 'Unable to register the patient. Please try again.'];
    }
}

function geocodeAddress(string $address): ?array
{
    if (empty($address)) {
        return null;
    }

    $parts = array_map('trim', explode(',', $address));
    $attempts = [];
    for ($i = 0; $i < count($parts); $i++) {
        $attempts[] = implode(', ', array_slice($parts, $i));
    }

    foreach ($attempts as $index => $query) {
        $cleanQuery = preg_replace('/\s*\(Pob\.?\)\s*/i', ' ', $query);
        $cleanQuery = preg_replace('/^City of\s+/i', '', $cleanQuery);

        $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
            'q' => $cleanQuery,
            'format' => 'json',
            'limit' => 5,
            'countrycodes' => 'ph'
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['User-Agent: OrthopeadicClinic/1.0 (rafaelsanoria506@gmail.com)'],
            CURLOPT_TIMEOUT => 5
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $results = json_decode($response, true);
            if (is_array($results)) {
                foreach ($results as $result) {
                    if (
                        !empty($result['lat']) && !empty($result['lon'])
                        && in_array($result['class'], ['place', 'boundary'], true)
                    ) {
                        return [
                            'lat' => (float) $result['lat'],
                            'lng' => (float) $result['lon']
                        ];
                    }
                }
            }
        }

        if ($index < count($attempts) - 1) {
            usleep(1100000); // respect Nominatim's 1 req/sec limit before the next attempt
        }
    }

    return null;
}

/**
 * @param int|null $actorUserId The staff user performing this action.
 */
function addUser(PDO $pdo, array $data, ?int $actorUserId = null): array
{
    try {
        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            'INSERT INTO users (Username, PasswordHash, FirstName, LastName, Role, IsDoctor, Email, Phone)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $statement->execute([
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['firstName'],
            $data['lastName'],
            $data['role'],
            $data['isDoctor'],
            $data['email'] ?: null,
            $data['phone'] ?: null
        ]);
        $newUserId = (int) $pdo->lastInsertId();

        // AUDIT: user account created (never log the password itself)
        logAudit(
            $pdo,
            $actorUserId,
            'CREATE',
            'users',
            $newUserId,
            null,
            null,
            json_encode([
                'Username' => $data['username'],
                'FirstName' => $data['firstName'],
                'LastName' => $data['lastName'],
                'Role' => $data['role'],
                'IsDoctor' => $data['isDoctor'],
                'Email' => $data['email'] ?: null,
            ])
        );

        $pdo->commit();
        return ['status' => 'success', 'message' => 'User account created successfully.'];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('AddUser failed: ' . $e->getMessage());
        return [
            'status' => 'error',
            'message' => $e->getCode() === '23000'
                ? 'That username or email is already in use.'
                : 'Unable to create the User account. Please try again.'
        ];
    }
}

function expirePendingAppointments(PDO $pdo): void
{
    // AUDIT: capture which appointments are about to be auto-cancelled, before we lose that info
    $stmt = $pdo->query(
        "SELECT AppointmentID FROM appointments
         WHERE Status = 'Pending' AND CreatedAt < (NOW() - INTERVAL 24 HOUR)"
    );
    $expiringIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $pdo->exec(
        "UPDATE appointments
         SET Status = 'Cancelled'
         WHERE Status = 'Pending' AND CreatedAt < (NOW() - INTERVAL 24 HOUR)"
    );

    // Logged with no actor since this is an automatic system action, not a person.
    foreach ($expiringIds as $appointmentId) {
        logAudit(
            $pdo,
            null,
            'UPDATE',
            'appointments',
            (int) $appointmentId,
            'Status',
            'Pending',
            'Cancelled'
        );
    }
}

/**
 * @param int|null $actorUserId The staff/patient user creating this appointment.
 */
function bookAppointment(PDO $pdo, array $data, ?int $actorUserId = null): array
{
    try {
        expirePendingAppointments($pdo);

        $activeAppointment = $pdo->prepare(
            "SELECT AppointmentID, Status FROM appointments
             WHERE PatientID = ? AND Status IN ('Pending', 'Confirmed')
             LIMIT 1"
        );
        $activeAppointment->execute([$data['patientId']]);
        $activeAppointmentStatus = $activeAppointment->fetchColumn(1);
        if ($activeAppointmentStatus) {
            return [
                'status' => 'error',
                'message' => "This patient already has a {$activeAppointmentStatus} appointment."
            ];
        }

        $existingAppointment = $pdo->prepare(
            "SELECT AppointmentID FROM appointments
             WHERE AppointmentDate = ? AND AppointmentTime = ? AND Status <> 'Cancelled'
             LIMIT 1"
        );
        $existingAppointment->execute([$data['appointmentDate'], $data['appointmentTime']]);
        if ($existingAppointment->fetchColumn()) {
            return ['status' => 'error', 'message' => 'That appointment time is already taken for the selected date.'];
        }

        $stmt = $pdo->prepare(
            'INSERT INTO appointments (PatientID, DoctorID, AppointmentDate, AppointmentTime, meridiem, Purpose, ChiefComplaint, Status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['patientId'],
            $data['doctorId'],
            $data['appointmentDate'],
            $data['appointmentTime'],
            $data['meridiem'],
            $data['purpose'],
            $data['chiefComplaint'],
            'Pending'
        ]);
        $appointmentId = (int) $pdo->lastInsertId();

        // AUDIT: appointment created
        logAudit(
            $pdo,
            $actorUserId,
            'CREATE',
            'appointments',
            $appointmentId,
            null,
            null,
            json_encode([
                'PatientID' => $data['patientId'],
                'DoctorID' => $data['doctorId'],
                'AppointmentDate' => $data['appointmentDate'],
                'AppointmentTime' => $data['appointmentTime'],
                'meridiem' => $data['meridiem'],
                'Purpose' => $data['purpose'],
            ])
        );

        return ['status' => 'success', 'message' => 'Appointment booked successfully.'];
    } catch (PDOException $e) {
        error_log('BookAppointment failed: ' . $e->getMessage());
        return ['status' => 'error', 'message' => 'Unable to book the appointment. Please try again.'];
    }
}

function saveConsultation(PDO $pdo, array $data, int $doctorID): array
{
    try {
        $pdo->beginTransaction();

        $appointmentID = intval($data['appointment_id'] ?? 0);
        $consultationID = intval($data['consultation_id'] ?? 0);

        if (!$consultationID) {
            $pdo->rollBack();
            return ['status' => 'error', 'message' => 'No active consultation found. Please click Start Consultation first.'];
        }

        // Lock the appointment row and check it hasn't already been consulted
        $checkStmt = $pdo->prepare("
            SELECT Status FROM appointments WHERE AppointmentID = :appointment_id FOR UPDATE
        ");
        $checkStmt->execute([':appointment_id' => $appointmentID]);
        $appointment = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$appointment) {
            $pdo->rollBack();
            return ['status' => 'error', 'message' => 'Appointment not found.'];
        }
        if ($appointment['Status'] === 'Completed') {
            $pdo->rollBack();
            return ['status' => 'error', 'message' => 'This consultation has already been saved and cannot be submitted again.'];
        }

        // Lock and verify the consultation row started earlier actually belongs to this appointment
        $consultCheck = $pdo->prepare("
            SELECT ConsultationID FROM consultations 
            WHERE ConsultationID = :consultation_id AND AppointmentID = :appointment_id AND IsCompleted = 0
            FOR UPDATE
        ");
        $consultCheck->execute([
            ':consultation_id' => $consultationID,
            ':appointment_id' => $appointmentID
        ]);
        if (!$consultCheck->fetch()) {
            $pdo->rollBack();
            return ['status' => 'error', 'message' => 'Consultation record not found or already completed.'];
        }

        // Fill in the details and close out the consultation
        $stmt = $pdo->prepare("
            UPDATE consultations 
            SET Diagnosis = :diagnosis, 
                Treatment = :treatment, 
                Notes = :notes, 
                ConsultationFee = :consultation_fee, 
                EndTime = CURTIME(), 
                IsCompleted = 1
            WHERE ConsultationID = :consultation_id
        ");
        $stmt->execute([
            ':diagnosis' => $data['diagnosis'] ?? '',
            ':treatment' => $data['treatment'] ?? '',
            ':notes' => $data['notes'] ?? '',
            ':consultation_fee' => floatval($data['consultation_fee'] ?? 0),
            ':consultation_id' => $consultationID
        ]);

        // AUDIT: diagnosis/treatment recorded for this consultation
        logAudit($pdo, $doctorID, 'UPDATE', 'consultations', $consultationID, 'Diagnosis', null, $data['diagnosis'] ?? '');
        logAudit($pdo, $doctorID, 'UPDATE', 'consultations', $consultationID, 'Treatment', null, $data['treatment'] ?? '');

        if (($data['has_prescription'] ?? false) && !empty($data['prescriptions']) && is_array($data['prescriptions'])) {
            $rxStmt = $pdo->prepare("
                INSERT INTO prescriptions (ConsultationID, Medicine, Dosage, Frequency, Duration, Instructions)
                VALUES (:consultation_id, :medicine, :dosage, :frequency, :duration, :instructions)
            ");
            foreach ($data['prescriptions'] as $rx) {
                $rxStmt->execute([
                    ':consultation_id' => $consultationID,
                    ':medicine' => $rx['medicine'] ?? '',
                    ':dosage' => $rx['dosage'] ?? '',
                    ':frequency' => $rx['frequency'] ?? '',
                    ':duration' => $rx['duration'] ?? '',
                    ':instructions' => $rx['instructions'] ?? ''
                ]);

                // AUDIT: each prescribed medicine
                logAudit(
                    $pdo,
                    $doctorID,
                    'CREATE',
                    'prescriptions',
                    (int) $pdo->lastInsertId(),
                    null,
                    null,
                    json_encode($rx)
                );
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO billing (ConsultationID, PatientID, OriginalAmount, DiscountType, DiscountPercent, DiscountAmount, FinalAmount, Status)
            VALUES (:consultation_id, :patient_id, :original_amount, 'None', 0, 0, :original_amount, 'Unpaid')
        ");
        $stmt->execute([
            ':consultation_id' => $consultationID,
            ':patient_id' => intval($data['patient_id'] ?? 0),
            ':original_amount' => floatval($data['consultation_fee'] ?? 0)
        ]);

        // AUDIT: billing record created
        logAudit(
            $pdo,
            $doctorID,
            'CREATE',
            'billing',
            (int) $pdo->lastInsertId(),
            null,
            null,
            json_encode([
                'ConsultationID' => $consultationID,
                'PatientID' => intval($data['patient_id'] ?? 0),
                'OriginalAmount' => floatval($data['consultation_fee'] ?? 0),
            ])
        );

        $stmt = $pdo->prepare("
            UPDATE appointments 
            SET Status = 'Completed' 
            WHERE AppointmentID = :appointment_id
        ");
        $stmt->execute([':appointment_id' => $appointmentID]);

        // AUDIT: appointment marked completed
        logAudit($pdo, $doctorID, 'UPDATE', 'appointments', $appointmentID, 'Status', $appointment['Status'], 'Completed');

        $followupID = null;
        if (($data['has_followup'] ?? false) && !empty($data['followup']['date'])) {
            $followupDate = $data['followup']['date'];

            $dateObj = DateTime::createFromFormat('Y-m-d', $followupDate);
            if (!$dateObj || $dateObj->format('Y-m-d') !== $followupDate) {
                throw new PDOException('Invalid follow-up date format.');
            }
            if ($dateObj < new DateTime('today')) {
                throw new PDOException('Follow-up date cannot be in the past.');
            }

            $availabilityStmt = $pdo->prepare(
                "SELECT COUNT(*) FROM appointments
                 WHERE DoctorID = ? AND AppointmentDate = ? AND Status <> 'Cancelled'
                 UNION ALL
                 SELECT COUNT(*) FROM followups
                 WHERE DoctorID = ? AND FollowUpDate = ? AND Status = 'Scheduled'"
            );
            $availabilityStmt->execute([$doctorID, $followupDate, $doctorID, $followupDate]);
            $availabilityCounts = $availabilityStmt->fetchAll(PDO::FETCH_COLUMN);

            if (array_sum(array_map('intval', $availabilityCounts)) > 0) {
                $availableDates = [];
                $candidateDate = clone $dateObj;

                for ($offset = 0; $offset < 30 && count($availableDates) < 5; $offset++) {
                    if ($offset > 0) {
                        $candidateDate->modify('+1 day');
                    }

                    if ((int) $candidateDate->format('N') >= 6) {
                        continue;
                    }

                    $candidate = $candidateDate->format('Y-m-d');
                    $candidateStmt = $pdo->prepare(
                        "SELECT
                            (SELECT COUNT(*) FROM appointments WHERE DoctorID = ? AND AppointmentDate = ? AND Status <> 'Cancelled')
                            + (SELECT COUNT(*) FROM followups WHERE DoctorID = ? AND FollowUpDate = ? AND Status = 'Scheduled')"
                    );
                    $candidateStmt->execute([$doctorID, $candidate, $doctorID, $candidate]);

                    if ((int) $candidateStmt->fetchColumn() === 0) {
                        $availableDates[] = $candidate;
                    }
                }

                $pdo->rollBack();
                return [
                    'status' => 'error',
                    'code' => 'followup_date_unavailable',
                    'message' => 'The doctor is unavailable on ' . $dateObj->format('F j, Y') . '.',
                    'requested_date' => $followupDate,
                    'available_dates' => $availableDates,
                    'patient_id' => intval($data['patient_id'] ?? 0)
                ];
            }

            $stmt = $pdo->prepare("
                INSERT INTO followups (PatientID, DoctorID, AppointmentID, FollowUpDate, Status, Remarks)
                VALUES (:patient_id, :doctor_id, :appointment_id, :followup_date, 'Scheduled', :remarks)
            ");
            $stmt->execute([
                ':patient_id' => intval($data['patient_id'] ?? 0),
                ':doctor_id' => $doctorID,
                ':appointment_id' => $appointmentID,
                ':followup_date' => $followupDate,
                ':remarks' => $data['followup']['remarks'] ?? null
            ]);
            $followupID = $pdo->lastInsertId();

            // AUDIT: follow-up scheduled
            logAudit(
                $pdo,
                $doctorID,
                'CREATE',
                'followups',
                (int) $followupID,
                null,
                null,
                json_encode([
                    'PatientID' => intval($data['patient_id'] ?? 0),
                    'DoctorID' => $doctorID,
                    'AppointmentID' => $appointmentID,
                    'FollowUpDate' => $followupDate,
                ])
            );

            createPatientNotification(
                $pdo,
                intval($data['patient_id'] ?? 0),
                'Follow-up check-up scheduled',
                'Your follow-up check-up is scheduled for ' . date('F j, Y', strtotime($followupDate)) . '.',
                'followup',
                (int) $followupID
            );
        }

        $pdo->commit();

        return [
            'status' => 'success',
            'message' => 'Consultation saved successfully.',
            'consultation_id' => $consultationID,
            'followup_id' => $followupID
        ];

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('SaveConsultation failed: ' . $e->getMessage());
        return ['status' => 'error', 'message' => 'Unable to save the consultation. Please try again.'];
    }
}

/**
 * @param int|null $actorUserId The staff user performing this action.
 */
function updatePatient(PDO $pdo, array $data, ?int $actorUserId = null): array
{
    try {
        $pdo->beginTransaction();

        if (empty($data['patient_code'] ?? '')) {
            return ['status' => 'error', 'message' => 'Patient code is required.'];
        }

        // Fetch full old rows BEFORE updating, so we can diff afterwards
        $stmt = $pdo->prepare('SELECT * FROM patients WHERE PatientCode = ?');
        $stmt->execute([$data['patient_code']]);
        $oldPatientRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$oldPatientRow) {
            $pdo->rollBack();
            return ['status' => 'error', 'message' => 'Patient not found.'];
        }

        $userID = $oldPatientRow['UserID'];
        $patientId = (int) $oldPatientRow['PatientID'];

        $stmt = $pdo->prepare('SELECT * FROM users WHERE UserID = ?');
        $stmt->execute([$userID]);
        $oldUserRow = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $stmt = $pdo->prepare(
            'UPDATE users 
             SET FirstName = ?, LastName = ?, Phone = ?
             WHERE UserID = ?'
        );
        $stmt->execute([
            $data['firstName'] ?? '',
            $data['lastName'] ?? '',
            $data['phone'] ?? null,
            $userID
        ]);

        $stmt = $pdo->prepare(
            'UPDATE patients 
     SET FirstName = ?, MiddleName = ?, LastName = ?, BirthDate = ?, Gender = ?, Phone = ?, PatientType = ?, Address = ?, Province = ?, City = ?, Barangay = ?, Allergies = ?
     WHERE PatientCode = ?'
        );
        $stmt->execute([
            $data['firstName'] ?? '',
            $data['middleName'] ?? '',
            $data['lastName'] ?? '',
            $data['birthDate'] ?? '',
            $data['gender'] ?? '',
            $data['phone'] ?? null,
            $data['patientType'] ?? 'Regular',
            $data['address'] ?? null,
            $data['province'] ?? null,
            $data['city'] ?? null,
            $data['barangay'] ?? null,
            $data['allergies'] ?? null,
            $data['patient_code']
        ]);

        // AUDIT: fetch the new rows and log per-field differences
        $stmt = $pdo->prepare('SELECT * FROM patients WHERE PatientCode = ?');
        $stmt->execute([$data['patient_code']]);
        $newPatientRow = $stmt->fetch(PDO::FETCH_ASSOC);
        logFieldChanges($pdo, $actorUserId, 'patients', $patientId, $oldPatientRow, $newPatientRow);

        if (!empty($oldUserRow)) {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE UserID = ?');
            $stmt->execute([$userID]);
            $newUserRow = $stmt->fetch(PDO::FETCH_ASSOC);
            logFieldChanges($pdo, $actorUserId, 'users', (int) $userID, $oldUserRow, $newUserRow);
        }

        $pdo->commit();
        return ['status' => 'success', 'message' => 'Patient information updated successfully.'];

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('UpdatePatient failed: ' . $e->getMessage());
        return ['status' => 'error', 'message' => 'Unable to update the patient. Please try again.'];
    }
}

/**
 * @param int|null $actorUserId The staff user performing this action.
 */
function updateUser(PDO $pdo, array $data, ?int $actorUserId = null): array
{
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('SELECT * FROM users WHERE UserID = ?');
        $stmt->execute([$data['userId']]);
        $oldUserRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$oldUserRow) {
            $pdo->rollBack();
            return ['status' => 'error', 'message' => 'That account no longer exists.'];
        }

        if ($data['password'] !== '') {
            $statement = $pdo->prepare(
                'UPDATE users
                 SET Username = ?, PasswordHash = ?, FirstName = ?, LastName = ?, Role = ?, IsDoctor = ?, Email = ?, Phone = ?
                 WHERE UserID = ?'
            );
            $statement->execute([
                $data['username'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['firstName'],
                $data['lastName'],
                $data['role'],
                $data['isDoctor'],
                $data['email'] ?: null,
                $data['phone'] ?: null,
                $data['userId']
            ]);
        } else {
            $statement = $pdo->prepare(
                'UPDATE users
                 SET Username = ?, FirstName = ?, LastName = ?, Role = ?, IsDoctor = ?, Email = ?, Phone = ?
                 WHERE UserID = ?'
            );
            $statement->execute([
                $data['username'],
                $data['firstName'],
                $data['lastName'],
                $data['role'],
                $data['isDoctor'],
                $data['email'] ?: null,
                $data['phone'] ?: null,
                $data['userId']
            ]);
        }

        // AUDIT: fetch new row and log per-field differences (PasswordHash auto-excluded)
        $stmt = $pdo->prepare('SELECT * FROM users WHERE UserID = ?');
        $stmt->execute([$data['userId']]);
        $newUserRow = $stmt->fetch(PDO::FETCH_ASSOC);
        logFieldChanges($pdo, $actorUserId, 'users', (int) $data['userId'], $oldUserRow, $newUserRow);

        // Separately log that a password reset occurred, without storing any hash value
        if ($data['password'] !== '') {
            logAudit($pdo, $actorUserId, 'UPDATE', 'users', (int) $data['userId'], 'PasswordHash', '(hidden)', '(hidden)');
        }

        $pdo->commit();
        return ['status' => 'success', 'message' => 'User account updated successfully.'];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('UpdateUser failed: ' . $e->getMessage());
        return [
            'status' => 'error',
            'message' => $e->getCode() === '23000'
                ? 'That username or email is already in use.'
                : 'Unable to update the user account. Please try again.'
        ];
    }
}