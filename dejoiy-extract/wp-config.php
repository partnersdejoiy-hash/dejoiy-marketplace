<?php
define( 'WP_CACHE', true );

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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u101086720_JaGmZ' );

/** Database username */
define( 'DB_USER', 'u101086720_nx47o' );

/** Database password */
define( 'DB_PASSWORD', 'p4fHOylyME' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

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
define( 'AUTH_KEY',          'O1u|v+n]bUUA0qO:}TX}#JACpsQpRMZ,bZC2^(pBK<5-Hu0CB0A!^qce=9]&NIxO' );
define( 'SECURE_AUTH_KEY',   'vrCd+L!seFV!^}uIf1!8O2sCO]1NsDo9`*c6?GznC:_U2AKz7~nXa&wjg+ApH_A-' );
define( 'LOGGED_IN_KEY',     '.%EWa&0,/2n {@=S7{[Jv6K~Dxg$7oA%EFaZ;}zcnQ]vY=q5m&4hMAJvIB>Q++&n' );
define( 'NONCE_KEY',         '/)enzByC*R$CB(BFoM8(_$l/NFVVXYx]o)FQ?+|Yd_4Krl)_kj}DJ]]yD4ovPH! ' );
define( 'AUTH_SALT',         'Q[7?:OM7de6hlR7oYDc8T,!6d|{N:k-gj]~r3!uYqp5l9HL_deHCC+?(;kCD^SP}' );
define( 'SECURE_AUTH_SALT',  '|Jkc-~YDVD%-$PJw4H;aHf&5Zo= J(kVz.V:(F(spU|7y<HnR8>xZy8-<>22Q:t_' );
define( 'LOGGED_IN_SALT',    'YMHfTE`(d,I%voSdd@N63zk(`=x^UVLE%0>ebcq?%>|@hk:/+e(.@};UZRTl!KT)' );
define( 'NONCE_SALT',        '!MyJVaLt++BV%):9@#/lHp`S}V<4nQKFYyP2yO;$]rk8Gz2iQMY;uW6Xhx1!PPIu' );
define( 'WP_CACHE_KEY_SALT', 'Bvq9?_7P+v58ZqAm! ezxj/7N:pBUy}`J/z:[!spr`h`J}mc#!SY!.+>saKpinTP' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'FS_METHOD', 'direct' );
define( 'COOKIEHASH', '8f9be602ec79f9dd4320c07f75f539e7' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
