<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <header>
        <div class="logo">
            <?php if (has_custom_logo()) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <a href="<?php echo home_url(); ?>"><?php bloginfo('name'); ?></a>
            <?php endif; ?>
        </div>
        
        <nav class="main-menu">
            <?php wp_nav_menu(array('theme_location' => 'primary')); ?>
        </nav>
    </header>