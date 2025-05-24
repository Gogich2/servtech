<?php
// Шлях до файлу з даними
$dataDir = __DIR__ . '/../data';
$dataFile = $dataDir . '/users.txt';

// Перевірка та створення папки data, якщо не існує
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0777, true);
}

// Перевірка та створення файлу, якщо не існує
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, '');
}

// Функція для зчитування всіх користувачів
function getAllUsers() {
    global $dataFile;
    $users = [];
    
    if (filesize($dataFile) > 0) {
        $lines = file($dataFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $user = json_decode($line, true);
            if ($user) {
                $users[] = $user;
            }
        }
    }
    
    return $users;
}

// Функція для додавання нового користувача
function addUser($userData) {
    global $dataFile;
    $line = json_encode($userData) . PHP_EOL;
    return file_put_contents($dataFile, $line, FILE_APPEND) !== false;
}
?>