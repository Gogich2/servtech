<?php
session_start();

// Лічильник відвідувань
if (!isset($_SESSION['visits'])) {
    $_SESSION['visits'] = 1;
} else {
    $_SESSION['visits']++;
}

// Перевірка авторизації
$isLoggedIn = isset($_SESSION['user']);
$username = $isLoggedIn ? $_SESSION['user'] : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Головна сторінка</title>
</head>
<body>
    <h1>Ласкаво просимо!</h1>
    
    <?php if ($isLoggedIn): ?>
        <p>Ви увійшли як <?= htmlspecialchars($username) ?></p>
        <a href="logout.php">Вийти</a>
    <?php else: ?>
        <a href="login.php">Увійти</a> | <a href="register.php">Зареєструватися</a>
    <?php endif; ?>
    
    <p>Кількість відвідувань цієї сторінки: <?= $_SESSION['visits'] ?></p>
</body>
</html>