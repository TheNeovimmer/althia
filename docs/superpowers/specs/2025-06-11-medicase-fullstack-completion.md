# Medicase Full-Stack Completion & Enhancement

## Overview
Complete the Medicase healthcare platform by fixing critical bugs, enhancing all dashboard UI, adding missing pages across admin/doctor/patient dashboards, and linking frontend to backend.

## Phase 1 — Critical Bug Fixes

### 1.1 Patient Profile — Phone Field
- **File**: `app/Views/patient/profile.php:51`
- **Bug**: References `$patient['phone']` — phone column is on `users` table, not `patients`
- **Fix**: Change to `$user['phone']`

### 1.2 Patient Profile — Email Update
- **File**: `app/Controllers/PatientController.php:80-81`
- **Bug**: Profile UPDATE only sets `first_name`, `last_name`, `phone` — doesn't update email
- **Fix**: Add `email` to the UPDATE query and to the view template

### 1.3 Patient Profile — Password Route (Critical)
- **File**: `app/Views/patient/profile.php:80`
- **Bug**: Password form POSTs to `/patient/profile/password` but no route exists
- **Fix**: Merge password change into main profile POST handler (same pattern as admin/doctor). Remove separate form. Add password fields to main profile form with conditional update.

### 1.4 Status Checks — Wrong DB Values
- **Files**: `app/Views/patient/appointments.php:32`, `app/Views/doctor/appointments.php:31`
- **Bug**: Check `$apt['status'] === 'scheduled'` but DB stores `pending`, `confirmed`, `completed`, `cancelled`
- **Fix**: Change to `$apt['status'] === 'pending' || $apt['status'] === 'confirmed'`

### 1.5 Patient Records — Invalid Column Names
- **File**: `app/Views/patient/records.php`
- **Bug**: Uses `$record['first_name']`, `$record['record_type']`, `$record['attachment_path']` — none exist in the MedicalRecord query
- **Fix**: Replace with `$record['doctor_first_name']` / `$record['doctor_last_name']`. Remove `record_type` and `attachment_path` references since this view shows medical records (not reports).

### 1.6 Medical Report Upload
- **File**: `public/index.php` + `app/Views/patient/records.php`
- **Bug**: `PatientController@uploadReport` exists but no route or upload form
- **Fix**: Add POST route `/patient/reports/upload`. Add upload form section above records list in records.php.

## Phase 2 — UI & Form Enhancement

### 2.1 Standardized Form Pattern
Apply the `form-section`/`form-section-title`/`form-row` pattern (already used in admin/doctors-create/edit) to:
- `patient/appointments-create.php` — Add form sections, icons on labels
- `doctor/prescriptions-create.php` — Add form sections, form-row layout
- `doctor/records-create.php` — Add form sections, icons
- `patient/profile.php` — Add form sections with icons (Personal Info, Medical Info)

### 2.2 Consistent Data Tables
Wrap tables in `data-table-wrapper` with styled header:
- `doctor/appointments.php`
- `patient/appointments.php`

### 2.3 Sidebar Enhancements
- Add notification count badge next to nav items where applicable
- Fix active state for nested routes: `str_starts_with($currentUrl, $link['url'])` instead of exact match

### 2.4 Enhanced Empty States
Convert bare empty states to `empty-state-enhanced` with icon, title, description, CTA:
- `doctor/appointments.php`
- `doctor/prescriptions.php`
- `doctor/patients.php`

### 2.5 File Input Styling
Apply `file-input-wrapper` with dashed border to all avatar upload fields (some are missing the wrapper pattern).

### 2.6 Form Action Buttons
Ensure all forms have `form-actions` with consistent button styling — primary submit + cancel/back outline button.

## Phase 3 — Missing Pages

### 3.1 Admin — Appointments Management
- **Route**: `/admin/appointments` (GET)
- **Controller**: `AdminController@appointments`
- **View**: `admin/appointments.php`
- **Data**: All appointments with patient/doctor names, status filter, confirm/cancel actions
- **Features**: Status filter tabs, date range, mark confirmed/cancelled

### 3.2 Admin — Blog Management
- **Route**: `/admin/blog` (GET), `/admin/blog/create` (GET/POST), `/admin/blog/{id}/edit` (GET/POST), `/admin/blog/{id}/delete` (POST)
- **Controller**: `AdminController@blog`, `@createBlogPost`, `@editBlogPost`, `@deleteBlogPost`
- **View**: `admin/blog.php`, `admin/blog-create.php`, `admin/blog-edit.php`
- **Features**: List posts, create/edit with title, content, category, tags, featured image, publish/unpublish

### 3.3 Admin — Blog Categories
- **Route**: `/admin/blog/categories` (GET/POST)
- **Controller**: `AdminController@blogCategories`
- **View**: `admin/blog-categories.php`
- **Features**: List, add, rename categories inline

### 3.4 Admin — Contact Submissions
- **Route**: `/admin/contacts` (GET), `/admin/contacts/{id}/mark-read` (POST)
- **Controller**: `AdminController@contacts`
- **View**: `admin/contacts.php`
- **Features**: View submissions, mark as read, delete

### 3.5 Admin — Notifications
- **Route**: `/admin/notifications` (GET)
- **Controller**: `AdminController@notifications`
- **View**: `admin/notifications.php`
- **Features**: List all system notifications, mark as read

### 3.6 Doctor — Messaging
- **Route**: `/doctor/messages` (GET), `/doctor/messages/{id}` (GET), `/doctor/messages/send` (POST)
- **Controller**: `DoctorController@messages`, `@conversation`, `@sendMessage`
- **View**: `doctor/messages.php`, `doctor/messages-conversation.php`
- **Features**: Inbox list, conversation thread, send message to patient

### 3.7 Doctor — Availability
- **Route**: `/doctor/availability` (GET/POST)
- **Controller**: `DoctorController@availability`
- **View**: `doctor/availability.php`
- **Features**: Set available weekdays, hours per day (JSON in `available_days` / `available_hours` columns)

### 3.8 Patient — Messaging
- **Route**: `/patient/messages` (GET), `/patient/messages/send` (POST)
- **Controller**: `PatientController@messages`, `@sendMessage`
- **View**: `patient/messages.php`
- **Features**: Inbox list, send message to doctor

### 3.9 Patient — Notifications
- **Route**: `/patient/notifications` (GET)
- **Controller**: `PatientController@notifications`
- **View**: `patient/notifications.php`
- **Features**: List notifications, mark as read, clear all

## Phase 4 — Linking & Logic

### 4.1 Notification Service Integration
- Call `NotificationService::send()` when:
  - Admin creates a user (welcome notification)
  - Appointment status changes
  - New medical record is added
  - New prescription is created

### 4.2 CSRF Validation
- Add CSRF token validation to all POST routes that are missing it
- Verify `_token` matches session token in controller base method

### 4.3 Navigation & Breadcrumbs
- Add breadcrumb-style secondary nav to complex pages (blog management)
- Link dashboard tables to detail/edit pages

### 4.4 Delete Confirmations
- Add `onsubmit="return confirm('...')"` to all delete/destructive action forms

## Routes to Add

```php
// Admin
$router->get('/admin/appointments', 'AdminController@appointments', [Middleware::admin()]);
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
$router->get('/admin/notifications', 'AdminController@notifications', [Middleware::admin()]);
$router->post('/admin/notifications/mark-all-read', 'AdminController@markAllNotifications', [Middleware::admin()]);

// Doctor
$router->get('/doctor/messages', 'DoctorController@messages', [Middleware::doctor()]);
$router->get('/doctor/messages/{id}', 'DoctorController@conversation', [Middleware::doctor()]);
$router->post('/doctor/messages/send', 'DoctorController@sendMessage', [Middleware::doctor()]);
$router->get('/doctor/availability', 'DoctorController@availability', [Middleware::doctor()]);
$router->post('/doctor/availability', 'DoctorController@saveAvailability', [Middleware::doctor()]);

// Patient
$router->get('/patient/messages', 'PatientController@messages', [Middleware::patient()]);
$router->post('/patient/messages/send', 'PatientController@sendMessage', [Middleware::patient()]);
$router->get('/patient/notifications', 'PatientController@notifications', [Middleware::patient()]);
$router->post('/patient/reports/upload', 'PatientController@uploadReport', [Middleware::patient()]);
```

## CSS Additions

Add to `public/assets/css/style.css`:

```css
/* Messages */
.messages-layout, .message-list, .message-item, .message-conversation, .message-bubble, .message-form { ... }

/* Availability */
.availability-grid, .day-toggle, .time-slots { ... }

/* Blog editor */
.blog-editor-wrapper, .blog-preview { ... }

/* Notification badges */
.notification-badge { ... }

/* Breadcrumb nav */
.sub-nav { ... }
```

## Database Schema Notes
All required tables already exist in `database/schema.sql`. No schema changes needed — the messaging, notifications, blog categories, contacts tables are already defined.

## File Changes Summary

### Modified files (bugs):
- `app/Views/patient/profile.php` — Fix phone field, add email field, merge password
- `app/Views/patient/appointments.php` — Fix status check
- `app/Views/patient/records.php` — Fix column names, add upload form
- `app/Views/doctor/appointments.php` — Fix status check
- `app/Controllers/PatientController.php` — Add email update, merge password handler
- `public/index.php` — Add missing routes

### Modified files (UI):
- `app/Views/layouts/header.php` — Sidebar enhancements, notification badge
- `app/Views/patient/appointments-create.php` — Form sections
- `app/Views/doctor/prescriptions-create.php` — Form sections
- `app/Views/doctor/records-create.php` — Form sections
- `app/Views/doctor/appointments.php` — Table wrapper, empty states
- `app/Views/doctor/prescriptions.php` — Empty states
- `app/Views/doctor/patients.php` — Empty states
- `public/assets/css/style.css` — New CSS for messages, availability, blog, etc.

### New files:
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
