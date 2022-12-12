<?php
/**
 * XML export functions
 *
 * Lets you browse through your movie collection
 *
 * @package Core
 * @author  Andreas Götz    <cpuidle@gmx.de>
 * @author	Kokanovic Branko    <branko.kokanovic@gmail.com>
 * @version $Id: xml.php,v 1.34 2013/03/10 16:25:35 andig2 Exp $
 */

require_once './core/functions.php';
require_once './core/export.core.php';
require_once './core/xml.core.php';

/**
 * Export XML data
 *
 * @param   string  $where  WHERE clause for SQL statement
 */
function xmlexport($WHERE)
{
    global $config;
    
    // get data
    $result = exportData($WHERE);
    
    // do adultcheck
    if (is_array($result))
    {
        $result = array_filter($result, create_function('$video', 'return adultcheck($video["id"]);'));
    }
    
    $xml = '';
    
    // loop over items
    foreach ($result as $item)
    {
        $xml_item = '';
        
        // loop over attributes
        foreach ($item as $key => $value)
        {
            if (!empty($value))
            {
                if (($key != 'owner_id') && ($key != 'actors'))
                {
                    $tag       = strtolower($key);
                    $xml_item .= createTag($tag, trim(html_entity_decode_all($value)));
                }    
            }
        }

        // this is a hack for exporting thumbnail URLs
        if ($item['imgurl'] && $config['xml_thumbnails'])
        {
            $thumb = getThumbnail($item['imgurl']);
            if (preg_match('/cache/', $thumb))
                $xml_item .= createTag('thumbnail', trim($thumb));
        }
        
        // genres
        if (count($row['genres']))
        {
            $xml_genres = '';
            foreach ($row['genres'] as $genre)
            {
                $xml_genres .= createTag('genre', $genre['name']);
            }
            $xml_item .= createContainer('genres', $xml_genres);
        }
        
        // actors
        $actors = explode ("\n",$item['actors']);
        if (count($actors))
        {
            $xml_actors = '';
            foreach ($actors as $actor)
            {
                $xml_actor_data = '';
                $actor_data = explode("::",$actor);
                $xml_actor_data .= createTag('name', $actor_data[0]);
                $xml_actor_data .= createTag('role', $actor_data[1]);
                $xml_actor_data .= createTag('imdbid', $actor_data[2]);
                $xml_actors .= createContainer('actor', $xml_actor_data);
            }
            $xml_item .= createContainer('actors', $xml_actors);
        }
        $xml .= createContainer('item', $xml_item);
    }

    $xml    = '<?xml version="1.0" encoding="utf-8"?>'.
    		  "\n".createContainer('catalog', $xml);

//    header('Content-type: text/xml');
    $mime   = (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ? 'application/force-download' : 'application/octet-stream';
    header('Content-type: '.$mime);
    header('Content-length: '.strlen($xml));
    header('Content-disposition: attachment; filename=videoDB.xml');

    echo $xml;
}

/**
 * Update RSS File
 *
 * @author  Mike Clark    <mike.clark@cinven.com>
 */
function rssexport($WHERE)
{
    global $config, $rss_timestamp_format, $filter;

    // make sure server doesn't specify something else
    header('Content-type: text/xml; charset=utf-8');

    if ($filter)
    {
        $result = exportData($WHERE);
    }
    else
    {
        // get the latest items from the DB according to config setting
        $SQL    = 'SELECT id, title, plot, created 
                     FROM '.TBL_DATA.' 
                 ORDER BY created DESC LIMIT '.$config['shownew'];
        $result = runSQL($SQL);
    }

    // script root
    $base = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
        
	// setup the RSS Feed
    $rssfeed  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
    $rssfeed .= '<rss version="2.0"  xmlns:atom="http://www.w3.org/2005/Atom">';
	$rssfeed .= '<channel>';
    $rssfeed .= '<atom:link href="'.$base.'/index.php?export=rss" rel="self" type="application/rss+xml" />';
	$rssfeed .= createTag('title', 'VideoDB');
	$rssfeed .= createTag('link', $base.'/index.php?export=rss');
	$rssfeed .= createTag('description', 'New items posted on VideoDB');
	$rssfeed .= createTag('language', 'en-us');
    $rssfeed .= createTag('lastBuildDate', date($rss_timestamp_format));

	// build the <item></item> section of the Feed
	foreach ($result as $item)
	{
        $xml_item  = createTag('title', $item['title']);
        $xml_item .= createTag('link', $base.'/show.php?id='.$item['id']);
        $xml_item .= createTag('description', $item['plot']);
        $xml_item .= createTag('guid', $base.'/show.php?id='.$item['id']);
        $xml_item .= createTag('pubDate', rss_timestamp($item['created']));

        $rssfeed  .= createTag('item', $xml_item, false);
	}
	$rssfeed .= '</channel>';
	$rssfeed .= '</rss>';

    header('Content-type: text/xml');
#   header('Content-length: '.rssfeed($xml));
#   header('Content-disposition: filename=rss.xml');
    echo $rssfeed;
}

?>
