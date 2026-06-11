<?php
namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static string $table = 'users';
    protected static array $fillable = ['email', 'password', 'role', 'first_name', 'last_name', 'phone', 'avatar', 'is_active'];

    public static function findByEmail(string $email): ?array
    {
        return self::whereFirst('email', $email);
    }

    public static function getDoctorUsers(): array
    {
        return self::where('role', 'doctor');
    }

    public static function getPatientUsers(): array
    {
        return self::where('role', 'patient');
    }

    public static function getAdminUsers(): array
    {
        return self::where('role', 'admin');
    }

    public static function getRecent(int $limit = 5): array
    {
        return self::query("SELECT * FROM users ORDER BY created_at DESC LIMIT ?", [$limit]);
    }

    public static function search(string $query): array
    {
        return self::query(
            "SELECT * FROM users WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? ORDER BY created_at DESC",
            ["%{$query}%", "%{$query}%", "%{$query}%"]
        );
    }

    public static function getUnreadNotificationsCount(int $userId): int
    {
        $db = \App\Core\Database::getInstance();
        return (int) $db->fetch("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0", [$userId])['count'];
    }
}
