# Medicase — Full Stack Medical Platform Design

## Overview
Medicase is a full-stack healthcare platform with PHP MVC backend, responsive frontend, role-based access (Admin, Doctor, Patient), and complete CRUD for all medical entities.

## Architecture
- **Backend:** Native PHP 8.4 MVC, PDO/MySQL, JWT auth
- **Frontend:** Semantic HTML5, CSS3 (custom properties), vanilla ES6, Fetch API
- **Server:** nginx-fpm via DDEV, MariaDB 11.8
- **Pattern:** Front Controller → Router → Controller → Service → Model → DB

## Directory Structure
```
medicase/
├── public/                    # Web root (document root)
│   ├── index.php             # Front controller (all requests)
│   ├── .htaccess             # URL rewriting
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css     # All styles (no inline CSS)
│   │   ├── js/
│   │   │   └── main.js       # App JS (nav, forms, AJAX)
│   │   └── images/           # Local images
│   └── uploads/              # Patient file uploads
├── app/
│   ├── core/
│   │   ├── Router.php        # Route matching + dispatch
│   │   ├── Controller.php    # Base controller (render, redirect, JSON)
│   │   ├── Model.php         # Base model (PDO wrapper)
│   │   ├── Database.php      # Singleton DB connection
│   │   ├── Auth.php          # JWT + session auth manager
│   │   ├── Validator.php     # Input validation
│   │   └── Middleware.php    # Auth/role middleware
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── HomeController.php
│   │   ├── PatientController.php
│   │   ├── DoctorController.php
│   │   ├── AppointmentController.php
│   │   ├── MedicalRecordController.php
│   │   ├── AIController.php
│   │   ├── MessageController.php
│   │   ├── BlogController.php
│   │   └── AdminController.php
│   ├── models/
│   │   ├── User.php
│   │   ├── Patient.php
│   │   ├── Doctor.php
│   │   ├── Appointment.php
│   │   ├── MedicalRecord.php
│   │   ├── Prescription.php
│   │   ├── Notification.php
│   │   ├── Message.php
│   │   ├── Blog.php
│   │   └── AIHistory.php
│   ├── services/
│   │   ├── AIService.php
│   │   ├── NotificationService.php
│   │   ├── AppointmentService.php
│   │   └── FileService.php
│   └── views/
│       ├── layouts/
│       │   ├── header.php    # Dynamic navbar + head
│       │   └── footer.php
│       ├── auth/
│       │   ├── login.php
│       │   ├── register.php
│       │   └── forgot-password.php
│       ├── public/
│       │   ├── home.php
│       │   ├── about.php
│       │   ├── services.php
│       │   ├── experts.php
│       │   ├── blog.php
│       │   ├── blog-single.php
│       │   ├── contact.php
│       │   └── pricing.php
│       ├── patient/
│       │   ├── dashboard.php
│       │   ├── profile.php
│       │   ├── records.php
│       │   ├── appointments.php
│       │   └── prescriptions.php
│       ├── doctor/
│       │   ├── dashboard.php
│       │   ├── patients.php
│       │   ├── appointments.php
│       │   ├── prescriptions.php
│       │   └── reports.php
│       └── admin/
│           ├── dashboard.php
│           ├── users.php
│           ├── doctors.php
│           └── stats.php
├── database/
│   └── schema.sql            # Full DB schema
├── storage/
│   ├── logs/
│   └── temp/
└── vendor/                   # Composer deps (PHPMailer, JWT)
```

## Database Tables (22)
users, patients, doctors, specializations, medical_records, medical_reports, prescriptions, appointments, notifications, messages, groups, group_members, ai_conversations, ai_messages, blog_posts, blog_categories, reviews, services, audit_logs, password_resets, settings, role_permissions

## Frontend Design Tokens
- **Primary:** #072e61 (navy), #0360d9 (blue), #1b4fd8 (cta blue)
- **Accent:** #4a7cff, #0d9488 (teal for AI status)
- **Neutrals:** #0f172a, #64748b, #6b7a99, #f8faff
- **Font:** Poppins (headlines), Inter/IBM Plex Sans (body), DM Sans (stats)
- **Radius:** 50px (pills), 22px (cards), 14px (sections)
- **Navbar:** Fixed-top, glass-morphism blur, role-responsive links
- **Buttons:** Pill-shaped, hover scale + shadow transitions, 5 variants

## Routes
| Method | Path | Controller | Auth |
|--------|------|-----------|------|
| GET | / | HomeController@index | No |
| GET/POST | /login | AuthController@login | No |
| GET/POST | /register | AuthController@register | No |
| GET/POST | /forgot-password | AuthController@forgotPassword | No |
| POST | /logout | AuthController@logout | Yes |
| GET | /about | HomeController@about | No |
| GET | /services | HomeController@services | No |
| GET | /experts | HomeController@experts | No |
| GET | /blog | BlogController@index | No |
| GET | /blog/{slug} | BlogController@show | No |
| GET | /contact | HomeController@contact | No |
| GET | /pricing | HomeController@pricing | No |
| GET | /patient/dashboard | PatientController@dashboard | Patient |
| GET | /patient/profile | PatientController@profile | Patient |
| PUT | /patient/profile | PatientController@updateProfile | Patient |
| GET | /patient/records | PatientController@records | Patient |
| POST | /patient/upload | PatientController@uploadReport | Patient |
| GET | /patient/appointments | PatientController@appointments | Patient |
| POST | /patient/appointments | PatientController@createAppointment | Patient |
| DELETE | /patient/appointments/{id} | PatientController@cancelAppointment | Patient |
| GET | /doctor/dashboard | DoctorController@dashboard | Doctor |
| GET | /doctor/patients | DoctorController@patients | Doctor |
| GET | /doctor/patient/{id} | DoctorController@patientDetail | Doctor |
| POST | /doctor/prescription | DoctorController@createPrescription | Doctor |
| POST | /doctor/report | DoctorController@createReport | Doctor |
| GET | /doctor/appointments | DoctorController@appointments | Doctor |
| PUT | /doctor/appointments/{id} | DoctorController@updateAppointment | Doctor |
| GET | /admin/dashboard | AdminController@dashboard | Admin |
| GET | /admin/users | AdminController@users | Admin |
| POST | /admin/users | AdminController@createUser | Admin |
| PUT | /admin/users/{id} | AdminController@updateUser | Admin |
| GET | /admin/doctors | AdminController@doctors | Admin |
| POST | /api/ai/chat | AIController@chat | Yes |
| POST | /api/ai/symptoms | AIController@analyzeSymptoms | Yes |

## Responsive Breakpoints
- Mobile: <576px (stack everything, hamburger nav)
- Tablet: 576-992px (2-column grids)
- Desktop: 992-1200px (3-column, full-width)
- Wide: >1200px (max-width 1400px content)

## Security
- bcrypt password hashing
- CSRF tokens on all POST forms
- PDO prepared statements
- Input sanitization (htmlspecialchars, strip_tags)
- Role-based middleware on all protected routes
- File upload validation (type, size, mime)
