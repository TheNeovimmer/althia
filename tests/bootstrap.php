<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Ensure session is available for tests that need it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
