<?php
namespace Tests\Unit\Models;

use App\Core\Auth;
use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_can_create_user(): void
    {
        $id = User::create([
            'email' => 'jane@example.com',
            'password' => Auth::hashPassword('secret123'),
            'role' => 'patient',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'phone' => '+212700000000',
            'is_active' => 1,
        ]);

        $this->assertIsInt($id);

        $user = User::find($id);
        $this->assertNotNull($user);
        $this->assertEquals('jane@example.com', $user['email']);
        $this->assertEquals('Jane', $user['first_name']);
        $this->assertEquals('Smith', $user['last_name']);
        $this->assertEquals('patient', $user['role']);
    }

    public function test_password_is_hashed(): void
    {
        $plainPassword = 'mySecretPassword123';
        $hashed = Auth::hashPassword($plainPassword);

        $this->assertNotEquals($plainPassword, $hashed);
        $this->assertTrue(password_verify($plainPassword, $hashed));
    }

    public function test_email_is_unique(): void
    {
        User::create([
            'email' => 'unique@example.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'patient',
            'first_name' => 'First',
            'last_name' => 'User',
            'is_active' => 1,
        ]);

        $this->expectException(\PDOException::class);
        User::create([
            'email' => 'unique@example.com',
            'password' => Auth::hashPassword('password456'),
            'role' => 'doctor',
            'first_name' => 'Second',
            'last_name' => 'User',
            'is_active' => 1,
        ]);
    }

    public function test_can_assign_role(): void
    {
        $adminId = User::create([
            'email' => 'admin@test.com',
            'password' => Auth::hashPassword('admin123'),
            'role' => 'admin',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'is_active' => 1,
        ]);

        $doctorId = User::create([
            'email' => 'doctor@test.com',
            'password' => Auth::hashPassword('doctor123'),
            'role' => 'doctor',
            'first_name' => 'Doc',
            'last_name' => 'Tor',
            'is_active' => 1,
        ]);

        $patientId = User::create([
            'email' => 'patient@test.com',
            'password' => Auth::hashPassword('patient123'),
            'role' => 'patient',
            'first_name' => 'Pat',
            'last_name' => 'Ient',
            'is_active' => 1,
        ]);

        $this->assertEquals('admin', User::find($adminId)['role']);
        $this->assertEquals('doctor', User::find($doctorId)['role']);
        $this->assertEquals('patient', User::find($patientId)['role']);
    }

    public function test_can_find_user_by_email(): void
    {
        User::create([
            'email' => 'findme@test.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'patient',
            'first_name' => 'Find',
            'last_name' => 'Me',
            'is_active' => 1,
        ]);

        $user = User::findByEmail('findme@test.com');
        $this->assertNotNull($user);
        $this->assertEquals('Find', $user['first_name']);

        $notFound = User::findByEmail('nonexistent@test.com');
        $this->assertNull($notFound);
    }

    public function test_can_get_users_by_role(): void
    {
        User::create([
            'email' => 'doc1@test.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'doctor',
            'first_name' => 'Doc',
            'last_name' => 'One',
            'is_active' => 1,
        ]);

        User::create([
            'email' => 'doc2@test.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'doctor',
            'first_name' => 'Doc',
            'last_name' => 'Two',
            'is_active' => 1,
        ]);

        User::create([
            'email' => 'pat1@test.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'patient',
            'first_name' => 'Pat',
            'last_name' => 'One',
            'is_active' => 1,
        ]);

        $doctors = User::getDoctorUsers();
        $patients = User::getPatientUsers();

        $this->assertCount(2, $doctors);
        $this->assertCount(1, $patients);

        foreach ($doctors as $doc) {
            $this->assertEquals('doctor', $doc['role']);
        }
    }

    public function test_can_update_user(): void
    {
        $id = User::create([
            'email' => 'update@test.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'patient',
            'first_name' => 'Old',
            'last_name' => 'Name',
            'is_active' => 1,
        ]);

        $affected = User::update($id, ['first_name' => 'New', 'last_name' => 'NameUpdated']);
        $this->assertEquals(1, $affected);

        $user = User::find($id);
        $this->assertEquals('New', $user['first_name']);
        $this->assertEquals('NameUpdated', $user['last_name']);
    }

    public function test_can_delete_user(): void
    {
        $id = User::create([
            'email' => 'delete@test.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'patient',
            'first_name' => 'Delete',
            'last_name' => 'Me',
            'is_active' => 1,
        ]);

        $affected = User::delete($id);
        $this->assertEquals(1, $affected);

        $user = User::find($id);
        $this->assertNull($user);
    }

    public function test_can_search_users(): void
    {
        User::create([
            'email' => 'alice@test.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'patient',
            'first_name' => 'Alice',
            'last_name' => 'Johnson',
            'is_active' => 1,
        ]);

        User::create([
            'email' => 'bob@test.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'patient',
            'first_name' => 'Bob',
            'last_name' => 'AliceSon',
            'is_active' => 1,
        ]);

        $results = User::search('Alice');
        $this->assertCount(2, $results);
    }

    public function test_user_count_works(): void
    {
        User::create([
            'email' => 'count1@test.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'patient',
            'first_name' => 'Count',
            'last_name' => 'One',
            'is_active' => 1,
        ]);

        User::create([
            'email' => 'count2@test.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'doctor',
            'first_name' => 'Count',
            'last_name' => 'Two',
            'is_active' => 1,
        ]);

        $this->assertEquals(2, User::count());
        $this->assertEquals(1, User::countWhere('role', 'doctor'));
    }

    public function test_create_user_filters_fillable_fields(): void
    {
        $id = User::create([
            'email' => 'fillable@test.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'patient',
            'first_name' => 'Fill',
            'last_name' => 'Able',
            'is_active' => 1,
            'nonexistent_field' => 'should be filtered',
        ]);

        $user = User::find($id);
        $this->assertArrayNotHasKey('nonexistent_field', $user);
    }
}
