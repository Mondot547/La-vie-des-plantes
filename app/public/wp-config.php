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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

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
define( 'AUTH_KEY',          'BX(;t/7?C&9>R~DnP@#1wRe~9|5!lBP8~faa7@m?k!Gi7T{M:xS!tN8be#R3[=<;' );
define( 'SECURE_AUTH_KEY',   'I%CT;,D_*^a?]0gRO+r]L*cVLZ%v+=qodn4Q>v>YD@]4XCLxf su}^]ebmX!3c3@' );
define( 'LOGGED_IN_KEY',     'mDEdX!bR}R+!hXD((AgV{_#?H2^Sn^D5xa?@Ud9)a(4Qi9(Io_k`ss~]1)Soz8Al' );
define( 'NONCE_KEY',         '(B^X&dI0VH{n&blKN;o8QbLCxfdFdIU^Gi6-g@[CrC1m~?0>5s[F|B4uL>uovaYD' );
define( 'AUTH_SALT',         'a]MmEfhDSMA~8k;fr]elS`Mpxb?P1@tIl)u`e^FG741yE0+My9a.}*N+$(ZjoExQ' );
define( 'SECURE_AUTH_SALT',  'bKKlzuCeZlU}7-A/=!?4yy;HW8he@GA/BLCa)6ZIk99!O,TpOzGd<tPRPHa)&t[1' );
define( 'LOGGED_IN_SALT',    'Hcmn T}pb02:Yqs(FXArJ.IYN6DUE%:c4,.+0i]uiZ#0D!_w&?<rm}?t&EnJQ5b,' );
define( 'NONCE_SALT',        ']apl;c}W|h=W.)P4U0$InL,TC/P_[&A*mNe,,,9Tvp/B$su|L8O%S}4MVgG5bqJ,' );
define( 'WP_CACHE_KEY_SALT', '0q][Bb4&LXEO{?3I]{>B#Od(*lv(|8->9B5<1./1a<kNOoOfz(Wp||O1mhM<)N0O' );


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

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
