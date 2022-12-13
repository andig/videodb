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
 * -1 : no scaling   - use of thumbnails is disabled
 *  0 : reduce only  - create thumbnails when requested image dimensions are smaller than original image
 *  1 : always scale - create thumbnails for all images (applies aliasing when scaling)
 * 
 * or define a positive integer to check filesize - thumbnail is created when existing file is bigger than specified value
 */
$config['thumbnail_level']      = 1;
$config['thumbnail_quality']    = 80;

// PDF Export
//      set to 1 to enable PDF data im/export
$config['pdf'] = 0;                     

// path to FPDF
//	    enter the path to fpdf.php (include trailing slash)
$config['pdf_module']       = './lib/fpdf/';

// font settings
//      if you want to use fonts not included in the FPDF distribution,
//      you'll need to do a one-time import into FPDF like this:
//
//            require './makefont/makefont.php';
//            $font = 'tahoma';
//            MakeFont('d:/windows/fonts/'.$font.'.ttf', $font.'.afm');
//            MakeFont('d:/windows/fonts/'.$font.'bd.ttf', $font.'b.afm');

$config['pdf_font_title']       = 'Arial';
$config['pdf_font_plot']        = 'Times';

// Overall font size. Title will be this size and the plot will be one point smaller.
$config['pdf_font_size']        = 10;

// Maximum "rescale" width and height for images
$config['pdf_image_max_height'] = 135;
$config['pdf_image_max_width']  = 95;
$config['pdf_image_media_width']= 8;

// Total Page width
$config['pdf_page_width']       = 210;

// Maximum plot text lenght
$config['pdf_text_length']      = 500;

// Margins 
$config['pdf_margin']           = 5; // margins between fields
$config['pdf_left_margin']      = 5;
$config['pdf_right_margin']     = 5;

// Image height and width on generated PDF
$config['pdf_image_height']     = 24;
$config['pdf_image_width']      = (int) $config['pdf_image_max_width'] / $config['pdf_image_max_height'] * $config['pdf_image_height'];


/*
 * To enable XLS export you can uncomment the following line.
 * The required libraries are now included via composer in folders
 * vendor/pear/ole and Vendor/pear/spreadsheet_excel_writer
 */

/* XLS Export */
$config['xls'] = 0;               // Set to 1 to enable Excel data im/export

// Name of the Excel sheet and headline for printing
$config['xls_sheet_title']     = 'VideoDB';

// Filename for the output file without xls extension!
$config['xls_output_filename'] = 'VideoDB';

// Show column headlines in the first row                 (1=Yes;0=No)
$config['xls_show_headline']   = 1;

// Set background color of unseen movie titles to yellow? (1=Yes;0=No) 
$config['xls_mark_unseen']     = 1;

// Set background color of borrowed movies to red?        (1=Yes;0=No)
$config['xls_mark_lent']       = 1;

/*
 * Build your own list, where you define which fields you want and in 
 * which order they should appear.
 * 
 * Supported fields are: 
 *    title       diskid    language    mediatype    runtime    year
 *    custom1     custom2   custom3     custom4      owner      lent 
 *    insertdate  genres    plot
 * 
 *  The length of the plot is limited to 253 characters!!!
 * 
 *  It's possible to use those fields also as an Excel note. For example if you want 
 *  to see the title of the movie, followed by the diskid and the running time. As 
 *  note you want to see the plot next to the title and the owner next to the diskid. 
 *  For this example the xls_extra_fields list would look like this:
 * 
 *  $config['xls_extra_fields'] = 'title (plot), diskid (owner), runtime';
 * 
 */
$config['xls_extra_fields']    = 'title (plot), diskid, genres, language, mediatype, runtime, year, custom1, custom2, custom3, custom4, insertdate, owner, lent';

/*
 *  To get access to FSK18 rated movies in the german dvdb engine you
 *  have to enter your dvdb user id and password. If you don't have a 
 *  user you can go to http://www.dvdb.de and click on 'Neu registrieren'.
 *  Don't forget to enter the identification card id to get FSK18 access!
 * @defunct
 */
$config['dvdb_user']     = '';
$config['dvdb_password'] = '';

