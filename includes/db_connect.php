<?php
// Налаштування бази даних
define('DB_HOST', 'localhost');
define('DB_NAME', 'user_management');
define('DB_USER', 'root');
define('DB_PASS', 'Egoga1245');

try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false // Рекомендовано для продуктивного середовища
        ]
    );
    
    // Додаткові налаштування (необов'язково)
    $pdo->exec("SET time_zone = '+00:00'");
    $pdo->exec("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'");
    
} catch (PDOException $e) {
    // Краще писати в лог, а не виводити повідомлення користувачу
    error_log("Database connection error: " . $e->getMessage());
    
    // Користувачеві показуємо загальне повідомлення
    die("Помилка підключення до бази даних. Будь ласка, спробуйте пізніше.");
}
?>