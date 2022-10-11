<?php
/**
 * Custom handlers
 *
 * Defines functions for displaying custom fields.
 * To add your own type define input and output functions for it here. These
 * functions will be called in show.php (output) and edit.php (input). You can
 * get other values from these files by global'ing them into your function. See
 * ed2k as an example.
 *
 * @todo    Check if this can be moved to Smarty plugins
 *
 * @package Custom
 * @author  Andreas Gohr    <a.gohr@web.de>
 * @version $Id: custom.php,v 1.16 2008/10/03 14:18:04 andig2 Exp $
 */


/* 
  Hint:

  $cn
    holds the name of the custom field (eg. 'custom2') use this as the name for
    the input formfield

  $cv
    holds the current value of that field if any. When you want to use it as
    value in a formfield quote it with the formvar() function!

  When you add new types send them to me. I will include them here.
*/


///////////////////////////////////////////////////////////////////////////////

/* This array contains all available types - be sure to add your type if
   you ad a new one */
$allcustomtypes=array('',
                      'text',
                      'url',
                      'ed2k',
                      'language',
                      'orgtitle',
                      'movix',
                      'mpaa',
                      'bbfc',
                      'fsk',
                      'barcode'
                      );

/**
 * Assigns custom field names and values to input object
 * @author                Andreas Goetz <cpuidle@gmx.de>
 * @param  hashref $video Reference to video hash structure
 * @param  string  $inout Either in or out, determines type of control 
 *						  returned (html input control or rendered output)
 */
function customfields(&$video, $inout)
{
	global $config;

	$inout_function = ($inout == 'in') ? '_input' : '_output';
	for ($i=1; $i < 5; $i++) 
    {
		if (!empty($config['custom'.$i])) 
        {
			$video['custom'.$i.'name'] = $config['custom'.$i];
			$run = 'custom_'.$config['custom'.$i.'type'].$inout_function;
			$video['custom'.$i.$inout]  = $run('custom'.$i,$video['custom'.$i]);			
		}
	}
}

/**
 * Custom Type: 
 *
 * Standardinputhandler for custom fields -> just calls text type
 */
function custom__input($cn,$cv)
{
    return custom_text_input($cn,$cv);
}

/**
 * Custom Type: 
 *
 * Standardoutputhandler for custom fields -> just calls text type
 */
function custom__output($cn,$cv)
{
    return custom_text_output($cn,$cv);
}

/**
 * Custom Type: text
 *
 * Standardinputhandler for custom fields
 */
function custom_text_input($cn,$cv)
{
    return '<input type="text" size="45" maxlength="255" name="'.$cn.'" id="'.$cn.'" value="'.formvar($cv).'" />';
}

/**
 * Custom Type: text
 *
 * Standardouputhandler for custom fields
 */
function custom_text_output($cn,$cv)
{
    return $cv;
}

/**
 * Custom Type: url
 *
 * Stores an URL in a custom file and shows a clickable link.
 */
function custom_url_input($cn,$cv)
{
    return '<input type="text" size="45" maxlength="255" name="'.$cn.'" id="'.$cn.'" value="'.formvar($cv).'" />';
}

/**
 * Custom Type: url
 *
 * Stores an URL in a custom file and shows a clickable link.
 */
function custom_url_output($cn,$cv)
{
    if (!empty($cv))
    {
        return '<a href="'.$cv.'">[ Link ]</a>';
    } else {
        return '';
    }
}

/**
 * Custom Type: ed2k
 *
 * Stores the MD4 sum of the File in a custom file and shows a clickable ed2k
 * Link for the eDonkey2000 client tools.
 */
function custom_ed2k_input($cn,$cv)
{
    return '<input type="text" size="40" maxlength="255" name="'.$cn.'" id="'.$cn.'" value="'.formvar($cv).'" /> (MD4)';
}

/**
 * Custom Type: ed2k
 *
 * Stores the MD4 sum of the File in a custom file and shows a clickable ed2k
 * Link for the eDonkey2000 client tools.
 */
function custom_ed2k_output($cn,$cv)
{
    global $video;

    if (!empty($video[0]['filesize']) && !empty($video[0]['filename']) && !empty($cv)) {
        return '<a href="ed2k://|file|'.$video[0]['filename'].'|'.$video[0]['filesize'].'|'.$cv.'|">[ Add to eDonkey ]</a>';
    } else {
        return '';
    }
}

/**
 * Custom Type: language
 *
 * Language Selection with Quickselectionbuttons configured in 
 * $config['languages']
 */
function custom_language_input($cn,$cv)
{
    global $config;

    $output = '';
    $output .= '<input type="text" size="15" maxlength="255" name="'.$cn.'" id="'.$cn.'" value="'.formvar($cv).'" /> ';
    foreach ($config['languages'] as $flag) 
    {
        $output .= '<a href="#" title="set to '.$flag.'" onclick="document.edi.'.$cn.'.value=\''.$flag.'\'">';
        $output .= '<img src="'.img('flags/'.$flag.'.gif').'" border="0" alt="'.formvar($cv).'" /></a> ';
    }
    return $output;
}

/**
 * Custom Type: language
 *
 * Language Selection with Quickselectionbuttons configured in 
 * $config[languages]
 */
function custom_language_output($cn,$cv)
{
    return custom_text_output($cn,$cv);
}

/**
 * Custom Type: FSK
 *
 * Allows you to set the FSK Rating of a movie.
 * 
 * @author  Chinamann <chinamann@users.sourceforge.net>
 */
function custom_fsk_input($cn,$cv)
{
	return custom_text_input($cn,$cv);
}

/**
 * Custom Type: FSK
 *
 * Allows you to display the FSK Rating.
 * 
 * @author  Chinamann <chinamann@users.sourceforge.net>
 */
function custom_fsk_output($cn,$cv)
{
	$allfsktypes = array('0','6','12','16','18');
	if (!in_array ($cv, $allfsktypes)) 
    {
		return custom_text_output($cn,$cv);
	}

    return '<a href="search.php?q='.$cv.'&fields='.$cn.'"><img border="0" src="'.img('r_'.$cv.'.gif').'" width="50" height="50" /></a>';
}


/**
 * Custom Type: Barcode
 *
 * Allows you to input the Barcode of a movie.
 * 
 * @author  Chinamann <chinamann@users.sourceforge.net>
 */
function custom_barcode_input($cn,$cv)
{
	return custom_text_input($cn,$cv);
}

/**
 * Custom Type: Barcode
 *
 * Allows you to display Barcode.
 * 
 * @author  Chinamann <chinamann@users.sourceforge.net>
 */
function custom_barcode_output($cn,$cv)
{
	return custom_text_output($cn,$cv);
}


/**
 * Custom Type: Originaltitle
 *
 * Holds the Original title of an movie
 *
 * @author Stephan Zalewski <stephan-01@gmx.de>
 */
function custom_orgtitle_input($cn,$cv)
{
    global $config;
    global $imdbdata;
    global $id;

    if (empty($cv) || ($config['lookupdefault'] > 0)) 
    {
        $cv = $imdbdata['title'];
        if (!empty($imdbdata['subtitle'])) $orgtitle .= ' - '.$imdbdata['subtitle'];

        // we need to save our self here!
        if (!empty($id) && $cv != '')
        {
            $qcv = escapeSQL($cv);
            $UPDATE = "UPDATE ".TBL_DATA." SET $cn = '$qcv' WHERE id = $id";
            runSQL($UPDATE);
        }
    }
    return custom_text_input($cn,$cv);
}

/**
 * Custom Type: Originaltitle
 *
 * Holds the Original title of an movie
 *
 * @author Stephan Zalewski <stephan-01@gmx.de>
 */
function custom_orgtitle_output($cn,$cv)
{
    return custom_text_output($cn,$cv);
}

/**
 * Custom Type: Movix
 *
 * Allows you to indicate if a movie is stored in a cd/dvd
 * with movix (http://movix.sourceforge.net/)
 *
 * @author Antonio Giungato <antonio_giungato@libero.it>
 */

function custom_movix_input($cn,$cv)
{
    $output ='<select name="'.$cn.'">
                <option selected value=""></option>
                <option value=eMovix>eMovix</option>
                <option value=Movix>Movix</option>
                <option value=Movix�>Movix�</option>
              </select>';
    return $output;
}

function custom_movix_output($cn,$cv)
{
    return custom_text_output($cn,$cv);
}

/**
 * Custom Type: MPAA
 *
 * Show the MPAA rating of a movie
 *
 * @author Tim M. Sanders <tsanders@thesanders.org>
 */
function custom_mpaa_input($cn,$cv)
{
    global $config;
    global $imdbdata;
    global $id;

    if (empty($cv) || ($config['lookupdefault'] > 0))
    {
        $cv = $imdbdata['mpaa'];
        if (!empty($imdbdata['mpaa'])) $mpaa .= $imdbdata['mpaa'];

        //we need to save our self here!
        if(!empty($id) && $cv != '')
        {
            $qcv = escapeSQL($cv);
            $UPDATE = "UPDATE ".TBL_DATA." SET $cn = '$qcv' WHERE id = $id";
            runSQL($UPDATE);
        }
    }
    $output = '<input type="text" size="50" maxlength="255" name="'.$cn.'" id="'.$cn.'" value="'.formvar($cv).'" /> ';
    return $output;
}

/**
 * Custom Type: MPAA
 *
 * Show the MPAA rating of a movie
 *
 * @author Tim M. Sanders <tsanders@thesanders.org>
 */
function custom_mpaa_output($cn,$cv)
{
    global $imdbdata;
    global $id;

    $ratings = array('Rated R' => 'mpaa-R.gif',
                     'Rated NC-17' => 'mpaa-NC-17.gif',
                     'Rated PG-13' => 'mpaa-PG-13.gif',
                     'Rated PG' => 'mpaa-PG.gif',
                     'Rated G' => 'mpaa-G.gif');

    $output = custom_text_output($cn,$cv);
    foreach ($ratings as $rating => $image) 
    {
        if (strstr($output, $rating)) 
        {
            $output .= '<br/><img src="'.img($image).'" alt="'.$rating.'"/>';
            break;
        }
    }
    return $output;
}

/**
 * Custom Type: BBFC
 *
 * Show the BBFC rating of a movie
 * Based on the MPAA ratings above
 *
 * @author Colin Ogilvie <csogilvie@users.sourceforge.net>
 */
function custom_bbfc_input($cn,$cv)
{
    global $config;
    global $imdbdata;
    global $id;

    if (empty($cv) || ($config['lookupdefault'] > 0))
    {
        $cv = $imdbdata['bbfc'];
        if (!empty($imdbdata['bbfc'])) $bbfc .= $imdbdata['bbfc'];

        //we need to save our self here!
        if(!empty($id) && $cv != '')
        {
            $qcv = escapeSQL($cv);
            $UPDATE = "UPDATE ".TBL_DATA." SET $cn = '$qcv' WHERE id = $id";
            runSQL($UPDATE);
        }
    }
    $output  = '<input type="text" size="10" maxlength="4" name="'.$cn.'" id="'.$cn.'" value="'.formvar($cv).'" />';
    $output .= " <a href='#' onclick='document.edi.".$cn.".value=\"U\"'>U</a>";
    $output .= " <a href='#' onclick='document.edi.".$cn.".value=\"12\"'>12</a>";
    $output .= " <a href='#' onclick='document.edi.".$cn.".value=\"12A\"'>12A</a>";
    $output .= " <a href='#' onclick='document.edi.".$cn.".value=\"15\"'>15</a>";
    $output .= " <a href='#' onclick='document.edi.".$cn.".value=\"18\"'>18</a>";
    $output .= " <a href='#' onclick='document.edi.".$cn.".value=\"PG\"'>PG</a>";
    return $output;
}

/**
 * Custom Type: BBFC
 *
 * Show the BBFC rating of a movie
 * Based on the MPAA ratings above
 *
 * @author Colin Ogilvie <csogilvie@users.sourceforge.net>
 */
function custom_bbfc_output($cn,$cv)
{
    global $imdbdata;
    global $id;

    $ratings = array('PG' 	=> 	'bbfc-PG.gif',
                     '12A' 	=> 	'bbfc-12A.gif',
                     '12' 	=> 	'bbfc-12.gif',
                     '15' 	=> 	'bbfc-15.gif',
                     '18' 	=> 	'bbfc-18.gif',
                     'U' 	=> 	'bbfc-U.gif');

   $output = custom_text_output($cn,$cv);
    foreach ($ratings as $rating => $image) 
    {
       if (strstr($output, (string) $rating)) 
        {
            $output = '<img src="'.img($image).'" alt="'.$rating.'"/>';
            break;
        }
    }
    return $output;
}

?>