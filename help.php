<?php
/**
 * Help Page
 *
 * Browses the manual
 *
 * @package videoDB
 * @author  Andreas Gohr <a.gohr@web.de>
 * @version $Id: help.php,v 1.10 2004/09/20 15:15:41 andig2 Exp $
 */

require_once './core/functions.php';

function _replace_anchors_callback($matches)
{
	if (!preg_match('=^https?://=',$matches[2])) 
    {
		$matches[2] = 'help.php?page='.$matches[2];
	}
	return $matches[1].$matches[2].$matches[3];
}


if (empty($page)) $page='index.html';
$page = 'doc/manual/'.$page;

$html = file_get_contents($page);
$html = preg_replace_callback("/(<a\s+.*?href\s*=\s*\")(.*?)(\".*?>)/is", '_replace_anchors_callback', $html);
preg_match('=<body.*?>(.*)</body>=is',$html,$matches);
$html = $matches[1];

// prepare templates
tpl_page();

$smarty->assign('helptext', $html);

// display templates
tpl_display('help.tpl');

?>
