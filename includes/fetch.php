<?php
function fetchAllData(PDO $pdo, string $query, array $params = []): array
{
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchOneData(PDO $pdo, string $query, array $params = []): ?array
{
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
}