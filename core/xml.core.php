<?php
/**
 * XML support functions
 *
 * @package Core
 * @author  Andreas Götz    <cpuidle@gmx.de>
 * @version $Id: xml.core.php,v 1.8 2013/04/26 15:09:35 andig2 Exp $
 */
 
/**
 * See http://feedvalidator.org/docs/error/InvalidRFC2822Date.html
 */
$GLOBALS['rss_timestamp_format']    = 'D, j M Y H:i:s O';

/**
 * Encodes HTML entities into XML character entities
 * this avoids problems with unknown entities in XML
 *
 * @param   string  $string HTML string to encode
 * @return  string          encoded string containing XML character entities  
 */
function encode_character_entities($string)
{
    return strtr($string, get_html_translation_table(HTML_SPECIALCHARS));
#   return strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
}

/**
 * Create an XML tag
 *
 * @param   string  $tag    XML tag name
 * @param   string  $value  value for XML tag
 * @param   boolean $encode require encoding of tag value
 * @return  string          XML tag
 */
function createTag($tag, $value, $encode = true)
{
    if ($encode) $value = encode_character_entities($value);
    return "<$tag>".$value."</$tag>\n";
}

function createContainer($tag, $value = '')
{
    return createTag($tag, $value, false);
}

/**
 * Convert MySQL Date to RSS timestamp
 *
 * @return string   RFC 2822 Date (http://feedvalidator.org/docs/error/InvalidRFC2822Date.html)
 */
function rss_timestamp($timestamp)
{
    global $rss_timestamp_format;

    // Lets sort this nasty timestamp nonsense out ;D
    $y          = substr($timestamp, 0, 4);
    $m          = substr($timestamp, 5, 2);
    $d          = substr($timestamp, 8, 2);
    $h          = substr($timestamp, 11, 2);
    $min        = substr($timestamp, 14, 2);
    $s          = substr($timestamp, 17, 2);

    $timestamp  = mktime($h, $min, $s, $m, $d, $y);

    return date($rss_timestamp_format, $timestamp);
}

/**
 * Load XML into SimpleXML object
 * Fixes potential encoding issue
 */
function load_xml($data)
{
    $xml = simplexml_load_string($data, $root = 'SimpleXMLElement', LIBXML_NOCDATA);

    // character encoding warning- hack
    if ($xml === false) 
    {
        $error = error_get_last();
#       dump($error);

        // this is nasty- sometimes simplexml_load_string fails but doesn't raise an error
        if (preg_match('/simplexml_load_string/i', $error['message']))
        {
            $xml = simplexml_load_string(utf8_encode($data), $root, LIBXML_NOCDATA);
        }
    }
    
    return $xml;
}

/**
 * Concatenate string-converted xml entities
 */
function xml_join($items, $str = "\n")
{
    foreach ($items as $item)
    {
        if ($data) $data .= $str;
        $data .= (string) $item;
    }

    return $data;
}

?>
