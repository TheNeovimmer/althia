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

function flash_message(string $key = 'success'): string
{
    if (!isset($_SESSION['_flash'][$key])) return '';
    $msg = htmlspecialchars($_SESSION['_flash'][$key]);
    unset($_SESSION['_flash'][$key]);
    $cssClass = match($key) {
        'error' => 'flash-error',
        'info' => 'flash-info',
        default => 'flash-success',
    };
    return '<div class="flash-message ' . $cssClass . '">' . $msg . '</div>';
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
