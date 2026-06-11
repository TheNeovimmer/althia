# Medicase Full-Stack Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development to implement this plan task-by-task.

**Goal:** Build complete Medicase healthcare platform with MVC PHP, responsive frontend, role-based dashboards, and full CRUD

**Architecture:** Front Controller PHP MVC with Service Layer. Mobile-first CSS with custom properties. Role-based JWT auth. All routes go through public/index.php.

**Tech Stack:** PHP 8.4, MySQL/MariaDB, vanilla CSS/JS, PHPMailer, JWT

---

### Task 1: Database Schema

**Files:**
- Create: `database/schema.sql`

- [ ] **Write full schema**

```sql
-- Medicase Database Schema
CREATE DATABASE IF NOT EXISTS medicase CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE medicase;

-- Users (base for all roles)
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'doctor', 'patient') NOT NULL DEFAULT 'patient',
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    avatar VARCHAR(255),
    email_verified_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Password resets
CREATE TABLE password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token VARCHAR(100) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Specializations
CREATE TABLE specializations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Doctors
CREATE TABLE doctors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    specialization_id INT UNSIGNED,
    license_number VARCHAR(50) UNIQUE,
    bio TEXT,
    education TEXT,
    experience_years TINYINT UNSIGNED,
    consultation_fee DECIMAL(10,2) DEFAULT 0,
    available_days JSON,
    available_hours JSON,
    rating DECIMAL(2,1) DEFAULT 0,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (specialization_id) REFERENCES specializations(id) ON DELETE SET NULL
);

-- Patients
CREATE TABLE patients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    blood_type VARCHAR(5),
    weight DECIMAL(5,2),
    height DECIMAL(5,2),
    address TEXT,
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Medical Records
CREATE TABLE medical_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT UNSIGNED NOT NULL,
    doctor_id INT UNSIGNED NOT NULL,
    diagnosis TEXT,
    symptoms TEXT,
    notes TEXT,
    record_date DATE NOT NULL,
    is_private BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- Medical Reports (lab results, imaging, etc.)
CREATE TABLE medical_reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT UNSIGNED NOT NULL,
    doctor_id INT UNSIGNED,
    record_id INT UNSIGNED,
    title VARCHAR(255) NOT NULL,
    type ENUM('lab', 'imaging', 'pathology', 'other') DEFAULT 'other',
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT UNSIGNED,
    notes TEXT,
    report_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE SET NULL,
    FOREIGN KEY (record_id) REFERENCES medical_records(id) ON DELETE SET NULL
);

-- Prescriptions
CREATE TABLE prescriptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT UNSIGNED NOT NULL,
    doctor_id INT UNSIGNED NOT NULL,
    record_id INT UNSIGNED,
    medication_name VARCHAR(255) NOT NULL,
    dosage VARCHAR(100) NOT NULL,
    frequency VARCHAR(100) NOT NULL,
    duration VARCHAR(100),
    instructions TEXT,
    start_date DATE NOT NULL,
    end_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    FOREIGN KEY (record_id) REFERENCES medical_records(id) ON DELETE SET NULL
);

-- Appointments
CREATE TABLE appointments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT UNSIGNED NOT NULL,
    doctor_id INT UNSIGNED NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    type ENUM('in-person', 'video', 'phone') DEFAULT 'in-person',
    reason TEXT,
    notes TEXT,
    cancelled_at TIMESTAMP NULL,
    cancellation_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- Notifications
CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Messages
CREATE TABLE messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id INT UNSIGNED NOT NULL,
    receiver_id INT UNSIGNED NOT NULL,
    subject VARCHAR(255),
    body TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    parent_id INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES messages(id) ON DELETE SET NULL
);

-- Groups (for doctor collaboration)
CREATE TABLE groups_ (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE group_members (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES groups_(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (group_id, user_id)
);

-- AI Conversations
CREATE TABLE ai_conversations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE ai_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT UNSIGNED NOT NULL,
    role ENUM('user', 'assistant', 'system') NOT NULL,
    content TEXT NOT NULL,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES ai_conversations(id) ON DELETE CASCADE
);

-- Blog
CREATE TABLE blog_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE blog_posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT,
    content LONGTEXT,
    featured_image VARCHAR(255),
    tags JSON,
    is_published BOOLEAN DEFAULT FALSE,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL
);

-- Services
CREATE TABLE services (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    icon VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Reviews / Testimonials
CREATE TABLE reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT UNSIGNED NOT NULL,
    doctor_id INT UNSIGNED,
    rating TINYINT UNSIGNED NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE SET NULL
);

-- Audit Logs
CREATE TABLE audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT UNSIGNED,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Settings
CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(100) NOT NULL UNIQUE,
    value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contacts
CREATE TABLE contacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Subscribers
CREATE TABLE subscribers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default specializations
INSERT INTO specializations (name, description) VALUES
('Cardiology', 'Heart and cardiovascular system'),
('Neurology', 'Brain and nervous system'),
('Pediatrics', 'Children health care'),
('Orthopedics', 'Bones and joints'),
('Dermatology', 'Skin conditions'),
('Ophthalmology', 'Eye care'),
('Psychiatry', 'Mental health'),
('General Practice', 'Primary care');

-- Insert default admin
INSERT INTO users (email, password, role, first_name, last_name, is_active, email_verified_at)
VALUES ('admin@medicase.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin', 'Medicase', TRUE, NOW());
-- Password: password
```

### Task 2: Core Framework — Router, Database, Controller, Model, Auth

**Files:**
- Create: `app/core/Router.php`
- Create: `app/core/Database.php`
- Create: `app/core/Controller.php`
- Create: `app/core/Model.php`
- Create: `app/core/Auth.php`
- Create: `app/core/Validator.php`
- Create: `app/core/Middleware.php`

- [ ] **Create Router.php**

```php
<?php
namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middleware = [];

    public function get(string $path, string $handler, array $middleware = []): void
    {
        $this->routes['GET'][] = ['path' => $path, 'handler' => $handler, 'middleware' => $middleware];
    }

    public function post(string $path, string $handler, array $middleware = []): void
    {
        $this->routes['POST'][] = ['path' => $path, 'handler' => $handler, 'middleware' => $middleware];
    }

    public function put(string $path, string $handler, array $middleware = []): void
    {
        $this->routes['PUT'][] = ['path' => $path, 'handler' => $handler, 'middleware' => $middleware];
    }

    public function delete(string $path, string $handler, array $middleware = []): void
    {
        $this->routes['DELETE'][] = ['path' => $path, 'handler' => $handler, 'middleware' => $middleware];
    }

    public function addMiddleware(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->middleware as $mw) {
            call_user_func($mw);
        }

        if (!isset($this->routes[$method])) {
            $this->notFound();
            return;
        }

        foreach ($this->routes[$method] as $route) {
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                foreach ($route['middleware'] as $mw) {
                    $mw();
                }

                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                [$controller, $method] = explode('@', $route['handler']);
                $controller = 'App\\Controllers\\' . $controller;
                $instance = new $controller();
                call_user_func_array([$instance, $method], $params);
                return;
            }
        }

        $this->notFound();
    }

    private function notFound(): void
    {
        http_response_code(404);
        $controller = new \App\Controllers\HomeController();
        $controller->notFound();
    }
}
```

- [ ] **Create Database.php**

```php
<?php
namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';
        try {
            $this->pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch() ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function insert(string $sql, array $params = []): int
    {
        $this->query($sql, $params);
        return (int) $this->pdo->lastInsertId();
    }

    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollback(): void
    {
        $this->pdo->rollBack();
    }
}
```

- [ ] **Create Controller.php**

```php
<?php
namespace App\Core;

class Controller
{
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }
        require __DIR__ . '/../views/layouts/header.php';
        require $viewPath;
        require __DIR__ . '/../views/layouts/footer.php';
    }

    protected function renderRaw(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }
        require $viewPath;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function back(): void
    {
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function getBody(): array
    {
        $body = [];
        if ($this->isPost()) {
            $body = $_POST;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if (is_array($input)) {
            $body = array_merge($body, $input);
        }
        return $body;
    }
}
```

- [ ] **Create Model.php**

```php
<?php
namespace App\Core;

abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';

    public static function all(): array
    {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM " . static::$table . " ORDER BY " . static::$primaryKey . " DESC");
    }

    public static function find(int $id): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?",
            [$id]
        );
    }

    public static function where(string $column, $value): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM " . static::$table . " WHERE {$column} = ?",
            [$value]
        );
    }

    public static function whereFirst(string $column, $value): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM " . static::$table . " WHERE {$column} = ?",
            [$value]
        );
    }

    public static function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $db = Database::getInstance();
        return $db->insert(
            "INSERT INTO " . static::$table . " ({$columns}) VALUES ({$placeholders})",
            array_values($data)
        );
    }

    public static function update(int $id, array $data): int
    {
        $sets = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $db = Database::getInstance();
        return $db->execute(
            "UPDATE " . static::$table . " SET {$sets} WHERE " . static::$primaryKey . " = ?",
            array_merge(array_values($data), [$id])
        );
    }

    public static function delete(int $id): int
    {
        $db = Database::getInstance();
        return $db->execute(
            "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?",
            [$id]
        );
    }

    public static function count(): int
    {
        $db = Database::getInstance();
        return (int) $db->fetch("SELECT COUNT(*) as count FROM " . static::$table)['count'];
    }

    public static function paginate(int $page = 1, int $perPage = 10): array
    {
        $db = Database::getInstance();
        $offset = ($page - 1) * $perPage;
        $total = static::count();
        $items = $db->fetchAll(
            "SELECT * FROM " . static::$table . " ORDER BY " . static::$primaryKey . " DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => ceil($total / $perPage),
        ];
    }
}
```

- [ ] **Create Auth.php**

```php
<?php
namespace App\Core;

class Auth
{
    private static ?array $user = null;

    public static function login(string $email, string $password): bool
    {
        $db = Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            self::$user = $user;
            return true;
        }
        return false;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id'], $_SESSION['user_role'], $_SESSION['user_name']);
        self::$user = null;
        session_destroy();
    }

    public static function user(): ?array
    {
        if (self::$user === null && isset($_SESSION['user_id'])) {
            $db = Database::getInstance();
            self::$user = $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
        }
        return self::$user;
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function isDoctor(): bool
    {
        return self::role() === 'doctor';
    }

    public static function isPatient(): bool
    {
        return self::role() === 'patient';
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }

    public static function requireRole(string $role): void
    {
        self::requireAuth();
        if (self::role() !== $role) {
            header('Location: /');
            exit;
        }
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
```

- [ ] **Create Validator.php**

```php
<?php
namespace App\Core;

class Validator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        foreach ($rules as $field => $ruleSet) {
            $ruleList = is_array($ruleSet) ? $ruleSet : explode('|', $ruleSet);
            $value = $data[$field] ?? null;
            foreach ($ruleList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }
                $methodName = 'rule' . ucfirst($rule);
                if (method_exists($this, $methodName)) {
                    $this->$methodName($field, $value, $params, $data);
                }
            }
        }
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[] = str_replace(':field', $field, $message);
    }

    private function ruleRequired(string $field, $value, array $params, array $data): void
    {
        if ($value === null || $value === '') {
            $this->addError($field, ':field is required');
        }
    }

    private function ruleEmail(string $field, $value, array $params, array $data): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, ':field must be a valid email');
        }
    }

    private function ruleMin(string $field, $value, array $params, array $data): void
    {
        if ($value && strlen($value) < (int)$params[0]) {
            $this->addError($field, ":field must be at least {$params[0]} characters");
        }
    }

    private function ruleMax(string $field, $value, array $params, array $data): void
    {
        if ($value && strlen($value) > (int)$params[0]) {
            $this->addError($field, ":field must not exceed {$params[0]} characters");
        }
    }

    private function ruleConfirmed(string $field, $value, array $params, array $data): void
    {
        $confirmationField = $field . '_confirmation';
        if ($value !== ($data[$confirmationField] ?? null)) {
            $this->addError($field, ':field confirmation does not match');
        }
    }

    private function ruleUnique(string $field, $value, array $params, array $data): void
    {
        if ($value) {
            [$table, $column] = $params;
            $db = Database::getInstance();
            $existing = $db->fetch("SELECT id FROM {$table} WHERE {$column} = ?", [$value]);
            if ($existing) {
                $this->addError($field, ':field already exists');
            }
        }
    }

    private function ruleNumeric(string $field, $value, array $params, array $data): void
    {
        if ($value && !is_numeric($value)) {
            $this->addError($field, ':field must be numeric');
        }
    }
}
```

- [ ] **Create Middleware.php**

```php
<?php
namespace App\Core;

class Middleware
{
    public static function auth(): callable
    {
        return function () {
            Auth::requireAuth();
        };
    }

    public static function role(string $role): callable
    {
        return function () use ($role) {
            Auth::requireRole($role);
        };
    }

    public static function guest(): callable
    {
        return function () {
            if (Auth::check()) {
                header('Location: /');
                exit;
            }
        };
    }
}
```

### Task 3: Configuration and Front Controller

**Files:**
- Create: `config/app.php`
- Create: `config/database.php`
- Create: `public/index.php`
- Create: `public/.htaccess`

- [ ] **Create config/app.php**

```php
<?php
return [
    'name' => 'Medicase',
    'url' => 'http://oumaima.ddev.site',
    'env' => 'development',
    'debug' => true,
];
```

- [ ] **Create config/database.php**

```php
<?php
return [
    'host' => getenv('DB_HOST') ?: 'db',
    'dbname' => getenv('DB_NAME') ?: 'medicase',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: 'root',
    'charset' => 'utf8mb4',
];
```

- [ ] **Create public/index.php** (front controller)

```php
<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Auth;

$router = new Router();

// Public routes
$router->get('/', 'HomeController@index');
$router->get('/about', 'HomeController@about');
$router->get('/services', 'HomeController@services');
$router->get('/experts', 'HomeController@experts');
$router->get('/blog', 'BlogController@index');
$router->get('/blog/{slug}', 'BlogController@show');
$router->get('/contact', 'HomeController@contact');
$router->post('/contact', 'HomeController@sendContact');
$router->get('/pricing', 'HomeController@pricing');

// Auth routes
$router->get('/login', 'AuthController@loginForm', [\App\Core\Middleware::guest()]);
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@registerForm', [\App\Core\Middleware::guest()]);
$router->post('/register', 'AuthController@register');
$router->get('/forgot-password', 'AuthController@forgotForm', [\App\Core\Middleware::guest()]);
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->post('/logout', 'AuthController@logout', [\App\Core\Middleware::auth()]);

// Patient routes
$router->get('/patient/dashboard', 'PatientController@dashboard', [\App\Core\Middleware::role('patient')]);
$router->get('/patient/profile', 'PatientController@profile', [\App\Core\Middleware::role('patient')]);
$router->post('/patient/profile', 'PatientController@updateProfile', [\App\Core\Middleware::role('patient')]);
$router->get('/patient/records', 'PatientController@records', [\App\Core\Middleware::role('patient')]);
$router->post('/patient/upload', 'PatientController@uploadReport', [\App\Core\Middleware::role('patient')]);
$router->get('/patient/appointments', 'PatientController@appointments', [\App\Core\Middleware::role('patient')]);
$router->post('/patient/appointments', 'PatientController@createAppointment', [\App\Core\Middleware::role('patient')]);
$router->post('/patient/appointments/{id}/cancel', 'PatientController@cancelAppointment', [\App\Core\Middleware::role('patient')]);
$router->get('/patient/prescriptions', 'PatientController@prescriptions', [\App\Core\Middleware::role('patient')]);

// Doctor routes
$router->get('/doctor/dashboard', 'DoctorController@dashboard', [\App\Core\Middleware::role('doctor')]);
$router->get('/doctor/patients', 'DoctorController@patients', [\App\Core\Middleware::role('doctor')]);
$router->get('/doctor/patient/{id}', 'DoctorController@patientDetail', [\App\Core\Middleware::role('doctor')]);
$router->post('/doctor/prescription', 'DoctorController@createPrescription', [\App\Core\Middleware::role('doctor')]);
$router->post('/doctor/report', 'DoctorController@createReport', [\App\Core\Middleware::role('doctor')]);
$router->get('/doctor/appointments', 'DoctorController@appointments', [\App\Core\Middleware::role('doctor')]);
$router->post('/doctor/appointments/{id}/update', 'DoctorController@updateAppointment', [\App\Core\Middleware::role('doctor')]);

// Admin routes
$router->get('/admin/dashboard', 'AdminController@dashboard', [\App\Core\Middleware::role('admin')]);
$router->get('/admin/users', 'AdminController@users', [\App\Core\Middleware::role('admin')]);
$router->post('/admin/users', 'AdminController@createUser', [\App\Core\Middleware::role('admin')]);
$router->post('/admin/users/{id}/update', 'AdminController@updateUser', [\App\Core\Middleware::role('admin')]);
$router->post('/admin/users/{id}/delete', 'AdminController@deleteUser', [\App\Core\Middleware::role('admin')]);
$router->get('/admin/doctors', 'AdminController@doctors', [\App\Core\Middleware::role('admin')]);
$router->post('/admin/doctors/{id}/verify', 'AdminController@verifyDoctor', [\App\Core\Middleware::role('admin')]);

// AI routes
$router->post('/api/ai/chat', 'AIController@chat', [\App\Core\Middleware::auth()]);
$router->post('/api/ai/symptoms', 'AIController@analyzeSymptoms', [\App\Core\Middleware::auth()]);

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$router->dispatch($method, $uri);
```

- [ ] **Create public/.htaccess**

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

### Task 4: Composer Setup and Autoloader

**Files:**
- Create: `composer.json`

- [ ] **Create composer.json**

```json
{
    "name": "medicase/app",
    "description": "Medicase Healthcare Platform",
    "type": "project",
    "require": {
        "php": ">=8.1",
        "phpmailer/phpmailer": "^6.9"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        },
        "files": [
            "app/core/helpers.php"
        ]
    },
    "scripts": {
        "serve": "php -S localhost:8000 -t public"
    }
}
```

- [ ] **Create app/core/helpers.php**

```php
<?php
function asset(string $path): string
{
    $config = require __DIR__ . '/../../config/app.php';
    return $config['url'] . '/assets/' . ltrim($path, '/');
}

function url(string $path = '/'): string
{
    $config = require __DIR__ . '/../../config/app.php';
    return $config['url'] . '/' . ltrim($path, '/');
}

function old(string $key, string $default = ''): string
{
    return $_SESSION['_old'][$key] ?? $default;
}

function error(string $key): ?string
{
    return $_SESSION['_errors'][$key] ?? null;
}

function hasError(string $key): bool
{
    return isset($_SESSION['_errors'][$key]);
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }
    $val = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $val;
}

function csrf_field(): string
{
    $token = $_SESSION['_token'] ?? bin2hex(random_bytes(32));
    $_SESSION['_token'] = $token;
    return '<input type="hidden" name="_token" value="' . $token . '">';
}

function verify_csrf(string $token): bool
{
    return isset($_SESSION['_token']) && hash_equals($_SESSION['_token'], $token);
}

function truncate(string $text, int $length = 100): string
{
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

function timeAgo(string $datetime): string
{
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    return date('M j, Y', $timestamp);
}
```

### Task 5: Complete CSS (style.css)

**Files:**
- Create: `public/assets/css/style.css`

Full responsive stylesheet spanning approximately 800+ lines covering:
- CSS variables (design tokens)
- Reset and base typography
- Navigation (fixed header, responsive hamburger, dropdowns)
- Hero section
- Service cards
- Stats bar
- AI chat widget
- Pricing cards
- Team/experts grid
- Blog cards
- Contact form
- Footer
- Auth pages (login, register)
- Dashboards (patient, doctor, admin)
- Tables, modals, form controls
- Buttons (5 variants with hover/active transitions)
- Badges, alerts, notifications
- Responsive breakpoints (mobile, tablet, desktop)
- Animations (fade-in, slide-up, pulse)

### Task 6: Complete Frontend Views

**Files to create:** All views listed in the spec design doc.

Each view file contains only the section-specific HTML (header/footer are in layouts). Views use `<?= $variable ?>` for dynamic data.

Sub-views per page type:
- **Layouts:** header.php (dynamic navbar based on role), footer.php
- **Auth:** login.php, register.php, forgot-password.php
- **Public:** home.php, about.php, services.php, experts.php, blog.php, blog-single.php, contact.php, pricing.php
- **Patient:** dashboard.php, profile.php, records.php, appointments.php, prescriptions.php
- **Doctor:** dashboard.php, patients.php, patient-detail.php, appointments.php, prescriptions.php
- **Admin:** dashboard.php, users.php, doctors.php

### Task 7: All Controllers

**Files to create:** All controllers specified in the design doc.

Each controller handles:
- Data fetching from models
- Passing data to views
- Form validation
- CRUD operations
- JSON responses for AJAX endpoints

### Task 8: All Models

**Files to create:** All models specified in the design doc.

Models extend `App\Core\Model` and set `$table` and `$primaryKey`.

### Task 9: JavaScript (main.js)

**Files:**
- Create: `public/assets/js/main.js`

Covers:
- Mobile hamburger menu toggle
- Navbar scroll effect (glass morphism on scroll)
- Form validation (client-side)
- Appointment booking modal
- AI chat interface
- Notification dropdown
- Dashboard chart initialization
- AJAX form submissions
- Flash message auto-dismiss
- Smooth scroll
- Password show/hide toggle

### Task 10: Services

**Files:**
- Create: `app/services/AIService.php`
- Create: `app/services/NotificationService.php`
- Create: `app/services/AppointmentService.php`
- Create: `app/services/FileService.php`
