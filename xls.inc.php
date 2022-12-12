<?php
/**
 * XLS configuration file for Spreadsheet_Excel_Writer
 *
 * Installation
 *
 * modify the xls_mode setting below to match your
 * Spreadsheet_Excel_Writer installation path
 *
 * @package Setup
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @link    http://pear.php.net/package/Spreadsheet_Excel_Writer
 * @version $Id: xls.inc.php,v 2.4 2008/01/27 20:36:17 andig2 Exp $
 */

/* XLS Export */
$config['xls'] = 1;               // Set to 1 to enable Excel data im/export

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
 * $config['xls_extra_fields'] is now set in config.inc.php 
 * 
 * Below code included only to stop problems if users
 * config.inc.php has not been updated inline with config.sample.php
 * as not sure how to enforce.
 * 
*/
if (!isset($config['xls_extra_fields']))
{
    $config['xls_extra_fields']    = 'title (plot), diskid, genres, language, mediatype, runtime, year, custom1, custom2, custom3, custom4, insertdate, owner, lent';
}

