<?php
/**
 * Розширені функції безпеки для авторизації
 */

/**
 * Генерує токен для "Запам'ятати мене" та оновлює його в БД
 */
function generate_and_store_remember_token(PDO $pdo, int $user_id): string {
    $token = bin2hex(random_bytes(32));
    $hashed_token = password_hash($token, PASSWORD_DEFAULT);
    
    $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?")
        ->execute([$hashed_token, $user_id]);
    
    return $token;
}

/**
 * Валідує токен "Запам'ятати мене" з бази даних
 */
function validate_remember_token(PDO $pdo, string $token, int $user_id): bool {
    $stmt = $pdo->prepare("SELECT remember_token FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $stored_hash = $stmt->fetchColumn();
    
    return $stored_hash && password_verify($token, $stored_hash);
}

/**
 * Очищення вхідних даних з глибокою обробкою масивів
 */
function clean_input($data) {
    if (is_array($data)) {
        return array_map('clean_input', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Захищений старт сесії з додатковими налаштуваннями
 */
function secure_session_start() {
    $session_name = 'secure_session';
    $secure = true;
    $httponly = true;
    $samesite = 'Strict';

    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);

    session_name($session_name);
    session_start();
    
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

/**
 * Перевірка авторизації з підтримкою рівнів доступу
 */
function check_auth(PDO $pdo, string $required_level = 'user'): array {
    secure_session_start();
    
    // Перевірка звичайної сесії
    if (!empty($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("
            SELECT id, username, access_level 
            FROM users 
            WHERE id = ? AND is_blocked = 0
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user && ($user['access_level'] === $required_level || $user['access_level'] === 'admin')) {
            return $user;
        }
    }
    
    // Перевірка токена "Запам'ятати мене"
    if (!empty($_COOKIE['remember_token']) && !empty($_COOKIE['remember_user_id'])) {
        $user_id = (int)$_COOKIE['remember_user_id'];
        $token = $_COOKIE['remember_token'];
        
        if (validate_remember_token($pdo, $token, $user_id)) {
            $stmt = $pdo->prepare("
                SELECT id, username, access_level 
                FROM users 
                WHERE id = ? AND is_blocked = 0
            ");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if ($user && ($user['access_level'] === $required_level || $user['access_level'] === 'admin')) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['access_level'] = $user['access_level'];
                return $user;
            }
        }
    }
    
    throw new Exception("Доступ заборонено", 403);
}

/**
 * Оновлення лічильника невдалих спроб входу
 */
function update_failed_login(PDO $pdo, string $username): void {
    $pdo->prepare("
        UPDATE users 
        SET login_attempts = login_attempts + 1, 
            last_failed_login = NOW() 
        WHERE username = ?
    ")->execute([$username]);
    
    // Блокування при перевищенні ліміту
    $pdo->prepare("
        UPDATE users 
        SET is_blocked = 1 
        WHERE username = ? AND login_attempts >= 5
    ")->execute([$username]);
}

/**
 * Скидання лічильника спроб при успішному вході
 */
function reset_login_attempts(PDO $pdo, int $user_id): void {
    $pdo->prepare("
        UPDATE users 
        SET login_attempts = 0, 
            last_failed_login = NULL 
        WHERE id = ?
    ")->execute([$user_id]);
}

/**
 * Генерація CSRF токена для форм
 */
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Валідація CSRF токена
 */
function validate_csrf_token(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Перевірка складності пароля
 */
function is_password_strong(string $password): bool {
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password)
        && preg_match('/[\W]/', $password);
}

/**
 * Логування подій безпеки
 */
function log_security_event(string $event, string $username = null): void {
    $log = sprintf(
        "[%s] %s %s %s\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'],
        $username ? "user:$username" : 'unknown',
        $event
    );
    
    file_put_contents(__DIR__ . '/logs/security.log', $log, FILE_APPEND);
}