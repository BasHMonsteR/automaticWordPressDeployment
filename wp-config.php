<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wp' );

/** Database username */
define( 'DB_USER', 'wp_user' );

/** Database password */
define( 'DB_PASSWORD', 'SupeRStrong#7' );

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
define( 'AUTH_KEY',         ';>7b4e!zP~BTp@l*4lYy|l3nHL9xKYxb~P^&%(N2u/NE9:?3FYbFDzDLk3xt+:A)' );
define( 'SECURE_AUTH_KEY',  'N&FJzBes!.YA8G1p?z)QH+:4J[6r[4yQluQfyw$m=/*-=fdcK7)^M(6@7^dz%*&X' );
define( 'LOGGED_IN_KEY',    'Wm9g@(n<;%9<,hi;u=SE.=&Rd[&[_,2YP~K;&2*uFNb}NBT{u*yWUFZ2?:{/af8J' );
define( 'NONCE_KEY',        'ny(b>X.9-~7y `gaX~pgyP` =/ 4<CNe*)[o&J oIf*i#6f,^I1<Yk>_@zih-.I`' );
define( 'AUTH_SALT',        'IMJ$LRaf@qpGMfod:f4z9FR5VP:ypvNM%}6]u?d%G1KA-M%!FI)GC%!jb<*IP+@u' );
define( 'SECURE_AUTH_SALT', 'SfD{nX~sm#;$(;G6!w>[p/mQC6:bc0)g`pq<{ge07P!VF8xGqBF [v 7WSj>i_8V' );
define( 'LOGGED_IN_SALT',   'e!*@ppfjE@FXs&iWcrDo18u/7x}3o:o0t!|>u6upn~}Se`nOT:3qhU)~PjYzAMcn' );
define( 'NONCE_SALT',       'zEedsu4Ht)1EZ}Y]h$27O{/tS_s]@DU5s>%S3L/2LyA6q{`cNYHSI=Nw^y?KUnu$' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
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
