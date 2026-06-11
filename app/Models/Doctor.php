<?php
namespace App\Models;

use App\Core\Model;

class Doctor extends Model
{
    protected static string $table = 'doctors';
    protected static array $fillable = ['user_id', 'specialization_id', 'license_number', 'bio', 'education', 'experience_years', 'consultation_fee', 'available_days', 'available_hours', 'is_verified'];

    public static function findByUserId(int $userId): ?array
    {
        return self::whereFirst('user_id', $userId);
    }

    public static function getAllWithUsers(): array
    {
        return self::query(
            "SELECT d.*, u.email, u.first_name, u.last_name, u.phone, u.avatar, u.is_active,
                    s.name as specialization_name
             FROM doctors d
             JOIN users u ON d.user_id = u.id
             LEFT JOIN specializations s ON d.specialization_id = s.id
             ORDER BY u.first_name ASC"
        );
    }

    public static function getVerified(): array
    {
        return self::query(
            "SELECT d.*, u.first_name, u.last_name, u.avatar, s.name as specialization_name
             FROM doctors d
             JOIN users u ON d.user_id = u.id
             LEFT JOIN specializations s ON d.specialization_id = s.id
             WHERE d.is_verified = 1 AND u.is_active = 1
             ORDER BY d.rating DESC"
        );
    }

    public static function getBySpecialization(int $specializationId): array
    {
        return self::query(
            "SELECT d.*, u.first_name, u.last_name, u.avatar
             FROM doctors d
             JOIN users u ON d.user_id = u.id
             WHERE d.specialization_id = ? AND d.is_verified = 1",
            [$specializationId]
        );
    }

    public static function getWithUser(int $id): ?array
    {
        return self::queryFirst(
            "SELECT d.*, u.email, u.first_name, u.last_name, u.phone, u.avatar,
                    s.name as specialization_name, s.description as specialization_description
             FROM doctors d
             JOIN users u ON d.user_id = u.id
             LEFT JOIN specializations s ON d.specialization_id = s.id
             WHERE d.id = ?", [$id]
        );
    }

    public static function getAppointmentCount(int $doctorId): int
    {
        $db = \App\Core\Database::getInstance();
        return (int) $db->fetch("SELECT COUNT(*) as count FROM appointments WHERE doctor_id = ?", [$doctorId])['count'];
    }

    public static function getPatientCount(int $doctorId): int
    {
        $db = \App\Core\Database::getInstance();
        return (int) $db->fetch(
            "SELECT COUNT(DISTINCT patient_id) as count FROM appointments WHERE doctor_id = ?",
            [$doctorId]
        )['count'];
    }
}
