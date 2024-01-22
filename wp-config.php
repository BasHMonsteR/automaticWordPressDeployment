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
define( 'DB_CHARSET', 'utf8' );

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
define('AUTH_KEY',         'T]9zq=ag.|5lSodxfrP@}n5@fYo<Rq=zp|Y {D@S+(`h!3gQxnTT$yiC2]yDY>h6');
define('SECURE_AUTH_KEY',  ')MgyM}WrcePqXNK_7qwW]k9Cr3|]b/g4d,B:0--I_NyNPFX!8}(S%K.}EuLgVK#p');
define('LOGGED_IN_KEY',    '<XBwjSPkBrL]`Fsg^]_,WmfKy}xmpZPfwuaEZ?lS!mJPtT)l}]{<V)Y|mdZpH>lT');
define('NONCE_KEY',        'VKV&ZhZn/C`7}D7+O||M:AyB|<ngD5Cl7h!3[+V%- %ItRip`^ZDD7omC6)x,Ex^');
define('AUTH_SALT',        'kX~V#Yl=2[/Z3H^n[qDTC #R_hU00d+]++L!Q,eixO-..cFX(6wYe},-}^8*|gGv');
define('SECURE_AUTH_SALT', 'lfd.Y^5mnAha2(}<:(@1@K@:B%!(.vgnhcJ*-|Hx}q>SHVn}N-&{n*fYG[+v[O!]');
define('LOGGED_IN_SALT',   'FtC10rZ4ECLH~^AbHZw-d1]Z39;+Ts^QnuH||&s)(L4OR|:]zx0g!|&ITfLQ_k>5');
define('NONCE_SALT',       ']4n2[H0/XV7&wU-A=T-O_0D[F}$z?^qb>XHQ G6+&ZK;(wc5Ay8=02*R[xZBnY]f');
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
define( 'FS_METHOD', 'direct' );
/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
