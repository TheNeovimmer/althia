<?php
namespace Tests\Support;

use App\Core\Auth;
use App\Core\Database;

class TestSeeder
{
    private \PDO $db;

    public function __construct()
    {
        $reflection = new \ReflectionClass(Database::class);
        $instanceProp = $reflection->getProperty('instance');
        $instance = $instanceProp->getValue(null);

        $pdoProp = $reflection->getProperty('pdo');
        $this->db = $pdoProp->getValue($instance);
    }

    public function createAdmin(array $overrides = []): array
    {
        return $this->createUser(array_merge(['role' => 'admin', 'email' => 'admin@medicase.test'], $overrides));
    }

    public function createDoctorUser(array $overrides = []): array
    {
        $user = $this->createUser(array_merge(['role' => 'doctor', 'email' => 'doctor@medicase.test'], $overrides));

        $specializationId = $this->ensureSpecialization();
        $stmt = $this->db->prepare(
            "INSERT INTO doctors (user_id, specialization_id, license_number, bio, is_verified) VALUES (?, ?, ?, ?, 1)"
        );
        $stmt->execute([$user['id'], $specializationId, 'LIC-' . uniqid(), 'Test doctor']);

        return $user;
    }

    public function createPatientUser(array $overrides = []): array
    {
        $user = $this->createUser(array_merge(['role' => 'patient', 'email' => 'patient@medicase.test'], $overrides));

        $stmt = $this->db->prepare(
            "INSERT INTO patients (user_id, date_of_birth, gender) VALUES (?, ?, ?)"
        );
        $stmt->execute([$user['id'], '1990-01-01', 'male']);

        return $user;
    }

    public function createUser(array $data): array
    {
        $defaults = [
            'email' => 'user@medicase.test',
            'password' => Auth::hashPassword('password'),
            'role' => 'patient',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '+212600000000',
            'is_active' => 1,
        ];

        $data = array_merge($defaults, $data);
        $stmt = $this->db->prepare(
            "INSERT INTO users (email, password, role, first_name, last_name, phone, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$data['email'], $data['password'], $data['role'], $data['first_name'], $data['last_name'], $data['phone'], $data['is_active']]);

        $data['id'] = (int) $this->db->lastInsertId();
        return $data;
    }

    public function createAppointment(int $patientId, int $doctorId, array $overrides = []): int
    {
        $defaults = [
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'appointment_date' => date('Y-m-d', strtotime('+1 day')),
            'appointment_time' => '10:00:00',
            'status' => 'pending',
            'type' => 'in-person',
            'reason' => 'Checkup',
        ];

        $data = array_merge($defaults, $overrides);
        $stmt = $this->db->prepare(
            "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status, type, reason) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$data['patient_id'], $data['doctor_id'], $data['appointment_date'], $data['appointment_time'], $data['status'], $data['type'], $data['reason']]);

        return (int) $this->db->lastInsertId();
    }

    public function createBlogPost(int $authorId, array $overrides = []): int
    {
        $categoryId = $this->ensureBlogCategory();

        $defaults = [
            'author_id' => $authorId,
            'category_id' => $categoryId,
            'title' => 'Test Post',
            'slug' => 'test-post-' . uniqid(),
            'excerpt' => 'Excerpt',
            'content' => 'Content',
            'tags' => '[]',
            'is_published' => 1,
            'published_at' => date('Y-m-d H:i:s'),
        ];

        $data = array_merge($defaults, $overrides);
        $stmt = $this->db->prepare(
            "INSERT INTO blog_posts (author_id, category_id, title, slug, excerpt, content, tags, is_published, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$data['author_id'], $data['category_id'], $data['title'], $data['slug'], $data['excerpt'], $data['content'], $data['tags'], $data['is_published'], $data['published_at']]);

        return (int) $this->db->lastInsertId();
    }

    private function ensureSpecialization(): int
    {
        $stmt = $this->db->query("SELECT id FROM specializations LIMIT 1");
        $existing = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($existing) {
            return (int) $existing['id'];
        }

        $this->db->prepare("INSERT INTO specializations (name, slug) VALUES (?, ?)")->execute(['General', 'general']);
        return (int) $this->db->lastInsertId();
    }

    private function ensureBlogCategory(): int
    {
        $stmt = $this->db->query("SELECT id FROM blog_categories LIMIT 1");
        $existing = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($existing) {
            return (int) $existing['id'];
        }

        $this->db->prepare("INSERT INTO blog_categories (name, slug) VALUES (?, ?)")->execute(['General', 'general']);
        return (int) $this->db->lastInsertId();
    }

    public function truncateAll(): void
    {
        $this->db->exec("PRAGMA foreign_keys = OFF");
        $tables = $this->db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $this->db->exec("DELETE FROM {$table}");
        }
        $this->db->exec("PRAGMA foreign_keys = ON");
    }
}
