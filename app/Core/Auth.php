<?php
namespace App\Core;

class Auth
{
    private static ?array $user = null;

    public static function login(string $email, string $password): bool
    {
        $db = Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            self::$user = $user;
            return true;
        }
        return false;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name']);
        self::$user = null;
        session_destroy();
    }

    public static function user(): ?array
    {
        if (self::$user === null && isset($_SESSION['user_id'])) {
            $db = Database::getInstance();
            self::$user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
        }
        return self::$user;
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function isDoctor(): bool
    {
        return self::role() === 'doctor';
    }

    public static function isPatient(): bool
    {
        return self::role() === 'patient';
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }

    public static function requireRole(string $role): void
    {
        self::requireAuth();
        if (self::role() !== $role) {
            header('Location: /');
            exit;
        }
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
