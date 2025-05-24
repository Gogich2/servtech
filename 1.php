<?php
// Встановлення рівня помилок (опціонально)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Підключення конфігурації та функцій
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Обробка форми реєстрації
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    handleRegistration();
}

// Налаштування пагінації
$currentPage = max(1, $_GET['page'] ?? 1);
$perPage = min(max(5, $_GET['per_page'] ?? 5), 50);

// Безпечне отримання параметрів сортування
$orderBy = $_GET['order_by'] ?? 'reg_date';
$allowedOrderFields = ['username', 'birthdate', 'reg_date'];
$orderBy = in_array($orderBy, $allowedOrderFields) ? $orderBy : 'reg_date';

$orderDir = $_GET['order_dir'] ?? 'DESC';
$orderDir = $orderDir === 'ASC' ? 'ASC' : 'DESC';

// Отримання даних
$users = getUsers($currentPage, $perPage, $orderBy, $orderDir);
$totalUsers = getTotalUsers();
$totalPages = max(1, ceil($totalUsers / $perPage)); // Забезпечуємо мінімум 1 сторінку
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Система реєстрації користувачів</title>
</head>
<body>
    <div class="container">
        <h1>Система реєстрації користувачів</h1>
        
        <section class="registration-section">
            <h2>Форма реєстрації</h2>
            <form method="post">
                <div class="form-group">
                    <label for="username">Ім'я користувача:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="birthdate">Дата народження:</label>
                    <input type="date" id="birthdate" name="birthdate" required>
                </div>
                
                <div class="form-group">
                    <label for="gender">Стать:</label>
                    <select id="gender" name="gender" required>
                        <option value="male">Чоловіча</option>
                        <option value="female">Жіноча</option>
                        <option value="other">Інше</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Інтереси:</label>
                    <div>
                        <input type="checkbox" id="sport" name="interests[]" value="sport">
                        <label for="sport" style="display: inline; font-weight: normal;">Спорт</label>
                    </div>
                    <div>
                        <input type="checkbox" id="music" name="interests[]" value="music">
                        <label for="music" style="display: inline; font-weight: normal;">Музика</label>
                    </div>
                    <div>
                        <input type="checkbox" id="books" name="interests[]" value="books">
                        <label for="books" style="display: inline; font-weight: normal;">Книги</label>
                    </div>
                </div>
                
                <button type="submit" name="register">Зареєструватися</button>
            </form>
        </section>

        <section class="users-section">
            <h2>Список користувачів</h2>
            
            <div class="controls">
                <form method="get">
                    <input type="hidden" name="page" value="<?= $currentPage ?>">
                    
                    <label>Кількість на сторінці:
                        <select name="per_page" onchange="this.form.submit()">
                            <option value="3" <?= $perPage == 3 ? 'selected' : '' ?>>3</option>
                            <option value="5" <?= $perPage == 5 ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                            <option value="20" <?= $perPage == 20 ? 'selected' : '' ?>>20</option>
                        </select>
                    </label>
                    
                    <label>Сортувати за:
                        <select name="order_by" onchange="this.form.submit()">
                            <option value="username" <?= $orderBy == 'username' ? 'selected' : '' ?>>Ім'ям</option>
                            <option value="birthdate" <?= $orderBy == 'birthdate' ? 'selected' : '' ?>>Датою народження</option>
                            <option value="reg_date" <?= $orderBy == 'reg_date' ? 'selected' : '' ?>>Датою реєстрації</option>
                        </select>
                    </label>
                    
                    <label>Напрямок:
                        <select name="order_dir" onchange="this.form.submit()">
                            <option value="ASC" <?= $orderDir == 'ASC' ? 'selected' : '' ?>>За зростанням (A-Z)</option>
                            <option value="DESC" <?= $orderDir == 'DESC' ? 'selected' : '' ?>>За спаданням (Z-A)</option>
                        </select>
                    </label>
                </form>
            </div>
            
            <?php if (!empty($users)): ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ім'я</th>
                            <th>Email</th>
                            <th>Дата народження</th>
                            <th>Дата реєстрації</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars(substr($user['id'] ?? '', 0, 8)) ?></td>
                                <td><?= htmlspecialchars($user['username'] ?? '') ?></td>
                                <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                                <td><?= htmlspecialchars($user['birthdate'] ?? '') ?></td>
                                <td><?= htmlspecialchars($user['reg_date'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=1&per_page=<?= $perPage ?>&order_by=<?= $orderBy ?>&order_dir=<?= $orderDir ?>">Перша</a>
                        <a href="?page=<?= $currentPage-1 ?>&per_page=<?= $perPage ?>&order_by=<?= $orderBy ?>&order_dir=<?= $orderDir ?>">‹ Попередня</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $currentPage-2); $i <= min($totalPages, $currentPage+2); $i++): ?>
                        <a href="?page=<?= $i ?>&per_page=<?= $perPage ?>&order_by=<?= $orderBy ?>&order_dir=<?= $orderDir ?>"
                           class="<?= $i == $currentPage ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?= $currentPage+1 ?>&per_page=<?= $perPage ?>&order_by=<?= $orderBy ?>&order_dir=<?= $orderDir ?>">Наступна ›</a>
                        <a href="?page=<?= $totalPages ?>&per_page=<?= $perPage ?>&order_by=<?= $orderBy ?>&order_dir=<?= $orderDir ?>">Остання</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>Немає зареєстрованих користувачів.</p>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>