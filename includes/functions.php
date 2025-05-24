<?php
function handleRegistration() {
    if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['birthdate'])) {
        return false;
    }

    $userData = [
        'id' => uniqid(),
        'ip' => $_SERVER['REMOTE_ADDR'],
        'username' => trim($_POST['username']),
        'email' => trim($_POST['email']),
        'birthdate' => $_POST['birthdate'],
        'gender' => $_POST['gender'] ?? 'other',
        'interests' => implode(', ', $_POST['interests'] ?? []),
        'reg_date' => date('Y-m-d H:i:s')
    ];
    
    return addUser($userData);
}

function getUsers($page, $perPage, $orderBy = 'reg_date', $orderDir = 'DESC') {
    $allUsers = getAllUsers();
    
    // Сортування
    usort($allUsers, function($a, $b) use ($orderBy, $orderDir) {
        if ($a[$orderBy] == $b[$orderBy]) {
            return 0;
        }
        $compare = ($a[$orderBy] < $b[$orderBy]) ? -1 : 1;
        return ($orderDir === 'ASC') ? $compare : -$compare;
    });
    
    // Пагінація
    $offset = ($page - 1) * $perPage;
    return array_slice($allUsers, $offset, $perPage);
}

function getTotalUsers() {
    return count(getAllUsers());
}
?>