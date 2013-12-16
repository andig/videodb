<?php
/**
 * Template functions
 *
 * These functions prepare the data for assignment to the template engine
 *
 * @todo replace additional assignments of config options by using $config
 *
 * @package Core
 * @author  Andreas Gohr    <a.gohr@web.de>
 * @author  Andreas Goetz    <cpuidle@gmx.de>
 * @author  Chinamann       <chinamann@users.sourceforge.net>
 * @version $Id: template.php,v 1.70 2013/03/21 16:27:57 andig2 Exp $
 */

require_once './core/session.php';
require_once './core/output.php';
require_once './core/genres.php';
require_once './core/functions.php';
require_once './engines/engines.php';

/**
 * Display template with standard header and footer
 */
function tpl_display_show($template, $flush = true)
{
    smarty_display('header.tpl');

    if ($flush) flush();
    smarty_display($template);
    smarty_display('footer.tpl');
}

/**
 * Display page using templates
 * If page content is unmodified, return HTTP 304 Not modified
 *
 * @param   string  $template Template name for main content
 */
function tpl_display($template)
{
    global $config;

    // caching enabled?
    if ($config['http_caching'])
    {
        require_once('./core/httpcache.php');
        httpCacheCaptureStart();
    }

    tpl_display_show($template, !$config['http_caching']);

    if ($config['http_caching'])
    {
        httpCacheOutput($template, httpCacheCaptureEnd());
    }
}

/**
 * Prepare standard page templates
 */
function tpl_page($help = '', $title = '')
{
    tpl_language();
    tpl_header($help, $title);
    tpl_footer();
}

/**
 * Assigns language strings and config options to the smarty engine
 */
function tpl_language()
{
    global $smarty, $lang, $config;

    $smarty->assign('lang', $lang);
    $smarty->assign('config', $config);
}

/**
 * Assigns the header urls to the smarty engine
 *
 * @param string $help    The helpfile to display (optional, without extension)
 * @param string $title   The text to add to html <title> tag (optional, will be html-encoded)
 */
function tpl_header($help = '', $title = '')
{
    global $smarty, $lang, $config;
    global $id, $diskid;

    // viewing is only availble if autorized or public access
    if (auth_check(false))
    {
        $header['browse'] = 'index.php';
        if (check_permission(PERM_READ, PERM_ANY))
        {
        	$header['random'] = 'show.php';
        	$header['search'] = 'search.php';
        }
        $header['stats']  = 'stats.php';
        if ($config['imdbBrowser']) $header['trace'] = 'trace.php';
        $header['help'] = 'help.php';
        if ($help) $header['help'] .= '?page='.$help.'.html';
    }

    // editing is only available in local network
    if (localnet())
    {
        if (check_permission(PERM_WRITE, PERM_ANY))
        {
            $header['new']    = 'edit.php';
            if ($config['showtools']) $header['contrib'] = 'contrib.php';
        }
        if (check_permission(PERM_ADMIN)) $header['setup'] = 'setup.php';

        // edit or show?
        if ($id)
        {
            if (check_videopermission(PERM_WRITE, $id)) $header['edit'] = 'edit.php?id='.$id;
            if (!preg_match('/show.php$/', $_SERVER['PHP_SELF']))
            {
                $header['view']   = 'show.php?id='.$id;
            }
            if (check_videopermission(PERM_WRITE, $id)) $header['del'] = 'delete.php?id='.$id;
        }
        if (check_permission(PERM_WRITE, PERM_ANY))
        {
            $header['borrow'] = 'borrow.php';
            if (isset($diskid)) $header['borrow'] .= '?diskid='.$diskid;
        }
    }

    // multiuser settings
    if ($config['multiuser'])
    {
        $header['login'] = 'login.php';

        // logged in?
        if (!empty($_COOKIE['VDBusername']) && $_COOKIE['VDBuserid'] != $config['guestid'])
        {
            $header['profile'] = 'profile.php';
            $smarty->assign('loggedin', $_COOKIE['VDBusername']);
        }
        else
        {
            // make sure anonymous users don't get access to trace for security reasons
            unset($header['trace']);
        }

        if (check_permission(PERM_ADMIN)) $header['users'] = 'users.php';
    }

	// determine active tab
	if (preg_match('/(\w+)\.php/', $_SERVER['PHP_SELF'], $m))
    {
		$tab = strtolower($m[1]);
		switch ($tab)
        {
			case 'show':
			case 'edit':
				if (!empty($id)) $header['active'] = $tab;
				// uncomment this if you want the 'Browse' tab to remember last visited movie
				// { ... $smarty->assign('browseid', $_REQUEST['id']); }
				else $header['active'] = ($tab == 'show') ? 'random' : 'new';
				break;
            default:
				/* legacy version 
                $translate = array('index' => 'browse', 'users' => 'setup', 'permissions' => 'setup', 'delete' => 'show');
                */
                $translate = array('index' => 'browse', 'permissions' => 'users', 'delete' => 'show');
                if (in_array($tab, array_keys($translate)))
                {
                    $tab = $translate[$tab];
                }
				$header['active'] = $tab;
		}
	}

	// breadcrumbs
    $breadcrumbs = session_get('breadcrumbs', array());
	$smarty->assign('breadcrumbs', $breadcrumbs);

    $smarty->assign('title',	htmlspecialchars($title));
    $smarty->assign('header',	$header);
    $smarty->assign('style',	$config['style']);
    $smarty->assign('langcode', $config['language']);
}

/**
 * Assigns the filter options to the smarty engine
 */
function tpl_filters($filter, $showtv)
{
    global $smarty, $lang;
    global $filter_expr;
    global $owner, $mediatype;
	global $config;
	
    // build filter array
    foreach ($filter_expr as $flt => $regex)
    {
        $filters[$flt] = ($flt == "NUM") ? "#" : $flt;
    }
    $filters['all']	    = $lang['radio_all'];
    $filters['unseen']  = $lang['radio_unseen'];
    $filters['new']     = $lang['radio_new'];
/*
    # removed as of 4.0 in favour of media type filter
    $filters['wanted']  = $lang['radio_wanted'];
*/
    $smarty->assign('filters',  $filters);
    $smarty->assign('filter',   $filter);
    $smarty->assign('showtv',   $showtv);

    // create owner selectbox
    $smarty->assign('owners', out_owners(array($lang['filter_any'] => $lang['filter_any']), PERM_READ));
    if (!$owner) $owner = $lang['filter_any']; //!! default owner hack
    $smarty->assign('owner', $owner);

    // create mediatype selectbox
    $smarty->assign('mediafilter', out_mediatypes(array(-2 => $lang['filter_any'], -1 => $lang['filter_available'])));
    if (!$mediatype) $mediatype = session_get('mediafilter'); //!! default media type hack
    $smarty->assign('mediatype', $mediatype);
	
	// create sorting selectbox
	// Sorting is disabled when ordering by diskid is enabled
	if(!$config['orderallbydisk']) {
		$smarty->assign('order_options', array(-1 => $lang['title'], 1 => $lang['rating'], 2 => $lang['date']));
		if(!$order) $order = session_get('order');
		$smarty->assign('order',  $order);
	} 


    // enable dynamic columns in list view
    $smarty->assign('listcolumns', session_get('listcolumns'));
}

/**
 * Assigns the searchresults/browselist to the smarty engine
 *
 * @param   array   indexed array containing the item data
 */
function tpl_list($list)
{
    global $smarty, $config;
    global $listcolumns;

    for ($i=0; $i < count($list); $i++)
    {
        // setup imgurls
        $list[$i]['imgurl'] = ($config['thumbnail']) ? getThumbnail($list[$i]['imgurl']) : '';

        // check for flagfile
        $languages = $list[$i]['language'];
        $flagfile = img('flags/'.$languages.'.gif');
        if (file_exists($flagfile))
        {
            // one langage
            $list[$i]['flagfile'][$languages] = $flagfile;
            $list[$i]['language'] = array($list[$i]['language']);
        }
        else
        {
            // multiple languages
            $langary  = preg_split('/,\s*/', $languages);
            $list[$i]['language'] = $langary;

            // assign them all
            foreach ($langary as $languagepart)
            {
                $flagfile = img('flags/'.$languagepart.'.gif');
                if (file_exists($flagfile))
                {
                    $list[$i]['flagfile'][$languagepart] = $flagfile;
                }
            }
        }

        // is this file editable?
        if (localnet())
        {
            $list[$i]['editable'] = ($config['multiuser']) ?
                check_permission(PERM_WRITE, $list[$i]['owner_id']) : true;
        }
        else
        {
            $list[$i]['editable'] = false;
        }
/*
    uncomment this to allow display of rating in the 'Browse' tab
    require_once 'custom.php';
    customfields($list[$i], 'out');
*/
    }

	// do adultcheck
	if (is_array($list))
	{
		$list = array_filter($list, create_function('$video', 'return adultcheck($video["id"]);'));
	}

    // enable dynamic columns in list view
    $smarty->assign('listcolumns', session_get('listcolumns'));
    $smarty->assign('list', $list);

    // show total number of movies in footer
    $smarty->assign('totalresults', count($list));
}

/**
 * Assigns debug infos and version to the smarty engine
 */
function tpl_footer()
{
    global $smarty, $config, $SQLtrace;

    if ($config['debug'])
    {
        $out                = $config;
        $out['db_password'] = '***';
        $session            = $_SESSION['vdb'];
        $session['db_password'] = '***';
        
        ob_start();
        print '<pre>';
        dump($SQLtrace);
        dump($out);
        dump($session);
        print '</pre>';
#        phpinfo();
        $debug = ob_get_contents();
        ob_end_clean();
        
        $smarty->assign('DEBUG', $debug);
    }
    $smarty->assign('version', VERSION);
}

/**
 * Function combines multiple actor thumbnail queries into single SQL query
 */
function get_actor_thumbnails_batched(&$actors)
{
    if (!count($actors)) return;
    
    $ids    = "'".join("','", array_map('addslashes', array_extract($actors, 'id')))."'";

    $SQL    = 'SELECT actorid, name, imgurl, UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(checked) AS cacheage
                 FROM '.TBL_ACTORS.' WHERE actorid IN ('.$ids.')';
    $result = runSQL($SQL);

    $result = array_associate($result, 'actorid');

    // loop over actors from full-text field
    foreach ($actors as $idx => $actor)
    {
        // check for actor thumbnail
        $batch_result = $result[$actor['id']];
        
        if ($batch_result)
            $actors[$idx]['imgurl'] = get_actor_image_from_cache($batch_result, $actor['name'], $actor['id']);
        else
            $actors[$idx]['imgurl'] = getActorThumbnail($actor['name'], $actor['id'], false);
    }
}

/**
 * Convert textbox/db presentation into actors array
 */
function split_cast_array(&$actor, $key)
{
    $ary            = explode('::', $actor);
    
    $actor          = array();   
    $actor['name']  = $ary[0];
    $actor['id']    = $ary[2];
    $actor['roles'] = preg_split('[^</]', $ary[1]);
}

/**
 * Converts plain cast data into array of actors with thumbnails
 *
 * @author  Andreas Goetz    <cpuidle@gmx.de>
 */
function prepare_cast($cast)
{
	global $config;

    // convert text represenatation into array
    $actors = array_filter(preg_split("/\r?\n/", trim($cast)));

    // reformat roles
    $actors = preg_replace('/\((.*?)\)/', '<small>($1)</small>', $actors);

    array_walk($actors, 'split_cast_array');

    // check for actor thumbnails
    if ($config['actorpics']) get_actor_thumbnails_batched($actors);

    // loop over actors from full-text field
    foreach ($actors as $idx => $actor)
    {
        $actors[$idx]['imdburl'] = engineGetActorUrl($actor['name'], $actor['id'], engineGetActorEngine($actor['id']));

        // check for actor thumbnail
#        if ($config['actorpics']) $actor['imgurl'] = getActorThumbnail($actor['name'], $actor['id']);
    }

    return $actors;
}

/**
 * Assigns the videoinfos to the smarty engine
 *
 * @param   array   associative array containing the item data
 */
function tpl_show($video)
{
    global $smarty, $config;

    // imageurl
    $video['imgurl'] = getThumbnail($video['imgurl'], $video['title']);

    // make soft linebreaks:
    $video['filename'] = preg_replace('/(_|\.|-)/', '$1<wbr />', $video['filename']);

    // split comma-separated countries, prevent empty array
    $video['country'] = preg_split('/,\s*/', $video['country'], -1, PREG_SPLIT_NO_EMPTY);

    // split comma-separated multiple languages, prevent empty array
    $video['language'] = preg_split('/,\s*/', $video['language'], -1, PREG_SPLIT_NO_EMPTY);

    // humanreadable filesize:
    $video['filesize'] = round($video['filesize']/(1024*1024), 2);

    // break plot and comment
    $video['plot']     = nl2br($video['plot']);
    $video['comment']  = nl2br($video['comment']);

	// cast
	$video['cast']     = prepare_cast($video['actors']);

    // prepare the custom fields
    customfields($video, 'out');

    // hide owner if not using multi-user
    if (!$config['multiuser']) unset($video['owner']);

    // get drilldown url for image
    if ($video['imdbID'])
    {
        require_once './engines/engines.php';
        $smarty->assign('link', engineGetContentUrl($video['imdbID'], engineGetEngine($video['imdbID'])));
    }

    // add episodes information
    if (is_array($video['episodes']))
    {
        // allow multiple columns
        $smarty->assign('listcolumns', session_get('listcolumns'));
    }

    $smarty->assign('castcolumns', $config['castcolumns']);
    $smarty->assign('video', $video);

    // get genre ids and names
    $smarty->assign('genres', getItemGenres($video['id'], true));

    // make engines available
    $smarty->assign('engines', $config['engine']);

    // allow XML export
    foreach (array('xls','pdf','xml') as $export)
    {
        if ($config[$export]) $smarty->assign($export, 'show.php?id='.$video['id'].'&amp;');
    }
    // new-style way of exporting
    // $smarty->assign('exports', listExports('show.php?id='.$video['id'].'&amp;'));
}

/**
 * Assigns the videoinfos to the smarty engine
 */
function tpl_edit($video)
{
	global $smarty, $config, $lang;

	// create a form ready quoted version for each value
	foreach (array_keys($video) as $key)
    {
		$video['q_'.$key] = formvar($video[$key]);
	}

	// use custom function for language
	$video['f_language']  = custom_language_input('language', $video['language']);

	// create mediatype selectbox
    $smarty->assign('mediatypes', out_mediatypes());
    if (!isset($video['mediatype'])) $video['mediatype'] = $config['mediadefault'];

	// prepare the custom fields
	customfields($video, 'in');

    if ($config['multiuser'])
    {
        $smarty->assign('owners', out_owners(array('0' => ''), (check_permission(PERM_ADMIN)) ? false : PERM_WRITE, true));
    }

	// item genres
	$item_genres = getItemGenres($video['id']);
	// new-style
    $smarty->assign('genres', out_genres2($item_genres));
#dlog(out_genres2($item_genres));
#dlog($item_genres);
    // classic
    $smarty->assign('genreselect', out_genres($item_genres));

	// assign data
	$smarty->assign('video', $video);

	// get drilldown url for visit link
	if ($video['imdbID'])
    {
        require_once './engines/engines.php';
        $engine = engineGetEngine($video['imdbID']);	
        $smarty->assign('link', engineGetContentUrl($video['imdbID'], $engine));
        $smarty->assign('engine', $engine);
	}

/*
    // populate autocomplete boxes
    $smarty->assign('audio_codecs', array_extract(runSQL('SELECT DISTINCT audio_codec FROM '.TBL_DATA.' WHERE audio_codec IS NOT NULL'), 'audio_codec'));
    $smarty->assign('video_codecs', array_extract(runSQL('SELECT DISTINCT video_codec FROM '.TBL_DATA.' WHERE video_codec IS NOT NULL'), 'video_codec'));
*/        
	$smarty->assign('lookup', array('0' => $lang['radio_look_ignore'],
							        '1' => $lang['radio_look_lookup'],
							        '2' => $lang['radio_look_overwrite']));

    // needed for ajax image lookup
    $smarty->assign('engines', $config['engines']);
}

/**
 * Prepare lookup template
 */
function tpl_lookup($find, $engine, $searchtype)
{
    global $smarty, $config;

    $find   = trim($find);
    $smarty->assign('find', $find);
    $smarty->assign('q_find', formvar($find));

    $smarty->assign('engine', $engine);

    $tpl    = array();
    foreach (engine_get_capable_engines($searchtype) as $eng => $enabled)
    {
        // url- make sure this is non-unicode
        $tpl[$eng]['url']   = 'lookup.php?find='.urlencode(utf8_smart_decode($find)).'&engine='.$eng.'&searchtype='.$searchtype;

        // title
        $tpl[$eng]['name']  = $config['engines'][$eng]['name'];
    }

    $smarty->assign('engines', $tpl);
}

?>