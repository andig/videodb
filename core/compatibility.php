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

/**
 * This file is part of the array_column library
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * @copyright Copyright (c) 2013 Ben Ramsey <http://benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * Returns the values from a single column of the input array, identified by
 * the $columnKey.
 *
 * Optionally, you may provide an $indexKey to index the values in the returned
 * array by the values from the $indexKey column in the input array.
 *
 * @param array $input A multi-dimensional array (record set) from which to pull
 * a column of values.
 * @param mixed $columnKey The column of values to return. This value may be the
 * integer key of the column you wish to retrieve, or it
 * may be the string key name for an associative array.
 * @param mixed $indexKey (Optional.) The column to use as the index/keys for
 * the returned array. This value may be the integer key
 * of the column, or it may be the string key name.
 * @return array
 */
if (!function_exists('array_column'))
{
    function array_column($input = null, $columnKey = null, $indexKey = null)
    {
        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $params = func_get_args();
        if (!isset($params[0])) {
            trigger_error('array_column() expects at least 2 parameters, 0 given', E_USER_WARNING);
            return null;
        } elseif (!isset($params[1])) {
            trigger_error('array_column() expects at least 2 parameters, 1 given', E_USER_WARNING);
            return null;
        }
        if (!is_array($params[0])) {
            trigger_error('array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given', E_USER_WARNING);
            return null;
        }
        if (!is_int($params[1])
            && !is_string($params[1])
            && !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return false;
        }
        if (isset($params[2])
            && !is_int($params[2])
            && !is_string($params[2])
            && !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return false;
        }
        $paramsInput = $params[0];
        $paramsColumnKey = (string) $params[1];
        $paramsIndexKey = (isset($params[2]) ? (string) $params[2] : null);
        $resultArray = array();
        foreach ($paramsInput as $row) {
            $key = $value = null;
            $keySet = $valueSet = false;
            if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                $keySet = true;
                $key = $row[$paramsIndexKey];
            }
            if (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                $valueSet = true;
                $value = $row[$paramsColumnKey];
            }
            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }
        }
        return $resultArray;
    }
}

?>