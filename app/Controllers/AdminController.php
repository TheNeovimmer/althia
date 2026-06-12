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
use App\Models\BlogCategory;
use App\Models\Contact;
use App\Models\Specialization;
use App\Models\Setting;
use App\Models\RagDocument;
use App\Services\AppointmentService;
use App\Services\FileService;

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
            if (!verify_csrf($body['_token'] ?? '')) {
                $_SESSION['_flash']['error'] = 'Invalid request.';
                $this->redirect('/admin/users'); return;
            }

            $action = $body['_action'] ?? '';

            if ($action === 'create') {
                if (empty($body['email']) || empty($body['password']) || empty($body['first_name']) || empty($body['last_name'])) {
                    $_SESSION['_flash']['error'] = 'All required fields must be filled.';
                    $this->redirect('/admin/users'); return;
                }
                $db = Database::getInstance();
                try {
                    $db->beginTransaction();
                    $userId = User::create([
                        'email' => $body['email'],
                        'password' => Auth::hashPassword($body['password']),
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
                    $_SESSION['_flash']['error'] = 'Failed to create user.';
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
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/users'); return;
        }

        if (empty($body['first_name']) || empty($body['last_name']) || empty($body['email'])) {
            $_SESSION['_flash']['error'] = 'Required fields cannot be empty.';
            $this->redirect('/admin/users'); return;
        }

        $updateData = [
            'first_name' => $body['first_name'],
            'last_name' => $body['last_name'],
            'email' => $body['email'],
            'phone' => $body['phone'] ?? '',
            'role' => $body['role'],
        ];

        if (!empty($body['password'])) {
            if (strlen($body['password']) < 6) {
                $_SESSION['_flash']['error'] = 'Password must be at least 6 characters.';
                $this->redirect('/admin/users'); return;
            }
            $updateData['password'] = Auth::hashPassword($body['password']);
        }

        User::update($id, $updateData);
        $_SESSION['_flash']['success'] = 'User updated successfully.';
        $this->redirect('/admin/users');
    }

    public function deleteUser(int $id): void
    {
        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/users'); return;
        }

        if ((int)$id === Auth::id()) {
            $_SESSION['_flash']['error'] = 'You cannot delete your own account.';
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
                if (!verify_csrf($body['_token'] ?? '')) {
                    $_SESSION['_flash']['error'] = 'Invalid request.';
                    $this->redirect('/admin/doctors'); return;
                }
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
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/doctors/create'); return;
        }

        if (empty($body['email']) || empty($body['password']) || empty($body['first_name']) || empty($body['last_name'])) {
            $_SESSION['_flash']['error'] = 'All required fields must be filled.';
            $this->redirect('/admin/doctors/create'); return;
        }

        if ($body['password'] !== $body['password_confirm']) {
            $_SESSION['_flash']['error'] = 'Passwords do not match.';
            $this->redirect('/admin/doctors/create');
            return;
        }

        if (strlen($body['password']) < 6) {
            $_SESSION['_flash']['error'] = 'Password must be at least 6 characters.';
            $this->redirect('/admin/doctors/create'); return;
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

            $avatarPath = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $avatarPath = \App\Services\FileService::upload($_FILES['avatar'], 'uploads/avatars');
                if ($avatarPath) {
                    $db->execute("UPDATE users SET avatar = ? WHERE id = ?", [$avatarPath, $userId]);
                }
            }

            $db->insert("INSERT INTO doctors (user_id, specialization_id, license_number, bio) VALUES (?, ?, ?, ?)", [
                $userId, $body['specialization_id'], $body['license_number'] ?? null, $body['bio'] ?? null
            ]);
            $db->commit();
            $_SESSION['_flash']['success'] = 'Doctor created successfully.';
        } catch (\Exception $e) {
            $db->rollback();
            $_SESSION['_flash']['error'] = 'Failed to create doctor.';
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
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/doctors'); return;
        }

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

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatarPath = \App\Services\FileService::upload($_FILES['avatar'], 'uploads/avatars');
            if ($avatarPath) {
                $oldUser = $db->fetch("SELECT avatar FROM users WHERE id = ?", [$doctor['user_id']]);
                if (!empty($oldUser['avatar'])) {
                    \App\Services\FileService::delete($oldUser['avatar']);
                }
                $db->execute("UPDATE users SET avatar = ? WHERE id = ?", [$avatarPath, $doctor['user_id']]);
            }
        }

        $_SESSION['_flash']['success'] = 'Doctor updated successfully.';
        $this->redirect('/admin/doctors');
    }

    public function toggleDoctorStatus(int $id): void
    {
        if (!$this->isPost()) { $this->redirect('/admin/doctors'); return; }
        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/doctors'); return;
        }

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
        if (!$this->isPost()) { $this->redirect('/admin/users'); return; }
        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/users'); return;
        }

        if ((int)$id === Auth::id()) {
            $_SESSION['_flash']['error'] = 'You cannot deactivate your own account.';
            $this->redirect('/admin/users'); return;
        }

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
        if (!$this->isPost()) { $this->redirect('/admin/doctors'); return; }
        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/doctors'); return;
        }
        Doctor::update($id, ['is_verified' => 1]);
        $_SESSION['_flash']['success'] = 'Doctor verified successfully.';
        $this->redirect('/admin/doctors');
    }

    public function profile(): void
    {
        $user = Auth::user();

        if ($this->isPost()) {
            $body = $this->getBody();
            if (!verify_csrf($body['_token'] ?? '')) {
                $_SESSION['_flash']['error'] = 'Invalid request.';
                $this->redirect('/admin/profile'); return;
            }

            $db = Database::getInstance();
            $action = $body['_action'] ?? '';

            if ($action === 'change_password') {
                if (empty($body['current_password']) || empty($body['new_password'])) {
                    $_SESSION['_flash']['error'] = 'All password fields are required.';
                    $this->redirect('/admin/profile'); return;
                }
                if (!Auth::verifyPassword($body['current_password'], $user['password'])) {
                    $_SESSION['_flash']['error'] = 'Current password is incorrect.';
                    $this->redirect('/admin/profile'); return;
                }
                if ($body['new_password'] !== ($body['confirm_password'] ?? '')) {
                    $_SESSION['_flash']['error'] = 'Passwords do not match.';
                    $this->redirect('/admin/profile'); return;
                }
                if (strlen($body['new_password']) < 6) {
                    $_SESSION['_flash']['error'] = 'Password must be at least 6 characters.';
                    $this->redirect('/admin/profile'); return;
                }
                $db->execute("UPDATE users SET password = ? WHERE id = ?",
                    [Auth::hashPassword($body['new_password']), $user['id']]);
                $_SESSION['_flash']['success'] = 'Password updated successfully.';
                $this->redirect('/admin/profile');
                return;
            }

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

            $_SESSION['_flash']['success'] = 'Profile updated successfully.';
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
                       du.first_name as doctor_first_name, du.last_name as doctor_last_name,
                       s.name as specialization_name
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                JOIN users pu ON p.user_id = pu.id
                JOIN doctors d ON a.doctor_id = d.id
                JOIN users du ON d.user_id = du.id
                LEFT JOIN specializations s ON d.specialization_id = s.id";
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
        if (!$this->isPost()) { $this->redirect('/admin/appointments'); return; }
        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/appointments'); return;
        }
        $apt = Appointment::find($id);
        if (!$apt) { $_SESSION['_flash']['error'] = 'Appointment not found.'; $this->redirect('/admin/appointments'); return; }
        AppointmentService::confirm($id);
        $_SESSION['_flash']['success'] = 'Appointment confirmed.';
        $this->redirect('/admin/appointments');
    }

    public function cancelAppointment(int $id): void
    {
        if (!$this->isPost()) { $this->redirect('/admin/appointments'); return; }
        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/appointments'); return;
        }
        $apt = Appointment::find($id);
        if (!$apt) { $_SESSION['_flash']['error'] = 'Appointment not found.'; $this->redirect('/admin/appointments'); return; }
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
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/blog'); return;
        }

        if (empty($body['title']) || empty($body['content'])) {
            $_SESSION['_flash']['error'] = 'Title and content are required.';
            $this->redirect('/admin/blog/create'); return;
        }

        $slug = strtolower(str_replace(' ', '-', $body['title']));
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        if (empty($slug)) $slug = 'post-' . time();

        $db = Database::getInstance();
        $existing = $db->fetch("SELECT id FROM blog_posts WHERE slug = ?", [$slug]);
        if ($existing) {
            $slug .= '-' . time();
        }

        $tags = !empty($body['tags']) ? json_encode(array_map('trim', explode(',', $body['tags']))) : '[]';

        $db->insert(
            "INSERT INTO blog_posts (author_id, category_id, title, slug, excerpt, content, tags, is_published, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [Auth::id(), !empty($body['category_id']) ? $body['category_id'] : null, $body['title'], $slug, $body['excerpt'] ?? '', $body['content'] ?? '', $tags, !empty($body['is_published']) ? 1 : 0]
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
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/blog'); return;
        }

        $slug = strtolower(str_replace(' ', '-', $body['title']));
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        if (empty($slug)) $slug = 'post-' . $id;

        $db = Database::getInstance();
        $existing = $db->fetch("SELECT id FROM blog_posts WHERE slug = ? AND id != ?", [$slug, $id]);
        if ($existing) {
            $slug .= '-' . time();
        }

        $tags = !empty($body['tags']) ? json_encode(array_map('trim', explode(',', $body['tags']))) : '[]';

        $db->execute(
            "UPDATE blog_posts SET category_id = ?, title = ?, slug = ?, excerpt = ?, content = ?, tags = ?, is_published = ? WHERE id = ?",
            [!empty($body['category_id']) ? $body['category_id'] : null, $body['title'], $slug, $body['excerpt'] ?? '', $body['content'] ?? '', $tags, !empty($body['is_published']) ? 1 : 0, $id]
        );
        $_SESSION['_flash']['success'] = 'Blog post updated.';
        $this->redirect('/admin/blog');
    }

    public function deleteBlogPost(int $id): void
    {
        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/blog'); return;
        }
        $post = BlogPost::find($id);
        if (!$post) { $_SESSION['_flash']['error'] = 'Post not found.'; $this->redirect('/admin/blog'); return; }
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
            if (!verify_csrf($body['_token'] ?? '')) {
                $_SESSION['_flash']['error'] = 'Invalid request.';
                $this->redirect('/admin/blog/categories'); return;
            }

            if (empty($body['name'])) {
                $_SESSION['_flash']['error'] = 'Category name is required.';
                $this->redirect('/admin/blog/categories'); return;
            }

            $slug = strtolower(str_replace(' ', '-', $body['name']));
            $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
            if (empty($slug)) $slug = 'cat-' . time();

            $existing = $db->fetch("SELECT id FROM blog_categories WHERE slug = ?", [$slug]);
            if ($existing) {
                $_SESSION['_flash']['error'] = 'Category already exists.';
                $this->redirect('/admin/blog/categories'); return;
            }

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
        if (!$this->isPost()) { $this->redirect('/admin/contacts'); return; }
        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/contacts'); return;
        }
        $db = Database::getInstance();
        $db->execute("UPDATE contacts SET is_read = 1 WHERE id = ?", [$id]);
        $this->redirect('/admin/contacts');
    }

    public function deleteContact(int $id): void
    {
        if (!$this->isPost()) { $this->redirect('/admin/contacts'); return; }
        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/contacts'); return;
        }
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
        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/notifications'); return;
        }
        Notification::markAllAsRead(Auth::id());
        $_SESSION['_flash']['success'] = 'All notifications marked as read.';
        $this->redirect('/admin/notifications');
    }

    // --- Admin Specializations CRUD ---
    public function specializations(): void
    {
        $db = Database::getInstance();
        if ($this->isPost()) {
            $body = $this->getBody();
            if (!verify_csrf($body['_token'] ?? '')) {
                $_SESSION['_flash']['error'] = 'Invalid request.';
                $this->redirect('/admin/specializations'); return;
            }
            $action = $body['_action'] ?? '';

            if ($action === 'create') {
                $name = trim($body['name'] ?? '');
                $desc = trim($body['description'] ?? '');
                if (empty($name)) {
                    $_SESSION['_flash']['error'] = 'Specialization name is required.';
                } else {
                    $existing = $db->fetch("SELECT id FROM specializations WHERE name = ?", [$name]);
                    if ($existing) {
                        $_SESSION['_flash']['error'] = 'Specialization already exists.';
                    } else {
                        $db->insert("INSERT INTO specializations (name, description) VALUES (?, ?)", [$name, $desc]);
                        $_SESSION['_flash']['success'] = 'Specialization created.';
                    }
                }
            } elseif ($action === 'update') {
                $id = (int)($body['id'] ?? 0);
                $name = trim($body['name'] ?? '');
                $desc = trim($body['description'] ?? '');
                if ($id && !empty($name)) {
                    $existing = $db->fetch("SELECT id FROM specializations WHERE name = ? AND id != ?", [$name, $id]);
                    if ($existing) {
                        $_SESSION['_flash']['error'] = 'Another specialization with this name already exists.';
                    } else {
                        $db->execute("UPDATE specializations SET name = ?, description = ? WHERE id = ?", [$name, $desc, $id]);
                        $_SESSION['_flash']['success'] = 'Specialization updated.';
                    }
                } else {
                    $_SESSION['_flash']['error'] = 'Name is required.';
                }
            } elseif ($action === 'delete') {
                $id = (int)($body['id'] ?? 0);
                if ($id) {
                    $assocDoctors = $db->fetch("SELECT COUNT(*) as c FROM doctors WHERE specialization_id = ?", [$id])['c'] ?? 0;
                    if ($assocDoctors > 0) {
                        $_SESSION['_flash']['error'] = "Cannot delete: $assocDoctors doctor(s) use this specialization.";
                    } else {
                        $db->execute("DELETE FROM specializations WHERE id = ?", [$id]);
                        $_SESSION['_flash']['success'] = 'Specialization deleted.';
                    }
                }
            }

            $this->redirect('/admin/specializations');
            return;
        }

        $specializations = Specialization::getAll();
        $this->render('admin/specializations', compact('specializations'));
    }

    // --- Admin AI Settings ---
    public function aiSettings(): void
    {
        if ($this->isPost()) {
            $body = $this->getBody();
            if (!verify_csrf($body['_token'] ?? '')) {
                $_SESSION['_flash']['error'] = 'Invalid request.';
                $this->redirect('/admin/ai-settings'); return;
            }

            Setting::set('openrouter_api_key', trim($body['openrouter_api_key'] ?? ''));
            Setting::set('openrouter_model', trim($body['openrouter_model'] ?? 'openai/gpt-oss-120b:free'));
            Setting::set('rag_enabled', isset($body['rag_enabled']) ? '1' : '0');
            Setting::set('rag_chunk_size', trim($body['rag_chunk_size'] ?? '500'));

            $_SESSION['_flash']['success'] = 'AI settings saved.';
            $this->redirect('/admin/ai-settings');
            return;
        }

        $apiKey = Setting::get('openrouter_api_key');
        $model = Setting::get('openrouter_model', 'openai/gpt-oss-120b:free');
        $ragEnabled = Setting::get('rag_enabled', '1');
        $ragChunkSize = Setting::get('rag_chunk_size', '500');
        $documents = RagDocument::getActive();

        $this->render('admin/ai-settings', compact('apiKey', 'model', 'ragEnabled', 'ragChunkSize', 'documents'));
    }

    // --- RAG Document CRUD ---
    public function ragDocuments(): void
    {
        $body = $this->getBody();
        if (!verify_csrf($body['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid request.';
            $this->redirect('/admin/ai-settings'); return;
        }
        $action = $body['_action'] ?? '';

        if ($action === 'create') {
            $title = trim($body['title'] ?? '');
            $content = trim($body['content'] ?? '');
            if (empty($title) || empty($content)) {
                $_SESSION['_flash']['error'] = 'Title and content are required.';
            } else {
                RagDocument::create([
                    'title' => $title,
                    'content' => $content,
                    'source' => $body['source'] ?? 'manual',
                    'is_active' => 1,
                ]);
                $_SESSION['_flash']['success'] = 'Document added to RAG knowledge base.';
            }
        } elseif ($action === 'delete') {
            $id = (int)($body['id'] ?? 0);
            if ($id) {
                RagDocument::update($id, ['is_active' => 0]);
                $_SESSION['_flash']['success'] = 'Document removed.';
            }
        }

        $this->redirect('/admin/ai-settings');
    }
}
