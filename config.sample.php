<?php
/**
 * Global config file
 *
 * Sets database connection Strings and debug option
 *
 * @package Setup
 * @author  Andreas Gohr    <a.gohr@web.de>
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @version $Id: config.sample.php,v 1.10 2012/06/14 17:17:36 andig2 Exp $
 */

/* Database configuration */
$config['db_server']    = 'localhost';  // database server
$config['db_user']      = 'videodb';    // DB user for connection
$config['db_password']  = '';           // password for above user
$config['db_database']  = 'videodb';    // Database name
$config['db_prefix']    = 'videodb_';   // Database table prefix (for use in hosting environments)

$config['offline']      = 0;            // Use to take videoDB offline

// Boxee box configuration
$config['boxeeHost']        	= '';	// boxee box host
$config['boxeePort']        	= 9090;	// boxee box port

/* Debug options */
$config['debug']                = 0;    // Usually leave this at 0
$config['httpclientlog']        = 0;    // HttpClient logging (debug only)

/* Cache configuration */
$config['IMDBage']      = 3600*24*7;    // Maximum cache age in seconds
$config['hierarchical']         = 1;    // Set to 1 to enable hierarchical cache folders (if existing!)
$config['cache_pruning']        = 1;    // Set to 1 to enable automatical cleanup of cache folders

/* Defaults for external data lookup */
$config['lookupdefault_edit']   = 0;    // 0=ignore, 1=lookup, 2=overwrite
$config['lookupdefault_new']    = 2;    // 0=ignore, 1=lookup, 2=overwrite

/* 
 * Amount of digits which are automatically generated as DiskID
 * if "Automatic DiskID" is enabled in the configuration tab
 */
$config['diskid_digits']    = 4;

/* XML Import/Export */
$config['xml']              = 0;        // Set to 1 to enable XML data im/export - import requires php5

/* RSS Feed */
$config['rss']              = 1;        // Set to 1 to enable RSS Feed


/*
 * Thumbnail configuration
 *
 * If you're running videodb over a low bandwidth connection with many users or want to enhance 
 * image quality by applying smooth scaling, use the following settings to control the behavior.
 *
 * Define when thumbnails are created and which jpeg quality to use:
 */ 
define('TUMB_NO_SCALE',   -1);  // no scaling     - use of thumbnails is disabled
define('TUMB_REDUCE_ONLY', 0);  // reduce only    - create thumbnails when requested image dimensions are smaller than original image
define('TUMB_SCALE',       1);  // always scale   - create thumbnails for all images (applies aliasing when scaling)

// or define a positive integer to check filesize - thumbnail is created when existing file is bigger than specified value
$config['thumbnail_level']      = TUMB_SCALE;
$config['thumbnail_quality']    = 80;

/*
 * To enable PDF export you can uncomment the following line and edit 
 * the settings in pdf.inc.php.
 * The required FPDF library is bundled in the lib/fpdf folder.
 */
require_once 'pdf.inc.php';

/*
  * To enable XLS export you can uncomment the following line.
 * The required libraries are now included via composer in folders
 * vendor/pear/ole and Vendor/pear/spreadsheet_excel_writer
 */
require_once 'xls.inc.php';

/*
 *  To get access to FSK18 rated movies in the german dvdb engine you
 *  have to enter your dvdb user id and password. If you don't have a 
 *  user you can go to http://www.dvdb.de and click on 'Neu registrieren'.
 *  Don't forget to enter the identification card id to get FSK18 access!
 */
$config['dvdb_user']     = '';
$config['dvdb_password'] = '';

?>
