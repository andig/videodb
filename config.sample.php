<?php
/**
 * Global configuration file
 *
 * In here you can set the database connection settings and a
 * few other configuration options that are not accessible
 * through the setup/profile pages. Copy this file to config.inc.php
 * and view and fill each item as required. Database configuration
 * is the most important part, all other parts are only needed
 * when needed ;-)
 *
 * @package Setup
 * @author Andreas Gohr <a.gohr@web.de>
 * @author Andreas Goetz <cpuidle@gmx.de>
 * @version $Id: config.sample.php,v 1.10 2012/06/14 17:17:36 andig2 Exp $
 */

/**
 * Database configuration 
 */

/**
 * the hostname of your database server
 * @default 'localhost'
 */
$config['db_server'] = 'localhost';

/**
 * the username of your database server
 * @default 'videodb'
 */
$config['db_user'] = 'videodb';

/**
 * the password for above user
 * @default ''
 */
$config['db_password'] = '';

/**
 * the name of the database
 * @default 'videodb'
 */
$config['db_database'] = 'videodb';

/**
 * an optional prefix for the tables (for use in hosting environments)
 * can be empty
 * @default: 'videodb_'
 */
$config['db_prefix'] = 'videodb_';

/**
 * Offline flag, set to 1 to take videoDB offline
 * @default 0
 */
$config['offline'] = 0;

/**
 * Debug options, set to 1 to enable debug logs
 * Usually leave this at 0 (to keep videoDB fast)
 * @default 0
 */
$config['debug'] = 0;

/**
 * HttpClient logging, is for debugging only
 * Usually leave this at 0 (to keep videoDB fast)
 * @default 0
 */
$config['httpclientlog'] = 0;

/**
 * Boxee box configuration
 * If you still have and use one of these you can enter
 * the hostname and port used. If not, leave as-is
 * @default ''
 */
$config['boxeeHost'] = '';
/**
 * @default 9090
 */
$config['boxeePort'] = 9090;

/**
 * Cache configuration
 * This setting determines how long to keep remote assets locally
 * After this many seconds they are considered stale and will be
 * fetched again for a fresh copy. You can set this to 0, but you
 * probably want to keep this at the default or higher.
 * @default 604800 (one week, 7*24*60*60)
 */
$config['IMDBage'] = 604800;

/**
 * Hierarchical cache folders, used to distribute the cached files
 * over multiple folders instead of one, prevents hitting filesystem
 * limits in due time. Set to 1 to enable, 0 to disable
 * @default 1
 */
$config['hierarchical'] = 1;

/**
 * Pruning means automatically cleaning the cache folders, removing
 * old/stale files. Set to 1 to enable, 0 to disable
 * @default 1
 */
$config['cache_pruning'] = 1;

/**
 * Enable use of HTTP 304 headers for unmodified content to save bandwidth
 * Set to 1 to enable, 0 to disable
 * @default 0
 */
$config['http_caching'] = 0;

/**
 * Defaults for external data lookup when editing entries
 * Set to 0 to ignore external data
 * Set to 1 to lookup missing data
 * Set to 2 to overwrite all entered data with the external version
 * @default 0
 */
$config['lookupdefault_edit'] = 0;

/**
 * Defaults for external data lookup when adding entries
 * Set to 0 to ignore external data
 * Set to 1 to lookup missing data
 * Set to 2 to overwrite all entered data with the external version
 * @default 2
 */
$config['lookupdefault_new'] = 2;

/**
 * Amount of digits which are automatically generated as DiskID
 * if "Automatic DiskID" is enabled in the configuration tab
 * @default 4
 */
$config['diskid_digits'] = 4;

/**
 * Thumbnail configuration
 *
 * If you're running videodb over a low bandwidth connection with many users or want to enhance
 * image quality by applying smooth scaling, use the following settings to control the behavior.
 *
 * Define when thumbnails are created and which jpeg quality to use:
 * -1 : no scaling - use of thumbnails is disabled
 * 0 : reduce only - create thumbnails when requested image dimensions are smaller than original image
 * 1 : always scale - create thumbnails for all images (applies aliasing when scaling)
 *
 * or define a positive integer to check filesize - thumbnail is created when existing file is bigger
 * than the specified value in bytes
 * @default 1
 */
$config['thumbnail_level'] = 1;

/**
 * Control the quality setting when generating jpeg images
 * Is a range from 0 to 100, where 0 is the lowest quality with smallest filesize
 * and 100 is the best quality with largest filesize. Industry standard setting is 70
 * @default 80
 */
$config['thumbnail_quality'] = 80;

/**
 * Export settings
 */

/**
 * XML Import/Export
 * Set to 1 to enable XML data im/export, set to 0 to disable
 * @note import is currently broken
 * @default 0
 */
$config['xml'] = 0;

/**
 * RSS Feed
 * Set to 1 to enable RSS Feed, set to 0 to disable
 * @default 1
 */
$config['rss'] = 1;

/**
 * PDF Export
 * set to 1 to enable PDF data export, set to 0 to disable
 * @default 1
 */
$config['pdf'] = 1; 

/**
 * Here you can set the fonts used for title. Available fonts:
 * - Arial
 * - Courier
 * - Helvetica
 * - Symbol
 * - Times
 * - ZapfDingBats
 * @default 'Arial'
 */
$config['pdf_font_title'] = 'Arial';

/**
 * Here you can set the fonts used for plot. Available fonts:
 * - Arial
 * - Courier
 * - Helvetica
 * - Symbol
 * - Times
 * - ZapfDingBats
 * @default 'Times'
 */
$config['pdf_font_plot'] = 'Times';

/**
 * Overall font size. Title will be this size and the plot will be one point smaller.
 * @default 10
 */
$config['pdf_font_size'] = 10;

/**
 * Maximum "rescale" width for images
 * @default 95
 */
$config['pdf_image_max_width'] = 95;

/**
 * Maximum "rescale" height for images
 * @default 135
 */
$config['pdf_image_max_height'] = 135;

/**
 * Set the width of the mediatype icon
 * @default 8
 */
$config['pdf_image_media_width'] = 8;

/**
 * Total Page width
 * @default 210
 */
$config['pdf_page_width'] = 210;

/**
 * Maximum plot text length
 * @default 500
 */
$config['pdf_text_length'] = 500;

/**
 * Margins between fields
 * @default 5
 */
$config['pdf_margin'] = 5;

/**
 * Left margin
 * @default 5
 */
$config['pdf_left_margin'] = 5;

/**
 * Right margin
 * @default 5
 */
$config['pdf_right_margin'] = 5;

/**
 * Image height and width on generated PDF
 * @default 24
 */
$config['pdf_image_height'] = 24;

/**
 * Image width on generated PDF, do not change
 * @default <calculated-value>
 */
$config['pdf_image_width'] = intval(($config['pdf_image_max_width'] / $config['pdf_image_max_height']) * $config['pdf_image_height']);

/**
 * XLS Export, set to 1 to enable Excel data export, 0 to disable
 * @default 1
 */
$config['xls'] = 1;

/**
 * Name of the Excel sheet and headline for printing
 * @default 'VideoDB'
 */
$config['xls_sheet_title'] = 'VideoDB';

/**
 * Filename for the output file without xls extension!
 * @default 'VideoDB'
 */
$config['xls_output_filename'] = 'VideoDB';

/**
 * Show column headlines in the first row (1=Yes;0=No)
 * @default 1
 */
$config['xls_show_headline'] = 1;

/**
 * Set background color of unseen movie titles to yellow? (1=Yes;0=No)
 * @default 1
 */
$config['xls_mark_unseen'] = 1;

/**
 * Set background color of borrowed movies to red? (1=Yes;0=No)
 * @default 1
 */
$config['xls_mark_lent'] = 1;

/**
 * Build your own list, where you define which fields you want and in 
 * which order they should appear, separated by comma.
 * 
 * Supported fields are: 
 * title diskid language mediatype runtime year
 * custom1 custom2 custom3 custom4 owner lent 
 * insertdate genres plot
 * 
 * The length of the plot is limited to 253 characters!!!
 * 
 * It's possible to use those fields also as an Excel note. For example if you want 
 * to see the title of the movie, followed by the diskid and the running time. As a
 * note you want to see the plot next to the title and the owner next to the diskid. 
 * For this example the xls_extra_fields list would look like this:
 * 
 * $config['xls_extra_fields'] = 'title (plot), diskid (owner), runtime';
 * @default 'title (plot), diskid, genres, language, mediatype, runtime, year, custom1, custom2, custom3, custom4, insertdate, owner, lent'
 */
$config['xls_extra_fields'] = 'title (plot), diskid, genres, language, mediatype, runtime, year, custom1, custom2, custom3, custom4, insertdate, owner, lent';

/**
 * To get access to FSK18 rated movies in the german dvdb engine you
 * have to enter your dvdb user id and password. If you don't have a 
 * user you can go to http://www.dvdb.de and click on 'Neu registrieren'.
 * Don't forget to enter the identification card id to get FSK18 access!
 * @default ''
 * @deprecated The dvdb.de website does not appear to exist anymore
 */
$config['dvdb_user'] = '';
$config['dvdb_password'] = '';

