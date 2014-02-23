<?php

/*
SET PERMSSISIONS
<files mypasswdfile>
order allow,deny
deny from all
</files>
*/


/**
 * Configuration settings 
 *
 * 
 */

// Enable TESTING to see debug messages in the response
define('TESTING', TRUE);

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database  */
define('DB_NAME', 'database_name');

/** MySQL database username */
define('DB_USER', 'username');

/** MySQL database password */
define('DB_PASSWORD', 'password');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 * 
 */

/** Cost */
define('BCRYPT_COST', 12);

/** This hash is generated with the default PIN '123' - generate your own hash at keygen.php  **/
define('PASSWORD_HASH','$2y$12$qBZbBy8pYA8Dfzuvy7jZ/.3gzjTCq3BDvvLWuWkdwII3tU.EUjUCG');

/**
 * Status Database Table prefix.
 *
 * You can have multiple installations in one database if you give each table an unique
 * prefix. Only numbers, letters, and underscores please!
 */

define('TABLE_PREFIX',       'st_');


/** Seconds for a validated session to live after succesful validation of a password */
define('SESSION_TIMEOUT', 10); 

/**
 * Cookie settings
 *
 */

//pini_set('session.cookie_path', "/php-app/");

/** Sessions should be limited with the SESSION_TIMEOUT definition and NOT by trusting cookies to expire. Measured in seconds */
ini_set('session.cookie_lifetime', SESSION_TIMEOUT );

//ini_set('session.cookie_secure',1);// specifies whether cookies should only be sent over secure connections. Defaults to off. 
ini_set('session.cookie_httponly',1); // Disables javascript cookie access on some browsers
ini_set('session.use_only_cookies',1); //specifies whether the module will only use cookies to store the session id on the client side. Enabling this setting prevents attacks involved passing session ids in URLs. 



