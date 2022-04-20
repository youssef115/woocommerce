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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

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
define( 'AUTH_KEY',         '<niny]kBBCh[Gv_:U RoXd]=o,k=BEX&qjvTv7eu+Mz<h@sbFKFzG~vr,@3Oh.)[' );
define( 'SECURE_AUTH_KEY',  '-rsDw[~Kz.B>pcxhC`Qq*zL+wMmG=2~q.K|JI&jS>Sc9Qce<c|?*w7pduR?cEY+e' );
define( 'LOGGED_IN_KEY',    'Y`^!=.BfLK@E{CAzf:U2+~:C83m:CVd/0`6VSl !h~xD#<wwM?BtF%E~nOkV1W1G' );
define( 'NONCE_KEY',        'MD<@qYUObt79LD{N>E;<wczx,la14tt@Vu;FQ&1 Y9330dKv!r/rg}Mb$V G8-l;' );
define( 'AUTH_SALT',        'avg`*(IUCzx^2mf|e*53{(T`{n,&w)<r;.Rjx<Y4&GHkV8BO1jca5A3|j,l:JGj4' );
define( 'SECURE_AUTH_SALT', '/MI;2%<qwjP]uNnrGfS{XAP?YM,`cL0E#IEB2>7oGr8mx!t: @G1qIAkA[n)}>Lg' );
define( 'LOGGED_IN_SALT',   'I*rPKX2j6g=Bd[1vxqNzE[.$6bV,w*)K:45+pjnkAVg=b:5Si;[Wj:^n((o*D4TG' );
define( 'NONCE_SALT',       'e=*@yV3yA`w/lTRMS CJN>0!0PyXO~;a?HQ{Ro-$(_S0uPgG1%0,3>SFrXt~!j2r' );

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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
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
