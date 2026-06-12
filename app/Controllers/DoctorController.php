<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\Message;
use App\Models\Notification;
use App\Services\AppointmentService;
use App\Services\FileService;
use App\Services\NotificationService;

class DoctorController extends Controller
{
    private function getDoctor(): ?array
    {
        $user = Auth::user();
        return Doctor::findByUserId($user['id']);
    }

    public function dashboard(): void
    {
        $doctor = $this->getDoctor();
        $user = Auth::user();
        if (!$doctor) { $this->redirect('/'); return; }

        $db = Database::getInstance();
        $todayAppointments = $db->fetchAll("
            SELECT a.*, u.first_name, u.last_name, u.avatar
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            JOIN users u ON p.user_id = u.id
            WHERE a.doctor_id = ? AND a.appointment_date = CURDATE()
            ORDER BY a.appointment_time
        ", [$doctor['id']]);

        $stats = [
            'todayAppointments' => count($todayAppointments),
            'totalPatients' => (int) ($db->fetch("SELECT COUNT(DISTINCT patient_id) as c FROM appointments WHERE doctor_id = ?", [$doctor['id']])['c'] ?? 0),
            'totalAppointments' => (int) ($db->fetch("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = ?", [$doctor['id']])['c'] ?? 0),
            'totalPrescriptions' => (int) ($db->fetch("SELECT COUNT(*) as c FROM prescriptions WHERE doctor_id = ?", [$doctor['id']])['c'] ?? 0),
        ];

        // Chart: Appointments this week
        $weekLabels = [];
        $weekData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $weekLabels[] = date('D', strtotime($date));
            $c = (int) ($db->fetch("SELECT COUNT(*) as c FROM appointments WHERE doctor_id = ? AND DATE(appointment_date) = ?", [$doctor['id'], $date])['c'] ?? 0);
            $weekData[] = $c;
        }

        // Chart: Status distribution
        $statuses = $db->fetchAll("SELECT status, COUNT(*) as c FROM appointments WHERE doctor_id = ? GROUP BY status", [$doctor['id']]);
        $sMap = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
        foreach ($statuses as $s) { $sMap[$s['status']] = (int)$s['c']; }

        $chartData = [
            'weekLabels' => json_encode($weekLabels),
            'weekData' => json_encode($weekData),
            'statusLabels' => json_encode(['Pending', 'Confirmed', 'Completed', 'Cancelled']),
            'statusData' => json_encode(array_values($sMap)),
            'statusColors' => json_encode(['#ffc107', '#0d6efd', '#198754', '#dc3545']),
        ];

        $this->render('doctor/dashboard', compact(
            'doctor', 'user', 'stats', 'todayAppointments', 'chartData'
        ));
    }

    public function patients(): void
    {
        $doctor = $this->getDoctor();
        if (!$doctor) { $this->redirect('/'); return; }

        $patients = Patient::getByDoctorId($doctor['id']);
        $this->render('doctor/patients', compact('patients', 'doctor'));
    }

    public function patientDetail(int $id): void
    {
        $doctor = $this->getDoctor();
        if (!$doctor) { $this->redirect('/'); return; }

        $patient = Patient::getWithUser($id);
        if (!$patient) { $this->redirect('/doctor/patients'); return; }

        $myPatientIds = array_column(Patient::getByDoctorId($doctor['id']), 'id');
        if (!in_array($patient['id'], $myPatientIds)) {
            $_SESSION['_flash']['error'] = 'Patient not found.';
            $this->redirect('/doctor/patients'); return;
        }

        $records = MedicalRecord::getPatientRecordsByDoctor($patient['id'], $doctor['id']);
        $prescriptions = Prescription::getPatientPrescriptions($patient['id']);
        $appointmentHistory = \App\Core\Database::getInstance()->fetchAll(
            "SELECT * FROM appointments WHERE patient_id = ? AND doctor_id = ? ORDER BY appointment_date DESC LIMIT 10",
            [$patient['id'], $doctor['id']]
        );

        $this->render('doctor/patient-detail', compact('patient', 'doctor', 'records', 'prescriptions', 'appointmentHistory'));
    }

    public function appointments(): void
    {
        $doctor = $this->getDoctor();
        if (!$doctor) { $this->redirect('/'); return; }

        $appointments = Appointment::getDoctorAppointments($doctor['id']);
        $this->render('doctor/appointments', compact('appointments', 'doctor'));
    }

    public function completeAppointment(int $id): void
    {
        $doctor = $this->getDoctor();
        if (!$doctor) { $this->redirect('/'); return; }
        if (!$this->isPost()) { $this->redirect('/doctor/appointments'); return; }

        $apt = Appointment::find($id);
        if (!$apt || $apt['doctor_id'] !== $doctor['id']) {
            $_SESSION['_flash']['error'] = 'Appointment not found.';
            $this->redirect('/doctor/appointments'); return;
        }

        AppointmentService::complete($id);
        $_SESSION['_flash']['success'] = 'Appointment marked as completed.';
        $this->redirect('/doctor/appointments');
    }

    public function cancelAppointment(int $id): void
    {
        $doctor = $this->getDoctor();
        if (!$doctor) { $this->redirect('/'); return; }
        if (!$this->isPost()) { $this->redirect('/doctor/appointments'); return; }

        $body = $this->getBody();
        $apt = Appointment::find($id);
        if (!$apt || $apt['doctor_id'] !== $doctor['id']) {
            $_SESSION['_flash']['error'] = 'Appointment not found.';
            $this->redirect('/doctor/appointments'); return;
        }

        AppointmentService::cancel($id, $body['reason'] ?? 'Cancelled by doctor');
        $_SESSION['_flash']['success'] = 'Appointment cancelled.';
        $this->redirect('/doctor/appointments');
    }

    public function prescriptions(): void
    {
        $doctor = $this->getDoctor();
        if (!$doctor) { $this->redirect('/'); return; }

        $db = Database::getInstance();
        $prescriptions = $db->fetchAll("
            SELECT p.*, u.first_name as patient_first_name, u.last_name as patient_last_name
            FROM prescriptions p
            JOIN patients pt ON p.patient_id = pt.id
            JOIN users u ON pt.user_id = u.id
            WHERE p.doctor_id = ?
            ORDER BY p.created_at DESC
        ", [$doctor['id']]);

        $this->render('doctor/prescriptions', compact('prescriptions', 'doctor'));
    }

    public function prescriptionsForm(?int $patientId = null): void
    {
        $doctor = $this->getDoctor();
        if (!$doctor) { $this->redirect('/'); return; }

        $patients = Patient::getByDoctorId($doctor['id']);
        $this->render('doctor/prescriptions-create', compact('patients', 'doctor', 'patientId'));
    }

    public function recordsForm(?int $patientId = null): void
    {
        $doctor = $this->getDoctor();
        if (!$doctor) { $this->redirect('/'); return; }

        $patients = Patient::getByDoctorId($doctor['id']);
        $this->render('doctor/records-create', compact('patients', 'doctor', 'patientId'));
    }

    public function createPrescription(): void
    {
        $doctor = $this->getDoctor();
        if (!$doctor) { $this->redirect('/'); return; }

        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/doctor/prescriptions/create'); return;
        }

        $patient = Patient::find($body['patient_id']);
        if (!$patient || !in_array($patient['id'], array_column(Patient::getByDoctorId($doctor['id']), 'id'))) {
            $_SESSION['_flash']['error'] = 'Invalid patient.';
            $this->redirect('/doctor/prescriptions/create'); return;
        }

        $id = Prescription::create([
            'patient_id' => $patient['id'],
            'doctor_id' => $doctor['id'],
            'record_id' => $body['record_id'] ?? null,
            'medication_name' => $body['medication_name'],
            'dosage' => $body['dosage'],
            'frequency' => $body['frequency'],
            'duration' => $body['duration'] ?? null,
            'instructions' => $body['instructions'] ?? '',
            'start_date' => $body['start_date'] ?? date('Y-m-d'),
            'end_date' => $body['end_date'] ?? null,
            'is_active' => 1,
        ]);

        if ($id) {
            NotificationService::sendPrescriptionNotification($patient['user_id'], $body['medication_name']);
        }

        $_SESSION['_flash']['success'] = 'Prescription created successfully.';
        $this->redirect('/doctor/patients/' . $patient['id']);
    }

    public function createReport(): void
    {
        $doctor = $this->getDoctor();
        if (!$doctor) { $this->redirect('/'); return; }

        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/doctor/records/create'); return;
        }

        $patient = Patient::find($body['patient_id']);
        if (!$patient || !in_array($patient['id'], array_column(Patient::getByDoctorId($doctor['id']), 'id'))) {
            $_SESSION['_flash']['error'] = 'Invalid patient.';
            $this->redirect('/doctor/records/create'); return;
        }

        MedicalRecord::create([
            'patient_id' => $patient['id'],
            'doctor_id' => $doctor['id'],
            'diagnosis' => $body['diagnosis'] ?? '',
            'symptoms' => $body['symptoms'] ?? '',
            'notes' => $body['notes'] ?? '',
            'record_date' => $body['record_date'] ?? date('Y-m-d'),
            'is_private' => !empty($body['is_private']) ? 1 : 0,
        ]);

        NotificationService::send($patient['user_id'], 'medical_record', 'New Medical Record', 'A new medical record has been added by your doctor.', '/patient/records');

        $_SESSION['_flash']['success'] = 'Medical record created successfully.';
        $this->redirect('/doctor/patients/' . $patient['id']);
    }

    public function profile(): void
    {
        $doctor = $this->getDoctor();
        $user = Auth::user();

        if (!$doctor) { $this->redirect('/'); return; }

        if ($this->isPost()) {
            $body = $this->getBody();
            if (!verify_csrf($body['_token'] ?? '')) {
                $_SESSION['_flash']['error'] = 'Invalid request.';
                $this->redirect('/doctor/profile'); return;
            }

            $db = \App\Core\Database::getInstance();
            $action = $body['_action'] ?? '';

            if ($action === 'change_password') {
                if (empty($body['current_password']) || empty($body['new_password'])) {
                    $_SESSION['_flash']['error'] = 'All password fields are required.';
                    $this->redirect('/doctor/profile'); return;
                }
                if (!Auth::verifyPassword($body['current_password'], $user['password'])) {
                    $_SESSION['_flash']['error'] = 'Current password is incorrect.';
                    $this->redirect('/doctor/profile'); return;
                }
                if ($body['new_password'] !== ($body['confirm_password'] ?? '')) {
                    $_SESSION['_flash']['error'] = 'Passwords do not match.';
                    $this->redirect('/doctor/profile'); return;
                }
                if (strlen($body['new_password']) < 6) {
                    $_SESSION['_flash']['error'] = 'Password must be at least 6 characters.';
                    $this->redirect('/doctor/profile'); return;
                }
                $db->execute("UPDATE users SET password = ? WHERE id = ?",
                    [Auth::hashPassword($body['new_password']), $user['id']]);
                $_SESSION['_flash']['success'] = 'Password updated successfully.';
                $this->redirect('/doctor/profile');
                return;
            }

            $db->execute(
                "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ?",
                [$body['first_name'], $body['last_name'], $body['email'], $body['phone'] ?? '', $user['id']]
            );

            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $path = FileService::upload($_FILES['avatar'], 'uploads/avatars');
                if ($path) {
                    if (!empty($user['avatar'])) {
                        FileService::delete($user['avatar']);
                    }
                    $db->execute("UPDATE users SET avatar = ? WHERE id = ?", [$path, $user['id']]);
                }
            }

            $db->execute(
                "UPDATE doctors SET license_number = ?, bio = ?, education = ?, experience_years = ? WHERE id = ?",
                [
                    $body['license_number'] ?? $doctor['license_number'],
                    $body['bio'] ?? $doctor['bio'],
                    $body['education'] ?? $doctor['education'],
                    $body['experience_years'] ?? $doctor['experience_years'],
                    $doctor['id']
                ]
            );

            $_SESSION['_flash']['success'] = 'Profile updated successfully.';
            $this->redirect('/doctor/profile');
            return;
        }

        $this->render('doctor/profile', compact('doctor', 'user'));
    }

    // --- Doctor Messages ---
    public function messages(): void
    {
        $user = Auth::user();
        $doctor = $this->getDoctor();
        if (!$doctor) { $this->redirect('/'); return; }

        $db = Database::getInstance();
        $conversations = $db->fetchAll("
            SELECT m.*, u.first_name, u.last_name, u.avatar, u.id as partner_id,
                   (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
            FROM messages m
            JOIN users u ON (CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END) = u.id
            WHERE m.id IN (
                SELECT MAX(m2.id) FROM messages m2
                WHERE (m2.sender_id = ? AND m2.receiver_id IN (SELECT user_id FROM patients))
                   OR (m2.receiver_id = ? AND m2.sender_id IN (SELECT user_id FROM patients))
                GROUP BY CASE WHEN m2.sender_id = ? THEN m2.receiver_id ELSE m2.sender_id END
            )
            ORDER BY m.created_at DESC
        ", [$user['id'], $user['id'], $user['id'], $user['id'], $user['id']]);
        $patients = $this->getPatients();
        $this->render('doctor/messages', compact('conversations', 'patients'));
    }

    public function conversation(int $patientUserId): void
    {
        $user = Auth::user();
        $doctor = $this->getDoctor();
        if (!$doctor) { $this->redirect('/'); return; }

        $patientUsers = array_column($this->getPatients(), 'user_id');
        if (!in_array($patientUserId, $patientUsers)) {
            $_SESSION['_flash']['error'] = 'Patient not found.';
            $this->redirect('/doctor/messages'); return;
        }

        $messages = Message::getConversation($user['id'], $patientUserId);
        $db = Database::getInstance();
        $db->execute("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?", [$patientUserId, $user['id']]);
        $patientUser = \App\Models\User::find($patientUserId);
        $patients = $this->getPatients();
        $this->render('doctor/messages-conversation', compact('messages', 'patientUser', 'patientUserId', 'patients'));
    }

    public function sendMessage(): void
    {
        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/doctor/messages'); return;
        }

        $receiverId = (int)($body['receiver_id'] ?? 0);
        $patientUsers = array_column($this->getPatients(), 'user_id');
        if (!$receiverId || !in_array($receiverId, $patientUsers)) {
            $_SESSION['_flash']['error'] = 'Invalid recipient.';
            $this->redirect('/doctor/messages'); return;
        }

        Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $receiverId,
            'subject' => $body['subject'] ?? '',
            'body' => $body['body'],
        ]);
        $_SESSION['_flash']['success'] = 'Message sent.';
        $this->redirect('/doctor/messages/conversation/' . $receiverId);
    }

    private function getPatients(): array
    {
        $doctor = $this->getDoctor();
        if (!$doctor) return [];
        return \App\Core\Database::getInstance()->fetchAll("
            SELECT p.id as patient_id, u.id as user_id, u.first_name, u.last_name
            FROM patients p JOIN users u ON p.user_id = u.id
            WHERE p.id IN (SELECT DISTINCT patient_id FROM appointments WHERE doctor_id = ?)
            ORDER BY u.first_name
        ", [$doctor['id']]);
    }

    // --- Doctor Notifications ---
    public function notifications(): void
    {
        $user = Auth::user();
        $notifs = Notification::getForUser($user['id'], 50);
        $this->render('doctor/notifications', compact('notifs'));
    }

    public function markAllNotificationsRead(): void
    {
        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/doctor/notifications'); return;
        }
        Notification::markAllAsRead(Auth::id());
        $_SESSION['_flash']['success'] = 'All notifications marked as read.';
        $this->redirect('/doctor/notifications');
    }

    // --- Doctor Availability ---
    public function availability(): void
    {
        $doctor = $this->getDoctor();
        $days = json_decode($doctor['available_days'] ?? '[]', true) ?: [];
        $hours = json_decode($doctor['available_hours'] ?? '[]', true) ?: [];
        $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $this->render('doctor/availability', compact('doctor', 'days', 'hours', 'weekDays'));
    }

    public function saveAvailability(): void
    {
        $doctor = $this->getDoctor();
        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/doctor/availability'); return;
        }

        $selectedDays = $body['days'] ?? [];
        $hoursData = $body['hours'] ?? [];
        Doctor::update($doctor['id'], [
            'available_days' => json_encode($selectedDays),
            'available_hours' => json_encode($hoursData),
        ]);
        $_SESSION['_flash']['success'] = 'Availability updated.';
        $this->redirect('/doctor/availability');
    }

    public function updateAppointment(int $id): void
    {
        $doctor = $this->getDoctor();
        if (!$doctor) { $this->redirect('/'); return; }

        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/doctor/appointments'); return;
        }

        $apt = Appointment::find($id);
        if (!$apt || $apt['doctor_id'] !== $doctor['id']) {
            $_SESSION['_flash']['error'] = 'Appointment not found.';
            $this->redirect('/doctor/appointments'); return;
        }

        $action = $body['action'] ?? '';
        if ($action === 'complete') AppointmentService::complete($id);
        elseif ($action === 'cancel') AppointmentService::cancel($id, $body['reason'] ?? '');

        $_SESSION['_flash']['success'] = 'Appointment updated successfully.';
        $this->redirect('/doctor/appointments');
    }
}
