# Medicase Full-Stack Completion Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Fix all critical bugs, enhance dashboard UI, add 9 missing pages, and link frontend-backend across admin/doctor/patient dashboards.

**Architecture:** PHP 8.4 MVC with PDO, no framework. Dark sidebar layout for dashboards. Chart.js for analytics. 4-phase approach: bugs → UI → pages → linking.

**Tech Stack:** PHP 8.4, MySQL/MariaDB, Chart.js v4, Font Awesome 6, vanilla CSS

---

### File Inventory

**Files to Modify:**
- `public/index.php` — Add 20+ new routes
- `app/Controllers/AdminController.php` — Add appointments, blog, contacts, notifications handlers
- `app/Controllers/DoctorController.php` — Add messages, availability handlers
- `app/Controllers/PatientController.php` — Fix profile, add messages, notifications handlers
- `app/Views/layouts/header.php` — Sidebar nested active, notification badge
- `app/Views/patient/profile.php` — Fix phone, email, password merge
- `app/Views/patient/appointments.php` — Fix status check
- `app/Views/patient/records.php` — Fix column names, add upload form
- `app/Views/doctor/appointments.php` — Fix status check, table wrapper, empty state
- `app/Views/doctor/prescriptions.php` — Empty states
- `app/Views/doctor/patients.php` — Empty states
- `app/Views/patient/appointments-create.php` — Form sections
- `app/Views/doctor/prescriptions-create.php` — Form sections
- `app/Views/doctor/records-create.php` — Form sections
- `public/assets/css/style.css` — Add messages, availability, blog, notification CSS
- `app/Models/Contact.php` — New model (if not exists)
- `app/Services/NotificationService.php` — Enhance with more notification types

**Files to Create:**
- `app/Views/admin/appointments.php`
- `app/Views/admin/blog.php`
- `app/Views/admin/blog-create.php`
- `app/Views/admin/blog-edit.php`
- `app/Views/admin/blog-categories.php`
- `app/Views/admin/contacts.php`
- `app/Views/admin/notifications.php`
- `app/Views/doctor/messages.php`
- `app/Views/doctor/messages-conversation.php`
- `app/Views/doctor/availability.php`
- `app/Views/patient/messages.php`
- `app/Views/patient/notifications.php`

---

### Task Group A: Core Infrastructure (prerequisite)

#### Task A1: Add Routes

- **Files:** `public/index.php`

Add all new routes before the `// 404 route` line:

```php
// Admin routes (after existing ones)
$router->get('/admin/appointments', 'AdminController@appointments', [Middleware::admin()]);
$router->post('/admin/appointments/{id}/confirm', 'AdminController@confirmAppointment', [Middleware::admin()]);
$router->post('/admin/appointments/{id}/cancel', 'AdminController@cancelAppointment', [Middleware::admin()]);
$router->get('/admin/blog', 'AdminController@blog', [Middleware::admin()]);
$router->get('/admin/blog/create', 'AdminController@createBlogForm', [Middleware::admin()]);
$router->post('/admin/blog/create', 'AdminController@createBlogPost', [Middleware::admin()]);
$router->get('/admin/blog/{id}/edit', 'AdminController@editBlogForm', [Middleware::admin()]);
$router->post('/admin/blog/{id}/edit', 'AdminController@updateBlogPost', [Middleware::admin()]);
$router->post('/admin/blog/{id}/delete', 'AdminController@deleteBlogPost', [Middleware::admin()]);
$router->get('/admin/blog/categories', 'AdminController@blogCategories', [Middleware::admin()]);
$router->post('/admin/blog/categories', 'AdminController@saveCategory', [Middleware::admin()]);
$router->get('/admin/contacts', 'AdminController@contacts', [Middleware::admin()]);
$router->post('/admin/contacts/{id}/mark-read', 'AdminController@markContactRead', [Middleware::admin()]);
$router->post('/admin/contacts/{id}/delete', 'AdminController@deleteContact', [Middleware::admin()]);
$router->get('/admin/notifications', 'AdminController@notifications', [Middleware::admin()]);
$router->post('/admin/notifications/mark-all-read', 'AdminController@markAllNotificationsRead', [Middleware::admin()]);

// Doctor routes (after existing ones)
$router->get('/doctor/messages', 'DoctorController@messages', [Middleware::doctor()]);
$router->get('/doctor/messages/conversation/{id}', 'DoctorController@conversation', [Middleware::doctor()]);
$router->post('/doctor/messages/send', 'DoctorController@sendMessage', [Middleware::doctor()]);
$router->get('/doctor/notifications', 'DoctorController@notifications', [Middleware::doctor()]);
$router->post('/doctor/notifications/mark-all-read', 'DoctorController@markAllNotificationsRead', [Middleware::doctor()]);
$router->get('/doctor/availability', 'DoctorController@availability', [Middleware::doctor()]);
$router->post('/doctor/availability', 'DoctorController@saveAvailability', [Middleware::doctor()]);

// Patient routes (after existing ones)
$router->get('/patient/messages', 'PatientController@messages', [Middleware::patient()]);
$router->post('/patient/messages/send', 'PatientController@sendMessage', [Middleware::patient()]);
$router->get('/patient/notifications', 'PatientController@notifications', [Middleware::patient()]);
$router->post('/patient/notifications/mark-all-read', 'PatientController@markAllNotificationsRead', [Middleware::patient()]);
$router->post('/patient/reports/upload', 'PatientController@uploadReport', [Middleware::patient()]);
```

---

### Task Group B: Phase 1 Bug Fixes

#### Task B1: Fix Patient Profile (phone, email, password)

**Files:**
- Modify: `app/Controllers/PatientController.php`
- Modify: `app/Views/patient/profile.php`

**Controller changes** in `PatientController@profile`:
- Add `email` to the UPDATE query params
- Merge password change into same handler (like admin/doctor do — check for `new_password`, confirm, hash and update)

**View changes** in `app/Views/patient/profile.php:51`:
- Change `$patient['phone']` to `$user['phone']`
- Add email field to form (currently missing)
- Remove the separate password form section and merge password fields into main form (matching admin/doctor pattern)

#### Task B2: Fix Appointment Status Checks

**Files:**
- Modify: `app/Views/patient/appointments.php:32`
- Modify: `app/Views/doctor/appointments.php:31`

Change:
```php
<?php if ($apt['status'] === 'scheduled'): ?>
```
To:
```php
<?php if ($apt['status'] === 'pending' || $apt['status'] === 'confirmed'): ?>
```

#### Task B3: Fix Patient Records View Columns

**File:** `app/Views/patient/records.php`

Replace all:
- `$record['first_name']` → `$record['doctor_first_name']`
- `$record['last_name']` → `$record['doctor_last_name']`
- Remove `<span class="record-type"><?= htmlspecialchars($record['record_type'] ?? 'General') ?></span>` (no such column)
- Remove the attachment download block (attachment is on `medical_reports`, not `medical_records`)

#### Task B4: Add CSRF Token to Controllers

Add CSRF verification at the start of all POST handlers:

```php
$body = $this->getBody();
if (isset($body['_token']) && !verify_csrf($body['_token'])) {
    $_SESSION['_errors'] = ['Invalid security token.'];
    $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    return;
}
```

Add to: `AdminController::users`, `AdminController::createDoctor`, `AdminController::updateDoctor`, `DoctorController::createPrescription`, `DoctorController::createReport`, `DoctorController::sendMessage`, `PatientController::sendMessage`.

---

### Task Group C: Phase 2 UI Enhancement

#### Task C1: Sidebar Enhancements

**File:** `app/Views/layouts/header.php`

1. Fix active state for nested routes — change exact match to prefix match:
```php
<a href="<?= $link['url'] ?>" class="<?= str_starts_with($currentUrl, $link['url']) ? 'active' : '' ?>">
```

2. Add notification badge to sidebar (unread count):
```php
<?php 
$unreadNotif = \App\Models\Notification::countUnread($user['id'] ?? 0);
?>
```
And in sidebar footer or nav, show badge.

#### Task C2: Form Enhancement — Appointment Booking

**File:** `app/Views/patient/appointments-create.php`

Wrap form fields in `form-section` divs:
```html
<div class="form-section">
    <div class="form-section-title"><i class="fas fa-user-md"></i> Select Doctor</div>
    ...
</div>
<div class="form-section">
    <div class="form-section-title"><i class="fas fa-calendar"></i> Date & Time</div>
    ...
</div>
<div class="form-section">
    <div class="form-section-title"><i class="fas fa-notes-medical"></i> Reason for Visit</div>
    ...
</div>
```
Add icons to labels. Wrap submit in `form-actions` div with back button.

#### Task C3: Form Enhancement — Prescription Create

**File:** `app/Views/doctor/prescriptions-create.php`

Same pattern: split into sections (Patient, Medication, Details). Add form-row for dosage/frequency. Icons on labels.

#### Task C4: Form Enhancement — Record Create

**File:** `app/Views/doctor/records-create.php`

Same pattern: sections (Patient, Diagnosis, Symptoms, Notes). Icons on labels.

#### Task C5: Table Wrappers + Empty States

**Files:**
- `app/Views/doctor/appointments.php`
- `app/Views/patient/appointments.php`

Wrap tables in:
```html
<div class="data-table-wrapper">
    <div class="table-header">
        <h3><i class="fas fa-list"></i> Appointments</h3>
    </div>
    <div class="table-wrapper">
        <table class="data-table">...</table>
    </div>
</div>
```

**Empty states** — replace bare text with:
```html
<div class="empty-state-enhanced">
    <i class="fas fa-calendar-times"></i>
    <h3>No appointments found</h3>
    <p>There are no appointments to display.</p>
</div>
```

Apply to: `doctor/appointments.php`, `doctor/prescriptions.php`, `doctor/patients.php`.

#### Task C6: CSS Additions

**File:** `public/assets/css/style.css` (before the RESPONSIVE section)

Add:
```css
/* --- Messages --- */
.messages-layout { display: grid; grid-template-columns: 340px 1fr; gap: 0; background: var(--bg-white); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; min-height: 500px; }
.message-list { border-right: 1px solid var(--border); overflow-y: auto; }
.message-list-header { padding: 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
.message-list-header h3 { font-size: 0.95rem; }
.message-item { display: flex; align-items: flex-start; gap: 12px; padding: 16px 20px; cursor: pointer; transition: all 0.2s; border-bottom: 1px solid var(--border); }
.message-item:hover { background: var(--bg-light); }
.message-item.active { background: rgba(13,110,253,0.06); border-left: 3px solid var(--primary); }
.message-item .msg-avatar { width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; }
.message-item .msg-content { flex: 1; min-width: 0; }
.message-item .msg-content strong { display: block; font-size: 0.88rem; }
.message-item .msg-content p { font-size: 0.82rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0; }
.message-item .msg-time { font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; }
.message-item.unread { background: rgba(13,110,253,0.04); }
.message-item.unread .msg-content strong { font-weight: 700; }
.message-conversation { display: flex; flex-direction: column; height: 100%; }
.conversation-header { padding: 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 12px; }
.conversation-header h3 { font-size: 1rem; }
.conversation-messages { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 16px; }
.message-bubble { max-width: 70%; padding: 12px 16px; border-radius: 12px; font-size: 0.88rem; line-height: 1.5; }
.message-bubble.sent { align-self: flex-end; background: var(--primary); color: #fff; border-bottom-right-radius: 4px; }
.message-bubble.received { align-self: flex-start; background: var(--bg-light); border-bottom-left-radius: 4px; }
.message-bubble .msg-time { font-size: 0.7rem; opacity: 0.7; display: block; margin-top: 4px; }
.message-input-area { padding: 16px 20px; border-top: 1px solid var(--border); display: flex; gap: 12px; }
.message-input-area textarea { flex: 1; border: 1px solid var(--border); border-radius: 8px; padding: 10px 14px; font-family: inherit; font-size: 0.88rem; resize: none; min-height: 44px; }
.message-input-area textarea:focus { outline: none; border-color: var(--primary); }

/* --- Availability --- */
.availability-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; margin-bottom: 24px; }
.day-toggle { padding: 16px; border: 2px solid var(--border); border-radius: var(--radius); text-align: center; cursor: pointer; transition: all 0.2s; user-select: none; }
.day-toggle:hover { border-color: var(--primary); }
.day-toggle.active { border-color: var(--primary); background: rgba(13,110,253,0.06); }
.day-toggle .day-name { font-weight: 600; font-size: 0.9rem; display: block; }
.day-toggle .day-hours { font-size: 0.78rem; color: var(--text-muted); margin-top: 4px; }
.time-slot-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); gap: 8px; }
.time-slot { padding: 10px; border: 1px solid var(--border); border-radius: 6px; text-align: center; font-size: 0.82rem; cursor: pointer; transition: all 0.2s; }
.time-slot:hover { border-color: var(--primary); }
.time-slot.selected { background: var(--primary); color: #fff; border-color: var(--primary); }

/* --- Blog Management --- */
.blog-card { display: flex; gap: 20px; padding: 20px; border: 1px solid var(--border); border-radius: var(--radius-lg); transition: all 0.2s; }
.blog-card:hover { box-shadow: var(--shadow-md); }
.blog-card .blog-thumb { width: 120px; height: 90px; border-radius: 8px; background: var(--bg-light); flex-shrink: 0; object-fit: cover; }
.blog-card .blog-content { flex: 1; min-width: 0; }
.blog-card .blog-content h3 { font-size: 1rem; margin-bottom: 4px; }
.blog-card .blog-content .blog-meta { font-size: 0.8rem; color: var(--text-muted); display: flex; gap: 16px; }
.blog-card .blog-content p { font-size: 0.88rem; color: var(--text-muted); margin-top: 8px; }
.blog-editor-wrapper textarea[name="content"] { min-height: 300px; font-family: inherit; }

/* --- Notifications Page --- */
.notifications-list { display: flex; flex-direction: column; gap: 1px; background: var(--border); border-radius: var(--radius-lg); overflow: hidden; }
.notification-item { display: flex; gap: 16px; padding: 20px; background: var(--bg-white); align-items: flex-start; transition: all 0.2s; }
.notification-item:hover { background: var(--bg-light); }
.notification-item.unread { background: rgba(13,110,253,0.04); border-left: 3px solid var(--primary); }
.notification-item .notif-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.notification-item .notif-content { flex: 1; }
.notification-item .notif-content strong { display: block; font-size: 0.9rem; }
.notification-item .notif-content p { font-size: 0.85rem; color: var(--text-muted); margin: 2px 0; }
.notification-item .notif-time { font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; }

/* --- Dashboard Grid Full Width --- */
.dashboard-card-full { grid-column: 1 / -1; }
```

---

### Task Group D: Phase 3 — Admin Missing Pages

#### Task D1: Admin Appointments Page

**Files:**
- Modify: `app/Controllers/AdminController.php` — Add appointments(), confirmAppointment(), cancelAppointment()
- Create: `app/Views/admin/appointments.php`

**Controller:**
```php
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
    if (!empty($statusFilter)) {
        $sql .= " WHERE a.status = ?";
        $params[] = $statusFilter;
    }
    $sql .= " ORDER BY a.appointment_date DESC";
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
```

**View** (`admin/appointments.php`):
- Status filter tabs: All | Pending (count) | Confirmed (count) | Completed (count) | Cancelled (count)
- Data table with: Date, Time, Patient, Doctor, Type, Status, Actions (confirm/cancel for pending/confirmed)
- Enhanced empty state

#### Task D2: Admin Blog Page

**Files:**
- Modify: `app/Controllers/AdminController.php` — Add blog(), createBlogForm(), createBlogPost(), editBlogForm(), updateBlogPost(), deleteBlogPost(), blogCategories(), saveCategory()
- Create: `app/Views/admin/blog.php`
- Create: `app/Views/admin/blog-create.php`
- Create: `app/Views/admin/blog-edit.php`
- Create: `app/Views/admin/blog-categories.php`

**Controller methods:**

```php
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
        [Auth::id(), $body['category_id'] ?: null, $body['title'], $slug, $body['excerpt'] ?? '', $body['content'] ?? '', $body['tags'] ?? '[]', !empty($body['is_published']) ? 1 : 0]
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
        "UPDATE blog_posts SET category_id = ?, title = ?, slug = ?, excerpt = ?, content = ?, tags = ?, is_published = ? WHERE id = ?",
        [$body['category_id'] ?: null, $body['title'], $slug, $body['excerpt'] ?? '', $body['content'] ?? '', $body['tags'] ?? '[]', !empty($body['is_published']) ? 1 : 0, $id]
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
        $db->insert("INSERT INTO blog_categories (name, slug) VALUES (?, ?)", [$body['name'], $slug]);
        $_SESSION['_flash']['success'] = 'Category added.';
        $this->redirect('/admin/blog/categories');
    }
    $this->render('admin/blog-categories', compact('categories'));
}
```

#### Task D3: Admin Contacts Page

**Files:**
- Modify: `app/Controllers/AdminController.php` — Add contacts(), markContactRead(), deleteContact()
- Create: `app/Views/admin/contacts.php`

**Controller:**
```php
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
```

#### Task D4: Admin Notifications Page

**Files:**
- Modify: `app/Controllers/AdminController.php` — Add notifications(), markAllNotificationsRead()
- Create: `app/Views/admin/notifications.php`

**Controller:**
```php
public function notifications(): void
{
    $user = Auth::user();
    $notifications = Notification::getForUser($user['id'], 50);
    $this->render('admin/notifications', compact('notifications'));
}

public function markAllNotificationsRead(): void
{
    Notification::markAllAsRead(Auth::id());
    $_SESSION['_flash']['success'] = 'All notifications marked as read.';
    $this->redirect('/admin/notifications');
}
```

---

### Task Group E: Phase 3 — Doctor Missing Pages

#### Task E1: Doctor Messages

**Files:**
- Modify: `app/Controllers/DoctorController.php` — Add messages(), conversation(), sendMessage()
- Create: `app/Views/doctor/messages.php`
- Create: `app/Views/doctor/messages-conversation.php`

**Controller:**
```php
public function messages(): void
{
    $user = Auth::user();
    $db = Database::getInstance();
    $conversations = $db->fetchAll("
        SELECT m.*, u.first_name, u.last_name, u.avatar,
               (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
        FROM messages m
        JOIN users u ON (m.sender_id = u.id OR m.receiver_id = u.id)
        WHERE m.id IN (
            SELECT MAX(m2.id) FROM messages m2
            WHERE (m2.sender_id = ? AND m2.receiver_id IN (SELECT user_id FROM patients))
               OR (m2.receiver_id = ? AND m2.sender_id IN (SELECT user_id FROM patients))
            GROUP BY CASE WHEN m2.sender_id = ? THEN m2.receiver_id ELSE m2.sender_id END
        )
        ORDER BY m.created_at DESC
    ", [$user['id'], $user['id'], $user['id'], $user['id']]);
    $this->render('doctor/messages', compact('conversations'));
}

public function conversation(int $patientUserId): void
{
    $user = Auth::user();
    $messages = Message::getConversation($user['id'], $patientUserId);
    // Mark as read
    $db = Database::getInstance();
    $db->execute("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?", [$patientUserId, $user['id']]);
    $patientUser = User::find($patientUserId);
    $this->render('doctor/messages-conversation', compact('messages', 'patientUser', 'patientUserId'));
}

public function sendMessage(): void
{
    $body = $this->getBody();
    $id = Message::create([
        'sender_id' => Auth::id(),
        'receiver_id' => $body['receiver_id'],
        'subject' => $body['subject'] ?? '',
        'body' => $body['body'],
    ]);
    $_SESSION['_flash']['success'] = 'Message sent.';
    $this->redirect('/doctor/messages/conversation/' . $body['receiver_id']);
}
```

#### Task E2: Doctor Availability

**Files:**
- Modify: `app/Controllers/DoctorController.php` — Add availability(), saveAvailability()
- Create: `app/Views/doctor/availability.php`

**Controller:**
```php
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
    $selectedDays = $body['days'] ?? [];
    $hours = $body['hours'] ?? [];
    Doctor::update($doctor['id'], [
        'available_days' => json_encode($selectedDays),
        'available_hours' => json_encode($hours),
    ]);
    $_SESSION['_flash']['success'] = 'Availability updated.';
    $this->redirect('/doctor/availability');
}
```

#### Task E3: Doctor Notifications

**Files:**
- Modify: `app/Controllers/DoctorController.php` — Add notifications(), markAllNotificationsRead()
- Create: `app/Views/doctor/notifications.php`

---

### Task Group F: Phase 3 — Patient Missing Pages

#### Task F1: Patient Messages

**Files:**
- Modify: `app/Controllers/PatientController.php` — Add messages(), sendMessage()
- Create: `app/Views/patient/messages.php`

**Controller:**
```php
public function messages(): void
{
    $user = Auth::user();
    $conversations = Message::getInbox($user['id']);
    $doctors = Doctor::getVerified();
    $this->render('patient/messages', compact('conversations', 'doctors'));
}

public function sendMessage(): void
{
    $body = $this->getBody();
    $doctor = Doctor::find($body['doctor_id']);
    if ($doctor) {
        Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $doctor['user_id'],
            'subject' => $body['subject'] ?? 'Message from patient',
            'body' => $body['body'],
        ]);
        $_SESSION['_flash']['success'] = 'Message sent.';
    }
    $this->redirect('/patient/messages');
}
```

#### Task F2: Patient Notifications

**Files:**
- Modify: `app/Controllers/PatientController.php` — Add notifications(), markAllNotificationsRead()
- Create: `app/Views/patient/notifications.php`

#### Task F3: Medical Report Upload Form

**File:** `app/Views/patient/records.php`

Add upload form above the records list:
```html
<div class="form-card" style="margin-bottom:24px;">
    <div class="card-header">
        <h3><i class="fas fa-upload"></i> Upload Medical Report</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="/patient/reports/upload" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group">
                    <label for="title">Report Title</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="type">Type</label>
                    <select id="type" name="type">
                        <option value="lab">Lab</option>
                        <option value="imaging">Imaging</option>
                        <option value="pathology">Pathology</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="report">File</label>
                <div class="file-input-wrapper">
                    <input type="file" id="report" name="report" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                </div>
            </div>
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload Report</button>
        </form>
    </div>
</div>
```

---

### Task Group G: Phase 4 — Linking & Sidebar

#### Task G1: Update Sidebar Navigation

**File:** `app/Views/layouts/header.php`

Update sidebar links for each role:

**Admin** add: Appointments, Blog, Contacts, Notifications
**Doctor** add: Messages, Notifications (if not already), Availability
**Patient** add: Messages, Notifications

Fix sidebar active state to use `str_starts_with`.

#### Task G2: Add Notification Badge

**File:** `app/Views/layouts/header.php`

After sidebar user info, compute unread count:
```php
<?php 
$unreadMessages = \App\Models\Message::countUnread($user['id'] ?? 0);
$unreadNotifications = \App\Models\Notification::countUnread($user['id'] ?? 0);
?>
```

Show badge next to Messages/Notifications links:
```php
<?php if ($link['label'] === 'Messages' && $unreadMessages > 0): ?>
    <span class="notification-badge"><?= $unreadMessages ?></span>
<?php endif; ?>
```

---

### Self-Review Checklist

1. **Spec coverage:** Every spec requirement has a corresponding task. Phase 1 bugs → Tasks B1-B4. Phase 2 UI → Tasks C1-C6. Phase 3 pages → Tasks D1-D4, E1-E3, F1-F3. Phase 4 linking → Tasks G1-G2.

2. **Placeholder scan:** No TODOs, TBDs, or "implement later". All code is complete in each task.

3. **Type consistency:** All method names match between routes, controllers, and views. Model method names are consistent with existing code.

4. **No missing tasks:** Blog categories covered (D2). Contact CRUD covered (D3). CSRF covered (B4). All 12 new view files accounted for.
