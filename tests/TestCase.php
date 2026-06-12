<?php
namespace Tests;

use App\Core\Database;
use PDO;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected static ?PDO $pdo = null;
    protected static bool $schemaLoaded = false;
    private static int $userCounter = 0;

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->initializeTestDatabase();
        $this->truncateAllTables();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->resetSession();
    }

    private function initializeTestDatabase(): void
    {
        if (self::$schemaLoaded) {
            return;
        }

        $reflection = new \ReflectionClass(Database::class);
        $instanceProp = $reflection->getProperty('instance');

        $db = $reflection->newInstanceWithoutConstructor();
        $pdoProp = $reflection->getProperty('pdo');

        self::$pdo = new \PDO('sqlite::memory:', null, null, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        self::$pdo->exec("PRAGMA foreign_keys = ON");

        // Register MySQL-compatible functions for SQLite
        $pdo = self::$pdo;
        $createFn = function (string $name, callable $fn, int $argCount) use ($pdo) {
            if ($pdo instanceof \PDO) {
                @$pdo->sqliteCreateFunction($name, $fn, $argCount);
            }
        };
        $createFn('NOW', fn() => date('Y-m-d H:i:s'), 0);
        $createFn('CURDATE', fn() => date('Y-m-d'), 0);
        $createFn('DATE_ADD', function ($date, $interval) {
            return date('Y-m-d H:i:s', strtotime($date . ' ' . $interval));
        }, 2);

        $pdoProp->setValue($db, self::$pdo);
        $instanceProp->setValue(null, $db);

        $schema = file_get_contents(__DIR__ . '/Schema.sql');
        self::$pdo->exec($schema);

        self::$schemaLoaded = true;
    }

    private function truncateAllTables(): void
    {
        $tables = self::$pdo->query(
            "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"
        )->fetchAll(\PDO::FETCH_COLUMN);

        self::$pdo->exec("PRAGMA foreign_keys = OFF");
        foreach ($tables as $table) {
            self::$pdo->exec("DELETE FROM {$table}");
        }
        self::$pdo->exec("PRAGMA foreign_keys = ON");
    }

    protected function resetSession(): void
    {
        $_SESSION = [];
        $_SERVER = [];
        $_POST = [];
        $_GET = [];
    }

    protected function getDb(): PDO
    {
        return self::$pdo;
    }

    protected function uniqueEmail(string $prefix = 'user'): string
    {
        self::$userCounter++;
        return $prefix . self::$userCounter . '@test.com';
    }

    protected function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $stmt = $this->getDb()->prepare("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})");
        $stmt->execute(array_values($data));
        return (int) $this->getDb()->lastInsertId();
    }

    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    protected function createUser(array $overrides = []): array
    {
        $defaults = [
            'email' => $this->uniqueEmail(),
            'password' => \App\Core\Auth::hashPassword('password123'),
            'role' => 'patient',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+212600000000',
            'is_active' => 1,
        ];

        $data = array_merge($defaults, $overrides);
        $id = $this->insert('users', [
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'is_active' => $data['is_active'],
        ]);

        return array_merge($data, ['id' => $id]);
    }

    protected function createPatient(array $userOverrides = [], array $patientOverrides = []): array
    {
        $email = $this->uniqueEmail('patient');
        $user = $this->createUser(array_merge(['role' => 'patient', 'email' => $email], $userOverrides));

        $defaults = [
            'user_id' => $user['id'],
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'blood_type' => 'A+',
            'weight' => 75.0,
            'height' => 175.0,
            'address' => '123 Test St',
            'emergency_contact_name' => 'Emergency Contact',
            'emergency_contact_phone' => '+212611111111',
        ];

        $data = array_merge($defaults, $patientOverrides);
        $patientId = $this->insert('patients', [
            'user_id' => $data['user_id'],
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'blood_type' => $data['blood_type'],
            'weight' => $data['weight'],
            'height' => $data['height'],
            'address' => $data['address'],
            'emergency_contact_name' => $data['emergency_contact_name'],
            'emergency_contact_phone' => $data['emergency_contact_phone'],
        ]);

        return [
            'user' => $user,
            'patient' => array_merge($data, ['id' => $patientId]),
        ];
    }

    protected function createDoctor(array $userOverrides = [], array $doctorOverrides = []): array
    {
        $specializationId = $this->ensureSpecialization();
        $email = $this->uniqueEmail('doctor');
        $user = $this->createUser(array_merge(['role' => 'doctor', 'email' => $email], $userOverrides));

        $defaults = [
            'user_id' => $user['id'],
            'specialization_id' => $specializationId,
            'license_number' => 'LIC-12345',
            'bio' => 'Experienced doctor',
            'education' => 'Medical School',
            'experience_years' => 10,
            'consultation_fee' => 200.00,
            'available_days' => json_encode(['Monday', 'Tuesday', 'Wednesday']),
            'available_hours' => json_encode(['09:00', '10:00', '11:00']),
            'is_verified' => 1,
        ];

        $data = array_merge($defaults, $doctorOverrides);
        $doctorId = $this->insert('doctors', [
            'user_id' => $data['user_id'],
            'specialization_id' => $data['specialization_id'],
            'license_number' => $data['license_number'],
            'bio' => $data['bio'],
            'education' => $data['education'],
            'experience_years' => $data['experience_years'],
            'consultation_fee' => $data['consultation_fee'],
            'available_days' => $data['available_days'],
            'available_hours' => $data['available_hours'],
            'is_verified' => $data['is_verified'],
        ]);

        return [
            'user' => $user,
            'doctor' => array_merge($data, ['id' => $doctorId]),
        ];
    }

    protected function ensureSpecialization(): int
    {
        $existing = $this->fetchOne("SELECT id FROM specializations LIMIT 1");
        if ($existing) {
            return (int) $existing['id'];
        }

        return $this->insert('specializations', [
            'name' => 'General Medicine',
            'slug' => 'general-medicine',
            'description' => 'General medical practice',
        ]);
    }

    protected function createAppointment(array $overrides = []): array
    {
        $patient = $this->createPatient();
        $doctor = $this->createDoctor();

        $defaults = [
            'patient_id' => $patient['patient']['id'],
            'doctor_id' => $doctor['doctor']['id'],
            'appointment_date' => date('Y-m-d', strtotime('+1 day')),
            'appointment_time' => '10:00:00',
            'status' => 'pending',
            'type' => 'in-person',
            'reason' => 'Regular checkup',
        ];

        $data = array_merge($defaults, $overrides);
        $id = $this->insert('appointments', [
            'patient_id' => $data['patient_id'],
            'doctor_id' => $data['doctor_id'],
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'status' => $data['status'],
            'type' => $data['type'],
            'reason' => $data['reason'],
        ]);

        return array_merge($data, [
            'id' => $id,
            'patient' => $patient,
            'doctor' => $doctor,
        ]);
    }

    protected function createBlogPost(array $overrides = []): array
    {
        $email = $this->uniqueEmail('admin');
        $user = $this->createUser(['role' => 'admin', 'email' => $email]);

        $existingCategory = $this->fetchOne("SELECT id FROM blog_categories LIMIT 1");
        if (!$existingCategory) {
            $categoryId = $this->insert('blog_categories', ['name' => 'Health Tips', 'slug' => 'health-tips']);
        } else {
            $categoryId = (int) $existingCategory['id'];
        }

        $defaults = [
            'author_id' => $user['id'],
            'category_id' => $categoryId,
            'title' => 'Test Blog Post',
            'slug' => 'test-blog-post',
            'excerpt' => 'A test excerpt',
            'content' => 'Full content of the test blog post.',
            'tags' => json_encode(['test', 'blog']),
            'is_published' => 1,
            'published_at' => date('Y-m-d H:i:s'),
        ];

        $data = array_merge($defaults, $overrides);
        $id = $this->insert('blog_posts', [
            'author_id' => $data['author_id'],
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'slug' => $data['slug'],
            'excerpt' => $data['excerpt'],
            'content' => $data['content'],
            'tags' => $data['tags'],
            'is_published' => $data['is_published'],
            'published_at' => $data['published_at'],
        ]);

        return array_merge($data, ['id' => $id]);
    }

    protected function simulateLogin(int $userId, string $role, string $name = 'John Doe'): void
    {
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $role;
        $_SESSION['user_name'] = $name;
    }

    protected function simulateRequest(string $method, array $post = [], array $server = []): void
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_POST = $post;
        foreach ($server as $key => $value) {
            $_SERVER[$key] = $value;
        }
    }
}
