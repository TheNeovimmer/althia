<?php
namespace App\Models;

use App\Core\Model;

class Appointment extends Model
{
    protected static string $table = 'appointments';
    protected static array $fillable = ['patient_id', 'doctor_id', 'appointment_date', 'appointment_time', 'status', 'type', 'reason', 'notes'];

    public static function getPatientAppointments(int $patientId): array
    {
        return self::query(
            "SELECT a.*, u.first_name as doctor_first_name, u.last_name as doctor_last_name,
                    u.avatar as doctor_avatar, s.name as specialization_name
             FROM appointments a
             JOIN doctors d ON a.doctor_id = d.id
             JOIN users u ON d.user_id = u.id
             LEFT JOIN specializations s ON d.specialization_id = s.id
             WHERE a.patient_id = ?
             ORDER BY a.appointment_date DESC, a.appointment_time DESC",
            [$patientId]
        );
    }

    public static function getDoctorAppointments(int $doctorId): array
    {
        return self::query(
            "SELECT a.*, u.first_name as patient_first_name, u.last_name as patient_last_name,
                    u.phone as patient_phone, p.date_of_birth, p.gender
             FROM appointments a
             JOIN patients p ON a.patient_id = p.id
             JOIN users u ON p.user_id = u.id
             WHERE a.doctor_id = ?
             ORDER BY a.appointment_date DESC, a.appointment_time DESC",
            [$doctorId]
        );
    }

    public static function getUpcomingForPatient(int $patientId, int $limit = 5): array
    {
        return self::query(
            "SELECT a.*, u.first_name as doctor_first_name, u.last_name as doctor_last_name,
                    s.name as specialization_name
             FROM appointments a
             JOIN doctors d ON a.doctor_id = d.id
             JOIN users u ON d.user_id = u.id
             LEFT JOIN specializations s ON d.specialization_id = s.id
             WHERE a.patient_id = ? AND a.status IN ('pending', 'confirmed')
             ORDER BY a.appointment_date ASC, a.appointment_time ASC
             LIMIT ?",
            [$patientId, $limit]
        );
    }

    public static function getUpcomingForDoctor(int $doctorId, int $limit = 10): array
    {
        return self::query(
            "SELECT a.*, u.first_name as patient_first_name, u.last_name as patient_last_name
             FROM appointments a
             JOIN patients p ON a.patient_id = p.id
             JOIN users u ON p.user_id = u.id
             WHERE a.doctor_id = ? AND a.appointment_date >= CURDATE() AND a.status IN ('pending', 'confirmed')
             ORDER BY a.appointment_date ASC, a.appointment_time ASC
             LIMIT ?",
            [$doctorId, $limit]
        );
    }

    public static function getTodayForDoctor(int $doctorId): array
    {
        return self::query(
            "SELECT a.*, u.first_name as patient_first_name, u.last_name as patient_last_name,
                    u.phone as patient_phone
             FROM appointments a
             JOIN patients p ON a.patient_id = p.id
             JOIN users u ON p.user_id = u.id
             WHERE a.doctor_id = ? AND a.appointment_date = CURDATE()
             ORDER BY a.appointment_time ASC",
            [$doctorId]
        );
    }

    public static function getStats(): array
    {
        $db = \App\Core\Database::getInstance();
        return [
            'total' => (int) $db->fetch("SELECT COUNT(*) as c FROM appointments")['c'],
            'pending' => (int) $db->fetch("SELECT COUNT(*) as c FROM appointments WHERE status = 'pending'")['c'],
            'confirmed' => (int) $db->fetch("SELECT COUNT(*) as c FROM appointments WHERE status = 'confirmed'")['c'],
            'completed' => (int) $db->fetch("SELECT COUNT(*) as c FROM appointments WHERE status = 'completed'")['c'],
            'cancelled' => (int) $db->fetch("SELECT COUNT(*) as c FROM appointments WHERE status = 'cancelled'")['c'],
            'today' => (int) $db->fetch("SELECT COUNT(*) as c FROM appointments WHERE appointment_date = CURDATE()")['c'],
        ];
    }

    public static function checkAvailability(int $doctorId, string $date): array
    {
        return self::query(
            "SELECT appointment_time FROM appointments
             WHERE doctor_id = ? AND appointment_date = ? AND status IN ('pending', 'confirmed')
             ORDER BY appointment_time ASC",
            [$doctorId, $date]
        );
    }
}
