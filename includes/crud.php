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
?>