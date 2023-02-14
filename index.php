<?php
/**
 * Browse View
 *
 * Lets you browse through your movie collection
 *
 * @package videoDB
 * @author  Andreas Gohr <a.gohr@web.de>
 * @author  Andreas Götz <cpuidle@gmx.de>
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @link	http://videodb.sf.net
 * @version $Id: index.php,v 2.102 2013/03/21 16:27:57 andig2 Exp $
 */

require_once './core/functions.php';
require_once './core/output.php';

/**
 * input
 */
$id = req_int('id');
$diskid = req_string('diskid');
// start session'd settings
$filter = req_string('filter');
$showtv = req_int('showtv');
$listcolumns = req_int('listcolumns');
$mediafilter = req_int('mediafilter');
$order = req_int('order');
// end session'd settings
$owner = req_string('owner');
$ajax_quicksearch = req_string('ajax_quicksearch'); // elegant template only
$quicksearch = req_string('quicksearch'); // elegant template only
$export = req_string('export');
$pageno = (req_string('pageno') == 'all' ? 'all' : req_int('pageno'));
$ajax_render = req_int('ajax_render'); // elegant template only
$deleteid = req_int('deleteid');

/**
 * Update item list asynchronously
 *
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 */ 
function ajax_render()
{
    global $smarty, $result, $filter, $config;
    global $pageno, $totalpages, $totalresults;

    // TODO Smarty caching would require further efforts $smarty->caching = 1;
#    if (!$smarty->is_cached('list.tpl', get_current_user_id().'|'.$filter.$pageno)) {
    // add some delay for debugging
    if ($config['debug'] && $_SERVER['SERVER_ADDR'] == '127.0.0.1')  usleep(rand(200,1000)*1000);

    // load languages and config into Smarty
    tpl_language();
    tpl_list($result);
    
    // TODO consider pagination
    $content = $smarty->fetch('list.tpl', get_current_user_id().'|'.$filter.$pageno);

    header('X-JSON: '.json_encode(array('totalresults' => $totalresults ? $totalresults : count($result), 
                                        'maxpageno' => $totalpages)));
    exit($content);
}

function prepareOrder($m) {
       switch($m) {
               case 1:
                       $ORDER = "rating desc";
                       break;
               case 2:
                       $ORDER = "created DESC, lastupdate DESC";
                       break;
               default:
                       $ORDER = "title, subtitle asc";
                       break;
       }
       return $ORDER;
}

function get_mediatype_sql($m)
{
    switch ($m) {
        case -2:    
            $mediatype  = '1=1';
            break;
        case -1:    
            $mediatype  = 'mediatype != '.MEDIA_WISHLIST;
            break;
        default:    
            $mediatype  = 'mediatype = '.$m;
    }
    
    return $mediatype;
}

// set defaults and update session
session_default('filter', $config['filterdefault']);
session_default('showtv', $config['showtv']);
session_default('listcolumns', $config['listcolumns']);
session_default('mediafilter', -1);
session_default('order', -1);

// enable redirects to last list view for delete.php
session_set('listview', 'index.php');

// standard filters
$filter_expr = array(
  'NUM'  => '^["\\\' ]*[^A-Za-zÄäÖöÜüß]',
  'ABC'  => '^["\\\' ]*[ABCabcÄä]',
  'DEF'  => '^["\\\' ]*[DEFdef]',
  'GHI'  => '^["\\\' ]*[GHIghi]',
  'JKL'  => '^["\\\' ]*[JKLjkl]',
  'MNO'  => '^["\\\' ]*[MNOmnoÖö]',
  'PQRS' => '^["\\\' ]*[PQRSpqrsß]',
  'TUV'  => '^["\\\' ]*[TUVtuvÜü]',
  'WXYZ' => '^["\\\' ]*[WXZwxy]'
);

if ($filter == 'wanted') $mediafilter = MEDIA_WISHLIST;
$WHERES = get_mediatype_sql(($mediafilter) ? $mediafilter : -1);

// create SQL according to selected filter
$JOINS = '';
$LIMIT = '';

// create SQL according to selected filter
switch ($filter)
{
    case 'all':
#                    $WHERES = 'mediatype != '.MEDIA_WISHLIST;
					if($config['orderallbydisk']) 
					{
						$ORDER  = 'diskid asc, title, subtitle';
					}
                    break;
    case 'seen':
                    $WHERES .= ' AND !ISNULL('.TBL_USERSEEN.'.video_id)';# AND mediatype != '.MEDIA_WISHLIST;
                    break;
    case 'unseen':
                    $WHERES .= ' AND ISNULL('.TBL_USERSEEN.'.video_id)';# AND mediatype != '.MEDIA_WISHLIST;
                    break;
    case 'new':
#                    $WHERES = 'mediatype != '.MEDIA_WISHLIST;
                    $ORDER  = 'created DESC, lastupdate DESC ';
                    $LIMIT  = ' LIMIT '.$config['shownew'];
                    break;
    case 'wanted':
#                    $WHERES = 'mediatype = '.MEDIA_WISHLIST;
                    break;
    case 'full':    // secret filter for exposing all data
#                    $WHERES = '1=1';  
                    break;
    default:
                    // make sure filter is valid
                    if (!array_key_exists($filter, $filter_expr)) $filter = 'ABC';
                    // apply filter
                    $WHERES .= ' AND title RLIKE \''.mb_convert_encoding($filter_expr[$filter], 'UTF-8', 'ISO-8859-1').'\'';# AND mediatype != '.MEDIA_WISHLIST;
}

if(!isset($ORDER))
{
	if(isset($order))
	{
		$ORDER = prepareOrder($order);
		session_set('order', $order);
	} else {
		$ORDER   = prepareOrder(-1);
		session_set('order', -1);
	}
}

if (!$showtv) $WHERES .= ' AND istv = 0';


// owner selection for multiuser mode- by default this is the logged in user
// any user has automatically read permissions for his personal data
if ($config['multiuser']) 
{
    // get owner from session- or use current user
    session_default_owner();
    // if we don't have read all permissions, limit visibility using cross-user permissions
    if (!check_permission(PERM_READ))
    {
        $JOINS   = ' LEFT JOIN '.TBL_PERMISSIONS.' ON '.TBL_DATA.'.owner_id = '.TBL_PERMISSIONS.'.to_uid';
        $WHERES .= ' AND '.TBL_PERMISSIONS.'.from_uid = '.get_current_user_id().' AND '.TBL_PERMISSIONS.'.permissions & '.PERM_READ.' != 0';
    }
    // further limit to single owner
    if (html_entity_decode($owner) != $lang['filter_any']) {
        $WHERES .= " AND ".TBL_USERS.".name = '".escapeSQL($owner)."'";
    }
}

// searching?
if ($ajax_quicksearch || $quicksearch)
{
    $qs      = escapeSQL($ajax_quicksearch ? $ajax_quicksearch : $quicksearch);
    $WHERES .= ' AND (title LIKE "%'.$qs.'%" OR subtitle LIKE "%'.$qs.'%")';
}

// async request for quick-searching within current spec
if ($ajax_quicksearch)
{
    // do hard work
    $SQL    = 'SELECT '.TBL_DATA.'.id, title, subtitle
                 FROM '.TBL_DATA.'
            LEFT JOIN '.TBL_USERS.' ON '.TBL_DATA.'.owner_id = '.TBL_USERS.'.id 
            LEFT JOIN '.TBL_USERSEEN.' ON '.TBL_DATA.'.id = '.TBL_USERSEEN.'.video_id AND '.TBL_USERSEEN.'.user_id = '.get_current_user_id()."
               $JOINS 
                WHERE $WHERES
             ORDER BY $ORDER
                LIMIT 20";
    $result = runSQL($SQL);

    $ret = '';
    foreach ($result as $item)
    {
        $title  = preg_replace('/('.$ajax_quicksearch.')/i', '<em>\1</em>', $item['title']);
        if ($item['subtitle']) $title .= ' - '.$item['subtitle'];
        $ret   .= "<li id='".$item['id']."'>".$title."</li>";
    }
    $ret = "<ul>$ret</ul>";
    
    exit($ret);
}

// XML / RSS / PDF export
if ($export && array_key_exists($export, $config) && $config[$export])
{
	// either (xml|rss|pdf|xls)export
    $func = $export.'export';
    if ($export == 'rss') $export = 'xml';
    require_once './core/'.$export.'.php';

    if (function_exists($func)) $func("$JOINS WHERE $WHERES ORDER BY $ORDER $LIMIT");
    exit;
}

/*
    Calculate pagination
    
    Check to see if user has selected the New items tab.
    This is seperately assigned as a LIMIT so, if this exists, 
    lets just skip page numbers and carry on
*/
if ($LIMIT == '' && ($config['pageno'] > 0) &! ($pageno == 'all'))
{
    // start at first page
    if (!$pageno) $pageno = ($deleteid) ? session_get('lastpageno', 1) : 1;
    session_set('lastpageno', $pageno);

    // define Max Results Per Page
    $maxresults = $config['pageno'];

    // define the Start Number
    $from   = (($pageno * $maxresults) - $maxresults);

    $LIMIT  = ' LIMIT '.$from.', '.$maxresults;

  	// get total amount of results from DB
  	$totalresults = runSQL('SELECT count(*) AS num 
                              FROM '.TBL_DATA.'
                         LEFT JOIN '.TBL_USERS.' ON '.TBL_DATA.'.owner_id = '.TBL_USERS.'.id 
                         LEFT JOIN '.TBL_USERSEEN.' ON '.TBL_DATA.'.id = '.TBL_USERSEEN.'.video_id AND '.TBL_USERSEEN.'.user_id = '.get_current_user_id()."
                            $JOINS WHERE $WHERES");
    $totalresults = (count($totalresults) > 0) ? (int)$totalresults[0]['num'] : 0;
    
  	// calculate total amount of pages
  	$totalpages = ceil($totalresults / $maxresults);

  	$smarty->assign('pageno', $pageno);               // assign current Page Number
  	$smarty->assign('maxpageno', $totalpages);		  // set Maximum Pages
  	$smarty->assign('totalresults', $totalresults);   // set Total Records Returned
}


// do hard work
$SQL    = 'SELECT '.TBL_DATA.'.id, '.TBL_DATA.'.diskid, 
                  title, subtitle, language, year,
                  director, plot, imgurl, 
                  owner_id, '.TBL_USERS.'.name AS owner, '.TBL_LENT.'.who, 
                  md5, comment, disklabel, imdbID, actors, runtime,
                  country, filename, filesize, filedate, audio_codec,
                  video_codec, video_width, video_height, istv,
                  lastupdate, mediatype, rating,
                  custom1, custom2, custom3, custom4, 
                  created, !ISNULL('.TBL_USERSEEN.'.video_id) AS seen,
                  '.TBL_MEDIATYPES.'.name AS mediatypename
             FROM '.TBL_DATA.'
        LEFT JOIN '.TBL_USERS.' ON '.TBL_DATA.'.owner_id = '.TBL_USERS.'.id 
        LEFT JOIN '.TBL_USERSEEN.' ON '.TBL_DATA.'.id = '.TBL_USERSEEN.'.video_id AND '.TBL_USERSEEN.'.user_id = '.get_current_user_id().'
        LEFT JOIN '.TBL_LENT.' ON '.TBL_DATA.'.diskid = '.TBL_LENT.'.diskid
        LEFT JOIN '.TBL_MEDIATYPES.' ON '.TBL_DATA.'.mediatype = '.TBL_MEDIATYPES.'.id'."
           $JOINS 
            WHERE $WHERES
         ORDER BY $ORDER
		   $LIMIT";
$result = runSQL($SQL);

// store query result in session for prev/next navigation
session_set('query_result', array_column($result, 'id'));

// process asynchronous refresh
if ($ajax_render)
{
    ajax_render();
}

// prepare
tpl_page('browse');
tpl_list($result);
tpl_filters($filter, $showtv);

// caching enabled?
if ($config['http_caching'])
{
    require_once('./core/httpcache.php');
    httpCacheCaptureStart();
}

$smarty->assign('moreless', true);           // show more/less control in list view

// allow data export
foreach (array('xls','pdf','xml','rss') as $export)
{
    if (array_key_exists($export, $config) && $config[$export]) $smarty->assign($export, 'index.php?');
}

// display templates
smarty_display('header.tpl');
smarty_display('filters.tpl');
if (!$config['http_caching']) flush();

if ($deleteid) $smarty->assign('deleted', true);

// TODO smarty caching would require further efforts
smarty_display('list.tpl', get_current_user_id().'|'.$WHERES);

smarty_display('footer.tpl');

// caching enabled?
if ($config['http_caching'])
{
    httpCacheOutput('index', httpCacheCaptureEnd());
}

