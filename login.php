<?php
// Включення необхідних файлів
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

// Ініціалізація сесії
session_start();

// Налаштування логування
define('LOG_FILE', __DIR__ . '/logs/auth.log');
@mkdir(dirname(LOG_FILE), 0755, true);

// Функція для логування
function log_auth($message) {
    $timestamp = date('[Y-m-d H:i:s]');
    file_put_contents(LOG_FILE, "$timestamp $message\n", FILE_APPEND);
}

// Початок логування
log_auth("=== Login Request Started ===");
log_auth("Remote IP: " . ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR']));
log_auth("User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'));

// Обробка POST-запиту
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Отримання даних форми
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember_me = isset($_POST['remember_me']);
    
    log_auth("Login attempt for username: '$username'");
    log_auth("Remember me: " . ($remember_me ? 'Yes' : 'No'));

    // Базова валідація
    if (empty($username) || empty($password)) {
        log_auth("Validation failed: Empty username or password");
        $_SESSION['login_error'] = "Будь ласка, заповніть всі поля";
        header("Location: login.php");
        exit();
    }

    try {
        // Пошук користувача в базі даних
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            log_auth("User found in database. User ID: {$user['id']}");
            log_auth("Account status: " . ($user['is_blocked'] ? 'BLOCKED' : 'ACTIVE'));

            // Перевірка на блокування
            if ($user['is_blocked']) {
                log_auth("Login failed: Account is blocked");
                $_SESSION['login_error'] = "Обліковий запис заблоковано";
                header("Location: login.php");
                exit();
            }

            // Перевірка пароля
            log_auth("Stored password hash: {$user['password']}");
            $password_valid = password_verify($password, $user['password']);
            log_auth("Password verification: " . ($password_valid ? 'SUCCESS' : 'FAILED'));

            if ($password_valid) {
                // Встановлюємо сесію ПЕРШ ніж оновлювати last_login
                $_SESSION = [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'access_level' => $user['access_level'],
                    'last_login' => date('Y-m-d H:i:s'),
                    'ip' => $_SERVER['REMOTE_ADDR']
                ];
                
                log_auth("Session variables set");

                // Оновлення часу входу (з обробкою помилок)
                try {
                    $update = $pdo->prepare("UPDATE users SET last_login = ? WHERE id = ?");
                    $update->execute([date('Y-m-d H:i:s'), $user['id']]);
                    log_auth("Last login updated successfully");
                } catch (PDOException $e) {
                    log_auth("Failed to update last_login: " . $e->getMessage());
                    // Продовжуємо роботу, навіть якщо оновлення не вдалося
                }

                // Запам'ятовування користувача, якщо обрано
                if ($remember_me) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, [
                        'expires' => time() + 60 * 60 * 24 * 30,
                        'path' => '/',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]);
                    log_auth("Remember token set");
                }

                // Перенаправлення
                $redirect = ($user['access_level'] === 'admin') ? 'admin.php' : 'index.php';
                log_auth("Redirecting to: $redirect");
                header("Location: $redirect");
                exit();
            }
        }

        // Невірні облікові дані
        log_auth("Login failed: Invalid credentials");
        sleep(2);
        $_SESSION['login_error'] = "Невірне ім'я користувача або пароль";
        header("Location: login.php");
        exit();

    } catch (PDOException $e) {
        log_auth("DATABASE ERROR: " . $e->getMessage());
        $_SESSION['login_error'] = "Помилка бази даних. Будь ласка, спробуйте пізніше";
        header("Location: login.php");
        exit();
    }
}

// Генерація CSRF-токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    log_auth("CSRF token generated");
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід в систему</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-logo {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="form-logo">
                <h2>Вхід в систему</h2>
            </div>
            
            <?php if (!empty($_SESSION['login_error'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_SESSION['login_error']) ?>
                    <?php unset($_SESSION['login_error']); ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Ім'я користувача</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Пароль</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                    <label class="form-check-label" for="remember_me">Запам'ятати мене</label>
                </div>
                
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                
                <button type="submit" class="btn btn-primary w-100">Увійти</button>
            </form>
            
            <div class="mt-3 text-center">
                <a href="register.php">Реєстрація</a> | <a href="forgot_password.php">Забули пароль?</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
log_auth("=== Login Request Completed ===");
?>