<?php
namespace App\Models;

use App\Core\Model;

class Prescription extends Model
{
    protected static string $table = 'prescriptions';
    protected static array $fillable = ['patient_id', 'doctor_id', 'record_id', 'medication_name', 'dosage', 'frequency', 'duration', 'instructions', 'start_date', 'end_date', 'is_active'];

    public static function getPatientPrescriptions(int $patientId): array
    {
        return self::query(
            "SELECT p.*, u.first_name as doctor_first_name, u.last_name as doctor_last_name
             FROM prescriptions p
             JOIN doctors d ON p.doctor_id = d.id
             JOIN users u ON d.user_id = u.id
             WHERE p.patient_id = ?
             ORDER BY p.created_at DESC",
            [$patientId]
        );
    }

    public static function getDoctorPrescriptions(int $doctorId): array
    {
        return self::query(
            "SELECT p.*, u.first_name as patient_first_name, u.last_name as patient_last_name
             FROM prescriptions p
             JOIN patients pa ON p.patient_id = pa.id
             JOIN users u ON pa.user_id = u.id
             WHERE p.doctor_id = ?
             ORDER BY p.created_at DESC",
            [$doctorId]
        );
    }

    public static function getActiveForPatient(int $patientId): array
    {
        return self::query(
            "SELECT p.*, u.first_name as doctor_first_name, u.last_name as doctor_last_name
             FROM prescriptions p
             JOIN doctors d ON p.doctor_id = d.id
             JOIN users u ON d.user_id = u.id
             WHERE p.patient_id = ? AND p.is_active = 1
             ORDER BY p.created_at DESC",
            [$patientId]
        );
    }
}
