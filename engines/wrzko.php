<?php
/**
 * wrzKo search
 *
 * @package Engines
 * @author  Samuel Engelking    <bloggs@gmx.net>
 *
 * @link    http://www.wrzko.com
 *
 * @version $Id: wrzko.php,v 1.0 2013/07/07 22:08:25 samE Exp $
 */

require_once './core/xml.core.php';
require_once './core/output.php';

$GLOBALS['wrzkoServer']    = 'http://wrzko.com';

/**
 * Get meta information about the engine
 *
 * @todo    Include image search capabilities etc in meta information
 */
function wrzkoMeta()
{
    return array('name' => 'wrzko.com', 'stable' => 1, 'capabilities' => array('download'));
}

/**
 * Get Url to search wrzko for an item
 *
 * @param   string    The search string
 * @return  string    The search URL (GET)
 */
function wrzkoSearchUrl($title)
{
    global $wrzkoServer;
    return $wrzkoServer.'/?s='.urlencode($title);
}

/**
 * Search an image on wrzko
 *
 * Searches for a given title on the wrzko and returns the found links in
 * an array
 *
 * @param   string    The search string
 * @return  array     Associative array with id and title
 */
function wrzkoSearch($title)
{
    global $CLIENTERROR;
    global $wrzkoServer;

    $data   = array();

    $url    = $wrzkoServer.'/feed/?s='.urlencode($title);
    
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
            [title] => wrzKO - Putlocker, Uploaded and Netload Links » Search Results » last passenger
            [link] => http://www.wrzko.eu
            [description] => 0day Applications, Movies, Games and TV Shows.
            [lastBuildDate] => Sun, 07 Jul 2013 06:44:32 +0000
            [language] => en-US
            [item] => Array
                (
                    [0] => SimpleXMLElement Object
                        (
                            [title] => Last.Passenger.2013.1080p.BluRay.x264-SONiDO
                            [link] => http://www.wrzko.eu/last-passenger-2013-1080p-bluray-x264-sonido/
                            [pubDate] => Thu, 09 May 2013 11:26:16 +0000
                            [description] => <![CDATA[
<div class="cover" align="center"><div class="image"><img src="http://images.wrzko.eu/images/86246004296223547937.jpeg" alt="Last Passenger" /><br /> <a href="http://www.imdb.com/title/tt1858481/">IMDB</a> <a href="http://nfomation.net/info/1368097734.lp_1080_son.nfo">NFO</a> <a href="http://images.wrzko.eu/images/67021005357817570747.jpg">1</a> <a href="http://images.wrzko.eu/images/76051700665748209661.jpg">2</a> <a href="http://images.wrzko.eu/images/93330886063697154019.jpg">3</a> <a href="http://images.wrzko.eu/images/19645280334575322865.jpg">4</a> <a href="http://www.multiupload.nl/RPIYILQZIR">Sample</a> </div></div><br /> <div class="description">A small group of everyday passengers on a speeding London commuter train battle their warped driver who has a dark plan for everyone on-board. </div><br /> <p align="center">
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
#        $res['filesize']    = (string) $row->enclosure['length'];
#        $res['subtitle']    = sizetostring($res['filesize'], 1);
#        $res['plot']        = (string) $row->description;
#        if (preg_match('#(Seeds: .+?)<#', $res['plot'], $m)) $res['sl'] = $m[1];
#       dump($res);
        $data[]          = $res;
    }
    
#   dump($data);
    
    return $data;
}

?>