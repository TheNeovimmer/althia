<?php
namespace App\Models;

use App\Core\Model;

class MedicalRecord extends Model
{
    protected static string $table = 'medical_records';
    protected static array $fillable = ['patient_id', 'doctor_id', 'diagnosis', 'symptoms', 'notes', 'record_date', 'is_private'];

    public static function getPatientRecords(int $patientId): array
    {
        return self::query(
            "SELECT mr.*, u.first_name as doctor_first_name, u.last_name as doctor_last_name,
                    s.name as specialization_name
             FROM medical_records mr
             JOIN doctors d ON mr.doctor_id = d.id
             JOIN users u ON d.user_id = u.id
             LEFT JOIN specializations s ON d.specialization_id = s.id
             WHERE mr.patient_id = ?
             ORDER BY mr.record_date DESC",
            [$patientId]
        );
    }

    public static function getPatientRecordsByDoctor(int $patientId, int $doctorId): array
    {
        return self::query(
            "SELECT * FROM medical_records WHERE patient_id = ? AND doctor_id = ? ORDER BY record_date DESC",
            [$patientId, $doctorId]
        );
    }

    public static function getWithReports(int $id): ?array
    {
        return self::queryFirst(
            "SELECT mr.*, u.first_name as doctor_first_name, u.last_name as doctor_last_name
             FROM medical_records mr
             JOIN doctors d ON mr.doctor_id = d.id
             JOIN users u ON d.user_id = u.id
             WHERE mr.id = ?", [$id]
        );
    }
}
