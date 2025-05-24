<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/functions.php';

require_auth('user');

// Пагінація
$per_page = 5;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $per_page;

try {
    // Загальна кількість користувачів
    $total_stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total_users = $total_stmt->fetchColumn();
    
    // Отримання користувачів для поточної сторінки
    $stmt = $pdo->prepare("
        SELECT id, username, access_level, is_blocked, created_at 
        FROM users 
        ORDER BY created_at DESC 
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
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
    <title>Список користувачів</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">User System</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Ви увійшли як: <?= e($_SESSION['username']) ?>
                </span>
                <a href="/logout.php" class="btn btn-outline-light">Вийти</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Список користувачів</h2>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Ім'я</th>
                        <th>Роль</th>
                        <th>Статус</th>
                        <th>Дата реєстрації</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= e($user['id']) ?></td>
                        <td><?= e($user['username']) ?></td>
                        <td><?= $user['access_level'] === 'admin' ? 'Адміністратор' : 'Користувач' ?></td>
                        <td>
                            <span class="badge <?= $user['is_blocked'] ? 'bg-danger' : 'bg-success' ?>">
                                <?= $user['is_blocked'] ? 'Заблоковано' : 'Активний' ?>
                            </span>
                        </td>
                        <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?= generate_pagination($total_users, $per_page, $page, '/users.php') ?>
    </div>
</body>
</html>