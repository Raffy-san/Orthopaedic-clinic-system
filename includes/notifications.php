<?php

function createPatientNotification(
    PDO $pdo,
    int $patientId,
    string $title,
    string $message,
    string $type = 'followup',
    ?int $referenceId = null
): bool {
    $userStatement = $pdo->prepare('SELECT UserID FROM patients WHERE PatientID = ?');
    $userStatement->execute([$patientId]);
    $userId = $userStatement->fetchColumn();

    if (!$userId) {
        return false;
    }

    $statement = $pdo->prepare(
        'INSERT INTO notifications (UserID, Title, Message, Type, ReferenceID)
         VALUES (?, ?, ?, ?, ?)'
    );
    $statement->execute([$userId, $title, $message, $type, $referenceId]);

    return true;
}