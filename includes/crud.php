<?php
function addPatient(PDO $pdo, array $data): array
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

        // Geocode the address if one was provided — fails gracefully to null/null
        $coords = geocodeAddress($data['address'] ?? '');

        $stmt = $pdo->prepare(
            'INSERT INTO patients
             (PatientCode, UserID, FirstName, LastName, BirthDate, Gender, Phone, PatientType, Address, Latitude, Longitude)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $patientCode,
            $userId,
            $data['firstName'],
            $data['lastName'],
            $data['birthDate'],
            $data['gender'],
            $data['phone'] ?: null,
            $data['patientType'] ?: 'Regular',
            $data['address'] ?: null,
            $coords['lat'] ?? null,
            $coords['lng'] ?? null
        ]);

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

    $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
        'q' => $address,
        'format' => 'json',
        'limit' => 1
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['User-Agent: OrthopeadicClinic/1.0 (rafaelsanoria506@gmail.com)'], 
        CURLOPT_TIMEOUT => 5
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        return null;
    }

    $results = json_decode($response, true);
    if (empty($results[0]['lat']) || empty($results[0]['lon'])) {
        return null; // address not found — fail gracefully, don't block registration
    }

    return [
        'lat' => (float) $results[0]['lat'],
        'lng' => (float) $results[0]['lon']
    ];
}

function addStaff(PDO $pdo, array $data): array
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

        $pdo->commit();
        return ['status' => 'success', 'message' => 'Staff account created successfully.'];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('AddStaff failed: ' . $e->getMessage());
        return [
            'status' => 'error',
            'message' => $e->getCode() === '23000'
                ? 'That username or email is already in use.'
                : 'Unable to create the staff account. Please try again.'
        ];
    }
}

function bookAppointment(PDO $pdo, array $data): array
{
    try {
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
            'INSERT INTO appointments (PatientID, DoctorID, AppointmentDate, AppointmentTime, meridiem, Purpose, Status)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['patientId'],
            $data['doctorId'],
            $data['appointmentDate'],
            $data['appointmentTime'],
            $data['meridiem'],
            $data['purpose'],
            'Pending'
        ]);

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

        $stmt = $pdo->prepare("
            INSERT INTO consultations (AppointmentID, PatientID, DoctorID, Diagnosis, Treatment, Notes, ConsultationFee, ConsultationDate)
            VALUES (:appointment_id, :patient_id, :doctor_id, :diagnosis, :treatment, :notes, :consultation_fee, NOW())
        ");
        $stmt->execute([
            ':appointment_id' => intval($data['appointment_id'] ?? 0),
            ':patient_id' => intval($data['patient_id'] ?? 0),
            ':doctor_id' => $doctorID,
            ':diagnosis' => $data['diagnosis'] ?? '',
            ':treatment' => $data['treatment'] ?? '',
            ':notes' => $data['notes'] ?? '',
            ':consultation_fee' => floatval($data['consultation_fee'] ?? 0)
        ]);

        $consultationID = $pdo->lastInsertId();

        if (($data['has_prescription'] ?? false) && ($data['prescription'] ?? null)) {
            $stmt = $pdo->prepare("
                INSERT INTO prescriptions (ConsultationID, Medicine, Dosage, Frequency, Duration, Instructions)
                VALUES (:consultation_id, :medicine, :dosage, :frequency, :duration, :instructions)
            ");
            $stmt->execute([
                ':consultation_id' => $consultationID,
                ':medicine' => $data['prescription']['medicine'] ?? '',
                ':dosage' => $data['prescription']['dosage'] ?? '',
                ':frequency' => $data['prescription']['frequency'] ?? '',
                ':duration' => $data['prescription']['duration'] ?? '',
                ':instructions' => $data['prescription']['instructions'] ?? ''
            ]);
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

        $stmt = $pdo->prepare("
            UPDATE appointments 
            SET Status = 'Completed' 
            WHERE AppointmentID = :appointment_id
        ");
        $stmt->execute([':appointment_id' => intval($data['appointment_id'] ?? 0)]);

        $pdo->commit();

        return [
            'status' => 'success',
            'message' => 'Consultation saved successfully.'
        ];

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('SaveConsultation failed: ' . $e->getMessage());
        return ['status' => 'error', 'message' => 'Unable to save the consultation. Please try again.'];
    }
}

function updatePatient(PDO $pdo, array $data): array
{
    try {
        $pdo->beginTransaction();

        if (empty($data['patient_code'] ?? '')) {
            return ['status' => 'error', 'message' => 'Patient code is required.'];
        }

        $stmt = $pdo->prepare('SELECT UserID FROM patients WHERE PatientCode = ?');
        $stmt->execute([$data['patient_code']]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$patient) {
            return ['status' => 'error', 'message' => 'Patient not found.'];
        }

        $userID = $patient['UserID'];

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

        $coords = geocodeAddress($data['address'] ?? '');

        $stmt = $pdo->prepare(
            'UPDATE patients 
             SET FirstName = ?, LastName = ?, BirthDate = ?, Gender = ?, Phone = ?, PatientType = ?, Address = ?, Latitude = ?, Longitude = ?
             WHERE PatientCode = ?'
        );
        $stmt->execute([
            $data['firstName'] ?? '',
            $data['lastName'] ?? '',
            $data['birthDate'] ?? '',
            $data['gender'] ?? '',
            $data['phone'] ?? null,
            $data['patientType'] ?? 'Regular',
            $data['address'] ?? null,
            $coords['lat'] ?? null,
            $coords['lng'] ?? null,
            $data['patient_code']
        ]);

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
?>