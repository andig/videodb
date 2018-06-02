<?php
/**
 * Output functions
 *
 * Functions for HTML output generation (Not templates!)
 *
 * @todo    Check if this can be moved to smarty plugins
 *
 * @package Core
 * @author  Andreas Gohr    <a.gohr@web.de>
 * @author  Andreas Goetz   <cpuidle@gmx.de> 
 * @version $Id: output.php,v 1.29 2013/04/19 07:55:58 andig2 Exp $
 */

require_once './core/functions.php';

/**
 * Return list of valid genres from db
 */
function getGenres()
{
    $SELECT = 'SELECT id, name
                 FROM '.TBL_GENRES.'
             ORDER BY name';
    $result = runSQL($SELECT);
    
    return $result;
}

/**
 * Display genre checkboxes
 *
 * @param  array $selected  selected genre IDs
 * @return                  string HTML for genre checkboxes
 */
function out_genres($selected)
{
    global $config,$lang;

    $result = getGenres();
	$out = '<table class="genreselect"><tr>';

    // get list of adult genres
    $adultgenres = array();
    if ($config['multiuser'] && !check_permission(PERM_ADULT))
    {
        $adultgenres = get_adult_genres();
    }

	$row = 0;
	foreach ($result as $res)
	{
        // don't show adult genres if no permissions
        if (in_array($res['id'], $adultgenres)) continue;

		$out .= '<td nowrap="nowrap">';
		$out .= '<input type="checkbox" name="genres[]" id="genreid'.$res['id'].'" value="'.$res['id'].'"';
		if (@in_array ($res['id'], $selected))
        {
			$out .= ' checked="checked"';
		}
		$out .= '/>';
		$out .= '<label for="genreid'.$res['id'].'">'.(isset($lang[$res['name']]) ? $lang[$res['name']] : $res['name']).'</label>';
		$out .= '</td>';
        if ((++$row % 5) == 0)
        {
			$out .= '</tr><tr>';
		}
	}
    $out .= '</tr></table>';

	return $out;
}

/**
 * Generate genres array for use with genre checkboxes
 *
 * @param  array $selected  selected genre IDs
 * @return                  string HTML for genre checkboxes
 */
function out_genres2($item_genres = null)
{
	// get detailed genres
    $all_genres = getGenres();
    $adultgenres = array();
    if ($config['multiuser'] && !check_permission(PERM_ADULT)) {
        $adultgenres = get_adult_genres();
    }
    
	$genres = array();
	foreach ($all_genres as $gen) {
        // don't show adult genres if no permissions
        if (in_array($gen['id'], $adultgenres)) continue;

		// selected?
		if ($item_genres) $gen['checked'] = (@in_array($gen['id'], $item_genres)) ? 1 : 0;
		
		$genres[] = $gen;
	}

    return($genres);
}

/**
 * Display selectbox with available Mediatypes
 *
 * @todo is this still used? can it be replaced by template code?
 * @author <rob@robvonk.com>
 * @return string   HTML of selectbox
 */
function out_mediatypes($prefix = null)
{
	global $config;

    // select mediatypes
	$SELECT = 'SELECT id, name
                 FROM '.TBL_MEDIATYPES.'
			 ORDER BY name';
	$result = runSQL($SELECT);

    // build associative array
    # array('0' => '') + 
    $mediatypes = is_array($prefix) ? $prefix : array();
    $mediatypes = $mediatypes + array_associate($result, 'id', 'name');

    return $mediatypes;
}

/**
 * All available language flags for config screen
 *
 * @param  array  $flags   selected flags
 * @return string          HTML of Languageflags
 */
function out_languageflags($flags)
{
	global $config;

	$out	= '';
	$count	= 1;
	
	if (($dh = @opendir('./'.$config['templatedir'].'images/flags')) || ($dh = opendir('./images/flags')))
	{
		while (($file = readdir($dh)) !== false)
		{
			if (preg_match("/(.*)\.gif$/", $file, $matches))
			{
				$CHECK= (in_array($matches[1], $flags)) ? 'checked="checked"' : '';
				$out .= '<input type="checkbox" name="languages[]" '.$CHECK.' id="flag_'.$matches[1].'" value="'.$matches[1].'" />';
				$out .= '<label for="flag_'.$matches[1].'">';
				$out .= '<img src="'.img('flags/'.$matches[1].'.gif').'" width="30" height="15" alt="'.ucwords($matches[1]).'" title="'.ucwords($matches[1]).'" />';
				$out .= '</label> ';
				if ($count++%4 == 0) $out.='<br />';
			}
		}
		closedir($dh);
	}
	return $out;
}

/**
 * List of owners names/ids with valid permissions for use in edit/index/search templates
 *
 * @author  <cpuidle@gmx.de>
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @param   string  $prefix         Predefined additional Array entries
 * @param   string  $permission     Honor permissions for selectbox
 * @return  string                  Array with keys=ownernames and values=ownerids
 */
function out_owners($prefix = null, $permission = false, $keyIsId = false)
{
    global $config;

    // all permissions available if admin
    if (check_permission(PERM_ADMIN)) $permission = false;

    // hide guest if he/she can't login
    $WHERES = ($config['denyguest']) ? " AND B.id != ".$config['guestid'] : '';

    // select user ids- if permissions are required and no all access given, this is done against xrefs
    if ($permission && !check_permission($permission))
    {
        // xref permissions
        // TODO use cached permission table instead
        $SELECT = 'SELECT DISTINCT(B.name) AS name, B.id
                     FROM '.TBL_PERMISSIONS.' A, '.TBL_USERS.' B
                    WHERE A.to_uid = B.id
                          AND A.from_uid = '.get_current_user_id().'
                          AND (A.permissions & '.$permission.') = '.$permission.$WHERES.'
                    ORDER BY name';
    }
    else
    {
        // all users +/- guest
        $SELECT = 'SELECT B.id, B.name
                     FROM '.TBL_USERS.' B
                    WHERE 1=1 '.$WHERES.'
                    ORDER BY B.name';
    }
    $result = runSQL($SELECT);

    $key = ($keyIsId) ? 'id' : 'name';

    // build associative array
    $owners = is_array($prefix) ? $prefix : array();
    $owners = $owners + array_unique(array_associate($result, $key, 'name'));

    return $owners;
}

/**
 * MySQL-compatible list of owner ids with required access permission
 *
 * @author  Andreas Goetz   <cpuidle@gmx.de> 
 */
function get_owner_ids($permission)
{
    foreach($_SESSION['vdb']['permissions']['to_uid'] as $to_uid => $perm)
    {
        if ($permission & $perm) $ids[] = $to_uid;
    }
    
    return (count($ids)) ? join(',', $ids) : -1;
}

/**
 * Present a size (in bytes) as a human-readable value
 *
 * @author  http://php.net
 *
 * @param int    $size        size (in bytes)
 * @param int    $precision    number of digits after the decimal point
 * @return string
 */
function sizetostring($size, $precision = 0)
{
    $sizes = array('YB', 'ZB', 'EB', 'PB', 'TB', 'GB', 'MB', 'kB', 'B');
    $total = count($sizes);

    while($total-- && $size > 1024) $size /= 1024;
    return round($size, $precision).$sizes[$total];
}

function img_avg_color($filename, $format=0)
{
	// networked file
	if (preg_match('/^http/i', $imgurl)) return(FALSE);

	// not a valid image
	if (!list($width, $height) = @getimagesize($filename)) return(FALSE);

	// resample
	switch (exif_imagetype($filename)) {
		case 2:
			$img = imagecreatefromjpeg($filename);
			break;
		case 3:
			$img = imagecreatefrompng($filename);
			break;
		case 1:
			$img = imagecreatefromgif($filename);
			break;
	}
	
	$tmp = imagecreatetruecolor(1, 1);
	if (!imagecopyresampled($tmp, $img, 0, 0, 0, 0, 1, 1, $width, $height)) return(FALSE);

	$rgb = imagecolorat($tmp, 0, 0);
    $r = dechex($rgb >> 16);
    $g = dechex($rgb >> 8 & 0xFF);
    $b = dechex($rgb & 0xFF);
    
	return('#'.$r.$g.$b);
}

?>