<?php
/**
 * Search page
 *
 * Database searches for movies
 *
 * @package Search
 * @author  parts by Justin Pasher <justin@pasher.org>
 * @author  parts by Chinamann <chinamann@users.sourceforge.net>
 * @version $Id: search.php,v 2.61 2013/03/16 14:29:47 andig2 Exp $
 */

require_once './core/session.php';
require_once './core/functions.php';
require_once './core/queryparser.php';
require_once './core/output.php';

// multiuser permission check
permission_or_die(PERM_READ, PERM_ANY);

/**
 * input
 */
$id = req_int('id');
$diskid = req_int('diskid');
$$;

// set defaults and update session
session_default('listcolumns', $config['listcolumns']);
session_set('genres', $genres = isset($genres) ? $genres : array());
// enable redirects to last list view for delete.php
session_set('listview', 'search.php');

/**
 * Update item list asynchronously
 *
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 */
function ajax_render()
{
    global $smarty, $result, $config;

    // add some delay for debugging
    if ($config['debug'] && $_SERVER['SERVER_ADDR'] == '127.0.0.1')  usleep(rand(200,1000)*1000);

    // load languages and config into Smarty
    tpl_language();
    tpl_list($result);

    $content = $smarty->fetch('list.tpl');
#    file_append(LOG_FILE, $content);

    header('X-JSON: '.json_encode(array('count' => count($result))));
    echo $content;

    exit;
}

// make sure we have an owner to filter search
if ($config['multiuser'])
{
    // get owner from session- or use current user
    session_default('owner', get_username(get_current_user_id()));

    $all = $lang['filter_any'];
}

// prepare fieldselect
$search_fields = array( 'title'         => $lang['title'],
                        'subtitle'      => $lang['subtitle'],
                        'director'      => $lang['director'],
                        'actors'        => $lang['cast'],       // differs
                        'plot'          => $lang['plot'],
                        'year'          => $lang['year'],
                        'country'       => $lang['country'],
                        'diskid'        => $lang['diskid'],
                        'owner'         => $lang['owner'],
                        'mediatype'     => $lang['mediatype'],
                        'language'      => $lang['language'],
                        'comment'       => $lang['comment'],
//                        'imgurl'        => $lang['coverurl'],
                        'audio_codec'   => $lang['audiocodec'],
                        'video_codec'   => $lang['videocodec'],
                        );

// search fields to replace by physical name
$replace_fields = array('mediatype' => TBL_MEDIATYPES.'.name',
                        'owner'     => TBL_USERS.'.name',
                        'diskid'    => TBL_DATA.'.diskid');

// add custom fields
for ($i=1; $i<=4; $i++)
{
	if (!empty($config['custom'.$i]))
    {
		$search_fields['custom'.$i] = $config['custom'.$i];
	}
}

// remove empty fields and make array
if (!is_array($fields)) $fields = array($fields);
$fields = array_filter($fields);

if (count($fields) == 0 && $isname) $fields = array('director', 'actors');

$smarty->assign('selected_fields', $fields);

// nothing selected? use all fields for searching
if (count($fields) == 0) $fields = array_keys($search_fields);

// translate search fields into SQL column names, store original for template first
foreach ($fields as $search_field)
{
    /*
     * field name conversion is required since the mysql AS columns can't be used in where clauses
     * add all fields here where the logical name in the template differs from the physical column name (e.g. mediatype)
     * or where the column name is ambiguous (e.g. diskid)
     */
    if (array_key_exists($search_field, $replace_fields))
    {
        if (($key = array_search($search_field, $fields)) !== false)
        {
            unset($fields[$key]);
            $fields[] = $replace_fields[$search_field];
        }
    }
}

// prepare search query
if (isset($q) &! (isset($default) && empty($q)))
{
    $JOINS  = '';
	$WHERES = '1=1 ';

	// remove empty genres
	$genres = array_filter($genres);
	
	if (!empty($q))
	{
		$error  = '';
		$tokens = queryparser($q, $error);

		$wild_char = (empty($nowild)) ? '%' : '';

		foreach ($tokens as $token)
		{
            // escape search token
			$token['token'] = addslashes($token['token']);

            // concatenate tokens with token operator
			$WHERES .= $token['ops'].' (';

            // concatenate all searchable fields with OR
			foreach ($fields as $field)
            {
				$WHERES .= " ($field LIKE '$wild_char".$token['token']."$wild_char') OR ";
			}

            // concatenate custom fields with OR
			for ($custom = 1; $custom <= 4; $custom++)
			{
				if (!empty($config['custom'.$custom]) && !empty($fields['custom'.$custom]))
                {
					$WHERES .= " (custom$custom LIKE '$wild_char".$token['token']."$wild_char') OR ";
				}
			}
			$WHERES .= ' 1=2)';
		}
	}
	
    // filter by genres
	if (count($genres))
	{
        $JOINS  .= ' LEFT JOIN '.TBL_VIDEOGENRE.' ON '.TBL_DATA.'.id = '.TBL_VIDEOGENRE.'.video_id ';
        $WHERES .= ' AND '.TBL_DATA.'.id = '.TBL_VIDEOGENRE.'.video_id AND (';

		foreach ($genres as $genre)
        {
            $FILTER .= 'OR '.TBL_VIDEOGENRE.'.genre_id = '.$genre.' ';
		}
		
        $FILTER  = preg_replace('/^OR/', '', $FILTER);
		$WHERES .= $FILTER;
		$WHERES .= ')';
	}

    // limit visibility
    if ($config['multiuser'])
    {
        // if we don't have read all permissions, limit visibility using cross-user permissions
        if (!check_permission(PERM_READ))
        {
            $JOINS  .= ' LEFT JOIN '.TBL_PERMISSIONS.' ON '.TBL_DATA.'.owner_id = '.TBL_PERMISSIONS.'.to_uid';
            $WHERES .= ' AND '.TBL_PERMISSIONS.'.from_uid = '.get_current_user_id().' AND '.TBL_PERMISSIONS.'.permissions & '.PERM_READ.' != 0';
        }

        // further limit to single owner
        if ($owner && $owner != $all) $WHERES .= " AND ".TBL_USERS.".name = '".addslashes($owner)."'";
    }

    // XML / PDF export
    if ($export && $config[$export])
    {
        $func = $export.'export';
        require_once './core/'.$export.'.php';

        if (function_exists($func)) $func("$JOINS WHERE $WHERES ORDER BY title, subtitle");
        exit();
    }

    $select = 'SELECT DISTINCT '.TBL_DATA.'.id, '.TBL_DATA.'.diskid,
                      title, subtitle, language, year, director, plot, imgurl,
                      md5, comment, disklabel, imdbID, actors, runtime,
                      country, filename, filesize, filedate, audio_codec,
                      video_codec, video_width, video_height, istv,
                      lastupdate, mediatype, created,
                      custom1, custom2, custom3, custom4,
                      '.TBL_LENT.'.who, '.TBL_USERS.'.name AS owner, '.TBL_MEDIATYPES.'.name AS mediatypename,
                      !ISNULL('.TBL_USERSEEN.'.video_id) AS seen
                 FROM '.TBL_DATA.'
            LEFT JOIN '.TBL_USERS.' ON owner_id = '.TBL_USERS.'.id
            LEFT JOIN '.TBL_LENT.' ON '.TBL_DATA.'.diskid = '.TBL_LENT.'.diskid
            LEFT JOIN '.TBL_MEDIATYPES.' ON '.TBL_DATA.'.mediatype = '.TBL_MEDIATYPES.'.id'."
            LEFT JOIN ".TBL_USERSEEN.' ON '.TBL_DATA.'.id = '.TBL_USERSEEN.'.video_id AND '.TBL_USERSEEN.'.user_id = '.get_current_user_id()."
               $JOINS
                WHERE ".$WHERES.'
             ORDER BY title, subtitle';

    $result = runSQL($select);

/*
	// prepare actors table if searching for them
	if (in_array('actors', $fields))
	{
		$actors = '';
		foreach ($result as $row)
		{
			$actors .= $row['actors']."\n";
		}
#		dump($actors);
		$qa = preg_replace('/"/', '', $q);
#		dump($qa);
		
		if (preg_match_all("#^.*$qa.*#im", $actors, $m, PREG_PATTERN_ORDER))
			$actors = join("\n", $m);
		else
			$actors = '';
	}
*/

    // autocomplete textbox
    if ($ajax_quicksearch)
    {
        foreach ($result as $item)
        {
            $title  = $item['title'];
            if ($item['subtitle'])  $title .= ' - '.$item['subtitle'];
            $title  = preg_replace('/('.$q.')/Ui', '<em>\1</em>', $title);
            $ret   .= "<li id='".$item['id']."'>".$title."</li>";
        }
        $ret = "<ul>$ret</ul>";

        echo $ret;
        exit;
    }

    // store query result in session for prev/next navigation
    session_set('query_result', array_column($result, 'id'));
}

// process asynchronous refresh
if ($ajax_render)
{
    ajax_render();
}

// prepare templates
tpl_page('search', $q);
tpl_list($result);

$smarty->assign('q', $q);
$smarty->assign('q_q', formvar($q));
$smarty->assign('search_fields', $search_fields);
$smarty->assign('genreselect', out_genres($genres));
$smarty->assign('genres', out_genres2($genres));
$smarty->assign('engine', $config['engine']);
$smarty->assign('actors', prepare_cast($actors));

// person search?
if ($isname && ($config['actorpics']))
{
    $smarty->assign('imgurl', getActorThumbnail(urldecode(preg_replace('/&quot;|"/', '', formvar($q)))));
}

// allow XML export
if (isset($q))
{
    $link = htmlentities($_SERVER['QUERY_STRING']);     // encode for XHTML compliance
    if ($link) $link .= '&amp;';
    $link = 'search.php?'.$link;

    if ($config['xls']) $smarty->assign('xls', $link);
    if ($config['xml']) $smarty->assign('xml', $link);
    if ($config['pdf']) $smarty->assign('pdf', $link);
}

if ($config['multiuser'])
{
    $smarty->assign('owners', out_owners(array($all => $all), PERM_READ));
    $smarty->assign('owner', $owner);
}

// display templates
smarty_display('header.tpl');
if (!$config['http_caching']) flush();

smarty_display('search.tpl');
smarty_display('list.tpl');
smarty_display('footer.tpl');

