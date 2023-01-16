<?php
/**
 * Movie Detail View
 *
 * Shows all data of a movie
 *
 * @package videoDB
 * @author  Andreas Gohr    <a.gohr@web.de>
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @version $Id: show.php,v 2.52 2013/03/15 16:42:46 andig2 Exp $
 */

require_once './core/functions.php';
require_once './core/custom.php';
require_once './core/output.php';

/**
 * input
 */
$id = req_int('id');
$ajax_update = req_int('ajax_update');
$seen = req_int('seen');
$rating = req_int('rating');
$export = req_string('export');
$save = req_int('save');
$method = req_string('method'); // only boxeePlay

/**
 * Play movie on configured Boxee box
 */
function boxeePlay($filename) {
    global $config;

    $socket = fsockopen($config['boxeeHost'], $config['boxeePort']);
    if (!$socket) {
        throw new Exception("Couldn't connect to boxee on ".$config['boxeeHost'].':'.$config['boxeePort']);
    }

    $data = array(
        'id' => 1,
        'jsonrpc' => '2.0', 
        'method' => 'XBMC.Play', 
        'params' => array('file' => $filename)
        );

    $json = json_encode($data, JSON_FORCE_OBJECT);

    fputs($socket, $json);
/*
    $response = '';
    while (!(feof($socket))) {
        $response .= fgets($socket);
    }
*/
    // close socket
    $status = stream_get_meta_data($socket);
    fclose($socket);

    // check for timeout
    if (@$status['timed_out']) {
        $response['error'] = "Connection timed out";
    }

    return trim($response);
}

/**
 * Toggle seen status
 *
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 */ 
if ($ajax_update)
{
    // add some delay for debugging
    if ($config['debug'] && $_SERVER['SERVER_ADDR'] == '127.0.0.1') usleep(rand(200,1000)*1000);
    
    if (isset($seen))
    {    
        set_userseen($ajax_update, $seen);

        header('X-JSON: '.json_encode(array('result' => $seen > 0)));
    }
    elseif (isset($rating))
    {
        // Permission check same as edit.php
        // check for localnet
        localnet_or_die();

        // multiuser permission check
        if (empty($id))
            permission_or_die(PERM_WRITE);
        else
            permission_or_die(PERM_WRITE, get_owner_id($ajax_update));
            
        runSQL('UPDATE '.TBL_DATA.' SET rating='.$rating.' WHERE id='.$ajax_update);
    }

    // make sure no artifacts
    $smarty->clearCache('list.tpl');

    exit;
}

// random view
if (empty($id)) 
{
	$count  = 0;
    $all    = strtoupper($lang['radio_all']);
    $WHERES = '';
    
    if ($config['multiuser'])
    {
        // explicit setting of owner
        $owner = session_get('owner');
        if ($owner && $owner == $all || check_permission(PERM_READ, $uid = get_userid($owner)))
        {
            if ($owner == $all) $uid = -1; 
            $owner_id = $uid;
        } 
        elseif (check_permission(PERM_READ, get_current_user_id())) 
        { 
            $owner_id = get_current_user_id();
        } 
        elseif (count($owners = out_owners(false, PERM_READ, true)) == 1) 
        { 
            // check if there is only one owner available
            $owners = array_keys($owners);
            $owner_id = $owners[0];
        } 
        else 
        {
            // help! take ALL
            $owner_id = -1;
        }

		session_set('owner', ($owner_id && $owner_id >= 0) ? get_username($owner_id) : $all);

        // if we don't have read all permissions, limit visibility using cross-user permissions
        if (!check_permission(PERM_READ))
        {
            $JOINS   = ' LEFT JOIN '.TBL_PERMISSIONS.' ON '.TBL_DATA.'.owner_id = '.TBL_PERMISSIONS.'.to_uid';
            $WHERES .= ' AND '.TBL_PERMISSIONS.'.from_uid = '.get_current_user_id().' AND '.TBL_PERMISSIONS.'.permissions & '.PERM_READ.' != 0';
        }

        // further limit to single owner
        if ($owner_id > 0) $WHERES .= " AND owner_id = '".$owner_id."' ";
    }

    // limit random to not unseen movies only
    if (array_key_exists( 'showrandomunseen', $config ))
    {
        // WARNING: this may make the SQL query expensive for large databases
        $WHERES .= ' AND ('.TBL_DATA.'.id NOT IN (SELECT video_id FROM '.TBL_USERSEEN.'))';
    }

    // find a random id
    $SELECT = 'SELECT id, REVERSE(RAND(NOW())) AS rnd
                 FROM '.TBL_DATA."
               $JOINS
                WHERE mediatype != ".MEDIA_WISHLIST." $WHERES
                ORDER BY rnd
                LIMIT 1";

	while (!$id)
	{
		$result = runSQL($SELECT);
        
        // prevent endless loop
        if (!count($result) || ($count++ > 50)) break;
        
		$id = $result[0]['id'];
		if (!adultcheck($id)) $id = 0;  //adult movie? -> try again
	}
    
	// id still empty? go back to index.
	if (empty($id)) redirect('index.php');
}

// get data (id may be empty on a empty database)
if (!empty($id))
{
    // no adult permissions? -> back to index
    if (!adultcheck($id) || !check_videopermission(PERM_READ, $id)) redirect('index.php');

    // XML / RSS / PDF export
    if ($export && $config[$export])
    {
        // either (xml|rss|pdf)export
        $func = $export.'export';
        if ($export == 'rss') $export = 'xml';
        require_once './core/'.$export.'.php';

        if (function_exists($func)) $func('WHERE '.TBL_DATA.'.id = '.$id);
        exit;
    }

    $SELECT = 'SELECT '.TBL_DATA.'.id, title, subtitle, language, diskid, comment,
                      disklabel, imdbID, year, imgurl, director, actors, runtime,  
                      country, plot, filename, filesize, filedate, audio_codec,  
                      video_codec, video_width, video_height, istv, lastupdate,  
                      email, rating, custom1, custom2, custom3, custom4, 
                      !ISNULL('.TBL_USERSEEN.'.video_id) AS seen,
                      '.TBL_USERS.'.name AS owner, '.TBL_MEDIATYPES.'.name AS mediatype  
                 FROM '.TBL_DATA.'   
            LEFT JOIN '.TBL_USERS.' ON owner_id = '.TBL_USERS.'.id  
            LEFT JOIN '.TBL_USERSEEN.' 
                   ON '.TBL_DATA.'.id = '.TBL_USERSEEN.'.video_id AND '.TBL_USERSEEN.'.user_id = '.get_current_user_id().'
            LEFT JOIN '.TBL_MEDIATYPES.' ON mediatype = '.TBL_MEDIATYPES.'.id  
                WHERE '.TBL_DATA.'.id = '.$id;  
	$res   = runSQL($SELECT);

    // existing id?
    if (!count($res)) redirect('index.php');
    
    // get the item
    $video = $res[0];

    // is it editable/ copyable?
    $video['editable'] = localnet();
    $video['copyable'] = localnet();
    
    // multi-user permissions
    if ($config['multiuser'])
    {
        $video['editable'] = $video['editable'] && check_permission(PERM_WRITE, get_userid($video['owner']));
        $video['copyable'] = $video['copyable'] && check_permission(PERM_WRITE, PERM_ANY);
    }

    // save seen state
    if ($save)
    {
        set_userseen($id, $seen);
        $video['seen'] = $seen;     // store in video for display
    }

    // diskid to global scope:
    $diskid = $video['diskid'];

    // check if it is lent and to whom- save query if id not set
    if ($diskid)
	{
        $SELECT = 'SELECT who
                     FROM '.TBL_LENT."
                    WHERE diskid = '".escapeSQL($diskid)."'";
        $result = runSQL($SELECT);

        if (isset($result[0]['who']))
        {
            $video['who'] = $result[0]['who'];
        }
    }
/*    
    // append child episodes
    $SELECT = 'SELECT id, title, season, episode, plot
                 FROM '.TBL_DATA.'                       
                WHERE '.TBL_DATA.'.parent_id = '.$id.'                      
             ORDER BY season, episode, title, subtitle';                      
    $episodes = runSQL($SELECT);
    
    $video['episodes'] = $episodes;
*/

    // previous/next buttons
    if (is_array($ids = session_get('query_result')))
    {
        if (($key = array_search($id, $ids)) !== false)
        {
            $video['prev_id'] = ($key > 0) ? $ids[$key-1] : 0;
            $video['next_id'] = ($key < count($ids)-1) ? $ids[$key+1] : 0;
        }
    }

	// breadcrumbs
    $breadcrumbs = session_get('breadcrumbs', array('current' => 0, 'crumbs' => array()));
	if (sizeof($breadcrumbs['crumbs']) > 10) 
		$breadcrumbs['crumbs'] = array_slice($breadcrumbs['crumbs'], 0, -10);
	$breadcrumbs['current']  = $id;
	$breadcrumbs['crumbs'][] = array(
		'id' => $id,
		'title' => $video['title']);
	$size = sizeof($breadcrumbs['crumbs']);
	for ($i; $i < $size-1; $i++) {
		if ($breadcrumbs['crumbs'][$i]['id'] == $id) {
			unset($breadcrumbs['crumbs'][$size-1]);
			break;
		}
	}
	session_set('breadcrumbs', $breadcrumbs);
}

if ($method == 'boxeePlay') {
    boxeePlay($video['filename']);
}

// prepare templates
tpl_page('detailview', $video['title']);
if (!empty($id)) tpl_show($video);

// caching enabled?
if ($config['http_caching'])
{
    require_once('./core/httpcache.php');
    httpCacheCaptureStart();
}

// display templates
smarty_display('header.tpl');
if (!$config['http_caching']) flush();

if (!empty($id)) smarty_display('show.tpl', $id);

smarty_display('footer.tpl');

// caching enabled?
if ($config['http_caching'])
{
    httpCacheOutput('show'.$id, httpCacheCaptureEnd());
}

