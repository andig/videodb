<?php
/**
 * Compatibility functions
 *
 * Borrowed simplified functions from PEAR module PHP_Compat
 *
 * @package Core
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @link    http://pear.php.net PEAR
 * @version $Id: compatibility.php,v 1.15 2013/03/13 16:38:19 andig2 Exp $
 */

/**
 * Implements file_get_contents introduced in v4.3.0
 */
if (!function_exists('file_get_contents')) 
{
	function file_get_contents($filename) 
    {
		$fh = @fopen($filename, 'rb');
		if (!$fh) return false;
		$content = fread($fh, filesize($filename));
		fclose($fh);
		return $content;
	}
}

/**
 * Implements file_put_contents introduced in v5.0.0
 */
if (!function_exists('file_put_contents')) 
{
	function file_put_contents($filename, $content) 
    {
		$fh = @fopen($filename, 'wb');
		if (!$fh) return false;
		if (!fwrite($fh, $content, strlen($content))) return false;
		fclose($fh);
		return true;
	}
}

/**
 * Implements html_entity_decode introduced in v4.3.0
 * @author <martin@swertcw.com>
 * @param   string  $string  HTML encoded string
 * @return  string           HTML decoded string
 */
if (!function_exists('html_entity_decode')) 
{
	function html_entity_decode($string) 
    {
		// replace numeric entities
		$string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
		$string = preg_replace('~&#([0-9]+);~e', 'chr(\\1)', $string);
		// replace literal entities
		$trans_tbl = get_html_translation_table(HTML_ENTITIES);
		$trans_tbl = array_flip($trans_tbl);
   		return strtr($string, $trans_tbl);
	}
}

/**
 * Implements http_build_query introduced in v5.0.0
 */
if (!function_exists('http_build_query'))
{
	function http_build_query ($formdata, $numeric_prefix = null)
	{
		// Check we have an array to work with
		if (!is_array($formdata)) {
			return $formdata;
		}

		// Start building the query
		$tmp = array ();
		foreach ($formdata as $key => $val)
		{
			array_push($tmp, urlencode($key).'='.urlencode($val));
		}

		return implode('&', $tmp);
	}
}

/**
 * Multibyte-aware character case conversion
 *
 * @author  tedemo  <tedemo@free.fr>
 */
if (!function_exists('mb_convert_case'))
{
    function mb_convert_case($str)
    {
        return ucwords(strtolower($str));
    }
}

/**
 * iconv alternatives
 *
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 */
if (!function_exists('iconv'))
{
    function iconv($source_encoding, $target_encoding, $str)
    {
        // remove transliteration- only available in native iconv
        $source_encoding = preg_replace('#^(.+?)(//.*)#', '\\1', $source_encoding);
        $target_encoding = preg_replace('#^(.+?)(//.*)#', '\\1', $target_encoding);

        if (function_exists('mb_convert_encoding'))
            return mb_convert_encoding($str, $target_encoding, $source_encoding);
        elseif (function_exists('recode_string'))
            return recode_string($source_encoding.'..'.$target_encoding, $str);
        else
            return $str;
    }
}

/**
 * Implements json_encode introduced in v5.2.0
 *
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 */
if (!function_exists('json_encode')) 
{
    function json_encode($data) 
    {
        require_once('./lib/json.php');
        $json = new Services_JSON();
        return($json->encode($data));
    }
}

/**
 * Implements json_decode introduced in v5.2.0
 *
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 */
if (!function_exists('json_decode')) 
{
    function json_decode($data) 
    {
        require_once('./lib/json.php');
        $json = new Services_JSON();
        return($json->decode($data));
    }
}

/**
 * Implements json_decode introduced in v5.0.0
 *
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 */
if (!function_exists('http_build_query')) 
{
    function http_build_query($formdata, $numeric_prefix = null, $key = null)
    {
        $res = array();
        foreach ((array)$formdata as $k=>$v)
        {
            $tmp_key = urlencode(is_int($k) ? $numeric_prefix.$k : $k);
            if ($key) $tmp_key = $key.'['.$tmp_key.']';
            
            if ( is_array($v) || is_object($v) ) {
                $res[] = http_build_query($v, null /* or $numeric_prefix if you want to add numeric_prefix to all indexes in array*/, $tmp_key);
            } else {
                $res[] = $tmp_key."=".urlencode($v);
            }
        }
        $separator = ini_get('arg_separator.output');
        return implode($separator, $res);
    }
}

/**
 * Quick image type detection
 *
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 */
if (!function_exists('exif_imagetype'))
{
    function exif_imagetype($filename)
    {
        if ((list($width, $height, $type, $attr) = getimagesize($filename )) !== false ) {
            return $type;
        }
        return false;
    }
}

/**
 * Ease PHP 5.3.0 requirement on Windows
 *
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 */
if (!function_exists('linkinfo'))
{
    function linkinfo($path)
    {
        return 0;
    }
}

?>