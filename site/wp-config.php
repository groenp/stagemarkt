<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //

if ($_SERVER['SERVER_PORT'] == "80" || $_SERVER['SERVER_PORT'] == "443") // default port number, so it must be on host

{			// *** GODADDY *** //

	/** The name of the database for WordPress */
	define( 'DB_NAME', 'i235769_wp6' );

	/** MySQL database username */
	define( 'DB_USER', 'i235769_wp6' );

	/** MySQL database password */
	define( 'DB_PASSWORD', 'A.dtl1Bjnv8CkIxATgJ98' );

} else { // *** LOCAL *** //

	/** The name of the database for WordPress */
	define( 'DB_NAME', 'i235769_groenp' );

	/** MySQL database username */
	define( 'DB_USER', 'i235769_groenp' );

	/** MySQL database password */
	define( 'DB_PASSWORD', 'L.Yhapy3dKkkq5mPEL' );
}


/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'J;rd7>Swe9,- 5t5LvmX.e{qU^LJTyuU6W=KH[fcM}uy:eT>e/E+[:1ATJ~.<Ddw' );
define( 'SECURE_AUTH_KEY',  '?Kv6q+&2&jr!uKtKj/A`gvBbAxZab[(Z@!K6e_61s.^lmIM{@C[0UFx++YjL@4Hm' );
define( 'LOGGED_IN_KEY',    'vq49[UzI&`8-;d8laQ| @dmc`JG3.#ds_3Mb*THl=M-fM_l.Eh#e7*,;wTF?[]q.' );
define( 'NONCE_KEY',        '[ERQmtQ)e#?Hl7k(Iscut yFm[.9y(!6a=A_`S&fwu6ps$[WzqJ2`WA~Vv^?IW1.' );
define( 'AUTH_SALT',        'wnP~Ip+;D2:<yYGD5QgWJGdNV|:g,2ocJ+Um[0TR(A/!?FDxJ{3RQ 6S59wg=>b#' );
define( 'SECURE_AUTH_SALT', 'bt)d5u,eC8yrW7j@=v+H+]!:g{oXZi:b}|ze9xPu1t?l7?%R(kiE`_x0`riFb?mZ' );
define( 'LOGGED_IN_SALT',   'mvfhiI,6D5N]#4I@R5-bT/|[FpjE@*FoQ]`-P(b1i&6^;44rop[<Rym~7YNMN]TF' );
define( 'NONCE_SALT',       'G_nnt||&[6crFP-t-KqO+^[l(*u?H*cPs`[UYT!Cvz%.+:Cc-8Yc(;sm[O0bC-#C' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */

 // first make sure it never shows in production
if ( strpos($_SERVER['SERVER_NAME'], "admin.groenproductions.com") === false ) {

    @ini_set('display_errors','1');
	error_reporting(E_ALL);
	
  	define('WP_DEBUG',         true);  // Turn debugging ON
  	define('WP_DEBUG_DISPLAY', false); // Turn forced display OFF
  	define('WP_DEBUG_LOG',     true);  // Turn logging to wp-content/debug.log ON
}

// WP Mail SMTP password for: registration@groenproductions.com
define( 'WPMS_ON', true );
define( 'WPMS_SMTP_PASS', 'reggpvYI=ukg5S^Z' );

// SVG support, but be careful - only allow for Admin ie. myself 
define('ALLOW_UNFILTERED_UPLOADS', true);

/* That's all, stop editing! Happy publishing. */


/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
