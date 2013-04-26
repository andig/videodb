<?php
/**
 * Mininova torrent search
 *
 * @package Engines
 * @author  Andreas Götz    <cpuidle@gmx.de>
 *
 * @link    http://www.mininova.org
 * @link    http://www.mininova.org/rss/greys+anatomy
 *
 * @version $Id: mininova.php,v 1.3 2009/04/04 16:17:22 andig2 Exp $
 */

require_once './core/xml.core.php';
require_once './core/output.php';

$GLOBALS['mininovaServer']    = 'http://www.mininova.org';

/**
 * Get meta information about the engine
 *
 * @todo    Include image search capabilities etc in meta information
 */
function mininovaMeta()
{
    return array('name' => 'mininova.org', 'stable' => 1, 'capabilities' => array('download'));
}

/**
 * Get Url to search mininova for an item
 *
 * @param   string    The search string
 * @return  string    The search URL (GET)
 */
function mininovaSearchUrl($title)
{
    global $mininovaServer;
    return $mininovaServer.'/search/?search='.urlencode($title);
}

/**
 * Search an image on mininova
 *
 * Searches for a given title on the mininova and returns the found links in
 * an array
 *
 * @param   string    The search string
 * @return  array     Associative array with id and title
 */
function mininovaSearch($title)
{
    global $CLIENTERROR;
    global $mininovaServer;

    $data   = array();

    $url    = 'http://www.mininova.org/rss/'.urlencode($title);
    
    $resp   = httpClient($url, 1);
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";
    
    // add encoding
    $data['encoding'] = get_response_encoding($resp);

    $xml    = load_xml($resp['data']);
#    dump($xml->channel);

/*
SimpleXMLElement Object
(
    [title] => Mininova
    [link] => http://www.mininova.org/
    [description] => Mininova RSS feed of search results for "scrubs"
    [language] => en-us
    [item] => Array
        (
            [0] => SimpleXMLElement Object
                (
                    [title] => Scrubs S08E11 VOSTFR HDTV XViD-OQS avi[www maroctorrent net]
                    [guid] => http://www.mininova.org/tor/2402512
                    [pubDate] => Sat, 21 Mar 2009 07:51:59 +0100
                    [category] => TV Shows
                    [link] => http://www.mininova.org/tor/2402512
                    [enclosure] => SimpleXMLElement Object
                        (
                            [@attributes] => Array
                                (
                                    [url] => http://www.mininova.org/get/2402512
                                    [length] => 186482688
                                    [type] => application/x-bittorrent
                                )

                        )

                    [description] => 
      Category: <a href="http://www.mininova.org/cat/8">TV Shows</a><br />
      Subcategory: <a href="http://www.mininova.org/sub/138">Scrubs</a><br />
      Size: 177.84&nbsp;megabyte<br />
      Ratio: 0 seeds, 0 leechers<br />
      Language: <img src="http://s.mininova.org/images/flags/fr.gif" /> French<br />

      Uploaded by: <a href="http://www.mininova.org/user/ibrahim41">ibrahim41</a>
                )
*/
    foreach ($xml->channel->item as $row)
    {
        $res    = array();
        $res['title']       = (string) $row->title;
#        $res['imgsmall']   = $img;
#        $res['coverurl']   = $img;
        $res['url']         = (string) $row->link;
        $res['torrent']     = (string) $row->enclosure['url'];
        $res['filesize']    = (string) $row->enclosure['length'];
        $res['subtitle']    = sizetostring($res['filesize'], 1);
        $res['plot']        = (string) $row->description;
        if (preg_match('#(Seeds: .+?)<#', $res['plot'], $m)) $res['s/l'] = $m[1];
#       dump($res);
        $data[]          = $res;
    }
    
#   dump($data);
    
    return $data;
}

?>