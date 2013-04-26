<?php
/**
 * Export functions. Returns standardized data for export.
 *
 * @package videoDB
 * @author  Andreas Götz <cpuidle@gmx.de>
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @version $Id: export.core.php,v 1.8 2013/03/15 16:42:46 andig2 Exp $
 */

require_once './core/genres.php';

function listExports($link, $omit = array('rss'))
{
    global $config;

    $exports = array('xls' => 'Microsoft Excel',
                     'pdf' => 'Adobe PDF',
                     'xml' => 'XML',
                     'rss' => 'RSS Feed');

    $res     = array();
    foreach ($exports as $export => $title)
    {
        if ($config[$export] &! in_array($export, $omit)) 
            $res[] = array('type' => $export, 'title' => $title, 'link' => $link);
    }
    return($res);
}

function exportData($WHERE)
{
     $SQL = 'SELECT '.TBL_DATA.'.*, 
                    '.TBL_USERS.'.name AS owner, 
                    '.TBL_MEDIATYPES.'.name AS mediatype,
                    '.TBL_LENT.'.who AS lentto,
          CASE WHEN '.TBL_USERSEEN.'.video_id IS NULL THEN 0 ELSE 1 END AS seen
               FROM '.TBL_DATA.'
          LEFT JOIN '.TBL_USERS.' ON '.TBL_DATA.'.owner_id = '.TBL_USERS.'.id
          LEFT JOIN '.TBL_USERSEEN.' ON '.TBL_DATA.'.id = '.TBL_USERSEEN.'.video_id AND '.TBL_USERSEEN.'.user_id = '.get_current_user_id().'
          LEFT JOIN '.TBL_LENT.' ON '.TBL_DATA.'.diskid = '.TBL_LENT.'.diskid 
          LEFT JOIN '.TBL_MEDIATYPES.' ON mediatype = '.TBL_MEDIATYPES.'.id '.
             $WHERE;
 
    $result = runSQL($SQL);

    // do adultcheck
    if (is_array($result))
    {
        $result = array_filter($result, create_function('$video', 'return adultcheck($video["id"]);'));
    }

    // genres
    for($i=0; $i<count($result); $i++)
    {
        $result[$i]['genres'] = getItemGenres($result[$i]['id'], true);
    }    

    return $result;
}

/**
 * Limit string length while honoring word breaks
 *
 * @param   string  string to trim
 * @param   int     target length
 * @result  string  trimmed string
 */
function leftString($plot, $text_length)
{
    if (strlen($plot) > $text_length+3)
    {
        $plot   = substr($plot, 0, $text_length);
        $space  = strrpos($plot, ' ');
        if ($space) $plot = substr($plot, 0, $space);
        $plot  .= '...';
    }
    return $plot;
}

?>
