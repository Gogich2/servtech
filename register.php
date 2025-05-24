<?php
// Initialize session
session_start();

// Database connection and functions
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $is_admin = isset($_POST['is_admin']);

    // Validation
    if (empty($username)) {
        $errors[] = "Ім'я користувача обов'язкове";
    } elseif (strlen($username) < 3) {
        $errors[] = "Ім'я користувача має містити щонайменше 3 символи";
    }

    if (empty($password)) {
        $errors[] = "Пароль обов'язковий";
    } elseif (strlen($password) < 6) {
        $errors[] = "Пароль має містити щонайменше 6 символів";
    }

    if (empty($errors)) {
        try {
            // Check if username exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetch()) {
                $errors[] = "Користувач з таким іменем вже існує";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $access_level = $is_admin ? 'admin' : 'user';
                
                $stmt = $pdo->prepare("INSERT INTO users (username, password, access_level) VALUES (?, ?, ?)");
                $stmt->execute([$username, $hashed_password, $access_level]);
                
                $_SESSION['registration_success'] = true;
                header("Location: login.php?success=registered");
                exit();
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $errors[] = "Сталася помилка під час реєстрації";
        }
    }
}

// HTML escape function (if not in functions.php)
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
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
        .container {
            max-width: 600px;
        }
        .card {
            border-radius: 15px;
        }
        .form-check-label {
            user-select: none;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="card-title text-center mb-4">Реєстрація нового користувача</h2>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= e($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" novalidate>
                            <div class="mb-3">
                                <label for="username" class="form-label">Ім'я користувача *</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?= e($_POST['username'] ?? '') ?>" required
                                       minlength="3" maxlength="50">
                                <div class="form-text">Мінімум 3 символи</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Пароль *</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required minlength="6">
                                <div class="form-text">Мінімум 6 символів</div>
                            </div>
                            
                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin"
                                    <?= isset($_POST['is_admin']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_admin">Зареєструвати як адміністратора</label>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Зареєструватися</button>
                            </div>
                        </form>
                        
                        <div class="mt-4 text-center">
                            <p>Вже маєте акаунт? <a href="login.php">Увійти</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            let valid = true;
            const username = document.getElementById('username');
            const password = document.getElementById('password');
            
            if (username.value.trim().length < 3) {
                valid = false;
                username.classList.add('is-invalid');
            } else {
                username.classList.remove('is-invalid');
            }
            
            if (password.value.length < 6) {
                valid = false;
                password.classList.add('is-invalid');
            } else {
                password.classList.remove('is-invalid');
            }
            
            if (!valid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>