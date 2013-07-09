<?php
/**
 * filestube search
 *
 * @package Engines
 * @author  Samuel Engelking    <bloggs@gmx.net>
 *
 * @link    http://www.filestube.com
 *
 * @version $Id: filestube.php,v 1.0 2013/07/07 22:08:25 samE Exp $
 */

require_once './core/xml.core.php';
require_once './core/output.php';

$GLOBALS['filestubeServer']    = 'http://filestube.com';

/**
 * Get meta information about the engine
 *
 * @todo    Include image search capabilities etc in meta information
 */
function filestubeMeta()
{
    return array('name' => 'filestube.com', 'stable' => 1, 'capabilities' => array('download'));
}

/**
 * Get Url to search filestube for an item
 *
 * @param   string    The search string
 * @return  string    The search URL (GET)
 */
function filestubeSearchUrl($title)
{
    global $filestubeServer;
    return $filestubeServer.'/query.html?q='.urlencode($title);
}

/**
 * Search an image on filestube
 *
 * Searches for a given title on the filestube and returns the found links in
 * an array
 *
 * @param   string    The search string
 * @return  array     Associative array with id and title
 */
function filestubeSearch($title)
{
    global $CLIENTERROR;
    global $filestubeServer;

    $data   = array();

    $url    = $filestubeServer.'/rss.rss?q='.urlencode($title);
    
    $resp   = httpClient($url, 1);
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";
    
    // add encoding
    $data['encoding'] = get_response_encoding($resp);

    $xml    = @simplexml_load_string($resp['data']);

/*
SimpleXMLElement Object
(
    [@attributes] => Array
        (
            [version] => 2.0
        )

    [channel] => SimpleXMLElement Object
        (
            [title] => <![CDATA[ Last Passenger - FilesTube RSS ]]>
            [link] => http://www.filestube.com/query.html?q=Last+Passenger
            [description] => <![CDATA[ Search results for: Last Passenger - FilesTube RSS ]]>
            [language] => <![CDATA[ en ]]>
           	[pubDate] => <![CDATA[ Thu, 20 Jun 2013 17:35:38 +0000 ]]>
            [item] => Array
                (
                    [0] => SimpleXMLElement Object
                        (
                            [title] => <![CDATA[ Last Passenger 2013 1080p BrRip x264-YIFY ]]>
                            [link] => <![CDATA[ http://www.filestube.com/bVfritTcItceVpDvyDgXUM ]]>
                            [pubDate] => <![CDATA[ Thu, 20 Jun 2013 17:35:38 +0000 ]]>
                            [description] => <![CDATA[
Last Passenger 2013 1080p BrRip x264-YIFY, hosted on rapidgator.net, 1 GB, mp4
]]>
                        )
                )
*/
    if (is_object($xml)) foreach ($xml->channel->item as $row)
    {
        $res    = array();
        $res['title']       = (string) $row->title;
#        $res['imgsmall']   = $img;
#        $res['coverurl']   = $img;
        $res['url']         = (string) $row->link;
#        $res['file']     = (string) $row->enclosure['url'];
#        $res['filesize']    = (string) $row->description['rapidgator.net, '];
		$descr 				= (string) $row->description;
		$sizes 				= explode(',', $descr);
        $res['subtitle']    = $sizes[2];
#        $res['plot']        = (string) $row->description;
#        if (preg_match('#(Seeds: .+?)<#', $res['plot'], $m)) $res['sl'] = $m[1];
#       dump($res);
        $data[]          = $res;
    }
    
#   dump($data);
    
    return $data;
}

?>