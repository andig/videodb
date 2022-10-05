<?php
/**
 * Edit Page
 *
 * The edit form for adding and editing video data
 *
 * @todo    Add error message for unknown genres
 *
 * @package videoDB
 * @author  Andreas Gohr <a.gohr@web.de>
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @version $Id: edit.php,v 2.90 2013/03/11 19:00:26 andig2 Exp $
 */

require_once './core/functions.php';
require_once './core/genres.php';
require_once './core/custom.php';
require_once './core/edit.core.php';
require_once './engines/engines.php';

// check for localnet
localnet_or_die();

// multiuser permission check
permission_or_die(PERM_WRITE, ($id) ? get_owner_id($id) : PERM_ANY);

/**
 * input
 */
$id = req_int('id');
$diskid = req_int('diskid');
$$;

// clean input data
$genres = (is_array($genres)) ? array_filter($genres) : array();

// ajax autocomplete?
if ($ajax_prefetch_id || $ajax_autocomplete_title || $ajax_autocomplete_subtitle)
{
    // add some delay for debugging
    if ($config['debug'] && $_SERVER['SERVER_ADDR'] == '127.0.0.1')  usleep(rand(200,1000)*1000);

    // prefetch external data
    if ($ajax_prefetch_id)
    {
        $data = engineGetData($ajax_prefetch_id, engineGetEngine($ajax_prefetch_id));
        if (count($data))
        {
            $data['imdbID'] = $ajax_prefetch_id;
            $data['actors'] = $data['cast'];
            $data['imgurl'] = $data['coverurl'];
        }    
/*
        // load languages and config into Smarty
        tpl_language();
        tpl_edit($data);
        $content = $smarty->fetch('edit.tpl');
#       file_append('log.txt', $content);

        echo $content;
*/
        exit;
    }

    // use subtitle for aka search
    $data   = ($ajax_autocomplete_title) ? 
    				engineSearch($ajax_autocomplete_title, engineGetDefault()) : 
    				engineSearch($ajax_autocomplete_subtitle, engineGetDefault(), true);
    
    if ($ajax_type == 'json') {
#        file_append('log.txt', $res);
		header("Content-Type: application/json");
		echo(json_encode($data));
		exit;
    }
    
    foreach ($data as $item)
    {
        $text = preg_replace('/('.$ajax_autocomplete_title.')/i', '<em>\1</em>', $item['title']);
        $text.= (($item['year']) ? "<span class='informal'> (".$item['year'].")</span>" : '');
        $ret .= "<li id='".$item['id']."'>".$text."</li>";
    }
    $ret = "<ul>$ret</ul>";

    exit($ret);
} 

// duplicate check
if ($ajax_check_duplicate)
{
    $q      = escapeSQL($ajax_check_duplicate);
    $res    = runSQL("SELECT id, title FROM ".TBL_DATA." WHERE imdbid='".$q."' OR title LIKE '%".$q."%' AND owner_id=".get_current_user_id());
    
    header('X-JSON: '.json_encode($res));
    exit;
}

// XML import
if ($config['xml'] && ($import == 'xml'))
{
    require_once './core/xml.php';

    // xml file upload
    if (isset($_FILES['xmlfile']) && is_uploaded_file($_FILES['xmlfile']['tmp_name']))
    {
        $file    = $_FILES['xmlfile']['tmp_name'];
        $xmldata = file_get_contents($file);
        unlink($file);
    }

    // uploading XML data directly or loaded from file
    if (!empty($xmldata))
    {
        $error      = '';
        $item_id    = 0;

	    require_once './core/xmlimport.php';

        if (($xmlitems = xmlimport($xmldata, $error)) !== false)
        {
            // multiple items imported
            if ($xmlitems === true)
            {
                redirect('index.php?filter=new');
            }
            // exactly one movie imported?
            else
            {
                redirect('show.php?id='.$xmlitems);
            }
        }
        $smarty->assign('xmlerror', $error);
    }

    // prepare templates
    tpl_page();

    // display templates
    tpl_display('xmlimport.tpl');
    exit;
}

// legacy
if ($imdb) $lookup = 1;

// get default lookup mode (0=ignore, 1=lookup, 2=overwrite) if not set
if (!isset($lookup)) $lookup = (empty($id)) ? $lookup = $config['lookupdefault_new'] : $config['lookupdefault_edit'];

// preload old data for refresh all mechanism
if ($lookup > 2)
{
    // get a list of movies in DB
    $video = runSQL('SELECT * FROM '.TBL_DATA.' WHERE id = '.$id);

    // get fields (according to list) from db to be saved later
    foreach ($video[0] as $name => $val)
    {
       if (in_array($name, $imdb_set_fields)) $$name = $val;
    }
    $owner_id = $video[0]['owner_id'];

    // Build a list of all fields which are allowed to be overwritten
    $overwrites = array();
    foreach ($imdb_set_fields as $field)
    {
        $tempFieldName = 'update_'.$field;
        if (isset($$tempFieldName) && $$tempFieldName == 1) $overwrites[] = $field;
    }
    $imdb_set_fields = $overwrites;

    // valid input values for lookup > 2 are either 5 (add missing) or 6 (overwrite)
    $lookup -= 4;
}

// lookup imdb
if ($lookup && $imdbID)
{
    // get engine from id
    if (empty($engine)) $engine = engineGetEngine($imdbID);

    // get external data
    $imdbdata = engineGetData($imdbID, $engine);

    // lookup cover
    if (empty($imgurl) || ($lookup > 1))
    {
        $imgurl = $imdbdata['coverurl'];
    }

    // lookup genres
    if (count($genres) == 0 || ($lookup > 1))
    {
        $genres = array();
        $gnames = $imdbdata['genres'];
        if (isset($gnames))
        {
            foreach ($gnames as $gname)
            {
                // check if genre is found- otherwise fail silently
                if (is_numeric($genre = getGenreId($gname)))
                {
                    $genres[] = $genre;
                }
            }
        }
    }

    // lookup actors
    if (empty($actors) || ($lookup > 1))
    {
        $actors = $imdbdata['cast'];
    }

    // lookup all other fields
    foreach (array_keys($imdbdata) as $name)
    {
        if (in_array($name, array('coverurl', 'genres', 'cast', 'id'))) continue;

        // use !$$ as empty($$) doesn't seem to work
        if (!$$name || ($lookup > 1))
        {
            $$name = $imdbdata[$name];
        }
    }

    // custom fields
    for ($i=1; $i<=4; $i++)
    {
        $custom = 'custom'.$i;
        $type   = $config[$custom.'type'];
        if (!empty($type) && isset($$type))
        {
            // copy imdb data into corresponding custom field
            $$custom = $$type;
        }
    }
}

// get fields from db if copying
if ($copy && $copyid)
{
    $video = runSQL('SELECT * FROM '.TBL_DATA.' WHERE id = '.$copyid);

    // get fields (according to list) from db to be saved later
    foreach ($video[0] as $name => $val)
    {
        // don't copy diskid
        if ($name == 'diskid')
        {
            if ($config['autoid'])
            {
                $$name = getDiskId();
            }
        }
		else
        {
            if (in_array($name, $imdb_set_fields)) $$name = $val;
        }
    }
    
    $genres = getItemGenres($copyid);
}


// save data
if ($save)
{
    // uncomment the following line to provide simple protection for your own public access videoDB
    //if (!preg_match('/[0-9]{2+}/', $id)) break;
    
    // implicit owner id if not set
    if (!$owner_id) $owner_id = get_current_user_id();

    // generate diskid
    if (empty($diskid) && $config['autoid'] && $mediatype != MEDIA_WISHLIST) $diskid = getDiskId();

    // write videodata table
    $SETS   = prepareSQL($GLOBALS);
    $id     = updateDB($SETS, $id, ($oldmediatype == MEDIA_WISHLIST) && ($mediatype != MEDIA_WISHLIST));

    // save genres
    setItemGenres($id, $genres);

    // set seen for currently logged in user
    set_userseen($id, $seen);

    // uploaded cover?
    processUpload($id, $_FILES['coverupload']['tmp_name'], $_FILES['coverupload']['type'], $_FILES['coverupload']['name']);

    // make sure no artifacts
    $smarty->clearCache('list.tpl');
    $smarty->clearCache('show.tpl', get_current_user_id().'|'.$id);

    // add another?
    if ($add_flag)
    {
        // remove id to prevent edit mode instead of new
        $id = '';
        $smarty->assign('add_flag', $add_flag);
    }
    else
    {
        // show the saved movie
        redirect('show.php?id='.$id);
    }
}


// load existing data
if ($id)
{
	// select all fields according to list, plus id
	foreach ($imdb_set_fields as $name)
    {
		if ($SELECT) $SELECT .= ', ';
		$SELECT .= $name;
	}

    $SELECT = 'SELECT '.TBL_DATA.'.id, '.TBL_DATA.'.owner_id, '.TBL_USERS.'.name AS owner, 
                      !ISNULL('.TBL_USERSEEN.'.video_id) AS seen, '.$SELECT.'
                 FROM '.TBL_DATA.'
            LEFT JOIN '.TBL_USERS.' ON '.TBL_DATA.'.owner_id = '.TBL_USERS.'.id
            LEFT JOIN '.TBL_USERSEEN.' 
                   ON '.TBL_DATA.'.id = '.TBL_USERSEEN.'.video_id AND '.TBL_USERSEEN.'.user_id = '.get_current_user_id().'
                WHERE '.TBL_DATA.'.id = '.$id;
	$video = runSQL($SELECT);

	// diskid to global scope:
	$diskid = $video[0]['diskid'];
}
else
{
	$video[0]['language'] = $config['langdefault'];
}


// assign automatic disk id
if ($config['autoid'] && (empty($diskid) || $add_flag) && $mediatype != MEDIA_WISHLIST)
{
    $video[0]['diskid'] = getDiskId();
    
	// Fix for Bugreport [1122052] Automatic DiskID generation problem
	$smarty->assign('autoid', $result[0]['max']);
}

if (empty($video[0]['owner_id']) && !empty($owner_id))
{
    $video[0]['owner_id'] = $owner_id;
}


// prepare templates
tpl_page();
tpl_edit($video[0]);

$smarty->assign('lookup_id', $lookup);
$smarty->assign('http_error', $CLIENTERROR);

// allow XML import
if ($config['xml'] && empty($id)) $smarty->assign('xmlimport', true);

// display templates
tpl_display('edit.tpl');	

