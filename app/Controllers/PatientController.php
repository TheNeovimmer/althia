<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Specialization;
use App\Services\AppointmentService;
use App\Services\FileService;
use App\Services\NotificationService;

class PatientController extends Controller
{
    private function getPatient(): ?array
    {
        $user = Auth::user();
        return Patient::findByUserId($user['id']);
    }

    public function dashboard(): void
    {
        $patient = $this->getPatient();
        $user = Auth::user();
        if (!$patient) {
            $this->redirect('/');
            return;
        }

        $upcomingAppointments = Appointment::getUpcomingForPatient($patient['id']);
        $records = MedicalRecord::getPatientRecords($patient['id']);
        $recentPrescriptions = Prescription::getActiveForPatient($patient['id']);
        $doctors = Doctor::getVerified();

        $db = Database::getInstance();
        $allAppointments = $db->fetchAll(
            "SELECT status, COUNT(*) as c FROM appointments WHERE patient_id = ? GROUP BY status",
            [$patient['id']]
        );
        $statusMap = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
        foreach ($allAppointments as $a) { $statusMap[$a['status']] = (int)$a['c']; }

        $appointmentHistory = [];
        $daysHistLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $daysHistLabels[] = date('D', strtotime($date));
            $count = (int) ($db->fetch(
                "SELECT COUNT(*) as c FROM appointments WHERE patient_id = ? AND DATE(appointment_date) = ?",
                [$patient['id'], $date]
            )['c'] ?? 0);
            $appointmentHistory[] = $count;
        }

        $chartData = [
            'statusLabels' => json_encode(['Pending', 'Confirmed', 'Completed', 'Cancelled']),
            'statusData' => json_encode(array_values($statusMap)),
            'statusColors' => json_encode(['#ffc107', '#0d6efd', '#198754', '#dc3545']),
            'weekLabels' => json_encode($daysHistLabels),
            'weekData' => json_encode($appointmentHistory),
        ];

        $this->render('patient/dashboard', compact(
            'patient', 'user', 'upcomingAppointments', 'records', 'recentPrescriptions', 'doctors', 'chartData'
        ));
    }

    public function profile(): void
    {
        $patient = $this->getPatient();
        $user = Auth::user();

        if ($this->isPost()) {
            $body = $this->getBody();
            if (!verify_csrf($body['_token'] ?? '')) {
                $_SESSION['_flash']['error'] = 'Invalid request.';
                $this->redirect('/patient/profile'); return;
            }

            $db = Database::getInstance();
            $action = $body['_action'] ?? '';

            if ($action === 'change_password') {
                if (empty($body['current_password']) || empty($body['new_password'])) {
                    $_SESSION['_flash']['error'] = 'All password fields are required.';
                    $this->redirect('/patient/profile'); return;
                }
                if (!Auth::verifyPassword($body['current_password'], $user['password'])) {
                    $_SESSION['_flash']['error'] = 'Current password is incorrect.';
                    $this->redirect('/patient/profile'); return;
                }
                if ($body['new_password'] !== ($body['confirm_password'] ?? '')) {
                    $_SESSION['_flash']['error'] = 'Passwords do not match.';
                    $this->redirect('/patient/profile'); return;
                }
                if (strlen($body['new_password']) < 6) {
                    $_SESSION['_flash']['error'] = 'Password must be at least 6 characters.';
                    $this->redirect('/patient/profile'); return;
                }
                $db->execute("UPDATE users SET password = ? WHERE id = ?",
                    [Auth::hashPassword($body['new_password']), $user['id']]);
                $_SESSION['_flash']['success'] = 'Password updated successfully.';
                $this->redirect('/patient/profile');
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
                "UPDATE patients SET date_of_birth = ?, gender = ?, blood_type = ?, weight = ?, height = ?, address = ?, emergency_contact_name = ?, emergency_contact_phone = ? WHERE id = ?",
                [$body['date_of_birth'], $body['gender'], $body['blood_type'], $body['weight'] ?? null, $body['height'] ?? null, $body['address'], $body['emergency_contact_name'], $body['emergency_contact_phone'], $patient['id']]
            );

            $_SESSION['_flash']['success'] = 'Profile updated successfully.';
            $this->redirect('/patient/profile');
            return;
        }

        $this->render('patient/profile', compact('patient', 'user'));
    }

    public function records(): void
    {
        $patient = $this->getPatient();
        if (!$patient) { $this->redirect('/'); return; }

        $records = MedicalRecord::getPatientRecords($patient['id']);
        $this->render('patient/records', compact('records', 'patient'));
    }

    public function uploadReport(): void
    {
        $patient = $this->getPatient();
        if (!$patient) { $this->redirect('/'); return; }

        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/patient/records'); return;
        }

        if (isset($_FILES['report']) && $_FILES['report']['error'] === UPLOAD_ERR_OK) {
            $path = FileService::upload($_FILES['report'], 'uploads/reports');
            if ($path) {
                $db = Database::getInstance();
                $db->insert(
                    "INSERT INTO medical_reports (patient_id, title, type, file_path, file_type, file_size, notes, report_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [$patient['id'], $body['title'] ?? 'Medical Report', $body['type'] ?? 'other', $path, pathinfo($_FILES['report']['name'], PATHINFO_EXTENSION), $_FILES['report']['size'], $body['notes'] ?? '', date('Y-m-d')]
                );
                $_SESSION['_flash']['success'] = 'Report uploaded successfully.';
            } else {
                $_SESSION['_flash']['error'] = 'File upload failed. Please check file type and size.';
            }
        }

        $this->redirect('/patient/records');
    }

    public function appointments(): void
    {
        $patient = $this->getPatient();
        if (!$patient) { $this->redirect('/'); return; }

        $appointments = Appointment::getPatientAppointments($patient['id']);
        $doctors = Doctor::getVerified();

        $this->render('patient/appointments', compact('appointments', 'doctors', 'patient'));
    }

    public function appointmentsForm(): void
    {
        $patient = $this->getPatient();
        if (!$patient) { $this->redirect('/'); return; }

        $specializations = Specialization::getAll();
        $doctors = Doctor::getVerified();
        $this->render('patient/appointments-create', compact('doctors', 'patient', 'specializations'));
    }

    public function createAppointment(): void
    {
        $patient = $this->getPatient();
        if (!$patient) { $this->redirect('/'); return; }

        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/patient/appointments/create'); return;
        }

        $result = AppointmentService::book([
            'patient_id' => $patient['id'],
            'doctor_id' => $body['doctor_id'],
            'appointment_date' => $body['appointment_date'],
            'appointment_time' => $body['appointment_time'],
            'type' => $body['type'] ?? 'in-person',
            'reason' => $body['reason'] ?? '',
        ]);

        if ($result['success']) {
            $_SESSION['_flash']['success'] = 'Appointment booked successfully!';
        } else {
            $_SESSION['_flash']['error'] = implode('<br>', $result['errors']);
        }

        $this->redirect('/patient/appointments');
    }

    public function cancelAppointment(int $id): void
    {
        $patient = $this->getPatient();
        if (!$patient) { $this->redirect('/'); return; }

        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/patient/appointments'); return;
        }

        $apt = Appointment::find($id);
        if (!$apt || $apt['patient_id'] !== $patient['id']) {
            $_SESSION['_flash']['error'] = 'Appointment not found.';
            $this->redirect('/patient/appointments'); return;
        }

        AppointmentService::cancel($id, $body['reason'] ?? '');
        $_SESSION['_flash']['success'] = 'Appointment cancelled.';
        $this->redirect('/patient/appointments');
    }

    public function prescriptions(): void
    {
        $patient = $this->getPatient();
        if (!$patient) { $this->redirect('/'); return; }

        $prescriptions = Prescription::getPatientPrescriptions($patient['id']);
        $this->render('patient/prescriptions', compact('prescriptions', 'patient'));
    }

    // --- Patient Messages ---
    public function messages(): void
    {
        $user = Auth::user();
        $db = Database::getInstance();
        $conversations = $db->fetchAll("
            SELECT m.*, u.first_name, u.last_name, u.avatar, u.id as partner_id,
                   (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
            FROM messages m
            JOIN users u ON (CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END) = u.id
            WHERE m.id IN (
                SELECT MAX(m2.id) FROM messages m2
                WHERE (m2.sender_id = ? AND m2.receiver_id IN (SELECT user_id FROM doctors))
                   OR (m2.receiver_id = ? AND m2.sender_id IN (SELECT user_id FROM doctors))
                GROUP BY CASE WHEN m2.sender_id = ? THEN m2.receiver_id ELSE m2.sender_id END
            )
            ORDER BY m.created_at DESC
        ", [$user['id'], $user['id'], $user['id'], $user['id'], $user['id']]);
        $doctors = Doctor::getVerified();
        $this->render('patient/messages', compact('conversations', 'doctors'));
    }

    public function conversation(int $doctorUserId): void
    {
        $user = Auth::user();
        $messages = Message::getConversation($user['id'], $doctorUserId);
        $db = Database::getInstance();
        $db->execute("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?", [$doctorUserId, $user['id']]);
        $doctorUser = \App\Models\User::find($doctorUserId);
        $doctors = Doctor::getVerified();
        $this->render('patient/messages-conversation', compact('messages', 'doctorUser', 'doctorUserId', 'doctors'));
    }

    public function sendMessage(): void
    {
        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/patient/messages'); return;
        }

        $receiverId = (int)($body['receiver_id'] ?? 0);
        $validDoctors = array_column(Doctor::getVerified(), 'user_id');
        if (!$receiverId || !in_array($receiverId, $validDoctors)) {
            $_SESSION['_flash']['error'] = 'Invalid recipient.';
            $this->redirect('/patient/messages'); return;
        }

        Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $receiverId,
            'subject' => $body['subject'] ?? 'Message from patient',
            'body' => $body['body'],
        ]);
        $_SESSION['_flash']['success'] = 'Message sent.';
        $this->redirect('/patient/messages');
    }

    // --- Patient Notifications ---
    public function notifications(): void
    {
        $user = Auth::user();
        $notifs = Notification::getForUser($user['id'], 50);
        $this->render('patient/notifications', compact('notifs'));
    }

    public function markAllNotificationsRead(): void
    {
        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/patient/notifications'); return;
        }
        Notification::markAllAsRead(Auth::id());
        $_SESSION['_flash']['success'] = 'All notifications marked as read.';
        $this->redirect('/patient/notifications');
    }
}
