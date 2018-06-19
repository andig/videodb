<?php
/**
 * Constants
 *
 * Contains global constants for table names
 * Must only be loaded after config.inc.php
 *
 * @package Core
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @version $Id: constants.php,v 1.65 2013/04/25 15:00:32 andig2 Exp $
 */

// Config file
define('CONFIG_FILE', './config.inc.php');
define('VERSION', '4.1.0');

define('LOG_FILE', 'log.txt');

// User Permission bit masks
define('PERM_ADMIN', 1);
define('PERM_READ',  2);
define('PERM_WRITE', 4);
define('PERM_ADULT', 8);
define('PERM_ALL',  -1); // used to check for "all" permissions only
define('PERM_ANY',  -2); // used to check for exististance of any cross-user permission

// Cache folders
define('CACHE_IMG',    'img');
define('CACHE_HTML',   'imdb');
define('CACHE_THUMBS', 'thumbs');
define('CACHE_LOCAL',  'local');   // local images for covers and actors

// Table names
define('TBL_DATA',          $config['db_prefix'].'videodata');

define('TBL_CONFIG',        $config['db_prefix'].'config');
define('TBL_USERCONFIG',    $config['db_prefix'].'userconfig');

define('TBL_USERS',         $config['db_prefix'].'users');
define('TBL_USERSEEN',      $config['db_prefix'].'userseen');
define('TBL_PERMISSIONS',   $config['db_prefix'].'permissions');

define('TBL_ACTORS',        $config['db_prefix'].'actors');
define('TBL_GENRES',        $config['db_prefix'].'genres');

define('TBL_VIDEOGENRE',    $config['db_prefix'].'videogenre');
define('TBL_MEDIATYPES',    $config['db_prefix'].'mediatypes');

define('TBL_LENT',          $config['db_prefix'].'lent');

define('TBL_CACHE',         $config['db_prefix'].'cache');

// Wishlist
define('MEDIA_WISHLIST', 50);

// Amazon associates token
define('AMAZON_ASSOCIATE', 'cpuidle-20');

// Database character set - only valid values are UTF8, LATIN1 (legacy only) or empty
define('DB_CHARSET', 'UTF8');
// Database sort order - if empty sorting is defined by language file or db standard.
// use UTF8_GENERAL_CI or other valid MySQL collation to override
define('DB_COLLATION', '');

// Required database version
define('DB_REQUIRED', 41);

?>
