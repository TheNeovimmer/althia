# 🏥 Medicase — Smart Healthcare Platform

A full-featured healthcare management platform built with **native PHP MVC**, **MySQL**, and **vanilla JavaScript**. Medicase connects patients, doctors, and administrators through a unified digital ecosystem with role-based dashboards, AI-assisted features, and secure communication tools.

---

## ✨ Features

### 👤 Patient
- Register & manage profile with avatar upload
- Book appointments filtered by medical specialty
- View, and cancel appointments
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
- Set weekly availability schedule

### 🛡️ Admin
- Full system overview with statistics and charts
- Manage users (activate/deactivate)
- Create, edit, verify, and manage doctors
- Monitor and approve appointments across the platform
- Blog management with categories
- Contact form inbox
- **Specialization CRUD** (add, rename, update, delete)
- **AI Chatbot Settings** (OpenRouter API key, model selection)
- **RAG Knowledge Base** (upload documents the AI references)

### 🤖 AI Assistant
- OpenRouter-powered chat with configurable model
- RAG (Retrieval-Augmented Generation) from knowledge base docs
- Rule-based fallback when API is unavailable
- Symptom analysis with condition suggestions
- Medication explanation (dosage, side effects)
- Drug interaction checker with warnings
- Admin-configurable system prompt context

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
| **AI** | OpenRouter API (multi-model) + rule-based fallback |
| **Mail** | PHPMailer |
| **Server** | Apache (Laragon) / Nginx / DDEV |

---

## 📁 Project Structure

```
medicase/
├── app/
│   ├── Controllers/       # Request handlers
│   │   ├── AdminController.php
│   │   ├── AIController.php
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
│   │   ├── RagDocument.php # RAG knowledge base
│   │   ├── Setting.php     # Key-value settings
│   │   ├── Specialization.php
│   │   └── ...etc
│   ├── Services/           # Business logic
│   │   ├── AIService.php
│   │   ├── AppointmentService.php
│   │   ├── FileService.php
│   │   ├── NotificationService.php
│   │   └── OpenRouterService.php
│   └── Views/              # PHP templates
│       ├── layouts/        # Header/footer
│       ├── public/         # Landing pages
│       ├── auth/           # Login/register
│       ├── patient/        # Patient dashboards
│       ├── doctor/         # Doctor dashboards
│       └── admin/          # Admin dashboards (incl. specializations, ai-settings)
├── config/
│   ├── app.php             # App configuration
│   └── database.php        # Database settings
├── database/
│   └── schema.sql          # Full schema + seed data
├── public/
│   ├── index.php           # Front controller
│   ├── .htaccess           # URL rewriting (Apache)
│   ├── assets/
│   │   ├── css/style.css   # Complete stylesheet
│   │   └── js/main.js      # Client-side scripts
│   └── uploads/
│       ├── avatars/        # Profile photos
│       └── reports/        # Medical documents
├── .env                    # Environment variables (gitignored)
├── .env.example            # Environment template
├── composer.json
└── README.md
```

---

## 🚀 Installation — Laragon (Windows)

### Prerequisites

- [Laragon](https://laragon.org/download/) (includes PHP, MySQL, Apache, Composer)
- Git

### Step-by-Step

#### 1. Install Laragon

Download and run the Laragon installer. Use the **Full** or **Portable** version.

#### 2. Start Laragon Services

Open Laragon → click **"Start All"**. Apache and MySQL should turn green.

#### 3. Clone the Project

Open Laragon's terminal (**Menu → Terminal**) or use Git Bash:

```bash
cd C:/laragon/www
git clone <repo-url> medicase
cd medicase
```

Or manually download and extract the zip into `C:/laragon/www/medicase`.

#### 4. Install PHP Dependencies

```bash
composer install
```

If Composer is not recognized, use Laragon's **Menu → PHP → Composer** or restart the terminal.

#### 5. Configure Environment

Copy the environment template:

```bash
cp .env.example .env
```

Or create `.env` manually:

```
OPENROUTER_API_KEY=sk-or-v1-your-key-here
```

> Get a free API key at [openrouter.ai/keys](https://openrouter.ai/keys).

#### 6. Create the Database

- Click Laragon's **"Database"** button to open HeidiSQL (or Adminer)
- Create a new database named `medicase`
  - **Charset:** `utf8mb4`
  - **Collation:** `utf8mb4_unicode_ci`
- Or run in Laragon's terminal:
  ```bash
  mysql -u root -e "CREATE DATABASE medicase CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
  ```

#### 7. Import the Schema (Tables)

```bash
mysql -u root medicase < database/schema.sql
```

This creates all 26 tables and inserts base data (admin account, specializations, services).

#### 8. Seed Demo Data (Rich Sample Data)

Run the PHP seeder to populate patients, doctors, appointments, records, and more:

```bash
php database/seed.php
```

> Requires `composer install` to have been run first.

**What gets seeded:**

| Data | Details |
|------|---------|
| **Admin account** | `admin@medicase.com` / `password` |
| **Doctors** (4) | Ahmed Hassan (Cardiology), Maria Santos (Neurology), Pierre Dubois (Pediatrics), Wei Li (Orthopedics) — all password `password` |
| **Patients** (5) | Sarah Johnson, Mike Chen, Emma Davis, James Wilson, Olivia Brown — all password `password` |
| **Specializations** (8) | Cardiology, Neurology, Pediatrics, Orthopedics, Dermatology, Ophthalmology, Psychiatry, General Medicine |
| **Appointments** (25) | Mixed statuses (pending, confirmed, completed, cancelled) across patients and doctors |
| **Medical Records** (20) | Diagnoses, symptoms, and notes per patient |
| **Prescriptions** (15) | Medications, dosages, and instructions per patient |
| **Notifications** (15) | Sample in-app notifications for users |
| **Services** (4) | Easy Appointments, Care Coordination, AI Health Assistant, Health Records |

#### 9. Configure Database Connection

Edit **`config/database.php`**:

```php
return [
    'host'     => '127.0.0.1',
    'port'     => 3306,
    'dbname'   => 'medicase',
    'username' => 'root',
    'password' => '',
    'charset'  => 'utf8mb4',
];
```

Laragon's MySQL has no password by default. Keep `'password' => ''`.

#### 10. Configure App URL

Edit **`config/app.php`**:

```php
return [
    'name'  => 'Medicase',
    'url'   => 'http://medicase.test',
    'env'   => 'development',
    'debug' => true,
];
```

#### 11. Add the Project URL (Laragon Auto-Virtual-Host)

Laragon automatically serves folders from `C:/laragon/www` as `http://folder-name.test`.

Open your browser and visit:

```
http://medicase.test
```

If it doesn't resolve, restart Apache in Laragon or add manually to `C:/Windows/System32/drivers/etc/hosts`:

```
127.0.0.1  medicase.test
```

#### 12. Login

| Role      | Email                  | Password   |
|-----------|------------------------|------------|
| Admin     | admin@medicase.com     | password   |

- **Admin Dashboard:** http://medicase.test/admin/dashboard
- **Register a new patient:** http://medicase.test/register

---

### 🛠️ Troubleshooting Laragon

**"403 Forbidden" or "404 Not Found"**
- Make sure the `.htaccess` file exists in `public/`
- Enable `mod_rewrite` in Apache: **Laragon Menu → Apache → mod_rewrite**

**"PDO Exception — could not find driver"**
- Enable `pdo_mysql` extension: **Laragon Menu → PHP → Extensions → php_pdo_mysql**

**Blank white page**
- Enable error display in `config/app.php`: set `'debug' => true`

**Database connection refused**
- Make sure MySQL is running (green indicator in Laragon)
- Check `config/database.php` — Laragon MySQL uses `127.0.0.1:3306` with no password

---

## 🚀 Alternative Installation — DDEV

```bash
git clone <repo-url> medicase
cd medicase
ddev start
ddev composer install
ddev mysql < database/schema.sql
open https://medicase.ddev.site
```

---

## 🚀 Alternative Installation — PHP Built-in Server

```bash
git clone <repo-url> medicase
cd medicase
composer install
mysql -u root -p -e "CREATE DATABASE medicase CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p medicase < database/schema.sql
# Edit config/database.php with your credentials
php -S localhost:8000 -t public
# Visit http://localhost:8000
```

---

## ⚙️ Configuration

### OpenRouter AI

The chatbot uses OpenRouter to query various LLMs. Configure via the **Admin Panel → AI Settings** or via `.env`:

```
OPENROUTER_API_KEY=sk-or-v1-your-key-here
```

Free models available:
- `openai/gpt-oss-120b:free` (default)
- `google/gemini-2.0-flash-001`
- `meta-llama/llama-3.3-70b-instruct`

### RAG Knowledge Base

The AI can reference custom documents. Add them in **Admin Panel → AI Settings → Knowledge Base Documents**. When a user asks a question, the AI searches these documents for relevant context before responding.

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
| `/newsletter` | POST | — | Email subscription |
| `/login` | GET/POST | Guest | Authentication |
| `/register` | GET/POST | Guest | Registration |
| `/forgot-password` | GET/POST | Guest | Password reset |
| `/patient/*` | * | Patient | Patient dashboard |
| `/doctor/*` | * | Doctor | Doctor dashboard |
| `/admin/*` | * | Admin | Administration |
| `/admin/specializations` | GET/POST | Admin | Specialization CRUD |
| `/admin/ai-settings` | GET/POST | Admin | AI configuration |
| `/admin/rag-documents` | POST | Admin | RAG document management |
| `/api/ai/chat` | POST | Auth | AI chat (authenticated) |
| `/api/ai/public-chat` | POST | — | AI chat (public widget) |
| `/api/ai/symptoms` | POST | Auth | Symptom analysis |

---

## 🧩 Database Schema

**26 tables** with full foreign key relationships:

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
- **settings** — Key-value configuration (API keys, model settings)
- **rag_documents** — AI knowledge base documents
- **blog_posts** — Public health articles
- **blog_categories** — Blog taxonomy
- **services** — Offered medical services
- **contacts** — Contact form submissions
- **subscribers** — Newsletter emails
- **password_resets** — Password recovery
- **audit_logs** — Activity tracking

---

## 🔒 Security

- **CSRF Protection** — Token validation on all POST endpoints
- **XSS Prevention** — `htmlspecialchars()` on all output
- **SQL Injection** — PDO prepared statements throughout
- **Password Hashing** — bcrypt via `password_hash()`
- **Role Middleware** — Route-level access control (admin/doctor/patient/guest)
- **File Upload** — Extension & size validation
- **Session** — Server-side session management with `session_regenerate_id()`
- **IDOR Prevention** — Ownership checks on all role-scoped resources

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
# Start PHP built-in server
php -S 0.0.0.0:8080 -t public

# Or with Composer script (if configured)
composer serve
```

---

## 📄 License

Proprietary — All rights reserved.

---

## 🙏 Acknowledgements

- [PHPMailer](https://github.com/PHPMailer/PHPMailer) — Email handling
- [Font Awesome](https://fontawesome.com) — Icon library
- [Inter](https://rsms.me/inter/) — Typeface
- [Laragon](https://laragon.org) — Windows development environment
- [OpenRouter](https://openrouter.ai) — Multi-model AI API
