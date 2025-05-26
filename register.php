<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

session_start();

$errors = [];
$success = false; // Додано прапорець успішної реєстрації

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');

    // Валідація даних
    if (empty($username) || strlen($username) < 3) {
        $errors[] = "Ім'я користувача має містити щонайменше 3 символи";
    } elseif (strlen($username) > 50) {
        $errors[] = "Ім'я користувача занадто довге (максимум 50 символів)";
    }

    if (empty($password) || strlen($password) < 8) {
        $errors[] = "Пароль має містити щонайменше 8 символів";
    }

    if ($password !== $password_confirm) {
        $errors[] = "Паролі не співпадають";
    }

    if (empty($errors)) {
        try {
            // Перевірка на існуючого користувача
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetch()) {
                $errors[] = "Користувач з таким іменем вже існує";
            } else {
                // Хешування пароля
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Додавання нового користувача
                $stmt = $pdo->prepare("INSERT INTO users 
                    (username, password, access_level, is_blocked, created_at) 
                    VALUES (?, ?, 'user', 0, CURRENT_TIMESTAMP)");
                
                if ($stmt->execute([$username, $hashed_password])) {
                    // Отримання ID нового користувача
                    $user_id = $pdo->lastInsertId();
                    
                    // Встановлення сесії
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['access_level'] = 'user';
                    
                    $success = true; // Позначимо успішну реєстрацію
                }
            }
        } catch (PDOException $e) {
            error_log("Помилка реєстрації: " . $e->getMessage());
            $errors[] = "Сталася помилка під час реєстрації. Код помилки: " . $e->getCode();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Реєстрація</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 500px; }
        .form-group { margin-bottom: 1rem; }
        .error { color: #dc3545; font-size: 0.875em; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Реєстрація</h2>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <h4 class="alert-heading">Реєстрація успішна!</h4>
                        <p>Ви успішно зареєструвалися як <strong><?= htmlspecialchars($username) ?></strong></p>
                        <hr>
                        <p class="mb-0">
                            <a href="index.php" class="alert-link">На головну</a> | 
                            <a href="login.php" class="alert-link">Увійти</a>
                        </p>
                    </div>
                <?php else: ?>
                
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" novalidate>
                        <div class="form-group">
                            <label for="username" class="form-label">Ім'я користувача*</label>
                            <input type="text" id="username" name="username" class="form-control" 
                                   value="<?= htmlspecialchars($username ?? '') ?>" required
                                   minlength="3" maxlength="50">
                            <small class="form-text text-muted">Від 3 до 50 символів</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">Пароль*</label>
                            <input type="password" id="password" name="password" class="form-control" 
                                   required minlength="8">
                            <small class="form-text text-muted">Мінімум 8 символів</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirm" class="form-label">Підтвердження пароля*</label>
                            <input type="password" id="password_confirm" name="password_confirm" 
                                   class="form-control" required minlength="8">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mt-3">Зареєструватися</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <small>Вже маєте акаунт? <a href="login.php">Увійти</a></small>
                    </div>
                
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Валідація форми на клієнтській стороні
        document.querySelector('form')?.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('password_confirm').value;
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Паролі не співпадають!');
            }
        });
    </script>
</body>
</html>