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

        $stmt = $pdo->prepare(
            'INSERT INTO patients
             (PatientCode, UserID, FirstName, LastName, BirthDate, Gender, Phone, PatientType)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $patientCode,
            $userId,
            $data['firstName'],
            $data['lastName'],
            $data['birthDate'],
            $data['gender'],
            $data['phone'] ?: null,
            $data['patientType'] ?: 'Regular'
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
?>