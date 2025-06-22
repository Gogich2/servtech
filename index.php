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
    <div class="poll-container">
    <h3>Голосування</h3>
    <form id="pollForm">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="pollOption" id="option1" value="1">
            <label class="form-check-label" for="option1">Варіант 1</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="pollOption" id="option2" value="2">
            <label class="form-check-label" for="option2">Варіант 2</label>
        </div>
        <button type="submit" class="btn btn-primary mt-2">Проголосувати</button>
    </form>
    <div id="pollResults" class="mt-3"></div>
</div>

<script>
// Обробка голосування
document.getElementById('pollForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('vote.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        loadPollResults();
    });
});

// Завантаження результатів
function loadPollResults() {
    fetch('get_results.php')
        .then(response => response.json())
        .then(data => {
            let html = '<h4>Результати:</h4>';
            data.forEach(option => {
                html += `<p>${option.title}: ${option.votes} голосів (${option.percent}%)</p>`;
            });
            document.getElementById('pollResults').innerHTML = html;
        });
}

// Завантажити результати при завантаженні сторінки
loadPollResults();
</script>
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