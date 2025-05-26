<?php
// Базові налаштування
define('DB_HOST', 'localhost');
define('DB_NAME', 'user_management');
define('DB_USER', 'root');
define('DB_PASS', 'Egoga1245');

// Налаштування сесії
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
define('RECAPTCHA_SITE_KEY', '6LfgPUorAAAAAO4AOJLCEKNu_60V9UK0VVb8WGVP');
define('RECAPTCHA_SECRET_KEY', '6LfgPUorAAAAAD6xv2BoK3i1DDqJcAEOEPiq-Wo0');
define('RECAPTCHA_THRESHOLD', 0.5); // Рівень довіри

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php-error.log');
?>