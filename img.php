<?php
/**
 * Image loader
 *
 * Loads an image from the net and creates a chachefile for it.
 *
 * @package videoDB
 * @author  Andreas Gohr    <a.gohr@web.de>
 * @version $Id: img.php,v 2.29 2013/03/10 16:24:32 andig2 Exp $ 
 */

require_once './core/functions.php';
require_once './core/httpclient.php';

/**
 * input
 */
$name = req_string('name');
$actorid = req_int('actorid');
$url = req_url('url');

/* 
 * Note:
 *
 * We don't clear overage thumbnails. Instead, 
 * the table entries will be replaced when an image is finally available
 */

// since we don't need session functionality, use this as workaround 
// for php bug #22526 session_start/popen hang 
session_write_close();

/**
 * amazon workaround for 1 pixel transparent images
 */
function checkAmazonSmallImage($url, $ext, $file)
{
   global $config;    
    
	if (preg_match('/^(.+)L(Z{7,}.+)$/', $url, $m)) 
    {
		if (list($width, $height, $type, $attr) = getimagesize($file)) {
			if ($width <= 1) {
				$smallurl = $m[1].'M'.$m[2];
				if (cache_file_exists($smallurl, $cache_file, CACHE_IMG, $ext) || 
					download($smallurl, $cache_file)) {
					copy($cache_file, $file);
				}
			}
		}
	}
}


// default - no url given or no image
$file = img();

// Get imgurl for the actor
if ($name) 
{
    require_once './engines/engines.php';

    // name given
	$name   = html_entity_decode($name);
 
        if ( $config['debug'] )
        {
            // save data to pass to functions.php - erropage 
            // if engineActor fails in httpclient it goes directly to errorpage which loses
            // message set in httpCLient
            // this is a cause of broken actor images appearing
            $save_data_if_error_getting_image =  'Name: '.$name.' - Actorid: '.$actorid;
        }
        
	$result = engineActor($name, $actorid, engineGetActorEngine($actorid));

        if ( $config['debug}'] )
        {
            unset($save_data_if_error_getting_image);
        }
	
	if (!empty($result)) {
		$url = $result[0][1];
	}
	if (preg_match('/nohs(-[f|m])?.gif$/', $url)) {
        // imdb no-image picture
		$url = '';
	} 

    // write actor last checked record
    // NOTE: this is only called if the template preparation has determined the actor record needs checking
    {
        // write only if HTTP lookup physically successful
        $SQL = 'REPLACE '.TBL_ACTORS." (name, imgurl, actorid, checked)
                 VALUES ('".escapeSQL($name)."', '".escapeSQL($url)."', '".escapeSQL($actorid)."', NOW())";
        runSQL($SQL);
    }
}

// Get cached image for the given url
if (preg_match('/\.(jpe?g|gif|png)$/i', $url, $matches))
{
    // calculate cache filename if we're not looking into the cache again- otherwise this is done by cache_file_exists
    // $file is further needed for downloading the file
    // This is only effective if function is enabled in getThumbnail function
    # if ($cache_ignore) $file = cache_get_filename($url, CACHE_IMG, $matches[1]));
    
	// does the cache file exist?
    if (cache_file_exists($url, $targetfile, CACHE_IMG, $matches[1])) {  
		// amazon workaround for 1 pixel transparent images
		checkAmazonSmallImage($url, $matches[1], $targetfile);
	} 
    // try to download and make sure it's really an image
    else {
    	download($url, $targetfile);
    }
    
    // double-check this is really an image
    if (@exif_imagetype($targetfile)) {
	    // success- the result is an actual image
        $file = $targetfile;
	}
}

// fix url for redirect
$file = preg_replace('/img\.php$/', $file, $_SERVER['PHP_SELF']);

header('Location: '.$file);

