<?php
require_once __DIR__ . '/includes/config.php';

session_start();

// Знищуємо всі дані сесії
$_SESSION = [];
session_destroy();

// Видаляємо куки
setcookie('remembered_username', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'secure' => true,
    'httponly' => true
]);

header("Location: login.php");
exit();
?>