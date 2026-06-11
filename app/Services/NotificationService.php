<?php
namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public static function send(int $userId, string $type, string $title, string $message, ?string $link = null): int
    {
        return Notification::send($userId, $type, $title, $message, $link);
    }

    public static function getUnread(int $userId): array
    {
        return Notification::getUnreadForUser($userId);
    }

    public static function markRead(int $id): int
    {
        return Notification::update($id, ['is_read' => 1]);
    }

    public static function markAllRead(int $userId): int
    {
        return Notification::markAllAsRead($userId);
    }

    public static function countUnread(int $userId): int
    {
        return Notification::countUnread($userId);
    }

    public static function sendAppointmentReminder(int $userId, array $appointment): int
    {
        $title = 'Appointment Reminder';
        $message = "You have an appointment on {$appointment['appointment_date']} at {$appointment['appointment_time']}.";
        return self::send($userId, 'appointment', $title, $message, '/patient/appointments');
    }

    public static function sendPrescriptionNotification(int $userId, string $medication): int
    {
        $title = 'New Prescription';
        $message = "A new prescription for {$medication} has been created.";
        return self::send($userId, 'prescription', $title, $message, '/patient/prescriptions');
    }

    public static function sendReportNotification(int $userId, string $reportName): int
    {
        $title = 'New Medical Report';
        $message = "A new report '{$reportName}' has been uploaded to your records.";
        return self::send($userId, 'report', $title, $message, '/patient/records');
    }
}
