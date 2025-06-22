<?php
header('Content-Type: application/json; charset=utf-8');

// 1. Асоціативний масив в JSON-об'єкт
$assocArray = [
    "university" => "СумДУ",
    "departments" => ["ФІТ", "ФЕМ", "ФПМ"],
    "location" => [
        "city" => "Суми",
        "address" => "вул. Римського-Корсакова, 2"
    ],
    "studentsCount" => 15000,
    "hasDormitory" => true,
    "ranking" => null
];

$jsonObject = json_encode($assocArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// 2. Індексний масив в JSON-масив
$indexedArray = [
    ["id" => 1, "name" => "Програмування", "hours" => 120],
    ["id" => 2, "name" => "Математика", "hours" => 90],
    ["id" => 3, "name" => "Фізика", "hours" => 60]
];

$jsonArray = json_encode($indexedArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// Вивід результатів
$result = [
    "associative_array_to_json" => json_decode($jsonObject),
    "indexed_array_to_json" => json_decode($jsonArray),
    "metadata" => [
        "generated_at" => date("Y-m-d H:i:s"),
        "php_version" => phpversion()
    ]
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>