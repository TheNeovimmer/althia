<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\MedicalRecord;
use App\Models\Notification;
use App\Models\BlogPost;
use App\Services\AppointmentService;

class AdminController extends Controller
{
    public function dashboard(): void
    {
        $db = Database::getInstance();
        $stats = [
            'totalUsers' => User::count(),
            'totalDoctors' => User::countWhere('role', 'doctor'),
            'totalPatients' => User::countWhere('role', 'patient'),
            'totalAppointments' => Appointment::count(),
            'totalPrescriptions' => Prescription::count(),
            'totalRecords' => MedicalRecord::count(),
            'totalBlogPosts' => (int) ($db->fetch("SELECT COUNT(*) as c FROM blog_posts WHERE is_published = 1")['c'] ?? 0),
        ];

        $recentUsers = User::getRecent(5);
        $upcomingAppointments = $db->fetchAll("
            SELECT a.*, 
                   pu.first_name as patient_first_name, pu.last_name as patient_last_name,
                   pu.avatar as patient_avatar,
                   du.first_name as doctor_first_name, du.last_name as doctor_last_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            JOIN users pu ON p.user_id = pu.id
            JOIN doctors d ON a.doctor_id = d.id
            JOIN users du ON d.user_id = du.id
            WHERE a.appointment_date >= CURDATE()
            ORDER BY a.appointment_date ASC
            LIMIT 5
        ");

        // Chart data: role distribution
        $adminCount = User::countWhere('role', 'admin');
        $doctorCount = User::countWhere('role', 'doctor');
        $patientCount = User::countWhere('role', 'patient');
        $roleChart = [$adminCount, $doctorCount, $patientCount];

        // Chart data: appointments last 7 days
        $appointmentsWeek = [];
        $daysLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $daysLabels[] = date('D', strtotime($date));
            $count = (int) ($db->fetch("SELECT COUNT(*) as c FROM appointments WHERE DATE(appointment_date) = ?", [$date])['c'] ?? 0);
            $appointmentsWeek[] = $count;
        }

        // Chart data: appointments by status
        $pendingCount = (int) ($db->fetch("SELECT COUNT(*) as c FROM appointments WHERE status = 'pending'")['c'] ?? 0);
        $confirmedCount = (int) ($db->fetch("SELECT COUNT(*) as c FROM appointments WHERE status = 'confirmed'")['c'] ?? 0);
        $completedCount = (int) ($db->fetch("SELECT COUNT(*) as c FROM appointments WHERE status = 'completed'")['c'] ?? 0);
        $cancelledCount = (int) ($db->fetch("SELECT COUNT(*) as c FROM appointments WHERE status = 'cancelled'")['c'] ?? 0);

        $chartData = [
            'roleLabels' => json_encode(['Admin', 'Doctors', 'Patients']),
            'roleData' => json_encode($roleChart),
            'roleColors' => json_encode(['#dc3545', '#198754', '#0d6efd']),
            'weekLabels' => json_encode($daysLabels),
            'weekData' => json_encode($appointmentsWeek),
            'statusLabels' => json_encode(['Pending', 'Confirmed', 'Completed', 'Cancelled']),
            'statusData' => json_encode([$pendingCount, $confirmedCount, $completedCount, $cancelledCount]),
            'statusColors' => json_encode(['#ffc107', '#0d6efd', '#198754', '#dc3545']),
        ];

        $this->render('admin/dashboard', compact('stats', 'recentUsers', 'upcomingAppointments', 'chartData'));
    }

    public function users(): void
    {
        $users = User::all();

        if ($this->isPost()) {
            $body = $this->getBody();
            $action = $body['_action'] ?? '';

            if ($action === 'create') {
                $db = Database::getInstance();
                try {
                    $db->beginTransaction();
                    $userId = User::create([
                        'email' => $body['email'],
                        'password' => Auth::hashPassword($body['password'] ?? 'password'),
                        'role' => $body['role'] ?? 'patient',
                        'first_name' => $body['first_name'],
                        'last_name' => $body['last_name'],
                        'phone' => $body['phone'] ?? '',
                        'is_active' => 1,
                    ]);
                    if ($body['role'] === 'patient') {
                        $db->insert("INSERT INTO patients (user_id) VALUES (?)", [$userId]);
                    } elseif ($body['role'] === 'doctor') {
                        $db->insert("INSERT INTO doctors (user_id, specialization_id) VALUES (?, ?)", [$userId, $body['specialization_id'] ?? null]);
                    }
                    $db->commit();
                    $_SESSION['_flash']['success'] = 'User created successfully.';
                } catch (\Exception $e) {
                    $db->rollback();
                    $_SESSION['_errors'] = ['Failed to create user.'];
                }
            }

            $this->redirect('/admin/users');
            return;
        }

        $this->render('admin/users', compact('users'));
    }

    public function createUser(): void
    {
        $this->users();
    }

    public function updateUser(int $id): void
    {
        $body = $this->getBody();

        $updateData = [
            'first_name' => $body['first_name'],
            'last_name' => $body['last_name'],
            'email' => $body['email'],
            'phone' => $body['phone'] ?? '',
            'role' => $body['role'],
        ];

        if (!empty($body['password'])) {
            $updateData['password'] = Auth::hashPassword($body['password']);
        }

        User::update($id, $updateData);
        $_SESSION['_flash']['success'] = 'User updated successfully.';
        $this->redirect('/admin/users');
    }

    public function deleteUser(int $id): void
    {
        if ($id == Auth::id()) {
            $_SESSION['_errors'] = ['You cannot delete your own account.'];
            $this->redirect('/admin/users');
            return;
        }

        User::delete($id);
        $_SESSION['_flash']['success'] = 'User deleted successfully.';
        $this->redirect('/admin/users');
    }

    public function doctors(): void
    {
        $doctors = Doctor::getAllWithUsers();

        if ($this->isPost()) {
            $body = $this->getBody();
            $action = $body['_action'] ?? '';

            if ($action === 'verify' && !empty($body['doctor_id'])) {
                Doctor::update((int)$body['doctor_id'], ['is_verified' => 1]);
                $_SESSION['_flash']['success'] = 'Doctor verified successfully.';
            }

            $this->redirect('/admin/doctors');
            return;
        }

        $this->render('admin/doctors', compact('doctors'));
    }

    public function createDoctorForm(): void
    {
        $db = Database::getInstance();
        $specializations = $db->fetchAll("SELECT * FROM specializations ORDER BY name");
        $this->render('admin/doctors-create', compact('specializations'));
    }

    public function createDoctor(): void
    {
        $body = $this->getBody();

        if ($body['password'] !== $body['password_confirm']) {
            $_SESSION['_errors'] = ['Passwords do not match.'];
            $this->redirect('/admin/doctors/create');
            return;
        }

        $db = Database::getInstance();
        try {
            $db->beginTransaction();
            $userId = User::create([
                'email' => $body['email'],
                'password' => Auth::hashPassword($body['password']),
                'role' => 'doctor',
                'first_name' => $body['first_name'],
                'last_name' => $body['last_name'],
                'is_active' => 1,
            ]);
            $db->insert("INSERT INTO doctors (user_id, specialization_id, license_number, bio) VALUES (?, ?, ?, ?)", [
                $userId, $body['specialization_id'], $body['license_number'] ?? null, $body['bio'] ?? null
            ]);
            $db->commit();
            $_SESSION['_flash']['success'] = 'Doctor created successfully.';
        } catch (\Exception $e) {
            $db->rollback();
            $_SESSION['_errors'] = ['Failed to create doctor.'];
        }

        $this->redirect('/admin/doctors');
    }

    public function editDoctorForm(int $id): void
    {
        $db = Database::getInstance();
        $doctor = $db->fetch("
            SELECT d.*, u.first_name, u.last_name, u.email
            FROM doctors d
            JOIN users u ON d.user_id = u.id
            WHERE d.id = ?
        ", [$id]);

        if (!$doctor) {
            $this->redirect('/admin/doctors');
            return;
        }

        $specializations = $db->fetchAll("SELECT * FROM specializations ORDER BY name");
        $this->render('admin/doctors-edit', compact('doctor', 'specializations'));
    }

    public function updateDoctor(int $id): void
    {
        $body = $this->getBody();

        $db = Database::getInstance();
        $doctor = $db->fetch("SELECT * FROM doctors WHERE id = ?", [$id]);
        if (!$doctor) {
            $this->redirect('/admin/doctors');
            return;
        }

        $db->execute("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?", [
            $body['first_name'], $body['last_name'], $body['email'], $doctor['user_id']
        ]);
        $db->execute("UPDATE doctors SET specialization_id = ?, license_number = ?, bio = ? WHERE id = ?", [
            $body['specialization_id'], $body['license_number'] ?? null, $body['bio'] ?? null, $id
        ]);

        $_SESSION['_flash']['success'] = 'Doctor updated successfully.';
        $this->redirect('/admin/doctors');
    }

    public function toggleDoctorStatus(int $id): void
    {
        $db = Database::getInstance();
        $doctor = $db->fetch("SELECT * FROM doctors WHERE id = ?", [$id]);
        if ($doctor) {
            $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$doctor['user_id']]);
            $newStatus = $user['is_active'] ? 0 : 1;
            $db->execute("UPDATE users SET is_active = ? WHERE id = ?", [$newStatus, $doctor['user_id']]);
            $_SESSION['_flash']['success'] = $newStatus ? 'Doctor activated.' : 'Doctor deactivated.';
        }
        $this->redirect('/admin/doctors');
    }

    public function toggleUserStatus(int $id): void
    {
        $db = Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if ($user) {
            $newStatus = $user['is_active'] ? 0 : 1;
            $db->execute("UPDATE users SET is_active = ? WHERE id = ?", [$newStatus, $id]);
            $_SESSION['_flash']['success'] = $newStatus ? 'User activated.' : 'User deactivated.';
        }
        $this->redirect('/admin/users');
    }

    public function verifyDoctor(int $id): void
    {
        Doctor::update($id, ['is_verified' => 1]);
        $_SESSION['_flash']['success'] = 'Doctor verified successfully.';
        $this->redirect('/admin/doctors');
    }

    public function profile(): void
    {
        $user = Auth::user();

        if ($this->isPost()) {
            $body = $this->getBody();
            $db = Database::getInstance();

            $db->execute(
                "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ?",
                [$body['first_name'], $body['last_name'], $body['email'], $body['phone'] ?? '', $user['id']]
            );

            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $path = \App\Services\FileService::upload($_FILES['avatar'], 'uploads/avatars');
                if ($path) {
                    if (!empty($user['avatar'])) {
                        \App\Services\FileService::delete($user['avatar']);
                    }
                    $db->execute("UPDATE users SET avatar = ? WHERE id = ?", [$path, $user['id']]);
                }
            }

            if (!empty($body['new_password'])) {
                if ($body['new_password'] === ($body['confirm_password'] ?? '')) {
                    $db->execute("UPDATE users SET password = ? WHERE id = ?",
                        [Auth::hashPassword($body['new_password']), $user['id']]);
                    $_SESSION['_flash']['success'] = 'Profile and password updated successfully.';
                } else {
                    $_SESSION['_errors'] = ['Passwords do not match.'];
                    $this->redirect('/admin/profile');
                    return;
                }
            } else {
                $_SESSION['_flash']['success'] = 'Profile updated successfully.';
            }

            $this->redirect('/admin/profile');
            return;
        }

        $this->render('admin/profile', compact('user'));
    }

    // --- Admin Appointments ---
    public function appointments(): void
    {
        $db = Database::getInstance();
        $statusFilter = $_GET['status'] ?? '';
        $sql = "SELECT a.*, 
                       pu.first_name as patient_first_name, pu.last_name as patient_last_name,
                       du.first_name as doctor_first_name, du.last_name as doctor_last_name
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                JOIN users pu ON p.user_id = pu.id
                JOIN doctors d ON a.doctor_id = d.id
                JOIN users du ON d.user_id = du.id";
        $params = [];
        if (!empty($statusFilter) && in_array($statusFilter, ['pending', 'confirmed', 'completed', 'cancelled'])) {
            $sql .= " WHERE a.status = ?";
            $params[] = $statusFilter;
        }
        $sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        $appointments = $db->fetchAll($sql, $params);

        $stats = [
            'total' => Appointment::count(),
            'pending' => Appointment::countWhere('status', 'pending'),
            'confirmed' => Appointment::countWhere('status', 'confirmed'),
            'completed' => Appointment::countWhere('status', 'completed'),
            'cancelled' => Appointment::countWhere('status', 'cancelled'),
        ];

        $this->render('admin/appointments', compact('appointments', 'stats', 'statusFilter'));
    }

    public function confirmAppointment(int $id): void
    {
        AppointmentService::confirm($id);
        $_SESSION['_flash']['success'] = 'Appointment confirmed.';
        $this->redirect('/admin/appointments');
    }

    public function cancelAppointment(int $id): void
    {
        $body = $this->getBody();
        AppointmentService::cancel($id, $body['reason'] ?? 'Cancelled by admin');
        $_SESSION['_flash']['success'] = 'Appointment cancelled.';
        $this->redirect('/admin/appointments');
    }

    // --- Admin Blog ---
    public function blog(): void
    {
        $db = Database::getInstance();
        $posts = $db->fetchAll("
            SELECT bp.*, u.first_name, u.last_name, bc.name as category_name
            FROM blog_posts bp
            JOIN users u ON bp.author_id = u.id
            LEFT JOIN blog_categories bc ON bp.category_id = bc.id
            ORDER BY bp.created_at DESC
        ");
        $categories = $db->fetchAll("SELECT * FROM blog_categories ORDER BY name");
        $this->render('admin/blog', compact('posts', 'categories'));
    }

    public function createBlogForm(): void
    {
        $db = Database::getInstance();
        $categories = $db->fetchAll("SELECT * FROM blog_categories ORDER BY name");
        $this->render('admin/blog-create', compact('categories'));
    }

    public function createBlogPost(): void
    {
        $body = $this->getBody();
        $slug = strtolower(str_replace(' ', '-', $body['title']));
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        $db = Database::getInstance();
        $db->insert(
            "INSERT INTO blog_posts (author_id, category_id, title, slug, excerpt, content, tags, is_published, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [Auth::id(), !empty($body['category_id']) ? $body['category_id'] : null, $body['title'], $slug, $body['excerpt'] ?? '', $body['content'] ?? '', '[]', !empty($body['is_published']) ? 1 : 0]
        );
        $_SESSION['_flash']['success'] = 'Blog post created.';
        $this->redirect('/admin/blog');
    }

    public function editBlogForm(int $id): void
    {
        $db = Database::getInstance();
        $post = BlogPost::find($id);
        if (!$post) { $this->redirect('/admin/blog'); return; }
        $categories = $db->fetchAll("SELECT * FROM blog_categories ORDER BY name");
        $this->render('admin/blog-edit', compact('post', 'categories'));
    }

    public function updateBlogPost(int $id): void
    {
        $body = $this->getBody();
        $slug = strtolower(str_replace(' ', '-', $body['title']));
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        $db = Database::getInstance();
        $db->execute(
            "UPDATE blog_posts SET category_id = ?, title = ?, slug = ?, excerpt = ?, content = ?, is_published = ? WHERE id = ?",
            [!empty($body['category_id']) ? $body['category_id'] : null, $body['title'], $slug, $body['excerpt'] ?? '', $body['content'] ?? '', !empty($body['is_published']) ? 1 : 0, $id]
        );
        $_SESSION['_flash']['success'] = 'Blog post updated.';
        $this->redirect('/admin/blog');
    }

    public function deleteBlogPost(int $id): void
    {
        BlogPost::delete($id);
        $_SESSION['_flash']['success'] = 'Blog post deleted.';
        $this->redirect('/admin/blog');
    }

    public function blogCategories(): void
    {
        $db = Database::getInstance();
        $categories = $db->fetchAll("SELECT * FROM blog_categories ORDER BY name");
        if ($this->isPost()) {
            $body = $this->getBody();
            $slug = strtolower(str_replace(' ', '-', $body['name']));
            $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
            $db->insert("INSERT INTO blog_categories (name, slug) VALUES (?, ?)", [$body['name'], $slug]);
            $_SESSION['_flash']['success'] = 'Category added.';
            $this->redirect('/admin/blog/categories');
        }
        $this->render('admin/blog-categories', compact('categories'));
    }

    // --- Admin Contacts ---
    public function contacts(): void
    {
        $db = Database::getInstance();
        $contacts = $db->fetchAll("SELECT * FROM contacts ORDER BY created_at DESC");
        $unreadCount = (int) $db->fetch("SELECT COUNT(*) as c FROM contacts WHERE is_read = 0")['c'];
        $this->render('admin/contacts', compact('contacts', 'unreadCount'));
    }

    public function markContactRead(int $id): void
    {
        $db = Database::getInstance();
        $db->execute("UPDATE contacts SET is_read = 1 WHERE id = ?", [$id]);
        $this->redirect('/admin/contacts');
    }

    public function deleteContact(int $id): void
    {
        $db = Database::getInstance();
        $db->execute("DELETE FROM contacts WHERE id = ?", [$id]);
        $_SESSION['_flash']['success'] = 'Contact deleted.';
        $this->redirect('/admin/contacts');
    }

    // --- Admin Notifications ---
    public function notifications(): void
    {
        $user = Auth::user();
        $notifs = Notification::getForUser($user['id'], 50);
        $this->render('admin/notifications', compact('notifs'));
    }

    public function markAllNotificationsRead(): void
    {
        Notification::markAllAsRead(Auth::id());
        $_SESSION['_flash']['success'] = 'All notifications marked as read.';
        $this->redirect('/admin/notifications');
    }
}
