<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/functions.php';

require_auth('admin');

// Початок вимірювання часу генерації сторінки
$start_time = microtime(true);

// Отримання даних адміністратора
$adminRegisteredAt = null;
try {
    $stmt = $pdo->prepare("SELECT created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $adminData = $stmt->fetch();
    $adminRegisteredAt = $adminData['created_at'] ?? null;
} catch (PDOException $e) {
    error_log("Помилка отримання даних адміністратора: " . $e->getMessage());
}

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
        
        header("Location: /admin.php?success=1");
        exit();
        
    } catch (PDOException $e) {
        error_log("Помилка виконання дії адміністратора: " . $e->getMessage());
        $_SESSION['error'] = "Помилка виконання дії: " . $e->getMessage();
        header("Location: /admin.php?error=1");
        exit();
    }
}

// Отримання списку користувачів
$users = [];
try {
    $stmt = $pdo->query("
        SELECT id, username, email, access_level, is_blocked, created_at 
        FROM users 
        ORDER BY access_level DESC, created_at DESC
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Помилка отримання списку користувачів: " . $e->getMessage());
    $_SESSION['error'] = "Не вдалося завантажити список користувачів";
}

// Час генерації сторінки
$generation_time = microtime(true) - $start_time;

/* Функції для роботи з датами */
function currentDateUkrainian() {
    $months = [
        1 => 'січня', 'лютого', 'березня', 'квітня', 'травня', 'червня',
        'липня', 'серпня', 'вересня', 'жовтня', 'листопада', 'грудня'
    ];
    $days = [
        'неділя', 'понеділок', 'вівторок', 'середа', 
        'четвер', 'п\'ятниця', 'субота'
    ];
    
    $dayOfWeek = $days[date('w')];
    $day = date('j');
    $month = $months[date('n')];
    $year = date('Y');
    
    return ucfirst($dayOfWeek) . ", $day $month $year року";
}

function formatRegistrationTime($datetime) {
    try {
        $now = new DateTime();
        $regDate = new DateTime($datetime);
        $interval = $now->diff($regDate);
        
        $parts = [];
        if ($interval->y > 0) $parts[] = $interval->y . ' ' . pluralize($interval->y, 'рік', 'роки', 'років');
        if ($interval->m > 0) $parts[] = $interval->m . ' ' . pluralize($interval->m, 'місяць', 'місяці', 'місяців');
        if ($interval->d > 0) $parts[] = $interval->d . ' ' . pluralize($interval->d, 'день', 'дні', 'днів');
        if ($interval->h > 0) $parts[] = $interval->h . ' ' . pluralize($interval->h, 'година', 'години', 'годин');
        if ($interval->i > 0) $parts[] = $interval->i . ' ' . pluralize($interval->i, 'хвилина', 'хвилини', 'хвилин');
        
        return implode(', ', $parts) ?: 'менше хвилини';
    } catch (Exception $e) {
        error_log("Помилка форматування часу: " . $e->getMessage());
        return 'невідомо';
    }
}

function pluralize($number, $one, $two, $five) {
    $n = abs($number) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) return $five;
    if ($n1 > 1 && $n1 < 5) return $two;
    if ($n1 == 1) return $one;
    return $five;
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
    <style>
        .time-info { font-size: 0.9rem; color: #6c757d; margin-bottom: 1rem; }
        .user-time { font-size: 0.8rem; color: #6c757d; margin-top: 0.25rem; }
        .table-responsive { margin-top: 1.5rem; }
        .action-buttons { white-space: nowrap; }
        .error-card { border-left: 4px solid #dc3545; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Панель адміністратора</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="bi bi-person-circle"></i> <?= e($_SESSION['username']) ?>
                </span>
                <a href="/logout.php" class="btn btn-outline-light">
                    <i class="bi bi-box-arrow-right"></i> Вийти
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (!empty($_GET['error']) && !empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= e($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (!empty($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill"></i> Дія успішно виконана!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-calendar-date"></i> Сьогодні</h5>
                        <p class="card-text"><?= currentDateUkrainian() ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-speedometer2"></i> Час генерації</h5>
                        <p class="card-text"><?= number_format($generation_time, 4) ?> сек</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-clock-history"></i> Ваш стаж</h5>
                        <p class="card-text">
                            <?= $adminRegisteredAt ? formatRegistrationTime($adminRegisteredAt) : 'Невідомо' ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="mb-0"><i class="bi bi-people-fill"></i> Управління користувачами</h2>
            </div>
            <div class="card-body">
                <?php if (empty($users)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> Не вдалося завантажити список користувачів
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Користувач</th>
                                    <th>Роль</th>
                                    <th>Статус</th>
                                    <th>Дата реєстрації</th>
                                    <th class="action-buttons">Дії</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= e($user['id']) ?></td>
                                    <td>
                                        <div><?= e($user['username']) ?></div>
                                        <div class="user-time">
                                            <i class="bi bi-clock"></i> <?= formatRegistrationTime($user['created_at']) ?>
                                        </div>
                                    </td>
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
                                    <td class="action-buttons">
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?= e($user['id']) ?>">
                                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                            
                                            <?php if ($user['is_blocked']): ?>
                                                <button type="submit" name="action" value="unblock" class="btn btn-sm btn-success">
                                                    <i class="bi bi-unlock"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" name="action" value="block" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-lock"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <button type="submit" name="action" value="delete" 
                                                    class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Видалити користувача <?= e(addslashes($user['username'])) ?>?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        <?php else: ?>
                                            <span class="text-muted">Поточний</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>