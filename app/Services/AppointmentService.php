<?php
namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;

class AppointmentService
{
    public static function book(array $data): array
    {
        $errors = [];

        if (empty($data['doctor_id'])) {
            $errors[] = 'Please select a doctor';
        }
        if (empty($data['appointment_date'])) {
            $errors[] = 'Please select a date';
        }
        if (empty($data['appointment_time'])) {
            $errors[] = 'Please select a time';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $date = $data['appointment_date'];

        if (!strtotime($date)) {
            return ['success' => false, 'errors' => ['Invalid date format']];
        }

        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            return ['success' => false, 'errors' => ['Cannot book appointments in the past']];
        }

        $existing = Appointment::checkAvailability($data['doctor_id'], $date);
        foreach ($existing as $slot) {
            if ($slot['appointment_time'] === $data['appointment_time']) {
                return ['success' => false, 'errors' => ['This time slot is already booked']];
            }
        }

        $id = Appointment::create([
            'patient_id' => $data['patient_id'],
            'doctor_id' => $data['doctor_id'],
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'type' => $data['type'] ?? 'in-person',
            'reason' => $data['reason'] ?? '',
            'status' => 'pending',
        ]);

        if (!empty($data['doctor_id'])) {
            $doctorRec = Doctor::find($data['doctor_id']);
            if ($doctorRec && !empty($doctorRec['user_id'])) {
                NotificationService::send(
                    $doctorRec['user_id'],
                    'appointment',
                    'New Appointment Request',
                    'A new appointment has been booked for ' . $data['appointment_date'],
                    '/doctor/appointments'
                );
            }
        }

        $admins = User::where('role', 'admin');
        foreach ($admins as $admin) {
            NotificationService::send(
                $admin['id'],
                'appointment',
                'New Appointment Booking',
                'A new appointment has been booked, pending your approval.',
                '/admin/appointments?status=pending'
            );
        }

        $patient = \App\Models\Patient::find($data['patient_id']);
        if ($patient && !empty($patient['user_id'])) {
            NotificationService::send(
                $patient['user_id'],
                'appointment',
                'Appointment Booked',
                'Your appointment has been submitted and is pending admin approval.',
                '/patient/appointments'
            );
        }

        return ['success' => true, 'id' => $id];
    }

    public static function cancel(int $id, string $reason = ''): bool
    {
        $appointment = Appointment::find($id);
        if (!$appointment) return false;

        Appointment::update($id, [
            'status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
            'cancellation_reason' => $reason,
        ]);

        $patient = \App\Models\Patient::find($appointment['patient_id']);
        if ($patient && !empty($patient['user_id'])) {
            NotificationService::send(
                $patient['user_id'],
                'appointment',
                'Appointment Cancelled',
                'Your appointment on ' . $appointment['appointment_date'] . ' has been cancelled.' . ($reason ? " Reason: $reason" : ''),
                '/patient/appointments'
            );
        }

        return true;
    }

    public static function confirm(int $id): bool
    {
        $apt = Appointment::find($id);
        if (!$apt) return false;

        Appointment::update($id, ['status' => 'confirmed']);

        $patient = \App\Models\Patient::find($apt['patient_id']);
        if ($patient && !empty($patient['user_id'])) {
            NotificationService::send(
                $patient['user_id'],
                'appointment',
                'Appointment Confirmed',
                'Your appointment on ' . $apt['appointment_date'] . ' has been confirmed.',
                '/patient/appointments'
            );
        }

        return true;
    }

    public static function complete(int $id): bool
    {
        $appointment = Appointment::find($id);
        if (!$appointment) return false;

        Appointment::update($id, ['status' => 'completed']);

        $patient = \App\Models\Patient::find($appointment['patient_id']);
        if ($patient && !empty($patient['user_id'])) {
            NotificationService::send(
                $patient['user_id'],
                'appointment',
                'Appointment Completed',
                'Your appointment on ' . $appointment['appointment_date'] . ' has been marked as completed.',
                '/patient/appointments'
            );
        }

        return true;
    }

    public static function getAvailableSlots(int $doctorId, string $date): array
    {
        $booked = Appointment::checkAvailability($doctorId, $date);
        $bookedTimes = array_column($booked, 'appointment_time');

        $allSlots = [];
        for ($h = 8; $h <= 17; $h++) {
            $time = sprintf('%02d:00:00', $h);
            if (!in_array($time, $bookedTimes)) {
                $allSlots[] = $time;
            }
            $time30 = sprintf('%02d:30:00', $h);
            if ($h < 17 && !in_array($time30, $bookedTimes)) {
                $allSlots[] = $time30;
            }
        }

        return $allSlots;
    }

    public static function getUpcoming(int $userId, string $role): array
    {
        if ($role === 'patient') {
            $patient = \App\Models\Patient::findByUserId($userId);
            return $patient ? Appointment::getUpcomingForPatient($patient['id']) : [];
        }
        if ($role === 'doctor') {
            $doctor = \App\Models\Doctor::findByUserId($userId);
            return $doctor ? Appointment::getUpcomingForDoctor($doctor['id']) : [];
        }
        return [];
    }
}
