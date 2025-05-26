<?php
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function display_messages() {
    if (!empty($_GET['error'])) {
        $error = match($_GET['error']) {
            'login_failed' => 'Невірний логін або пароль',
            'account_blocked' => 'Обліковий запис заблоковано',
            default => 'Сталася помилка'
        };
        echo '<div class="alert alert-danger">' . e($error) . '</div>';
    }
    
    if (!empty($_GET['success'])) {
        $success = match($_GET['success']) {
            'registered' => 'Реєстрація успішна! Тепер ви можете увійти',
            default => 'Операція успішна'
        };
        echo '<div class="alert alert-success">' . e($success) . '</div>';
    }
}

function init_visit_counter() {
    if (!isset($_SESSION['visits'])) {
        $_SESSION['visits'] = 1;
    } else {
        $_SESSION['visits']++;
    }
}

function password_strength_check($password) {
    if (strlen($password) < 8) return 'Мінімум 8 символів';
    if (!preg_match('/[A-Z]/', $password)) return 'Додайте великі літери';
    if (!preg_match('/[0-9]/', $password)) return 'Додайте цифри';
    return null;
}
?>