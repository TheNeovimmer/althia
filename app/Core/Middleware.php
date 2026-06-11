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

    public static function admin(): callable
    {
        return function () {
            Auth::requireRole('admin');
        };
    }

    public static function doctor(): callable
    {
        return function () {
            Auth::requireRole('doctor');
        };
    }

    public static function patient(): callable
    {
        return function () {
            Auth::requireRole('patient');
        };
    }
}
