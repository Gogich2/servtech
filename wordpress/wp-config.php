<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'user_management' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'Egoga1245' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '6vEG{$pKm)R@]D_*%geN)phn|20#!I3-)+n:|s<+MF>-sdo=^>q:Ue^V!JDr|JKF' );
define( 'SECURE_AUTH_KEY',  '?2j===;6`oJOP8K$]:3Wir!r,*8=7rd`LG 0DYd9=v.=B8.`xprIBIFEi2,B3Gc!' );
define( 'LOGGED_IN_KEY',    'dRB30^B/i}-^Ynj.,_@{7bjcD-]1mqC:IB5kK;p#*`_k0[Wk*I=`r^M{WHs*7N]V' );
define( 'NONCE_KEY',        'gi{`DSjoZ}afu%F@Yty2GQWW?Q3pr1J]><4PuhFY%Hs:$~-kEdY|,HJXLQ?A%^O$' );
define( 'AUTH_SALT',        '9q8Oqe8*vsyaruy|gG)3Ho;>~@(E4x5-3J,w@u8M1+T*w<|Ayo0Uqpe)ce ^^?/9' );
define( 'SECURE_AUTH_SALT', 'd@$N6;}vc*:j#x:VX=AcM`4I%EC*(3B<~kdqbh1R-6feq#M@YQx#mx<?IBXeQ<Gq' );
define( 'LOGGED_IN_SALT',   'qzdc}(Ee<k!yH+imJf_m7<qQN0a)tm#9kXCsK(1l Y>2l)tk.fpCB(<o0beg(mt+' );
define( 'NONCE_SALT',       'n~B4+h[Z]khWQD)XK_%*{3=<zh~`^k,&OFx*5c](2@Aw19?U=3pzW(6w%sg]xR=o' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
