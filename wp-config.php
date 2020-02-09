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
define( 'DB_NAME', 'smart747_db' );

/** MySQL database username */
define( 'DB_USER', 'smart747_user' );

/** MySQL database password */
define( 'DB_PASSWORD', 'm[Dx$O[upiTV' );

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
define( 'AUTH_KEY',         'F2W$=/3Czjrwj6)zpJ|gvYi^{2KJ0FVr,(*Ff?s%T!o=W:INI^& Oxi2,*hY7Z19' );
define( 'SECURE_AUTH_KEY',  'dhxAl}2$DK3X M{`W?N22I8:`+YjB6NR1/d5*AigB{)1C){]m2i~Z=h9LX?=wx,G' );
define( 'LOGGED_IN_KEY',    '<Oi!e3AwUc)wZP]Kx;`b|0;x;prMJJG[|o{w3.4E&jyh]DWF,@41&cz@C+z`-O|4' );
define( 'NONCE_KEY',        'BN}{_5xl9/rQg5<+L|v?1V]Z$He^E?QjAE)~5vZ6SVYcgnc#M5d`Kf3a`Xr*+x/`' );
define( 'AUTH_SALT',        '.eRqZDi]J!?rt-aNMatfq^[?qey(+TkdDrT/Sf&@WHP54r]E-CivgOE(7/}8%Pee' );
define( 'SECURE_AUTH_SALT', 'w@G6orzNDgov5M`:m.J4KX#WM :Vfmob1U$.p)Q{z536Jb~jxSYS<q)5}#[>-s>M' );
define( 'LOGGED_IN_SALT',   '6w]oTedR*nnwKy>ku0}snv4#<o%lhKqPGw+8k)iyFsWh2@Er(yE9QLl/IxFK@^[{' );
define( 'NONCE_SALT',       'n1odO7vf{AK0`R9tpc9,,w[@&d vyXx-YY6Ll9(Cp@q(Yg(9a>CaEt9fF*,^,zB6' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'sm_';

define( 'SMTP_HOST', 'mail.s-martsg.com' );  // A2 Hosting server name. For example, "a2ss10.a2hosting.com"
define( 'SMTP_AUTH', true );
define( 'SMTP_PORT', '465' );
define( 'SMTP_SECURE', 'ssl' );
define( 'SMTP_USERNAME', 'sales@s-martsg.com' );  // Username for SMTP authentication
define( 'SMTP_PASSWORD', 'j55e%bOxqscX' );          // Password for SMTP authentication
define( 'SMTP_FROM',     'sales@s-martsg.com' );  // SMTP From address
define( 'SMTP_FROMNAME', 'Sales' );         // SMTP From name



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
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
