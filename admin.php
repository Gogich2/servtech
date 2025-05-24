<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/functions.php';

require_auth('admin');

// Обробка дій адміністратора
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    
    $user_id = intval($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'block':
                $stmt = $pdo->prepare("UPDATE users SET is_blocked = TRUE WHERE id = ? AND id != ?");
                $stmt->execute([$user_id, $_SESSION['user_id']]);
                break;
                
            case 'unblock':
                $stmt = $pdo->prepare("UPDATE users SET is_blocked = FALSE WHERE id = ?");
                $stmt->execute([$user_id]);
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND id != ?");
                $stmt->execute([$user_id, $_SESSION['user_id']]);
                break;
        }
        
        // Оновити сторінку після дії
        header("Location: /admin.php");
        exit();
        
    } catch (PDOException $e) {
        error_log("Помилка адмін дії: " . $e->getMessage());
        die("Сталася помилка при виконанні дії");
    }
}

// Отримання списку користувачів
try {
    $stmt = $pdo->query("
        SELECT id, username, access_level, is_blocked, created_at 
        FROM users 
        ORDER BY access_level DESC, created_at DESC
    ");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Помилка отримання користувачів: " . $e->getMessage());
    die("Сталася помилка при отриманні даних користувачів");
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель адміністратора</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Admin Panel</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Адміністратор: <?= e($_SESSION['username']) ?>
                </span>
                <a href="/logout.php" class="btn btn-outline-light">Вийти</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Управління користувачами</h2>
        
        <?php if (!empty($_GET['success'])): ?>
            <div class="alert alert-success">Дія успішно виконана!</div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Ім'я</th>
                        <th>Роль</th>
                        <th>Статус</th>
                        <th>Дата реєстрації</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= e($user['id']) ?></td>
                        <td><?= e($user['username']) ?></td>
                        <td>
                            <span class="badge <?= $user['access_level'] === 'admin' ? 'bg-primary' : 'bg-secondary' ?>">
                                <?= $user['access_level'] === 'admin' ? 'Адмін' : 'Користувач' ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $user['is_blocked'] ? 'bg-danger' : 'bg-success' ?>">
                                <?= $user['is_blocked'] ? 'Заблоковано' : 'Активний' ?>
                            </span>
                        </td>
                        <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                        <td>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="user_id" value="<?= e($user['id']) ?>">
                                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                
                                <?php if ($user['is_blocked']): ?>
                                    <button type="submit" name="action" value="unblock" class="btn btn-sm btn-success">
                                        <i class="bi bi-unlock"></i> Розблокувати
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="action" value="block" class="btn btn-sm btn-warning">
                                        <i class="bi bi-lock"></i> Заблокувати
                                    </button>
                                <?php endif; ?>
                                
                                <button type="submit" name="action" value="delete" 
                                        class="btn btn-sm btn-danger" 
                                        onclick="return confirm('Ви впевнені, що хочете видалити цього користувача?')">
                                    <i class="bi bi-trash"></i> Видалити
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>