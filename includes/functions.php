<?php
/**
 * Виводить повідомлення про помилки/успіх
 */
function display_messages() {
    if (!empty($_GET['error'])) {
        $errors = [
            'account_blocked' => 'Ваш обліковий запис заблоковано!',
            'access_denied' => 'Доступ заборонено!',
            'login_failed' => 'Невірний логін або пароль!'
        ];
        
        if (isset($errors[$_GET['error']])) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($errors[$_GET['error']]) . '</div>';
        }
    }

    if (!empty($_GET['success'])) {
        $successes = [
            'registered' => 'Реєстрація успішна! Тепер ви можете увійти.',
            'logged_out' => 'Ви успішно вийшли з системи.'
        ];
        
        if (isset($successes[$_GET['success']])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($successes[$_GET['success']]) . '</div>';
        }
    }
}


if (!function_exists('e')) {
    /**
     * Escape output to prevent XSS attacks
     * @param string $string The string to escape
     * @return string Escaped string
     */
    function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Генерує HTML для пагінації
 */
function generate_pagination($total_items, $items_per_page, $current_page, $base_url) {
    $total_pages = ceil($total_items / $items_per_page);
    
    if ($total_pages <= 1) return '';

    $html = '<nav><ul class="pagination">';
    
    // Попередня сторінка
    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '&page=' . ($current_page - 1) . '">&laquo;</a></li>';
    }

    // Сторінки
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = $i == $current_page ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $base_url . '&page=' . $i . '">' . $i . '</a></li>';
    }

    // Наступна сторінка
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '&page=' . ($current_page + 1) . '">&raquo;</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}

/**
 * Екранує HTML вивід
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>