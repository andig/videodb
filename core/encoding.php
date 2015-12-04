<?php
/**
 * Encoding functions
 *
 * Contains HTML and Unicode conversion functions
 *
 * @package Core
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @version $Id: encoding.php,v 1.6 2013/03/10 16:25:35 andig2 Exp $
 */

/**
 * Check if string contains unicode characters
 */
function is_utf8($str)
{
	// array handling
    if (is_array($str)) {
		foreach($str as $k => $v) {
			$res = is_utf8($v);
			if (!$res) return(false);
		}
		return(true);
	}

	// From http://w3.org/International/questions/qa-forms-utf-8.html
	return preg_match('%^(?:
         [\x09\x0A\x0D\x20-\x7E]            # ASCII
       | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
       |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
       | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
       |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
       |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
       | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
       |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
   )*$%xs', $str);
}

/**
 * @author   "Sebastián Grignoli" <grignoli@framework2.com.ar>
 * @package  Encoding
 * @version  1.1
 * @link     http://www.framework2.com.ar/dzone/forceUTF8-es/
 * @example  http://www.framework2.com.ar/dzone/forceUTF8-es/
  */
function fix_utf8($text)
{
	$utf8ToWin1252 = array(
       "\xe2\x82\xac" => "\x80",
       
       "\xe2\x80\x9a" => "\x82",
       "\xc6\x92"     => "\x83",
       "\xe2\x80\x9e" => "\x84",
       "\xe2\x80\xa6" => "\x85",
       "\xe2\x80\xa0" => "\x86",
       "\xe2\x80\xa1" => "\x87",
       "\xcb\x86"     => "\x88",
       "\xe2\x80\xb0" => "\x89",
       "\xc5\xa0"     => "\x8a",
       "\xe2\x80\xb9" => "\x8b",
       "\xc5\x92"     => "\x8c",
       
       "\xc5\xbd"     => "\x8e",
       
       
       "\xe2\x80\x98" => "\x91",
       "\xe2\x80\x99" => "\x92",
       "\xe2\x80\x9c" => "\x93",
       "\xe2\x80\x9d" => "\x94",
       "\xe2\x80\xa2" => "\x95",
       "\xe2\x80\x93" => "\x96",
       "\xe2\x80\x94" => "\x97",
       "\xcb\x9c"     => "\x98",
       "\xe2\x84\xa2" => "\x99",
       "\xc5\xa1"     => "\x9a",
       "\xe2\x80\xba" => "\x9b",
       "\xc5\x93"     => "\x9c",
       
       "\xc5\xbe"     => "\x9e",
       "\xc5\xb8"     => "\x9f"
    );

	if (is_array($text)) {
		foreach($text as $k => $v) {
			$text[$k] = fix_utf8($v);
		}
		return $text;
	}

	$last = "";
	while ($last <> $text) {
		$last = $text;
		$text = utf8_encode(utf8_decode(str_replace(array_keys($utf8ToWin1252), array_values($utf8ToWin1252), $text)));
	}
	$text = utf8_encode(utf8_decode(str_replace(array_keys($utf8ToWin1252), array_values($utf8ToWin1252), $text)));
	return $text;
}

/**
 * Decode string is utf-8. Typically used for later URL encoding of the string
 */
function utf8_smart_decode($str)
{
    return (is_utf8($str)) ? utf8_decode($str) : $str;
}

/**
 * Like html_entity_decode() but also supports numeric entities. 
 * Output encoding is ISO-8852-1.
 *
 * @author www.php.net
 * @param  string   $string  html entity loaded string
 * @return string            html entity free string 
 */
function html_entity_decode_all($string) 
{
    // replace numeric entities
    $string = preg_replace_callback('~&#x([0-9a-f]+);~i', '_callback_chr_hexdec', $string);
    $string = preg_replace_callback('~&#([0-9]+);~', '_callback_chr', $string);
#   utf8 version commented out
#    $string = preg_replace_callback('~&#x([0-9a-f]+);~i', '_callback_code2utf_hexdec', $string);
#    $string = preg_replace_callback('~&#([0-9]+);~', '_callback_code2utf', $string);
    
    // replace literal entities
    $trans_tbl = get_html_translation_table(HTML_ENTITIES);    
    $trans_tbl = array_flip($trans_tbl);
#   utf8 version commented out
#    foreach (get_html_translation_table(HTML_ENTITIES) as $val=>$key) $trans_tbl[$key] = utf8_encode($val);
    
    return strtr($string, $trans_tbl);
}

/**
 * Like html_entity_decode() but also supports numeric entities. 
 * Output encoding is UTF-8.
 *
 * @author www.php.net
 * @param  string   $string  html entity loaded string
 * @return string            html entity free string 
 */
function html_entity_decode_all_utf8($string) 
{
    // replace numeric entities
#   non-utf8 version commented out
#    $string = preg_replace_callback('~&#x([0-9a-f]+);~i', '_callback_chr_hexdec', $string);
#    $string = preg_replace_callback('~&#([0-9]+);~', '_callback_chr', $string);
    $string = preg_replace_callback('~&#x([0-9a-f]+);~i', '_callback_code2utf_hexdec', $string);
    $string = preg_replace_callback('~&#([0-9]+);~', '_callback_code2utf', $string);
    
    // replace literal entities
#   non-utf8 version commented out
#    $trans_tbl = get_html_translation_table(HTML_ENTITIES);    
#    $trans_tbl = array_flip($trans_tbl);
    foreach (get_html_translation_table(HTML_ENTITIES) as $val=>$key) $trans_tbl[$key] = utf8_encode($val);
    
    return strtr($string, $trans_tbl);
}

/**
 * Returns the utf-8 encoding corresponding to the unicode character value 
 * @author  from php.net, courtesy - romans@void.lv
 */
function code2utf($num)
{
    if ($num < 128) return chr($num);
    if ($num < 2048) return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
    if ($num < 65536) return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    if ($num < 2097152) return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    return '';
}

/**
 * Clean HTML entities and replace &nbsp; special spaces
 *
 * @author Andreas Goetz	<cpuidle@gmx.de>
 * @param  string   $string  html entity loaded string
 * @return string            html entity free string 
 */
function html_clean($str) 
{
    return trim(str_replace(chr(160), ' ', html_entity_decode_all($str)));
}
        
/**
 * Clean HTML entities, tags and replace &nbsp; special spaces
 * Output encoding is UTF-8.
 *
 * @author Andreas Goetz	<cpuidle@gmx.de>
 * @param  string   $str    html entity loaded string
 * @return string           html entity free string 
 */
function html_clean_utf8($str) 
{
#   this replacement breaks unicode enitity encoding as A0 might occor as part of any character
#   $str    = str_replace(chr(160), ' ', $str);
    $str    = html_entity_decode_all_utf8(strip_tags($str));
    return trim($str);
}

/**
 * Chance character set encoding for hierarchical array
 *
 * @param  mixed    $data   string or hierarchical array to convert
 * @return mixed    data in target encoding
 */
function iconv_array($source_encoding, $target_encoding, $data)
{
    if (is_array($data))
    {
        // recursive call for array conversion
        foreach ($data as $key => $val)
        {
            $data[$key] = iconv_array($source_encoding, $target_encoding, $val);
        }
    }
    else
    {
        // finally convert string value
        $data = iconv($source_encoding, $target_encoding, $data);
        if ($data === FALSE) errorpage('Character set conversion error', "Error converting from $source_encoding to $target_encoding.");
    }
    
    return $data;
}

/**
 * Convert HTML to plain text for some common entities
 */
function html_to_text($str)
{
    // create list items
    $str = preg_replace("#<li.*?>#i", "\n-", $str);

    // de-html line breaks
    $str = preg_replace('#<(br|p).*?>#i', "\n", $str);
    
    // avoid double line breaks
    $str = preg_replace("#\n+#", "\n", $str);
    
    return $str;
}

/**
 * Ensure that there is only one match from a preg_replace_callback and return it
 */
function _get_only_match_from_callback($matches) {
    assert(sizeof($matches) === 2);
    return $matches[1];
}

/**
 * apply chr on the only match of a preg_replace_callback
 */
function _callback_chr($matches) {
    return chr(_get_only_match_from_callback($matches));
}

/**
 * apply hexdec and chr on the only match of a preg_replace_callback
 */
function _callback_chr_hexdec($matches) {
    return chr(hexdec(_get_only_match_from_callback($matches)));
}

/**
 * apply code2utf on the only match of a preg_replace_callback
 */
function _callback_code2utf($matches) {
    return code2utf(_get_only_match_from_callback($matches));
}

/**
 * apply hexdec and code2utf on the only match of a preg_replace_callback
 */
function _callback_code2utf_hexdec($matches) {
    return code2utf(hexdec(_get_only_match_from_callback($matches)));
}
?>