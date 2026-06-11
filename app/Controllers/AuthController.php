<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Validator;
use App\Core\Database;
use App\Models\User;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        $this->render('auth/login');
    }

    public function login(): void
    {
        $body = $this->getBody();
        $validator = new Validator();

        if (!$validator->validate($body, [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ])) {
            $_SESSION['_errors'] = $validator->errors();
            $_SESSION['_old'] = $body;
            $this->redirect('/login');
            return;
        }

        if (Auth::login($body['email'], $body['password'])) {
            $role = Auth::role();
            $_SESSION['_flash']['success'] = 'Welcome back, ' . Auth::user()['first_name'] . '!';

            if ($role === 'admin') $this->redirect('/admin/dashboard');
            elseif ($role === 'doctor') $this->redirect('/doctor/dashboard');
            else $this->redirect('/patient/dashboard');
        } else {
            $_SESSION['_errors'] = ['Invalid email or password'];
            $_SESSION['_old'] = $body;
            $this->redirect('/login');
        }
    }

    public function registerForm(): void
    {
        $this->render('auth/register');
    }

    public function register(): void
    {
        $body = $this->getBody();
        $validator = new Validator();

        if (!$validator->validate($body, [
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|phone',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:patient,doctor',
        ])) {
            $_SESSION['_errors'] = $validator->errors();
            $_SESSION['_old'] = $body;
            $this->redirect('/register');
            return;
        }

        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            $userId = User::create([
                'email' => $body['email'],
                'password' => Auth::hashPassword($body['password']),
                'role' => $body['role'],
                'first_name' => $body['first_name'],
                'last_name' => $body['last_name'],
                'phone' => $body['phone'],
                'is_active' => 1,
            ]);

            if ($body['role'] === 'patient') {
                $db->insert("INSERT INTO patients (user_id) VALUES (?)", [$userId]);
            } elseif ($body['role'] === 'doctor') {
                $db->insert("INSERT INTO doctors (user_id) VALUES (?)", [$userId]);
            }

            $db->commit();

            Auth::login($body['email'], $body['password']);
            $_SESSION['_flash']['success'] = 'Welcome to Medicase! Your account has been created.';

            if ($body['role'] === 'doctor') $this->redirect('/doctor/dashboard');
            else $this->redirect('/patient/dashboard');

        } catch (\Exception $e) {
            $db->rollback();
            $_SESSION['_errors'] = ['Registration failed. Please try again.'];
            $_SESSION['_old'] = $body;
            $this->redirect('/register');
        }
    }

    public function forgotForm(): void
    {
        $this->render('auth/forgot-password');
    }

    public function forgotPassword(): void
    {
        $body = $this->getBody();
        $validator = new Validator();

        if (!$validator->validate($body, ['email' => 'required|email'])) {
            $_SESSION['_errors'] = $validator->errors();
            $this->redirect('/forgot-password');
            return;
        }

        $user = User::findByEmail($body['email']);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $db = Database::getInstance();
            $db->insert(
                "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))",
                [$user['id'], $token]
            );
        }

        $_SESSION['_flash']['success'] = 'If the email exists, a password reset link has been sent.';
        $this->redirect('/login');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/');
    }
}
