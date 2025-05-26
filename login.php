<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

session_start();

// Захист від CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Обробка форми
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Захист від брутфорсу
    $login_attempts = $_SESSION['login_attempts'] ?? 0;
    if ($login_attempts > 5) {
        sleep(min(30, $login_attempts * 2));
    }

    $username = clean_input($_POST['username'] ?? '');
    $password = clean_input($_POST['password'] ?? '');
    $remember_me = isset($_POST['remember_me']);
    
    try {
        // Перевірка CSRF
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            throw new Exception("Неприпустимий запит");
        }
        
        // Перевірка reCAPTCHA
        if (!verify_recaptcha($_POST['g-recaptcha-response'] ?? '')) {
            throw new Exception("Помилка перевірки безпеки. Спробуйте ще раз.");
        }
        
        // Інший код обробки форми...
        // (залишається без змін)
        
    } catch (Exception $e) {
        $_SESSION['login_error'] = $e->getMessage();
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід в систему</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js?render=<?= htmlspecialchars(RECAPTCHA_SITE_KEY) ?>"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .form-label {
            font-weight: 500;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
            padding: 10px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2 class="text-center mb-4">Вхід в систему</h2>
            
            <?php if (!empty($_SESSION['login_error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['login_error']) ?></div>
                <?php unset($_SESSION['login_error']); ?>
            <?php endif; ?>

            <form id="login-form" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                
                <div class="mb-3">
                    <label for="username" class="form-label">Ім'я користувача</label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Пароль</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                    <label class="form-check-label" for="remember_me">Запам'ятати мене</label>
                </div>
                
                <button type="submit" name="login" class="btn btn-primary w-100">Увійти</button>
            </form>
        </div>
    </div>

    <script>
        // Обробка reCAPTCHA
        document.getElementById('login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            grecaptcha.ready(function() {
                grecaptcha.execute('<?= htmlspecialchars(RECAPTCHA_SITE_KEY) ?>', {action: 'login'}).then(function(token) {
                    document.getElementById('g-recaptcha-response').value = token;
                    e.target.submit();
                });
            });
        });
    </script>
</body>
</html>