<?php
/**
 * File caching functions
 *
 * @package Core
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @version $Id: cache.php,v 1.11 2013/04/26 15:09:35 andig2 Exp $
 */

// define cache folder
if (!defined('CACHE')) define('CACHE', 'cache');

/**
 * Get the hashed filename
 *
 * @param  string   url of the item
 * @param  string   $cache_folder  name ob the sub-cache to adress
 * @param  string   ext file extension of the cache file
 */
function cache_get_filename($url, $cache_folder, $ext = '')
{
    $hash       = md5($url) . (($ext) ? '.'.$ext : '');
    $cache_file = cache_get_folder($cache_folder, $hash) . $hash;

    return $cache_file;
}

/**
 * Get name of the cache folder
 *
 * @TODO  decouple from global config options
 *
 * @param   string  $cache_folder  name ob the sub-cache to adress
 * @param   string  $cache_filename   name of the item to be cached for use with hierarchical caches
 * @return  string  cache folder path including trailing /
 */
function cache_get_folder($cache_folder, $cache_filename = '')
{
    global $config;

    $cache_folder = CACHE.'/' .
                    (($cache_folder) ? $cache_folder.'/' : '');

    if ($cache_filename)
        $cache_folder .= substr($cache_filename, 0, @(int)$config['hierarchical']).'/';
    
    return $cache_folder;
}

/**
 * Cleanup a single cache folder
 *
 * @param string $cache_folder  path to cache folder
 * @param int    $cache_max_age maximum age of cached items in seconds
 * @param bool   $force_prune   force cache pruning even if not due according to schedule
 */
function cache_prune_folder($cache_folder, $cache_max_age, $force_prune = false, $simulate = false, $pattern = '*')
{
    if (!preg_match('#/$#', $cache_folder)) $cache_folder .= '/';
    $stamp       = $cache_folder.'cache_last_purge';
    $cache_mtime = @filemtime($stamp); // get time the cache was last purged (once a day)

    // if cache was last purged a day or more ago
    if ($force_prune || ((time() - $cache_mtime) > ($cache_max_age / 24))) # 86400)
    {
        foreach (glob($cache_folder.$pattern, GLOB_NOSORT) as $file)
        {
            // avoid hidden files and directories
            if (is_file($file) &! preg_match("/^\./", $file) && time() - filemtime($file) > $cache_max_age)
            {
                if ($simulate)
                    $files[] = $file;   // add to list of potentially purged files
                else
                    @unlink($file);     // purge cache
            }
        }

        if ($simulate) return $files;
        
        @touch($stamp);  // mark purge as having occurred
        return true;
    }
    
    return false;
}

/**
 * Cleanup a cache folder hierarchy
 *
 * @TODO  decouple from global config options
 *
 * @param string $cache_folder  path to cache folder
 * @param int    $cache_max_age maximum age of cached items in seconds
 * @param bool   $force_prune   force cache pruning even if not due according to schedule
 */
function cache_prune_folders($cache_folder, $cache_max_age, $force_prune = false, $simulate = false, $pattern = '*', $levels = 0)
{
    global $config;

    // root folder
    cache_prune_folder($cache_folder, $cache_max_age, $force_prune, $simulate, $pattern, $levels);

    // descent hierarchy
    if ($levels > 0)
        for ($i=0; $i<16; $i++)
            $error .= cache_prune_folders($cache_folder.dechex($i).'/', $cache_max_age, $force_prune, $simulate, $pattern, $levels-1);
}

/**
 * Create cache folders
 *
 * Check individual cache folder for existance, check if folder is writable and create folder if it doesn't exist
 */
function cache_create_folders($dir, $levels = 0)
{
    if (!is_dir($dir))
    {
        if (!@mkdir($dir, 0700)) $error = 'Directory <code>'.$dir.'</code> does not exist.<br/>';
    }
    elseif (!is_writable($dir))
    {
        $error = 'Directory <code>'.$dir.'</code> is not writable.<br/>';
    }

    // check hierarchical folders
    if (empty($error) && ($levels > 0))
    {
        for ($i=0; $i<16; $i++)
            $error .= cache_create_folders($dir.'/'.dechex($i), $levels-1);
    }

    return $error;
}

/**
 * Verify existance of cached file for given url/ extension
 *
 * @author Andreas Goetz <cpuidle@gmx.de>
 * @param  string   url of the item
 * @param  string   ext file extension of the cache file
 * @param  string   file result: URL to the cached image if exists
 * @return bool     result of check
 */
function cache_file_exists($url, &$cache_file, $cache_folder, $ext = '')
{
    $cache_file = cache_get_filename($url, $cache_folder, $ext);
//  Small performance fix
    $result     = file_exists($cache_file) && filesize($cache_file);
#   $result     = filesize($cache_file) > 0;
    
    return($result);
}

function cache_get($url, $cache_folder, $cache_max_age, $serialize = false)
{
    $data = false;
    
    if ($cache_max_age > 0)
    {
        if (cache_file_exists($url, $cache_file, $cache_folder))
        {
            if (time() - filemtime($cache_file) < $cache_max_age)
            {
                $data = file_get_contents($cache_file);
                if (($data !== false) && $serialize) $data = unserialize($data);
            }
            // TODO Check if outdated cache files should really be auto-deleted
            else @unlink($cache_file);
        }    
    }
    
    return $data;
}

function cache_put($url, $data, $cache_folder, $cache_max_age, $serialize = false)
{
    // only put file to cache if caching is enabled
    if ($cache_max_age > 0)
    {
        // get the cache file name
        $cache_file = cache_get_filename($url, $cache_folder);

        // commit to disk
        if ($serialize) $data = serialize($data);
        file_put_contents($cache_file, $data);
    }
}

?>