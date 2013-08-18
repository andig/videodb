<?php
/**
 * oneclickmoviez search
 *
 * @package Engines
 * @author  Samuel Engelking    <bloggs@gmx.net>
 *
 * @link    http://www.oneclickmoviez.com
 *
 * @version $Id: oneclick.php,v 1.0 2013/07/07 22:08:25 samE Exp $
 */

require_once './core/xml.core.php';
require_once './core/output.php';

$GLOBALS['oneclickServer']    = 'http://oneclickmoviez.com';

/**
 * Get meta information about the engine
 *
 * @todo    Include image search capabilities etc in meta information
 */
function oneclickMeta()
{
    return array('name' => 'oneclickmoviez.com', 'stable' => 1, 'capabilities' => array('download'));
}

/**
 * Get Url to search oneclickmoviez for an item
 *
 * @param   string    The search string
 * @return  string    The search URL (GET)
 */
function oneclickSearchUrl($title)
{
    global $oneclickServer;
    return $oneclickServer.'/?s='.urlencode($title);
}

/**
 * Search an image on oneclickmoviez
 *
 * Searches for a given title on the oneclickmoviez and returns the found links in
 * an array
 *
 * @param   string    The search string
 * @return  array     Associative array with id and title
 */
function oneclickSearch($title)
{
    global $CLIENTERROR;
    global $oneclickServer;

    $data   = array();

    $url    = $oneclickServer.'/feed/rss/?s='.urlencode($title);
    
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
            [version] => 0.92
        )

    [channel] => SimpleXMLElement Object
        (
            [title] => OneClickMoviez ! » Search Results » last passenger
            [link] => http://oneclickmoviez.com
            [description] => The Best Source To Get Your Favorite Movies 100% FREE
            [lastBuildDate] => Tue, 02 Jul 2013 06:41:15 +0000
            [language] => en
            [item] => Array
                (
                    [0] => SimpleXMLElement Object
                        (
                            [title] => Last Passenger 2013 REPACK DVDRip XviD AC3-PTpOWeR
                            [link] => http://oneclickmoviez.com/last-passenger-2013-repack-dvdrip-xvid-ac3-ptpower/
                            [description] => <![CDATA[
nfo IMDB SCREENSHOTS Last.Passenger.2013.REPACK.DVDRip.XviD.AC3-PTpOWeR BAYFILES RYUSHARE TURBOBIT RAPIDGATOR LETITBIT MULTIUPLOAD GO4UP UPLOADED EXTABIT
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
        $res['file']     = (string) $row->enclosure['url'];
//        $res['filesize']    = (string) $row->enclosure['length'];
//        $res['subtitle']    = sizetostring($res['filesize'], 1);
        $res['plot']        = (string) $row->description;
        if (preg_match('#(Seeds: .+?)<#', $res['plot'], $m)) $res['sl'] = $m[1];
#       dump($res);
        $data[]          = $res;
    }
    
#   dump($data);
    
    return $data;
}

?>