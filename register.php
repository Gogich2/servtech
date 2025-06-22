<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

session_start();

$errors = [];
$success = false;
$input = [
    'username' => '',
    'email' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Очищення та отримання даних з форми
    $input['username'] = trim($_POST['username'] ?? '');
    $input['email'] = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');

    // Валідація даних
    if (empty($input['username'])) {
        $errors['username'] = "Ім'я користувача обов'язкове";
    } elseif (strlen($input['username']) < 3) {
        $errors['username'] = "Ім'я має містити щонайменше 3 символи";
    } elseif (strlen($input['username']) > 50) {
        $errors['username'] = "Ім'я занадто довге (максимум 50 символів)";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $input['username'])) {
        $errors['username'] = "Ім'я може містити лише літери, цифри та підкреслення";
    }

    if (empty($input['email'])) {
        $errors['email'] = "Email обов'язковий";
    } elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Будь ласка, введіть дійсну email-адресу";
    }

    if (empty($password)) {
        $errors['password'] = "Пароль обов'язковий";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Пароль має містити щонайменше 8 символів";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = "Пароль має містити щонайменше одну велику літеру";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = "Пароль має містити щонайменше одну цифру";
    }

    if ($password !== $password_confirm) {
        $errors['password_confirm'] = "Паролі не співпадають";
    }

    if (empty($errors)) {
        try {
            // Перевірка на існуючого користувача
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$input['username'], $input['email']]);
            
            if ($existing_user = $stmt->fetch()) {
                $errors['general'] = "Користувач з таким іменем або email вже існує";
            } else {
                // Хешування пароля
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Додавання нового користувача
                $stmt = $pdo->prepare("INSERT INTO users 
                    (username, email, password, access_level, is_blocked, created_at) 
                    VALUES (?, ?, ?, 'user', 0, NOW())");
                
                if ($stmt->execute([$input['username'], $input['email'], $hashed_password])) {
                    // Отримання ID нового користувача
                    $user_id = $pdo->lastInsertId();
                    
                    // Встановлення сесії
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $input['username'];
                    $_SESSION['access_level'] = 'user';
                    $_SESSION['email'] = $input['email'];
                    
                    $success = true;
                    
                    // Перенаправлення після успішної реєстрації
                    header("Location: dashboard.php");
                    exit();
                }
            }
        } catch (PDOException $e) {
            error_log("Помилка реєстрації: " . $e->getMessage());
            $errors['general'] = "Сталася помилка під час реєстрації. Будь ласка, спробуйте пізніше.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Реєстрація</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 500px; }
        .form-group { margin-bottom: 1.5rem; }
        .error { color: #dc3545; font-size: 0.875em; margin-top: 0.25rem; }
        .card { border: none; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15); }
        .card-body { padding: 2rem; }
        .btn-primary { background-color: #0d6efd; border: none; padding: 0.5rem 1rem; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Реєстрація</h2>
                
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($errors['general']) ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" novalidate>
                    <div class="form-group">
                        <label for="username" class="form-label">Ім'я користувача*</label>
                        <input type="text" id="username" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                               value="<?= htmlspecialchars($input['username']) ?>" required
                               minlength="3" maxlength="50">
                        <?php if (isset($errors['username'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['username']) ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted">Від 3 до 50 символів (лише літери, цифри та _)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email*</label>
                        <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                               value="<?= htmlspecialchars($input['email']) ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Пароль*</label>
                        <input type="password" id="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                               required minlength="8">
                        <?php if (isset($errors['password'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['password']) ?></div>
                        <?php endif; ?>
                        <small class="form-text text-muted">Мінімум 8 символів, 1 велика літера та 1 цифра</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm" class="form-label">Підтвердження пароля*</label>
                        <input type="password" id="password_confirm" name="password_confirm" 
                               class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>" required minlength="8">
                        <?php if (isset($errors['password_confirm'])): ?>
                            <div class="error"><?= htmlspecialchars($errors['password_confirm']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mt-3 py-2">Зареєструватися</button>
                </form>
                
                <div class="text-center mt-4">
                    <small>Вже маєте акаунт? <a href="login.php">Увійти</a></small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Покращена клієнтська валідація
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('password_confirm').value;
            
            if (password !== confirm) {
                e.preventDefault();
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error';
                errorDiv.textContent = 'Паролі не співпадають!';
                
                const confirmInput = document.getElementById('password_confirm');
                const existingError = confirmInput.nextElementSibling;
                
                if (existingError && existingError.className === 'error') {
                    existingError.textContent = 'Паролі не співпадають!';
                } else {
                    confirmInput.insertAdjacentElement('afterend', errorDiv);
                }
                
                confirmInput.classList.add('is-invalid');
                confirmInput.focus();
            }
        });
    </script>
</body>
</html>