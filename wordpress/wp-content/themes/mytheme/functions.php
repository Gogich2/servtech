<?php
function mytheme_setup() {
    // Підтримка мініатюр постів
    add_theme_support('post-thumbnails');
    
    // Підтримка кастомного лого
    add_theme_support('custom-logo');
    
    // Реєстрація меню
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'mytheme'),
        'footer' => __('Footer Menu', 'mytheme')
    ));
}
add_action('after_setup_theme', 'mytheme_setup');

// Підключення стилів та скриптів
function mytheme_scripts() {
    wp_enqueue_style('mytheme-style', get_stylesheet_uri());
    wp_enqueue_style('mytheme-custom', get_template_directory_uri() . '/css/custom.css');
}
add_action('wp_enqueue_scripts', 'mytheme_scripts');
?>