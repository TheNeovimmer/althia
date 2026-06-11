<?php
namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;

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
        if (strtotime($date) <= strtotime(date('Y-m-d'))) {
            $existing = Appointment::checkAvailability($data['doctor_id'], $date);
            foreach ($existing as $slot) {
                if ($slot['appointment_time'] === $data['appointment_time']) {
                    return ['success' => false, 'errors' => ['This time slot is already booked']];
                }
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
            $doctorRec = \App\Models\Doctor::find($data['doctor_id']);
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

        return true;
    }

    public static function confirm(int $id): bool
    {
        Appointment::update($id, ['status' => 'confirmed']);
        return true;
    }

    public static function complete(int $id): bool
    {
        Appointment::update($id, ['status' => 'completed']);
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
