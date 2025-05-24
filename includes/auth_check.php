<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db_connect.php';

/**
 * Перевірка авторизації користувача
 * @param string $required_level Мінімальний необхідний рівень доступу
 */
function require_auth($required_level = 'user') {
    if (empty($_SESSION['user_id'])) {
        header("Location: /login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }

    check_user_block_status();

    if ($required_level === 'admin' && $_SESSION['access_level'] !== 'admin') {
        header("Location: /users.php?error=access_denied");
        exit();
    }
}

/**
 * Перевірка чи заблокований користувач
 */
function check_user_block_status() {
    global $pdo;

    $stmt = $pdo->prepare("SELECT is_blocked FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user && $user['is_blocked']) {
        session_unset();
        session_destroy();
        header("Location: /login.php?error=account_blocked");
        exit();
    }
}

/**
 * Перевірка CSRF токена
 */
function verify_csrf_token() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("Невалідний CSRF токен!");
        }
    }
}

// Генерація CSRF токена при кожному запиті
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>