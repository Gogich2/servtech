<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user) {
            if ($user['is_blocked']) {
                header("Location: /login.php?error=account_blocked");
                exit();
            }
            
            if (password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['access_level'] = $user['access_level'];
                
                $redirect = $_GET['redirect'] ?? ($user['access_level'] === 'admin' ? '/admin.php' : '/users.php');
                header("Location: $redirect");
                exit();
            }
        }
        
        // Якщо досягли сюди - невірні дані
        header("Location: /login.php?error=login_failed");
        exit();
        
    } catch (PDOException $e) {
        error_log("Помилка авторизації: " . $e->getMessage());
        header("Location: /login.php?error=login_failed");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">Вхід</h2>
                        
                        <?php display_messages(); ?>

                        <form method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Ім'я користувача</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Пароль</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Увійти</button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <a href="/register.php">Не маєте акаунту? Зареєструватися</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>