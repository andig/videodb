<?php
/**
 * Amazon API Parser
 *
 * @package Engines
 * @author  Andreas Goetz <cpuidle@gmx.de>
 *
 * @link    https://forums.aws.amazon.com/
 * @link	https://affiliate-program.amazon.com/gp/advertising/api/detail/main.html
 * @link	http://docs.amazonwebservices.com/AWSECommerceService/latest/DG/Welcome.html
 *
 * @version $Id: amazonaws.php,v 1.11 2013/02/16 22:04:30 andig2 Exp $
 */

require_once './core/xml.core.php';
require_once './core/genres.php';

$GLOBALS['amazonAWSServer']	= 'http://www.amazon.com';
$GLOBALS['amazonAWSUrl']	= 'http://ecs.amazonaws.com/onca/xml?Service=AWSECommerceService&Version=2007-12-24';
$GLOBALS['awsPrefix']       = 'aws:';

$GLOBALS['searchMode']		= 'dvd';

$GLOBALS['dbgenres']		= null;		// make sure genres are read once (via mapGenres in genres.php)

# amazonaws
define('AWS_KEY', 'apikey');
define('AWS_SECRET_KEY', 'apisecretkey');


/**
 * Get meta information about the engine
 *
 * @todo    Include image search capabilities etc in meta information
 */
function amazonawsMeta()
{
    return array('name' => 'Amazon (AWS)', 'stable' => 1, 'php' => '5.1.2', 'capabilities' => array('movie', 'image', 'purchase'),
                 'config' => array(
                                array('opt' => 'locale', 'name' => 'Amazon AWS country selection',
                                      'values' => array('US'=>'US', 'UK'=>'UK', 'DE'=>'DE', 'JP'=>'JP'), 
                                      'desc' => 'Select the country site you\'d like to use for querying Amazon data.'),
                                array('opt' => AWS_KEY, 'name' => 'Amazon AWS API access key',
                                      'desc' => 'To use the Amazon search engine you need to obtain your own Amazon AWS API access key <a href="http://aws.amazon.com">here</a>).'),
                                array('opt' => AWS_SECRET_KEY, 'name' => 'Amazon AWS API secret access key',
                                      'desc' => 'To use the Amazon search engine you need to obtain your own Amazon AWS API access key <a href="http://aws.amazon.com">here</a>).')
    ));
}


function aws_signed_request($params, $region = null)
{
    /*
    Copyright (c) 2009 Ulrich Mierendorff

    Permission is hereby granted, free of charge, to any person obtaining a
    copy of this software and associated documentation files (the "Software"),
    to deal in the Software without restriction, including without limitation
    the rights to use, copy, modify, merge, publish, distribute, sublicense,
    and/or sell copies of the Software, and to permit persons to whom the
    Software is furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
    THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
    FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
    DEALINGS IN THE SOFTWARE.
    */
    
    /*
    Parameters:
        $params - an array of parameters, eg. array("Operation"=>"ItemLookup",
                        "ItemId"=>"B000X9FLKM", "ResponseGroup"=>"Small")
    */

    global $config;
    
    // some paramters
    $method = "GET";
    $host = "ecs.amazonaws.".($region ? $region : awsGetRegion());
    $uri = "/onca/xml";
    
    // TODO this hard-coded optin name should be moved to engined.php
    $public_key     = $config['amazonaws'.AWS_KEY];
    $private_key    = $config['amazonaws'.AWS_SECRET_KEY];

    // additional parameters
    $params["Service"] = "AWSECommerceService";
    $params["AWSAccessKeyId"] = $public_key;
	// Associate tag hack
	// TODO check proper associate tag
	$params["AssociateTag"] = 'videoDB';
    // GMT timestamp
    $params["Timestamp"] = gmdate("Y-m-d\TH:i:s\Z");
    // API version
    $params["Version"] = "2009-03-31";
    
    // sort the parameters
    ksort($params);
    
    // create the canonicalized query
    $canonicalized_query = array();
    foreach ($params as $param=>$value)
    {
        $param = str_replace("%7E", "~", rawurlencode($param));
        $value = str_replace("%7E", "~", rawurlencode($value));
        $canonicalized_query[] = $param."=".$value;
    }
    $canonicalized_query = implode("&", $canonicalized_query);
    
    // create the string to sign
    $string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;
    
    // calculate HMAC with SHA256 and base64-encoding
    $signature = base64_encode(hash_hmac("sha256", $string_to_sign, $private_key, True));
    
    // encode the signature for the request
    $signature = str_replace("%7E", "~", rawurlencode($signature));
    
    // create request
    $request = "http://".$host.$uri."?".$canonicalized_query."&Signature=".$signature;
    
    return $request;
}

function awsGetRegion()
{
    global $config;

    $locale = $config['amazonawslocale'];
    if (!$locale) $locale = 'US';

    $hosts  = array('US' => 'com', 'UK' => 'co.uk', 'DE' => 'de', 'JP' => 'jp');
    return $hosts[$locale];
}

/**
 * returns the MediatypeID for a given name from the 'mediatypes' table
 *
 * @author  Kokanovic Branko     <branko.kokanovic@gmail.com>
 * @param   string  $name        the name of the media
 * @return  integer $mediatypeID the genre id
 */
function awsGetMediatypeId($name)
{
    $name   = escapeSQL($name);
    $result = runSQL("SELECT id FROM ".TBL_MEDIATYPES." WHERE LCASE(name) = LCASE('".$name."')");
    return (count($result) > 0) ? $result[0]['id'] : null;
}

/**
 * Convert XML result into videoDB result array
 *
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   domNode dvd     Parent XML document node to start search
 * @param   boolean short   Return title & id only
 * @return  array    	    The converted result set for a single item
 */
function awsNodeData($item, $short = false)
{
    $data   = array();

    // Id
    $data['id']         = (string) $item->ASIN;

    // Title
    $data['title']      = (string) $item->ItemAttributes->Title;

    // small image for overview
    $data['imgsmall']   = (string) $item->SmallImage->URL;

    // cover url for image lookup
    $data['coverurl']   = (string) $item->LargeImage->URL;
    if (empty($data['coverurl']))
    {
        $data['coverurl'] = $data['imgsmall'];
    }

    /**
     * Purchase-specific data
     */

    // Title
    $data['price']  = (string) $item->ItemAttributes->ListPrice->FormattedPrice;

    // url
    $data['url']  = (string) $item->DetailPageURL;
    
    // exit if called from search function
	if ($short) return $data;
    
	// Year
    $date = $item->ItemAttributes->TheatricalReleaseDate;
    if (empty($date)) $date = $item->ItemAttributes->ReleaseDate;
	if (preg_match('/\d{4}/', $date, $m))
	{
		$data['year']	= $m[0];
	}
	
	// MPAA Rating
    $data['mpaa']       = (string) $item->ItemAttributes->AudienceRating;

	// Runtime
    $data['runtime']    = (string) $item->ItemAttributes->RunningTime;

	// Director
    $data['director']   = xml_join($item->ItemAttributes->Director, ',');

	// Rating
    $data['rating']     = round($item->CustomerReviews->AverageRating * 2, 1);

	// Countries
#	$data['country']  = join(' ',$m[1]));

	// Media
    $data['mediatype']  = awsGetMediatypeId((string) $item->ItemAttributes->Binding);
/*
	// Genres (as Array), map to nearest match
	$genres = getElement($dvd, 'BrowseList/BrowseNode/BrowseName', ',');
    foreach (mapGenres(explode(',', $genres)) as $genre)
    {
        $data['genres'][] = $genre;
    }
*/
	// Cast
	$data['cast']		= xml_join($item->ItemAttributes->Actor);
    
	// Plot
    $data['plot']       = xml_join($item->EditorialReviews->EditorialReview->Content, "\n\n");
    if (empty($data['plot']))
    {
        // use customer reviews if plot not found
        $data['plot']   = xml_join($item->CustomerReviews->Review->Content);
	}

    // Country
    foreach ($item->ItemAttributes->Languages->Language as $row)
    {
        $lang = strtolower(trim($row->Name));
        if (!stristr($data['language'], $lang)) 
        {
            $data['language'] .= ($data['language']) ? ', ' : '';
            $data['language'] .= $lang;
        }
    }    

/*	
	// Tracks
	$tracks				= xml_join($item->Tracks->Track);
	$data['tracks']		= $tracks;

    // convert to numbered track list
	$track_number		= 1;
    foreach (explode("\n", $tracks) as $track)
    {
		$track = trim($track);
		if ($tracks) {
			$tracks_formatted .= $track_number++;
			$tracks_formatted .= '. '.$track."\n";
		}
	}

    // append to plot as long as we don't have a separate field
	if ($data['plot']) $data['plot'] .= "\n\n";
	$data['plot']   .= $tracks_formatted;
*/

    foreach(array('AspectRatio','Format','Label') as $node)
    {
        $val    = $node.': '.xml_join($item->ItemAttributes->$node);

        if ($val) 
        {
            $data['comment'] .= (($data['comment']) ? "\n" : '') . $val;
        }
    }

	return $data;
}

/**
 * Get search Url for an Amazon product
 *
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string    The search string
 * @return  string    The search URL (GET)
 */
function amazonawsSearchUrl($title)
{
	global $amazonAWSServer;
	return $amazonAWSServer;
}

/**
 * Get search Url to visit external site
 *
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string	$id	The movie's external id
 * @return  string		The visit URL
 */
function amazonawsContentUrl($id)
{
	global $amazonAWSServer;
	return $amazonAWSServer.'/exec/obidos/ASIN/'.$id;
}

/**
 * Search a Movie/DVD/Book etc
 *
 * Searches for a given title on Amazon and returns the found links in
 * an array
 *
 * @author  Andreas Goetz (cpuidle@gmx.de)
 * @param   string    The search string
 * @param   string    The amazon catalog to search in (dvd, books etc)
 * @return  array     Associative array with id and title
 */
function amazonawsSearch($title, $index = 'DVD', $region = null)
{
    global $amazonAWSUrl, $cache;
	global $CLIENTERROR;

    $page   = 1;
    $data   = array();
    $data['encoding'] = 'utf-8';
    
	do
	{
        $url    = aws_signed_request(array(
                    'Operation' => 'ItemSearch',
                    'ItemPage' => $page,
                    'Keywords' => urlencode($title),
                    'SearchIndex' => $index,
                    'ResponseGroup' => 'Small,Images,ItemAttributes'
#                    'SearchIndex' => 'Books,Classical,DigitalMusic,DVD,Electronics,Magazines,Music,MusicTracks,UnboxVideo,VHS,Video,VideoGames';
                    ), $region);
#        dump($url);
        
		$resp = httpClient($url, $cache);
#       dump($resp);
#       dump(preg_replace('/</', "\n<", $resp['data']));

/*
        [TotalResults] => 30
        [TotalPages] => 3
        [Item] => Array
            (
                [0] => SimpleXMLElement Object
                    (
                        [ASIN] => B001VPJYZ0
                        [DetailPageURL] => http://www.amazon.com/Scrubs-Complete-Eighth-Zach-Braff/dp/B001VPJYZ0%3FSubscriptionId%3D1CB01P12WQBRDNH10NR2%26tag%3Dcpuidle-20%26linkCode%3Dxm2%26camp%3D2025%26creative%3D165953%26creativeASIN%3DB001VPJYZ0
                        [SmallImage] => SimpleXMLElement Object
                            (
                                [URL] => http://ecx.images-amazon.com/images/I/51vHM7URliL._SL75_.jpg
                                [Height] => 75
                                [Width] => 53
                            )

                        [MediumImage] => SimpleXMLElement Object
                            (
                                [URL] => http://ecx.images-amazon.com/images/I/51vHM7URliL._SL160_.jpg
                                [Height] => 160
                                [Width] => 114
                            )

                        [LargeImage] => SimpleXMLElement Object
                            (
                                [URL] => http://ecx.images-amazon.com/images/I/51vHM7URliL.jpg
                                [Height] => 500
                                [Width] => 356
                            )

                        [ImageSets] => SimpleXMLElement Object
                            (
                                [ImageSet] => SimpleXMLElement Object
                                    (
                                        [@attributes] => Array
                                            (
                                                [Category] => primary
                                            )

                                        [SwatchImage] => SimpleXMLElement Object
                                            (
                                                [URL] => http://ecx.images-amazon.com/images/I/51vHM7URliL._SL30_.jpg
                                                [Height] => 30
                                                [Width] => 21
                                            )

                                        [SmallImage] => SimpleXMLElement Object
                                            (
                                                [URL] => http://ecx.images-amazon.com/images/I/51vHM7URliL._SL75_.jpg
                                                [Height] => 75
                                                [Width] => 53
                                            )

                                        [ThumbnailImage] => SimpleXMLElement Object
                                            (
                                                [URL] => http://ecx.images-amazon.com/images/I/51vHM7URliL._SL75_.jpg
                                                [Height] => 75
                                                [Width] => 53
                                            )

                                        [TinyImage] => SimpleXMLElement Object
                                            (
                                                [URL] => http://ecx.images-amazon.com/images/I/51vHM7URliL._SL110_.jpg
                                                [Height] => 110
                                                [Width] => 78
                                            )

                                        [MediumImage] => SimpleXMLElement Object
                                            (
                                                [URL] => http://ecx.images-amazon.com/images/I/51vHM7URliL._SL160_.jpg
                                                [Height] => 160
                                                [Width] => 114
                                            )

                                        [LargeImage] => SimpleXMLElement Object
                                            (
                                                [URL] => http://ecx.images-amazon.com/images/I/51vHM7URliL.jpg
                                                [Height] => 500
                                                [Width] => 356
                                            )

                                    )

                            )

                        [ItemAttributes] => SimpleXMLElement Object
                            (
                                [Actor] => Array
                                    (
                                        [0] => Zach Braff
                                        [1] => Donald Faison
                                        [2] => Sarah Chalke
                                    )

                                [Binding] => DVD
                                [EAN] => 0786936786934
                                [Format] => Array
                                    (
                                        [0] => Box set
                                        [1] => Color
                                        [2] => DVD-Video
                                        [3] => NTSC
                                    )

                                [Label] => Touchstone / Disney
                                [Languages] => SimpleXMLElement Object
                                    (
                                        [Language] => SimpleXMLElement Object
                                            (
                                                [Name] => English
                                                [Type] => Original Language
                                            )
                                    )

                                [ListPrice] => SimpleXMLElement Object
                                    (
                                        [Amount] => 3999
                                        [CurrencyCode] => USD
                                        [FormattedPrice] => $39.99
                                    )

                                [Manufacturer] => Touchstone / Disney
                                [NumberOfItems] => 3
                                [ProductGroup] => DVD
                                [ProductTypeName] => ABIS_DVD
                                [Publisher] => Touchstone / Disney
                                [ReleaseDate] => 2009-08-25
                                [RunningTime] => 414
                                [Studio] => Touchstone / Disney
                                [Title] => Scrubs: The Complete Eighth Season
                                [UPC] => 786936786934
                            )
                    )
*/
        if ($resp['success'])
        {
            $xml = load_xml($resp['data']);
#           dump($xml);
            $total_pages = (int) $xml->Items->TotalPages;

            foreach($xml->Items->Item as $item)
            {
                $data[] = awsNodeData($item, true);
			}
		}
		else $CLIENTERROR .= $resp['error']."\n";
	}	
    while ($resp['success'] && ($page++ < $total_pages) && ($page < 2));
#    dump($data);

	return $data;	
}

/**
 * Fetches the data for a given Amazon ID (equals ISBN)
 *
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   int   IMDB-ID
 * @return  array Result data
 */
function amazonawsData($id)
{
	global $amazonAWSUrl, $cache;
	global $CLIENTERROR;

    $url    = aws_signed_request(array(
                'Operation' => 'ItemLookup',
                'ItemId' => $id,
                'ResponseGroup' => 'Large'
                ));
	$resp = httpClient($url, $cache);
#   dump($resp);

	if ($resp['success'])
	{
        $xml = load_xml($resp['data']);
#       dump($xml);

        $data = awsNodeData($xml->Items->Item[0]);
#		dump($data);
	}
	else $CLIENTERROR .= $resp['error']."\n";

    // utf-8 according to HTTP response but simplexml complains?
    // utf-8 is already forced by load_xml
    $data['encoding'] = 'utf-8';
	
	return $data;
}

?>