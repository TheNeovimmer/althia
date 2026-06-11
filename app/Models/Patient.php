<?php
namespace App\Models;

use App\Core\Model;

class Patient extends Model
{
    protected static string $table = 'patients';
    protected static array $fillable = ['user_id', 'date_of_birth', 'gender', 'blood_type', 'weight', 'height', 'address', 'emergency_contact_name', 'emergency_contact_phone'];

    public static function findByUserId(int $userId): ?array
    {
        return self::whereFirst('user_id', $userId);
    }

    public static function getWithUser(int $id): ?array
    {
        return self::queryFirst(
            "SELECT p.*, u.email, u.first_name, u.last_name, u.phone, u.avatar
             FROM patients p
             JOIN users u ON p.user_id = u.id
             WHERE p.id = ?", [$id]
        );
    }

    public static function getAllWithUsers(): array
    {
        return self::query(
            "SELECT p.*, u.email, u.first_name, u.last_name, u.phone, u.avatar, u.created_at as registered_at
             FROM patients p
             JOIN users u ON p.user_id = u.id
             ORDER BY u.created_at DESC"
        );
    }

    public static function getByDoctorId(int $doctorId): array
    {
        return self::query(
            "SELECT DISTINCT p.*, u.email, u.first_name, u.last_name, u.phone, u.avatar,
                    (SELECT MAX(mr.record_date) FROM medical_records mr WHERE mr.patient_id = p.id) as last_visit,
                    (SELECT mr.diagnosis FROM medical_records mr WHERE mr.patient_id = p.id AND mr.doctor_id = ? ORDER BY mr.created_at DESC LIMIT 1) as last_diagnosis
             FROM patients p
             JOIN users u ON p.user_id = u.id
             LEFT JOIN medical_records mr ON mr.patient_id = p.id
             WHERE mr.doctor_id = ?
             GROUP BY p.id
             ORDER BY last_visit DESC",
            [$doctorId, $doctorId]
        );
    }
}
