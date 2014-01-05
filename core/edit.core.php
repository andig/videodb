<?php
/**
 * Edit functions. Split into separate file for reuse.
 *
 * @package videoDB
 * @author  Andreas Götz <cpuidle@gmx.de>
 * @author  Andreas Gohr <a.gohr@web.de>
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @version $Id: edit.core.php,v 1.9 2009/12/05 13:56:04 andig2 Exp $
 */

require_once './core/security.php';

// list of fields to be read/written from/to html form
$imdb_set_fields    = array('md5','title','subtitle','language','diskid','mediatype','comment','disklabel',
                            'imdbID','year','imgurl','director','actors','runtime','country','plot','filename',
                            'filesize','filedate','audio_codec','video_codec','video_width','video_height','istv',
                            'rating', 'custom1','custom2','custom3','custom4','has3d');

// list of fields to be overwritten by refetchAllInfos-Script
$imdb_overwrite_fields = array('comment','disklabel','imdbID','year','director','actors','runtime','country','plot',
                               'rating','custom1','custom2','custom3','custom4');

// special fields for SQL statements
$db_null_fields = array('runtime', 'filesize', 'filedate', 'video_width', 'video_height');
$db_zero_fields = array('istv', 'year');

/**
 * Obtain new disk id
 *
 * @return  string  Generated disk id
 */
function getDiskId()
{
    global $config;

    /*
     * change this if you have some fancy naming style
     */
    
    // how many digits have to be used for DiskId?
    $digits = ($config['diskid_digits']) ? $config['diskid_digits'] : 4;

    /*
     *  Old way automatic DiskID was generated: result was "highest ID + 1"
     *
    $NEXTUSERID = "SELECT LPAD(TRIM(LEADING '0' FROM MAX(LPAD(TRIM(LEADING '0' FROM diskid), 10, '0'))) + 1, ".
                  $digits.", '0') AS max FROM ".TBL_DATA.' WHERE diskid NOT REGEXP "[^0-9]"';
    $result = runSQL($NEXTUSERID);
    return $result[0]['max'];
    */

    // get all DiskIds ordered from DB
    $SQL = "SELECT LPAD(TRIM(LEADING '0' FROM diskid), 10, '0') AS id 
              FROM ".TBL_DATA.' 
             WHERE diskid NOT REGEXP "[^0-9]" AND
                   owner_id = '.get_current_user_id().'
             ORDER BY id';
             // sql looks strange but fixes problems with users who change their
             // diskid_digits while they have already movies in their DB.
             // added owner_id as fix for https://github.com/andig/videodb/issues/6
    $results = runSQL($SQL);

    // find first 'free' diskId
    $lastid = 0;
    foreach ($results as $result)
    {
        $thisid = preg_replace('/^0+/','',$result['id']);
        if ($lastid + 1 < $thisid) break;
        $lastid = $thisid;
    }

    // return the found id
    return str_pad($lastid + 1, $digits, '0', STR_PAD_LEFT);
}

/**
 * Strip leading articles
 *
 * @param   string  $field  Input field to be stripped
 * @return  string  Input with articles rearranged to end of string
 */
function removeArticles($field)
{
    $articles = array('the ', 'la ', 'a ', 'der ', 'die ', 'das ', 'des ', 'dem ', 'den ',
                      'ein ', 'eine ', 'eines ', 'le ', 'el ', "l'", 'il ', 'les ', 'i ',
                      'o ', 'un ', 'los ', 'de ', 'an ', 'una ', 'las ', 'gli ', 'het ',
                      'lo ', 'os ', 'az ', 'ha-', 'een ', 'det ', 'oi ', 'ang ', 'ta ',
                      'al-', 'uno ', "un'", 'ett ', 'mga ', 'Ï ', 'Ç ', 'els ', 'Ôï ', 'Ïé ');

    foreach ($articles as $article)
    {
        if (preg_match("/^$article+/i", $field))
        {
            $field = trim(preg_replace("/(^$article)(.+)/i", "$2, $1", $field));
            break;
        }
    }
    
    return $field;
}

/**
 * Prepare item for display based on input data
 */
function echoInput($data)
{
    global $imdb_set_fields;
    
    // error no owner specified
    $video = array();

    // select all fields according to list, plus id
    foreach ($imdb_set_fields as $name)
    {
        $video[0][$name] = $data[$name];
    }
    
    return $video;
}

/**
 * Prepare update SQL
 *
 * @param   array $data key/value pairs of data
 * @returns string      result SQL, suitable for INSERT/UPDATE
 */
function prepareSQL($data, $setonly = false)
{
    global $config, $imdb_set_fields, $db_null_fields, $db_zero_fields;
    
    // get global variables into local scope
    extract($data);

    // Fix for Bugreport [1122052] Automatic DiskID generation problem
    if ($config['autoid'] && !empty($diskid) && ($diskid == $autoid))
    {
        // in case DiskID is already used in meanwhile
        // -> update to new DiskId
        $diskid = getDiskId();
    }

    // set default mediatype
    if (empty($mediatype)) $mediatype = $config['mediadefault'];

    // set owner
    if (is_numeric($owner_id)) $SQL = 'owner_id = '.$owner_id;

    // rating up to 10
    $rating = min($rating, 10);
        
    // update all fields according to list
    foreach ($imdb_set_fields as $name)
    {
        if ($setonly && !isset($$name)) continue;
        
        // sanitize input
        $$name = removeEvilTags($$name);
        $$name = html_entity_decode($$name);
        
        // make sure no formatting contained in basic data
        if (in_array($name, array('title', 'subtitle')))
        {
            $$name = trim(strip_tags($$name));

            // string leading articles?
            if ($config['removearticles'])
            {
                $$name = removeArticles($$name);
            }
        }

        $SET = "$name = '".addslashes($$name)."'";

        // special null/zero handling
        if (empty($$name))
        {
            if (in_array($name, $db_null_fields))
                $SET = "$name = NULL";
            elseif (in_array($name, $db_zero_fields))
                $SET = "$name = 0";
        }

        if ($SQL) $SQL .= ', ';
        $SQL .= $SET;
    }

    return $SQL;
}

/**
 * @param   string  $SQL    set fields
 * @param   int     $id     id of item to update, insert if empty
 * @param   boolean $touch  true specifies to update created data of item 
 */
function updateDB($SQL, $id, $touch=false)
{
    if ($id)
    {
        // update existing record
        if ($touch)
        {
            // if the disk was on the wishlist and is now available, make sure it appears under 'new'
            $SQL .= ', created = NOW()';
        }

        $SQL = 'UPDATE '.TBL_DATA.' SET '.$SQL.' WHERE id = '.$id;
        runSQL($SQL);
    }
    else
    {
        // insert new record
        $SQL = 'INSERT INTO '.TBL_DATA.' SET '.$SQL.', created = NOW()';
        $id = runSQL($SQL);
    }
    
    return $id;
}

/**
 * Process HTTP file upload
 */
function processUpload($id, $file, $mime, $name)
{
    if (!(isset($_FILES['coverupload']) && is_uploaded_file($_FILES['coverupload']['tmp_name'])))
        return;

    // determine file extension
    if (preg_match('=image/jpe?g=i', $mime))
    {
        $ext = 'jpg';
    }
    elseif (preg_match('=image/(gif|png)=i', $mime, $m))
    {
        $ext = $m[1];
    }
    elseif (preg_match('=application/octet-stream=i', $mime))
    {
        if (preg_match("/\.(jpe?g|gif|png)$/i", $name, $m))
        {
            $ext = $m[1];
        }
    }

    // move to cache and update db
    if (!empty($ext))
    {
        $coverfile = 'cache/img/'.$id.'.'.$ext;
        if (move_uploaded_file($file, $coverfile))
        {
            // fix permission issues
            chmod($coverfile, 0644);
            
            $sql = "UPDATE ".TBL_DATA." SET imgurl='$coverfile' WHERE id=$id";
            runSQL($sql);
        }
    }
}

?>
