<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}
session_start();

class SessionManager
{
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) || isset($_SESSION['userId']) || isset($_SESSION['user']);
    }

    public static function requireLogin(string $redirect = '../index.php'): void
    {
        if (!self::isLoggedIn()) {
            self::redirect($redirect);
        }
    }

    public static function requireRole(string $accessType, string $redirect = '../unauthorized.php'): void
    {
        $role = $_SESSION['access_type']
            ?? $_SESSION['user']['Role']
            ?? $_SESSION['Role']
            ?? null;

        if (!self::roleMatchesAccessType($role, $accessType)) {
            self::redirect($redirect);
        }
    }

    public static function requireAnyRole(array $accessTypes, string $redirect = '../unauthorized.php'): void
    {
        $role = $_SESSION['access_type']
            ?? $_SESSION['user']['Role']
            ?? $_SESSION['Role']
            ?? null;

        foreach ($accessTypes as $accessType) {
            if (self::roleMatchesAccessType($role, (string) $accessType)) {
                return;
            }
        }

        self::redirect($redirect);
    }

    public static function logout(string $redirect = 'index.php'): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        self::redirect($redirect);
    }

    private static function redirect(string $url): void
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
            exit;
        }

        header("Location: $url");
        exit;
    }

    public static function getUser(PDO $pdo): ?array
    {
        if (!self::isLoggedIn()) {
            return null;
        }

        $userId = $_SESSION['user_id'] ?? $_SESSION['userId'] ?? $_SESSION['user']['UserID'] ?? null;
        if ($userId === null) {
            return $_SESSION['user'] ?? null;
        }

        $statement = $pdo->prepare(
            'SELECT UserID AS user_id, Username AS username, FirstName AS first_name,
                    LastName AS last_name, Email AS email, Phone AS phone,
                    Role AS role, IsDoctor AS is_doctor, Status AS status, CreatedAt AS created_at
             FROM users WHERE UserID = ?'
        );
        $statement->execute([$userId]);
        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function regenerateCsrfToken(): string
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        self::requireRole('admin', '../unauthorized.php');
    }

    private static function roleMatchesAccessType(?string $role, string $accessType): bool
    {
        $roleMap = [
            'admin' => 'Admin',
            'doctor' => 'Doctor',
            'receptionist' => 'Receptionist',
            'staff' => 'Staff',
        ];

        return strcasecmp((string) $role, $accessType) === 0
            || (isset($roleMap[strtolower($accessType)]) && $role === $roleMap[strtolower($accessType)]);
    }
}