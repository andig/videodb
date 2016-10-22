<?php
/**
 * Statistics
 *
 * Shows general statistics about the database
 *
 * @package videoDB
 * @author  Andreas Gohr <a.gohr@web.de>
 * @version $Id: stats.php,v 2.25 2009/04/04 16:22:56 andig2 Exp $
 */

require_once './core/functions.php';

/*
 * Helper function for comparing an associative array by it's 'count' values 
 */
function compare_count($a, $b)
{
    return strnatcasecmp($b['count'], $a['count']); 
}

/*
 * De-uniquify multi-language statistics
 *
 * Makes sure videos with mulitple languages are count once for each language
 * contained instead of once for a unique combination
 *
 * @param   array   $langs  array of all languages from 
 */ 
function collapse_multiple_languages($langs) 
{
    $templangs = array();
    
    foreach($langs as $key => $val) 
    {
        $lang_ary  = preg_split('/,\s*/', $val['language']);
        foreach($lang_ary as $numid => $lang) 
        {
            // take care of english vs. English
            $lang = ucwords($lang);
            
            $templangs[$lang]['language'] = $lang;
            $templangs[$lang]['count']   += $val['count'];
        }
    }
    usort($templangs, 'compare_count');
    
    return $templangs;
}

if ($config['multiuser']) 
{
    // get owner from session- or use current user
    session_default('owner', get_username(get_current_user_id()));

    // build html select box
    $all = strtoupper($lang['radio_all']);
    $smarty->assign('owners', out_owners(array($lang['filter_any'] => $lang['filter_any']), PERM_READ));
    $smarty->assign('owner', $owner);

    // further limit to single owner
    if ($owner == $lang['filter_any']) 
        $WHERES .= " AND owner_id IN (".get_owner_ids(PERM_READ).")";
    else
        $WHERES .= " AND owner_id = '".get_userid($owner)."'";
}

// Don't count movies which are on the wishlist
$WHERES .= ' AND mediatype != '.MEDIA_WISHLIST;

$result = runSQL('SELECT COUNT(*) AS count FROM '.TBL_DATA.' WHERE 1=1'.$WHERES);
$stats['count_all']  = $result[0]['count'];

$result = runSQL('SELECT COUNT(*) AS count FROM '.TBL_DATA.' WHERE istv = 1'.$WHERES);
$stats['count_tv']   = $result[0]['count'];

$result = runSQL('SELECT DISTINCT diskid FROM '.TBL_DATA.' WHERE 1=1'.$WHERES);
$stats['count_disk'] = count($result);

$result = runSQL('SELECT AVG(filesize) AS afs FROM '.TBL_DATA.' WHERE filesize > 0'.$WHERES);
$stats['avg_size']   = round(($result[0]['afs'])/(1024*1024), 2);

$result = runSQL('SELECT SUM(filesize) AS sfs,
                         AVG(runtime)  AS art,
                         SUM(runtime)  AS srt
                  FROM '.TBL_DATA.' WHERE 1=1'.$WHERES);
$stats['sum_size']   = round(($result[0]['sfs'])/(1024*1024*1024), 2);
$stats['avg_time']   = round($result[0]['art'], 2);
$stats['sum_time']   = round(($result[0]['srt'])/60, 2);

$result = runSQL('SELECT SUM(runtime) AS srt FROM '.TBL_DATA.'
               LEFT JOIN '.TBL_USERSEEN.' ON '.TBL_DATA.'.id = '.TBL_USERSEEN.'.video_id AND '.TBL_USERSEEN.'.user_id = '.get_current_user_id().'
                   WHERE !ISNULL('.TBL_USERSEEN.'.video_id)'.$WHERES);
$stats['seen_time']  = round($result[0]['srt']/60, 2);  

$result = runSQL('SELECT A.name, COUNT(*) AS count, A.id
                    FROM '.TBL_GENRES.' A, '.TBL_VIDEOGENRE.' B, '.TBL_DATA.' C 
                   WHERE B.genre_id = A.id
                   	 AND B.video_id = C.id'.$WHERES.'
                GROUP BY A.name, A.id
                ORDER BY count DESC');
$stats['count_genre'] = $result;

$result = runSQL('SELECT A.name, COUNT(*) AS count, A.id
                    FROM '.TBL_MEDIATYPES.' A, '.TBL_DATA.' B
                   WHERE B.mediatype = A.id'.$WHERES.' 
                GROUP BY A.name, A.id
                ORDER BY count DESC');
$stats['count_media'] = $result;

$result = runSQL('SELECT language, COUNT(*) AS count
                    FROM '.TBL_DATA.' WHERE 1=1'.$WHERES.' 
                GROUP BY language
                ORDER BY count DESC');
$stats['count_lang'] = collapse_multiple_languages($result);
                     
$result = runSQL('SELECT video_codec, COUNT(*) AS count
                    FROM '.TBL_DATA.' WHERE 1=1'.$WHERES.' 
                GROUP BY video_codec
                ORDER BY count DESC');
$stats['count_vcodec'] = $result;

$result = runSQL('SELECT audio_codec, COUNT(*) AS count
                    FROM '.TBL_DATA.' WHERE 1=1'.$WHERES.' 
                GROUP BY audio_codec
                ORDER BY count DESC');
$stats['count_acodec'] = $result;

// year statistics
$result = runSQL('SELECT year, COUNT(*) AS count
                    FROM '.TBL_DATA.'
                   WHERE year > 0'.$WHERES.' 
                GROUP BY year
                ORDER BY year');
$minyear = $result[0]['year'];
$maxyear = $result[count($result)-1]['year'];

for ($i = $minyear; $i <= $maxyear; $i++)
{
    $years[$i] = 0;
}

$maxcount = 0;
if (is_array($result))
{
	foreach ($result AS $year)
	{
		$years[$year['year']] = $year['count'];
        if ($year['count'] > $maxcount) $maxcount = $year['count'];
	}
}

$stats['count_year'] = $years;
$stats['first_year'] = $minyear;
$stats['last_year']  = $maxyear;
$stats['max_count']  = $maxcount;

// prepare templates
tpl_page();

$smarty->assign('stats', $stats);

// display templates
tpl_display('stats.tpl');

?>
