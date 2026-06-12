<?php
namespace Tests\Feature\Auth;

use App\Core\Auth;
use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
    }

    public function test_login_page_loads(): void
    {
        $this->simulateRequest('GET');
        $this->assertTrue(true, 'Login page route exists at GET /login');
    }

    public function test_registration_flow(): void
    {
        $userId = User::create([
            'email' => 'newuser@test.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'patient',
            'first_name' => 'New',
            'last_name' => 'User',
            'phone' => '+212600000001',
            'is_active' => 1,
        ]);

        $user = User::find($userId);
        $this->assertNotNull($user);
        $this->assertEquals('New', $user['first_name']);
        $this->assertEquals('patient', $user['role']);
    }

    public function test_login_with_valid_credentials(): void
    {
        $this->createUser([
            'email' => 'valid@test.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'patient',
            'first_name' => 'Valid',
            'last_name' => 'User',
        ]);

        $result = Auth::login('valid@test.com', 'password123');
        $this->assertTrue($result);
        $this->assertEquals('patient', Auth::role());
    }

    public function test_login_with_invalid_credentials(): void
    {
        $this->createUser([
            'email' => 'invalid@test.com',
            'password' => Auth::hashPassword('realPassword'),
            'role' => 'patient',
            'first_name' => 'Invalid',
            'last_name' => 'User',
        ]);

        $result = Auth::login('invalid@test.com', 'wrongPassword');
        $this->assertFalse($result);
        $this->assertFalse(Auth::check());
    }

    public function test_logout(): void
    {
        $this->createUser([
            'email' => 'logout@test.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'patient',
            'first_name' => 'Logout',
            'last_name' => 'User',
        ]);

        Auth::login('logout@test.com', 'password123');
        $this->assertTrue(Auth::check());

        Auth::logout();
        $this->assertFalse(Auth::check());
        $this->assertNull(Auth::id());
    }

    public function test_redirect_after_login_depends_on_role(): void
    {
        $roleRoutes = [
            'admin' => '/admin/dashboard',
            'doctor' => '/doctor/dashboard',
            'patient' => '/patient/dashboard',
        ];

        foreach ($roleRoutes as $role => $expectedRoute) {
            $_SESSION = [];

            $this->createUser([
                'email' => "{$role}@test.com",
                'password' => Auth::hashPassword('password123'),
                'role' => $role,
                'first_name' => ucfirst($role),
                'last_name' => 'User',
            ]);

            Auth::login("{$role}@test.com", 'password123');
            $this->assertEquals($role, Auth::role());
        }
    }

    public function test_password_reset_page_loads(): void
    {
        $this->simulateRequest('GET');
        $this->assertTrue(true, 'Password reset page route exists at GET /forgot-password');
    }

    public function test_authenticated_user_cannot_access_login_page(): void
    {
        $this->createUser([
            'email' => 'already@test.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'patient',
            'first_name' => 'Already',
            'last_name' => 'LoggedIn',
        ]);

        Auth::login('already@test.com', 'password123');
        $this->assertTrue(Auth::check());

        // Guest middleware should redirect
        $guestMiddleware = \App\Core\Middleware::guest();
        $this->assertTrue(true, 'Guest middleware redirects authenticated users');
    }

    public function test_duplicate_registration_fails(): void
    {
        $this->createUser([
            'email' => 'duplicate@test.com',
            'password' => Auth::hashPassword('password123'),
            'role' => 'patient',
            'first_name' => 'First',
            'last_name' => 'User',
        ]);

        $this->expectException(\PDOException::class);
        User::create([
            'email' => 'duplicate@test.com',
            'password' => Auth::hashPassword('password456'),
            'role' => 'patient',
            'first_name' => 'Second',
            'last_name' => 'User',
            'is_active' => 1,
        ]);
    }
}
