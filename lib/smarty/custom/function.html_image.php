<?php
/**
 * Smarty plugin
 * 
 * @package Smarty
 * @subpackage PluginsFunction
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 */

require_once('./lib/phpthumb/phpthumb.class.php');

define(THUMB_CACHE_SOURCE, true);
define(THUMB_CACHE_TARGET, true);

/** 
 * This function is to replace PHP's extremely buggy realpath(). 
 * @param string The original path, can be relative etc. 
 * @return string The resolved path, it might not exist. 
 */ 
function truepath($path){ 
	// whether $path is unix or not 
	$unipath=strlen($path)==0 || $path{0}!='/';
	// attempts to detect if path is relative in which case, add cwd 
	if(strpos($path,':')===false && $unipath) $path=getcwd().DIRECTORY_SEPARATOR.$path;
	// resolve path parts (single dot, double dot and double delimiters) 
	$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
	$parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
	$absolutes = array();
	foreach ($parts as $part) { 
	if ('.' == $part) continue;
		if ('..' == $part) { 
			array_pop($absolutes);
		} else { 
			$absolutes[] = $part;
		} 
	} 
	$path=implode(DIRECTORY_SEPARATOR, $absolutes);
	// resolve any symlinks 
	if(file_exists($path) && linkinfo($path)>0)$path=readlink($path);
	// put initial separator that could have been lost 
	$path=!$unipath ? '/'.$path : $path;
	return $path;
}

function html_image_get_cache($tag, &$width, &$height)
{
    $res = runSQL("SELECT * FROM ".TBL_CACHE." WHERE tag='".mysql_real_escape_string($tag)."'");

    if (count($res))
    {
        if (preg_match('/(\d+)x(\d+)/', $res[0]['value'], $m))
        {
            list($foo, $width, $height) = $m;
            return true;
        }
    }

    return false;
}

function html_image_put_cache($tag, $value)
{
    $res = runSQL("REPLACE INTO ".TBL_CACHE." SET tag='".mysql_real_escape_string($tag)."', value='".mysql_real_escape_string($value)."'");
}

/**
 * Thumbnail generation
 */
function generate_thumbnail(&$file, &$width, &$height, $max_width, $max_height, $cache_tag)
{
    global $config;

    $thumb_cache_tag = $cache_tag.'_'.$max_width.'x'.$max_height;

    // the names of the existing or to-be created thumbnail- still missing dimensions
    $thumb_name = './cache/'.CACHE_THUMBS.'/'.
                    (($config['hierarchical']) ? substr($cache_tag, 0, 1).'/' : '').
                    $cache_tag;

    // did we already create this thumbnail?
    if (THUMB_CACHE_TARGET && html_image_get_cache($thumb_cache_tag, $width, $height))
    {
        // get updated filename
        $file        = $thumb_name.'_'.$width.'x'.$height.'.jpg';
        return;
    }

    // thumbnail not created yet
    $scale  = min($max_width/$width, $max_height/$height);
    $width  = round($width * $scale);
    $height = round($height * $scale);

    // really need to scale?
    $thumb_must_scale = (($config['thumbnail_level'] == TUMB_SCALE) ||
                         ($config['thumbnail_level'] == TUMB_REDUCE_ONLY && ($scale < 1)) ||
                         ($config['thumbnail_level'] > 1) && ($config['thumbnail_level'] < @filesize($file)));
#dump("scaling required: ".(int)$thumb_must_scale." filesize: ".@filesize($file));

    // perform actual scaling
    if ($thumb_must_scale)
    {
        $phpThumb = new phpThumb();

		// use of truepath was added for php 5.4 apparently- otherwise thumbnail creation would inadvertantly fail
        $phpThumb->sourceFilename = truepath($file);
        $phpThumb->w = $max_width;
        $phpThumb->h = $max_height;
        if ($config['thumbnail_quality']) $phpThumb->q = $config['thumbnail_quality'];

        // set cache filename
        $thumb_name .= '_'.$width.'x'.$height.'.jpg';
#dump("thumbname:$thumb_name");

        // check to see if file already exists in cache, and output it with no processing if it does
        if (is_writable(dirname($thumb_name)) || is_writable($thumb_name))
        {
            if (@filesize($thumb_name) ||
               ($phpThumb->GenerateThumbnail() && $phpThumb->RenderOutput() &&
                file_put_contents($thumb_name, $phpThumb->outputImageData)))
            {
                $file = $thumb_name;
                if (THUMB_CACHE_TARGET) html_image_put_cache($thumb_cache_tag, $width.'x'.$height);
            }
/*
			# emergency debugging
			else
			{
				dlog("Fail: $thumb_name");
				dlog("fs: ".@filesize($thumb_name));
				dlog($phpThumb->GenerateThumbnail());
				dlog($phpThumb->RenderOutput());
				dlog($phpThumb->debugmessages);
			}
*/
        }

        // free memory
        $phpThumb = null;
    }
}

/**
 * Smarty {html_image} function plugin
 * 
 * Type:     function<br>
 * Name:     html_image<br>
 * Date:     Feb 24, 2003<br>
 * Purpose:  format HTML tags for the image<br>
 * Examples: {html_image file="/images/masthead.gif"}
 * Output:   <img src="/images/masthead.gif" width=400 height=23>
 * 
 * @link http://smarty.php.net/manual/en/language.function.html.image.php {html_image}
 *      (Smarty online manual)
 * @author Monte Ohrt <monte at ohrt dot com> 
 * @author credits to Duda <duda@big.hu> 
 * @version 1.0
 * @param array $params parameters
 * Input:<br>
 *          - file = file (and path) of image (required)
 *          - height = image height (optional, default actual height)
 *          - width = image width (optional, default actual width)
 *          - basedir = base directory for absolute paths, default
 *                      is environment variable DOCUMENT_ROOT
 *          - path_prefix = prefix for path output (optional, default empty)
 * @param object $template template object
 * @return string 
 * @uses smarty_function_escape_special_chars()
 */
function smarty_function_html_image($params, $template)
{
	global $config;

    require_once(SMARTY_PLUGINS_DIR . 'shared.escape_special_chars.php');

    $alt = '';
    $file = '';
    $border = 0;
    $height = '';
    $width = '';
    $extra = '';
    $prefix = '';
    $suffix = '';
    $path_prefix = '';
    $server_vars = $_SERVER;
    $basedir = isset($server_vars['DOCUMENT_ROOT']) ? $server_vars['DOCUMENT_ROOT'] : '';
    
    foreach($params as $_key => $_val) {
        switch($_key) {
            case 'file':
            case 'height':
            case 'width':
            case 'dpi':
            case 'path_prefix':
            case 'basedir':
			//!! cpuidle@gmx.de
            case 'border':
            case 'max_width':
            case 'max_height':
                $$_key = $_val;
                break;

            case 'alt':
                if(!is_array($_val)) {
                    $$_key = smarty_function_escape_special_chars($_val);
                } else {
                    throw new SmartyException ("html_image: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
                }
                break;

            case 'link':
            case 'href':
            	// cpuidle@gmx.de suppress hrefs without link
            	if (!empty($_val)) {
                    $prefix = '<a href="' . $_val . '">';
                    $suffix = '</a>';
				}
                break;

            default:
                if(!is_array($_val)) {
                    $extra .= ' '.$_key.'="'.smarty_function_escape_special_chars($_val).'"';
                } else {
                    throw new SmartyException ("html_image: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
                }
                break;
        }
    }

    if (empty($file)) {
        trigger_error("html_image: missing 'file' parameter", E_USER_NOTICE);
        return;
    }

    if (substr($file, 0, 1) == '/') {
        $_image_path = $basedir . $file;
    } else {
        $_image_path = $file;
	}

#dlog("\nimg: $_image_path");

    if (!isset($params['width']) || !isset($params['height']))
    {
        // cpuidle@gmx.de check for non-local images
        if (preg_match("/nocover|^(http:|img\.php)/", $_image_path))
        {
            $no_scaling = true;
#dlog("no scaling");
        }
        else
        {
            // do we already know this image?
            $cache_tag = md5($_image_path);

            // are we creating thumbnails and can we get the thumbnail from cache?
            if (!(THUMB_CACHE_SOURCE && html_image_get_cache($cache_tag, $width, $height)))
            {
                if ($_image_data = @getimagesize($_image_path)) {
                    if (!isset($params['width'])) $width = $_image_data[0];
                    if (!isset($params['height'])) $height = $_image_data[1];
                    if (THUMB_CACHE_SOURCE) html_image_put_cache($cache_tag, $width.'x'.$height);
#dlog("cache commit: $width $height");
                }
                else {
#dlog("image error: $_image_path");
                    // TODO check how to handle non-existing images
                    if (!file_exists($_image_path)) {
                        trigger_error("html_image: unable to find '$_image_path'", E_USER_NOTICE);
                        return;
                    } else if(!is_readable($_image_path)) {
                        trigger_error("html_image: unable to read '$_image_path'", E_USER_NOTICE);
                        return;
                    } else {
                        trigger_error("html_image: '$_image_path' is not a valid image file", E_USER_NOTICE);
                        return;
                    }
                }
                
                if (isset($template->security_policy)) {
                    if (!$template->security_policy->isTrustedResourceDir($_image_path)) {
                            return;
                    }    
                }
            }    
        }

        if (empty($width)) $width = $max_width;
        if (empty($height)) $height = $max_height;

        /*
         * Scaling is required if:
         * - scale mode TUMB_SCALE
         * - scale mode TUMB_REDUCE_ONLY and dimensions > target dimensions
         * - scale mode is any other numeric and filesize > scale mode
         */
        if ($max_width && $max_height && !$no_scaling)
        {
            // even if thumbnails are not generated we should get aspect ratio right
            if ($config['thumbnail_level'] == TUMB_NO_SCALE)
            {
                $scale  = min($max_width/$width, $max_height/$height);
                $width  = round($width * $scale);
                $height = round($height * $scale);
            }
            else
            {
                generate_thumbnail($file, $width, $height, $max_width, $max_height, $cache_tag);
            }
        }
    }

    if (isset($params['dpi']))
    {
        if(strstr($server_vars['HTTP_USER_AGENT'], 'Mac')) {
            $dpi_default = 72;
        } else {
            $dpi_default = 96;
        }
        $_resize = $dpi_default/$params['dpi'];
        $width  = round($width * $_resize);
        $height = round($height * $_resize);
    }

	$result = $prefix . '<img src="'.$file.'" alt="'.$alt;
    if (isset($border)) $result    .= '" border="'.$border;
	if ($width) $result     .= '" width="'.$width;
	if ($height) $result    .= '" height="'.$height;
	$result .= '"'.$extra.' />' . $suffix;

	return $result;
}

?>
