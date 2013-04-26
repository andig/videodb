<?php
/**
 * PDF configuration file for FPDF
 *
 * Installation
 *
 * 1)   modify the pdf_module setting below to match your fpdf installation path
 * 2)   modify the following settings to control PDF generation
 *
 * @package Setup
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @link    http://www.fpdf.org
 * @version $Id: pdf.inc.php,v 2.7 2008/01/23 09:38:12 andig2 Exp $
 */

// PDF Export
//      set to 1 to enable PDF data im/export
$config['pdf'] = 1;                     

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

?>
