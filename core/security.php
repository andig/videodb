<?php
/**
 * Security functions
 *
 * @package Core
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @author  tREXX           <www.trexx.ch>
 * @version $Id: security.php,v 1.2 2008/01/05 13:50:58 andig2 Exp $
 */

/**
 * Allow these tags
 */
$allowedTags = '<h1><h2><h3><h4><b><strong><i><a><ol><ul><li><pre><hr><blockquote>';

/**
 * Disallow these attributes/prefix within a tag
 */
$stripAttrib = 'javascript:|onclick|ondblclick|onmousedown|onmouseup|onmouseover|'.
               'onmousemove|onmouseout|onkeypress|onkeydown|onkeyup';

/**
 * @return string
 * @param string
 * @desc Strip forbidden attributes from a tag
 */
function removeEvilAttributes($tagSource)
{
    global $stripAttrib;
    return stripslashes(preg_replace("/$stripAttrib/i", 'forbidden', $tagSource));
}

/**
 * @return string
 * @param string
 * @desc Strip forbidden tags and delegate tag-source check to removeEvilAttributes()
 */
function removeEvilTags($source)
{
    global $allowedTags;
    $source = strip_tags($source, $allowedTags);
    return preg_replace('/<(.*?)>/ie', "'<'.removeEvilAttributes('\\1').'>'", $source);
}

?>