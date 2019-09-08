<?php
/**
 * Contrib tools index
 *
 * @package videoDB
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @version $Id: contrib.php,v 1.3 2005/05/26 12:24:15 andig2 Exp $
 */

require_once './core/functions.php';

// check for localnet
localnet_or_die();

// multiuser permission check
permission_or_die(PERM_WRITE);

/**
 * Sort multi-dimensional arrays 
 * (thanks to phpdotnet)
 */
function multidimsort($array_in, $column)
{
    $multiarray = array_column($array_in, $column);
    $array_out  = array();

    asort($multiarray);

    // traverse new array of index values and add the corresponding element of the input array to the correct position in the output array
    foreach ($multiarray as $key => $val)
    {
        $array_out[] = $array_in[$key];
    }

    // return the output array which is all nicely sorted by the index you wanted!
    return $array_out;
}


// dynamic contrib loader
$files   = array();
$dirpath = './contrib';

if ($dh = opendir($dirpath))
{
    while (($filename = readdir($dh)) !== false)
    {     
        $access = '';
        if (!preg_match('/.*\.php$/', $filename)) continue;
        if ($filename == 'index.php') continue;

        $info    = array('contrib/'.$filename);
        $file    = $dirpath.'/'.$filename;
        $content = file_get_contents($file);

        // title
        if (preg_match('/<TITLE.*?>(.+?)</msi', $content, $title))
        {
            $info[1] = $title[1];
        }
        else 
        {
            $info[1] = preg_replace('/\.php$/', '', $filename);
        }    
        
        // access
        if (preg_match('/^.*?\*[\t ]+@meta[\t ]+ACCESS:(.*?)\n/i', $content, $access))
        {
            $info[2] = trim($access[1]);
        }

        // add to list of access rights are valid
        if (empty($info[2]) || check_permission($info[2])) 
        {
            $files[] = $info;
        }
    }	
    closedir($dh);
}

// sort by title
$files = multidimsort($files, 1);

// prepare templates
tpl_page();
$smarty->assign('files', $files);

// display templates
tpl_display('contrib.tpl');

?>
