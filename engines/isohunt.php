<?php
/**
 * isohunt torrent search
 *
 * @package Engines
 * @author  Andreas Götz    <cpuidle@gmx.de>
 *
 * @link    http://www.isohunt.com
 * @link    http://www.isohunt.com/js/rss/greys+anatomy
 *
 * @version $Id: isohunt.php,v 1.4 2010/02/18 15:17:25 andig2 Exp $
 */

require_once './core/xml.core.php';
require_once './core/output.php';

$GLOBALS['isohuntServer']    = 'http://isohunt.com';

/**
 * Get meta information about the engine
 *
 * @todo    Include image search capabilities etc in meta information
 */
function isohuntMeta()
{
    return array('name' => 'isohunt.com', 'stable' => 1, 'capabilities' => array('download'));
}

/**
 * Get Url to search isohunt for an item
 *
 * @param   string    The search string
 * @return  string    The search URL (GET)
 */
function isohuntSearchUrl($title)
{
    global $isohuntServer;
    return $isohuntServer.'/torrents.php?ihq='.urlencode($title);
}

/**
 * Search an image on isohunt
 *
 * Searches for a given title on the isohunt and returns the found links in
 * an array
 *
 * @param   string    The search string
 * @return  array     Associative array with id and title
 */
function isohuntSearch($title)
{
    global $CLIENTERROR;
    global $isohuntServer;

    $data   = array();

    $url    = $isohuntServer.'/js/rss/'.urlencode($title);
    
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
            [title] => isoHunt > All > scrubs
            [link] => http://isohunt.com
            [description] => BitTorrent search feeds > All > scrubs
            [language] => en-us
            [category] => All
            [ttl] => 60
            [image] => SimpleXMLElement Object
                (
                    [title] => isoHunt > All > scrubs
                    [url] => http://isohunt.com/img/buttons/isohunt-02.gif
                    [link] => http://isohunt.com/
                    [width] => 157
                    [height] => 45
                )

            [lastBuildDate] => Sun, 22 Mar 2009 22:47:21 GMT
            [pubDate] => Sun, 22 Mar 2009 22:47:21 GMT
            [item] => Array
                (
                    [0] => SimpleXMLElement Object
                        (
                            [title] => Scrubs. S08E12. HDTV. XviD  [3/9]
                            [link] => http://isohunt.com/torrent_details/72045453/scrubs?tab=summary
                            [guid] => http://isohunt.com/torrent_details/72045453/scrubs?tab=summary
                            [enclosure] => SimpleXMLElement Object
                                (
                                    [@attributes] => Array
                                        (
                                            [url] => http://isohunt.com/download/72045453/scrubs.torrent
                                            [length] => 354292859
                                            [type] => application/x-bittorrent
                                        )

                                )

                            [description] => <h3>Bit Torrent details:</h3>Category: TV<br>Original site: http://thepiratebay.org/<br>Size: 337.88 MB, in 2 files<br><br>Seeds: 3 &nbsp; | &nbsp; Leechers: 9 &nbsp; | &nbsp; Downloads: 16<p>Description:<br>Torrent downloaded from http://thepiratebay.org
                            [pubDate] => Fri, 20 Mar 2009 22:55:22 GMT
                        )
*/
    if (is_object($xml)) foreach ($xml->channel->item as $row)
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
        if (preg_match('#(Seeds: .+?)<#', $res['plot'], $m)) $res['sl'] = $m[1];
#       dump($res);
        $data[]          = $res;
    }
    
#   dump($data);
    
    return $data;
}

?>