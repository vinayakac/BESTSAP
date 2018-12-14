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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'bestsap');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'iuyljah4kwy2ufzhwe8xw9txua8kp3rn0jiw2bkrset4uyet6cyorrawo0dbf3eg');
define('SECURE_AUTH_KEY',  'xg98zevbtkkrna1h8124l5ymfclrex8zzoskt6hbzn96aah8p2cuumivru3yubs1');
define('LOGGED_IN_KEY',    'ylxmsucxmlvh2atdxq1mjtu9cnthu0lj6xkqiwxqcfdxnwticmlpvd3aehw93baz');
define('NONCE_KEY',        'dsrwcn9q4cn5hhdgtv5m95gispiafm7m926bfhfdgep6zouhdeum6tsiepwaybdp');
define('AUTH_SALT',        '1onasutjr7fqhadz2ochrgbm4ijlzpmymwkfghrzrzux6jlquzie9oqc54gy6phf');
define('SECURE_AUTH_SALT', 'ti1xr7bfnqd3chnkzprmoditaeci4lulvgftel5ylpykgqf53jiv3zmhhmw1lc1r');
define('LOGGED_IN_SALT',   'm3o3uja08xulxz0qyjlorkspjjoyn16rslthdtyjtl0dcuzndezmobm9gh61p8ba');
define('NONCE_SALT',       'scw0jakpvcsqprgrgms6kldxnqef8segmxqb4huvkxndahhtkphtjawpktbtffmw');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wpub_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
