<?php
namespace Tests\Unit\Core;

use App\Core\Auth;
use Tests\TestCase;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $password = 'correctPassword123';
        $hashed = Auth::hashPassword($password);

        $this->createUser([
            'email' => 'login@test.com',
            'password' => $hashed,
            'role' => 'patient',
            'first_name' => 'Login',
            'last_name' => 'Test',
        ]);

        $result = Auth::login('login@test.com', $password);
        $this->assertTrue($result);
        $this->assertTrue(Auth::check());
        $this->assertEquals('patient', Auth::role());
    }

    public function test_login_fails_with_invalid_password(): void
    {
        $this->createUser([
            'email' => 'fail@test.com',
            'password' => Auth::hashPassword('realPassword'),
            'role' => 'patient',
            'first_name' => 'Fail',
            'last_name' => 'Test',
        ]);

        $result = Auth::login('fail@test.com', 'wrongPassword');
        $this->assertFalse($result);
        $this->assertFalse(Auth::check());
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $result = Auth::login('nonexistent@test.com', 'password123');
        $this->assertFalse($result);
        $this->assertFalse(Auth::check());
    }

    public function test_login_fails_for_inactive_user(): void
    {
        $this->createUser([
            'email' => 'inactive@test.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'patient',
            'first_name' => 'Inactive',
            'last_name' => 'User',
            'is_active' => 0,
        ]);

        $result = Auth::login('inactive@test.com', 'password123');
        $this->assertFalse($result);
    }

    public function test_user_can_logout(): void
    {
        $this->createUser([
            'email' => 'logout@test.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'patient',
            'first_name' => 'Logout',
            'last_name' => 'Test',
        ]);

        Auth::login('logout@test.com', 'password123');
        $this->assertTrue(Auth::check());

        Auth::logout();
        $this->assertFalse(Auth::check());
        $this->assertNull(Auth::user());
    }

    public function test_role_checking(): void
    {
        $_SESSION = [];

        $this->assertFalse(Auth::isAdmin());
        $this->assertFalse(Auth::isDoctor());
        $this->assertFalse(Auth::isPatient());

        $_SESSION['user_role'] = 'admin';
        $this->assertTrue(Auth::isAdmin());
        $this->assertFalse(Auth::isDoctor());
        $this->assertFalse(Auth::isPatient());

        $_SESSION['user_role'] = 'doctor';
        $this->assertFalse(Auth::isAdmin());
        $this->assertTrue(Auth::isDoctor());
        $this->assertFalse(Auth::isPatient());

        $_SESSION['user_role'] = 'patient';
        $this->assertFalse(Auth::isAdmin());
        $this->assertFalse(Auth::isDoctor());
        $this->assertTrue(Auth::isPatient());
    }

    public function test_session_management(): void
    {
        $this->assertFalse(Auth::check());
        $this->assertNull(Auth::id());
        $this->assertNull(Auth::role());

        $_SESSION['user_id'] = 42;
        $_SESSION['user_role'] = 'admin';

        $this->assertTrue(Auth::check());
        $this->assertEquals(42, Auth::id());
        $this->assertEquals('admin', Auth::role());
    }

    public function test_password_hashing(): void
    {
        $password = 'mySecurePass!42';
        $hash = Auth::hashPassword($password);

        $this->assertNotEmpty($hash);
        $this->assertNotEquals($password, $hash);
        $this->assertTrue(Auth::verifyPassword($password, $hash));
        $this->assertFalse(Auth::verifyPassword('wrongPassword', $hash));
    }

    public function test_user_method_returns_user_data(): void
    {
        $userData = $this->createUser([
            'email' => 'userdata@test.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'doctor',
            'first_name' => 'User',
            'last_name' => 'Data',
        ]);

        Auth::login('userdata@test.com', 'password123');
        $user = Auth::user();

        $this->assertNotNull($user);
        $this->assertEquals($userData['id'], $user['id']);
        $this->assertEquals('userdata@test.com', $user['email']);
        $this->assertEquals('doctor', $user['role']);
    }
}
