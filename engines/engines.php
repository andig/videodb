<?php
/**
 * Multi-engine glue logic
 *
 * @package Engines
 *
 * @todo    Investigate and remove global $cache variable
 *
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @version $Id: engines.php,v 1.45 2010/10/15 08:13:01 andig2 Exp $
 */

require_once './core/httpclient.php';
require_once './core/encoding.php';

/**
 * Determine the default engine
 *
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @return  string    engine name
 */
function engineGetDefault()
{
    global $config;
    
    if (!empty($config['enginedefault']))
    {
        $engine = $config['enginedefault'];
    }
    elseif (count($engine_list = array_keys($engines)))
    {
        // first valid engine from list
        $engine = $engine_list[0];
    }
    else $engine = 'imdb'; // last resort
    
    return $engine;
}

/**
 * Determine engine from id
 *
 * @todo    Enhance DB schema to store engine type explicitly
 *
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string    item id
 * @return  string    engine name
 */
function engineGetEngine($id)
{
    global $config;
    
	// recognize engine from id
	if ($id)
	{
        // engine prefixed (imdb:081547)
        // currently working for imdb, amazon, amazoncom and tvcom
        if (preg_match('/^(\w+):/', $id, $match)) $engine = $match[1];
#       elseif (preg_match('/^\d+-\d+$/', $id)) $engine = 'tvcom';
        elseif (preg_match('/^DP[0-9]/', $id)) $engine = 'dvdpalace'; // German Movie Database
        elseif (preg_match('/^[0-9A-Z]{10,}$/', $id)) $engine = 'amazonaws'; // Amazon 
        elseif (preg_match('/^GR[0-9]/', $id)) $engine = 'gamerankings';
        elseif (preg_match('/^DI[0-9]/', $id)) $engine = 'dvdinside';
#		elseif (preg_match('/^[0-9a-z]{6,}$/', $id)) $engine = 'freedb';
	}
	if (empty($engine)) $engine = 'imdb';
	return $engine;
}

/**
 * Include engine file and retrieve item data
 *
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string    item id
 * @param   string    engine name
 * @return  array     item data
 */
function engineGetData($id, $engine = 'imdb')
{
	global $lang, $cache;
	
	require_once($engine.'.php');
	$func = $engine.'Data';

    $result = array();
    if (function_exists($func))
    {
        $cache  = true;
        $result = $func($id);
    }

    // make sure all engines properly return the encoding type
    if (empty($result['encoding'])) errorpage('Engine Error', 'Engine does not properly return encoding');

	// set default encoding iso-8859-1
	$source_encoding = ($result['encoding']) ? $result['encoding'] : $lang['encoding'];
	$target_encoding = 'utf-8';
    unset($result['encoding']);
	
	// convert to unicode
	if ($source_encoding != $target_encoding)
	{
        $result = iconv_array($source_encoding, $target_encoding, $result);
	}	
	engine_clean_input($result);

	return $result;
}

/**
 * Include engine file and execute item search
 *
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string    search string
 * @param   string    engine name
 * @return  array     list of item data
 */
function engineSearch($find, $engine = 'imdb', $para1 = null, $para2 = null)
{
    global $lang, $cache;

    require_once($engine.'.php');
    $func = $engine.'Search';

    $result = array();
    if (function_exists($func))
    {
        $cache  = true;
        // check if additional parameters given to avoid overriding default values
        $result = (isset($para1)) ? $func($find, $para1, $para2) : $func($find);
    }
    
    // make sure all engines properly return the encoding type
#    if (empty($result['encoding'])) errorpage('Engine Error', 'Engine does not properly return encoding');

    // set default encoding iso-8859-1
    $source_encoding = ($result['encoding']) ? $result['encoding'] : $lang['encoding'];
    $target_encoding = 'utf-8';
    unset($result['encoding']);
    
    // convert to unicode
    if ($source_encoding != $target_encoding)
    {
        #dump("Converting from $source_encoding to $target_encoding");
        $result = iconv_array($source_encoding, $target_encoding, $result);
    }   

    // obtain unique entries
    $result = engine_deduplicate_result($result);

	engine_clean_input($result);

    return $result;
}

/**
 * Get item details URL in external site
 *
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string    item id
 * @param   string    engine name
 * @return  string    item details url
 */
function engineGetContentUrl($id, $engine = 'imdb')
{
    if (empty($id)) return '';
    
    require_once($engine.'.php');
    $func = $engine.'ContentUrl';
    
    $result = '';
    if (function_exists($func))
    {
        $result = $func($id);
    }

    return $result;
}

/**
 * Get complete search URL for external site
 *
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string    search string
 * @param   string    engine name
 * @return  string    item search url
 */
function engineGetSearchUrl($find, $engine = 'imdb')
{
    require_once($engine.'.php');
    $func = $engine.'SearchUrl';
    
    $result = '';
    if (function_exists($func))
    {
        $result = $func($find);
    }

    return $result;
}

/**
 * This function is used internally by setup and engines to add meta-engine of the engine's capability type
 * e.g. if the youtube engine provides 'trailer' capability, this will add $config[engine][trailer] = (youtube)
 */
function engine_setup_meta($engine, $meta)
{
    global $config;

    if (is_array($meta['capabilities'])) {
        foreach ($meta['capabilities'] as $caps) {
            $config['engine'][$caps][] = $engine;
        }
    }    
}

/**
 * Retrieve meta information about all available engines
 *
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @return  array     engines array containing engine names
 */
function engineMeta()
{
    $engines = array();
    
    if ($dh = @opendir('./engines'))
    {
        while (($file = readdir($dh)) !== false)
        {
            if ((preg_match("/(.*)\.php$/", $file, $matches)) && ($matches[1] != 'engines'))
            {
                // engine file
                $engine = $matches[1];            

                // get meta data
                require_once('./engines/'.$engine.'.php');
                $func = $engine.'Meta';

                if (function_exists($func))
                {
                    $meta             = $func();
                    $engines[$engine] = $meta;

                    // required php version present?
                    if ($engines[$engine]['php'] && (version_compare(phpversion(), $engines[$engine]['php']) < 0))
                    {
                        unset($engines[$engine]);
                    }
                }    
            }
        }
        closedir($dh);
    }

    return $engines;
}

/**
 * Determine actor engine from actor id, defaults to imdb
 *
 * @author  Michael Kollmann <acidity@online.de>
 * @param   string    actor id
 * @return  string    engine name
 */
function engineGetActorEngine($id)
{
    // recognize engine from id
    if ($id)
    {
        // actor engine prefixed, too? (imdb:nm0347149)
        if (preg_match('/^(\w+):/', $id, $match)) $engine = $match[1];
        elseif (preg_match('/^tv\d+$/', $id)) $engine = 'tvcom';
    }
    if (empty($engine)) $engine = 'imdb';
    
    return $engine;
}

/**
 * Get actors details URL in external site
 *
 * @author  Michael Kollmann <acidity@online.de>
 * @param   string    actor name
 * @param   string    actor id
 * @param   string    engine name
 * @return  string    actor details url
 */
function engineGetActorUrl($name, $id, $engine = 'imdb')
{
    require_once($engine.'.php');
    $func = $engine.'ActorUrl';
    
    $result = '';
    if (function_exists($func))
    {
        $id = preg_replace('|^'.$engine.':|', '', $id);
        $result = $func($name, $id);
    }

    return $result;
}

/**
 * Include engine file and execute item search
 *
 * @author  Michael Kollmann <acidity@online.de>
 * @param   string    actor name
 * @param   string    actor id
 * @param   string    engine name
 * @return  array     array with Actor-URL and Thumbnail
 */
function engineActor($name, $id, $engine = 'imdb')
{
    global $cache;

    require_once($engine.'.php');
    $func = $engine.'Actor';

    $result = array();
    if (function_exists($func))
    {
        $id = preg_replace('|^'.$engine.':|', '', $id);

        $cache  = true;
        $result = $func($name, $id);
    }

    return $result;
}

/**
 * Callback function for validating if an engine has a certain capability
 */
function engine_get_capability($engine, $searchtype)
{
    global $config;
    
    // get the meta information
    $engine = $config['engines'][$engine];

    if (is_array($engine['capabilities']))
    {
        return in_array($searchtype, $engine['capabilities']);
    }
    else
    {
        return $searchtype == 'movie';
    }
}

/**
 * Get list of engines which have certain capability
 *
 * 'movie' search capability is assumed as default, either if 
 * $searchtype is empty or engine does not maintain specific capability
 *
 * @return  array   list of capable engines
 */
function engine_get_capable_engines($searchtype)
{
    global $config;

    if (!$searchtype) $searchtype = 'movie';
    
    $engines = array();
    foreach ($config['engines'] as $engine => $meta)
    {
        $enabled = $config['engine'][$engine];
        if ($enabled && engine_get_capability($engine, $searchtype)) $engines[$engine] = $enabled;
    }

    return $engines;
}

/**
 * Clean HTML tags from hierarchical associative array
 *
 * @param   array	$data	string or hierarchical array to convert
 */
function engine_clean_input(&$data)
{
    if (is_array($data)) foreach ($data as $key => $val)
	{
		if (is_array($val)) 
			engine_clean_input($data[$key]);
		else
        {
            $val        = html_to_text($val);
            $data[$key] = html_clean_utf8($val);
        }    
	}
}

/**
 * Filter result set for unique engine ids. 
 * This avoids deduplication of search results inside every single engine.
 */
function engine_deduplicate_result($data)
{
	$keys = array();
    for ($i=0; $i<count($data); $i++)
    {
        $id = $data[$i]['id'];
        // early exit if engine (e.g. google images) doesn't return ids
        if (!$id) return $data;
        // exclude duplicates
        if (in_array($id, $keys)) 
            unset($data[$i]);
        else
            $keys[] = $id;
    }

    return $data;
}

?>
