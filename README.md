# 🏥 Medicase — Smart Healthcare Platform

A full-featured healthcare management platform built with **native PHP MVC**, **MySQL**, and **vanilla JavaScript**. Medicase connects patients, doctors, and administrators through a unified digital ecosystem with role-based dashboards, AI-assisted features, and secure communication tools.

---

## ✨ Features

### 👤 Patient
- Register & manage profile with avatar upload
- Book, view, and cancel appointments
- View medical records & download reports
- Track prescriptions with dosage details
- AI symptom analysis assistant
- Upload medical documents (PDF, JPG, PNG, DOCX)

### 👨‍⚕️ Doctor
- Role-based dashboard with today's schedule
- Manage assigned patients and their records
- Create prescriptions and medical reports
- View appointment history
- Update professional profile (bio, license, education)

### 🛡️ Admin
- Full system overview with statistics
- Manage users (activate/deactivate)
- Create, edit, and manage doctors
- Monitor appointments across the platform
- Audit user activity

### 🤖 AI Assistant
- Symptom analysis with condition suggestions
- Medication explanation (dosage, side effects)
- Medical report interpretation
- Drug interaction checker with warnings
- Smart appointment & medication reminders

---

## 🧱 Tech Stack

| Layer        | Technology |
|-------------|------------|
| **Backend** | PHP 8.4+, Native MVC |
| **Database** | MySQL 8+ / MariaDB |
| **Frontend** | Vanilla JavaScript, CSS3 (custom) |
| **Icons** | Font Awesome 6 |
| **Fonts** | Inter (Google Fonts) |
| **Auth** | bcrypt password hashing, session-based |
| **Mail** | PHPMailer |
| **Server** | DDEV / PHP built-in server |

---

## 📁 Project Structure

```
medicase/
├── app/
│   ├── Controllers/       # Request handlers
│   │   ├── AdminController.php
│   │   ├── AuthController.php
│   │   ├── BlogController.php
│   │   ├── DoctorController.php
│   │   ├── HomeController.php
│   │   └── PatientController.php
│   ├── Core/              # Framework core
│   │   ├── Auth.php        # Authentication
│   │   ├── Controller.php  # Base controller
│   │   ├── Database.php    # PDO wrapper
│   │   ├── Middleware.php  # Role middleware
│   │   ├── Model.php       # Base model
│   │   ├── Router.php      # URL routing
│   │   ├── Validator.php   # Form validation
│   │   └── helpers.php     # Global helpers
│   ├── Models/             # Database models
│   ├── Services/           # Business logic
│   │   ├── AIService.php
│   │   ├── AppointmentService.php
│   │   ├── FileService.php
│   │   └── NotificationService.php
│   └── Views/              # PHP templates
│       ├── layouts/        # Header/footer
│       ├── public/         # Landing pages
│       ├── auth/           # Login/register
│       ├── patient/        # Patient dashboards
│       ├── doctor/         # Doctor dashboards
│       └── admin/          # Admin dashboards
├── config/
│   ├── app.php             # App configuration
│   └── database.php        # Database settings
├── database/
│   └── schema.sql          # Full schema + seed data
├── public/
│   ├── index.php           # Front controller
│   ├── .htaccess           # URL rewriting
│   ├── assets/
│   │   ├── css/style.css   # Complete stylesheet
│   │   └── js/main.js      # Client-side scripts
│   └── uploads/
│       ├── avatars/        # Profile photos
│       └── reports/        # Medical documents
├── docs/superpowers/       # Design & planning docs
├── composer.json
└── README.md
```

---

## 🚀 Installation

### Prerequisites

- PHP 8.1+
- MySQL 8+ or MariaDB
- Composer
- DDEV (recommended) OR a web server (Apache/Nginx)

### Option A: DDEV (Recommended)

```bash
# Clone and enter the project
git clone <repo-url> medicase
cd medicase

# Start DDEV
ddev start

# Install PHP dependencies
ddev composer install

# Import database schema
ddev mysql < database/schema.sql

# Visit
open https://medicase.ddev.site
```

### Option B: Manual Setup

```bash
# Clone and enter the project
git clone <repo-url> medicase
cd medicase

# Install PHP dependencies
composer install

# Create database
mysql -u root -p -e "CREATE DATABASE medicase CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p medicase < database/schema.sql

# Configure database
# Edit config/database.php with your credentials

# Serve the application
php -S localhost:8000 -t public

# Visit
open http://localhost:8000
```

---

## ⚙️ Configuration

### `config/database.php`

```php
return [
    'host'     => '127.0.0.1',
    'dbname'   => 'medicase',
    'username' => 'root',
    'password' => 'your_password',
    'charset'  => 'utf8mb4',
];
```

### `config/app.php`

```php
return [
    'name'  => 'Medicase',
    'url'   => 'https://your-domain.com',
    'env'   => 'production',
    'debug' => false,
];
```

---

## 👥 Default Accounts

After importing `schema.sql`, the following accounts are available:

| Role      | Email                  | Password   |
|-----------|------------------------|------------|
| Admin     | admin@medicase.com     | password   |
| Patient   | *(register new)*       | —          |
| Doctor    | *(register new)*       | —          |

---

## 🧩 Database Schema

23 tables with full foreign key relationships:

- **users** — Authentication & profiles
- **patients** — Patient-specific data (blood type, DOB, emergency contact)
- **doctors** — Professional info (specialization, license, education)
- **specializations** — Medical specialties (cardiology, neurology, etc.)
- **appointments** — Scheduling with status flow (pending → confirmed → completed/cancelled)
- **prescriptions** — Medication, dosage, frequency, duration
- **medical_records** — Diagnoses, symptoms, notes
- **medical_reports** — Uploaded documents (PDF, images)
- **messages** — Patient-doctor communication
- **notifications** — System-wide alerts
- **discussion_groups** — Doctor collaboration
- **discussion_messages** — Group discussions
- **second_opinions** — Specialist consultation requests
- **ai_requests** — AI assistant query log
- **blog_posts** — Public health articles
- **services** — Offered medical services
- **password_resets** — Password recovery
- **audit_logs** — Activity tracking

Run `database/schema.sql` to create all tables and seed initial data.

---

## 🌐 Routes

| Route | Method | Middleware | Description |
|-------|--------|-----------|-------------|
| `/` | GET | — | Landing page |
| `/about` | GET | — | About page |
| `/services` | GET | — | Services listing |
| `/experts` | GET | — | Doctor directory |
| `/blog` | GET | — | Blog index |
| `/contact` | GET/POST | — | Contact form |
| `/pricing` | GET | — | Pricing page |
| `/login` | GET/POST | Guest | Authentication |
| `/register` | GET/POST | Guest | Registration |
| `/patient/*` | * | Patient | Patient dashboard |
| `/doctor/*` | * | Doctor | Doctor dashboard |
| `/admin/*` | * | Admin | Administration |
| `/api/ai/*` | POST | Auth | AI assistant API |

---

## 🎨 Dashboard Layouts

All three roles feature a **dark sidebar layout** with:

- Fixed sidebar (260px) with role-specific navigation
- Gradient stat cards with hover animations
- Data tables with avatar thumbnails
- Profile pages with avatar upload
- Fully responsive (sidebar → off-canvas overlay on mobile)

---

## 🔒 Security

- **CSRF Protection** — Token validation on all forms
- **XSS Prevention** — `htmlspecialchars()` on all output
- **SQL Injection** — PDO prepared statements throughout
- **Password Hashing** — bcrypt via `password_hash()`
- **Role Middleware** — Route-level access control
- **File Upload** — Extension & size validation
- **Session** — Server-side session management

---

## 📱 Responsive Design

- Mobile-first approach with CSS Grid/Flexbox
- Breakpoints at 480px, 768px, 1024px
- Sidebar collapses to off-canvas overlay on mobile
- Tables scroll horizontally on small screens
- All public pages are fully responsive

---

## 🔧 Development

```bash
# Start dev server
composer serve

# Or with custom host/port
php -S 0.0.0.0:8080 -t public
```

---

## 📄 License

Proprietary — All rights reserved.

---

## 🙏 Acknowledgements

- [PHPMailer](https://github.com/PHPMailer/PHPMailer) — Email handling
- [Font Awesome](https://fontawesome.com) — Icon library
- [Inter](https://rsms.me/inter/) — Typeface
- [Google Stitch](https://stitch.google) — Design reference
