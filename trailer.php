<?php
/**
 * Movie Trailers View
 *
 * Shows available youtube trailers
 *
 * @package videoDB
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @version $Id: trailer.php,v 1.4 2009/04/04 16:25:58 andig2 Exp $
 */

require_once './core/functions.php';
require_once './engines/youtube.php';

// decode entities to care for numeric html escapes of JS single quotes
$trailer = youtubeSearch(html_entity_decode_all($title));

// prepare templates
tpl_language();
tpl_header();

$smarty->assign('trailer', $trailer);

// display templates
smarty_display('trailer.tpl');

?>