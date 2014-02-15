<?php
/**
 * OFDB Parser
 *
 * Parses data from the OFDB
 *
 * @package Engines
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @link    http://www.ofdb.de
 * @version $Id: ofdb.php,v 1.24 2011/06/18 21:28:01 robelix Exp $
 */

$GLOBALS['ofdbscraperServer']	= 'http://www.ofdb.de';
$GLOBALS['ofdbscraperIdPrefix'] = 'ofdbscraper:';

/**
 * Get meta information about the engine
 *
 * @todo	Include image search capabilities etc in meta information
 */
function ofdbscraperMeta()
{
    return array(
    	'name' => 'OFDB (de) Scraper'
    	, 'stable' => 1
    	, 'supportsEANSearch' => 0
    );
}

/**
 * Get search Url for OfDB
 *
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @param   string	The search string
 * @return  string	The search URL (GET)
 */
function ofdbscraperSearchUrl($title, $searchType = 'title')
{
	global $ofdbscraperServer;

	// auto switch to ean Mode if title is exactly 13 digits
	if (preg_match('#^\s*[0-9]{13}\s*$#',$title)) $searchType = 'ean';

	$url = $ofdbscraperServer.'/view.php?page=suchergebnis&SText='.urlencode($title);
	switch($searchType)
	{
		default    :
		case 'text': {
			$url = $url.'&Kat=All'; break;
		}
		case 'ean' : {
			$url = $url.'&Kat=EAN'; break;
		}
	}

    return $url;
}

/**
 * Get content overview URL
 *
 * @author	Chinamann <chinamann@users.sourceforge.net>
 * @param	string	$id	The movie's external id
 * @return	string		The visit URL
 */
function ofdbscraperContentUrl($id)
{
	global $ofdbscraperServer;
	global $ofdbscraperIdPrefix;

	$id = preg_replace('/^'.$ofdbscraperIdPrefix.'/', '', $id);
    list($id, $vid) = explode("-", $id, 2);
	return $ofdbscraperServer.'/view.php?page=film&fid='.$id;
}

/**
 * Get content detail URL
 *
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @param   string	$id	The movie's external id
 * @return  string		The visit URL
 */
function ofdbscraperDetailUrl($id)
{
	global $ofdbscraperServer;
	return $ofdbscraperServer.'/view.php?page=film_detail&fid='.$id;
}

/**
 * Get explicit version URL
 *
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @param   string	$id		The movie's external id
 * @param   string	$vid	The movie's version id
 * @return  string			The visit URL
 */
function ofdbscraperVersionUrl($id, $vid)
{
	global $ofdbscraperServer;
	return $ofdbscraperServer.'/view.php?page=fassung&fid='.$id.'&vid='.$vid;
}

/**
 * Get content description URL
 *
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @param   string	$id		The movie's external id
 * @param   string	$sid	The movie's description id
 * @return  string			The visit URL
 */
function ofdbscraperDescriptionUrl($id, $sid)
{
	global $ofdbscraperServer;
	return $ofdbscraperServer.'/view.php?page=inhalt&fid='.$id.'&sid='.$sid;
}


/**
 * Search a Movie
 *
 * Searches for a given title on the OfDB and returns the found links in
 * an array
 *
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @param   string    The search string
 * @return  array     Associative array with id and title
 */
function ofdbscraperSearch($title, $searchType = 'title')
{
	global $ofdbscraperServer;
	global $ofdbscraperIdPrefix;
	global $CLIENTERROR;
	global $cache;

	// auto switch to ean Mode if title is exactly 13 digits
	if (preg_match('#^\s*[0-9]{13}\s*$#',$title)) $searchType = 'ean';

	// search for series
	$resp = httpClient(ofdbscraperSearchUrl($title, $searchType), $cache);
	if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";
#    dump($resp);

	// add encoding
    $ary['encoding'] = get_response_encoding($resp);

	if (preg_match_all('/<br>[0-9]+\.\s*<a href="film\/([0-9]+),[^"]*" onmouseover="[^"]*"[^>]*>([^<]*)<font.*?\/font> \(([\/\-0-9]+)\)<\/a>/', $resp['data'], $data, PREG_SET_ORDER))
	{
		foreach ($data as $row) {
            $info['id']     = $ofdbscraperIdPrefix.$row[1];
            $info['title']  = trim($row[2]).' ('.$row[3].')';
			$ary[]			= $info;
		}
	}
	if (preg_match_all('/<br>[0-9]+\.\s*<a href="film\/([0-9]+),[^"]*" onmouseover="[^"]*"><b>([^<]*)<.*?<a href="view\.php\?page=fassung.*?fid=[0-9]+.*?vid=([0-9]+)" onmouseover="[^"]*">([^<]*)</i', $resp['data'], $data, PREG_SET_ORDER))
	{
		foreach ($data as $row) {
		    $info['id']     = $ofdbscraperIdPrefix.$row[1]."-".$row[3];
            $info['title']  = trim($row[2]).' - '.$row[4];
			$ary[]			= $info;
		}
	}
	// do not return an array which contains only an encoding attribute
	if (count($ary) < 2) return array();

	return $ary;
}

/**
 * Fetches the data for a given OfDB id
 *
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @param   int   OfDB id
 * @return  array Result data
 */
function ofdbscraperData($id)
{
	global $CLIENTERROR;
	global $ofdbscraperServer;
	global $ofdbscraperIdPrefix;
	global $cache;
	global $config;

	$id = preg_replace('/^'.$ofdbscraperIdPrefix.'/', '', $id);
    list($id, $vid) = explode("-", $id, 2);

	$data = array(); //result
	$ary  = array(); //temp
	$ary2 = array(); //temp2

	// Fetch Mainpage
	$resp = httpClient(ofdbscraperContentUrl($id), $cache);
	if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

	// add encoding
    $data['encoding'] = get_response_encoding($resp);

    // add engine ID -> important for non edit.php refetch
    $data['imdbID'] = $ofdbscraperIdPrefix.$id;

	$resp['data'] = preg_replace('/[\r\n\t]/',' ', $resp['data']);

	// Titles / Year
	preg_match('/<title>(.*?)<\/title>/i', $resp['data'], $ary);
	$ary[1] = preg_replace('/^OFDb[\s-]*/', '', $ary[1]);
	$ary[1] = preg_replace('/\[.*\]/', ' ', $ary[1]);
	if (preg_match('/\(([0-9]*)\)/i',$ary[1],$ary2))
	{
		$data['year'] = trim($ary2[1]);
	}
	$ary[1] = preg_replace('/\([0-9]*\)/', ' ', $ary[1]);
	$ary[1] = preg_replace('/\s{2,}/s', ' ', $ary[1]);

	// check if there is a comma  sperated article at the end
	if (preg_match('#(.*),\s*(A|The|Der|Die|Das|Ein|Eine|Einer)\s*$#i',$ary[1],$subRes)) {
		$ary[1] = $subRes[2].' '.$subRes[1];
	}

    list($t,$s)         = explode(" - ",trim($ary[1]),2);
	$data['title']		= trim($t);
	$data['subtitle']	= trim($s);

    // Original Title
    if (preg_match('/Originaltitel.*?<b>(.*?)</i', $resp['data'], $ary))
    {
        $data['orgtitle'] .= trim($ary[1]);
    }

    // Country
    if (preg_match('/>Herstellungsland:.*?<b><a.*?>(.*?)<\/a>/i', $resp['data'], $ary))
    {
        $data['country'] .= trim($ary[1]);
    }

	// Rating
	if (preg_match('/Note: <span itemprop="ratingValue">([0-9\.]+)/', $resp['data'], $ary)) {
//	if (preg_match('/<br>Note:\s*([0-9\.]+)/', $resp['data'], $ary)) {
		$data['rating'] = $ary[1];
	}

	// Cover URL
    if (preg_match('#<img src="(http://img.ofdb.de/film/na.gif)"#i', $resp['data'], $ary))
    {
        $data['coverurl'] = "";
    }
    else if (preg_match('#<img src="(http://img.ofdb.de/film/.*?\.jpg)"#i', $resp['data'], $ary))
    {
        $data['coverurl'] =  trim($ary[1]);
    }

    // Fetch first VID if none already selected
    if (!$vid)
    {
    	if (preg_match_all('/view\.php\?page=fassung&fid='.$id.'&vid=([0-9]+)".*?class="Klein">(.*?)</i', $resp['data'], $ary, PREG_SET_ORDER))
    	{
			foreach($ary as $row)
			{
				if (trim($row[2]) == "K" || trim($row[2]) == "KV") // Check if there is a good result
				{
    				$vid=$row[1];
    				break;
				}
			}
			if (!$vid) // Still empty -> Take the first one
			{
				$vid=$ary[1][1];
			}
    	}
    }

    // IMDB ID
    $data['imdbID'] = $ofdbscraperIdPrefix."$id-$vid";

    // Fetch Plot
	if (preg_match('#href="(plot/[^"]+)"#i', $resp['data'], $ary))
	{
		$subresp = httpClient($ofdbscraperServer.'/'.$ary[1], $cache);
		if (!$resp['success']) $CLIENTERROR .= $subresp['error']."\n";
		$subresp['data'] = preg_replace('/[\r\n\t]/',' ', $subresp['data']);
		//ofdbDbg($subresp['data'],false);
		if (preg_match('#</b><br><br>(.*?)</font></p>#i', $subresp['data'], $ary))
	    {

	        $ary[1] = preg_replace('/\s{2,}/s', ' ', $ary[1]);
			$ary[1] = preg_replace('#<(br|p)[ /]*>#i', "\n", $ary[1]);
			$data['plot'] = trim($ary[1]);
			//$data['plot'] = "ae‰‰‰‰aaa‰";
	    }
	}

	// Fetch Details
	$resp = httpClient(ofdbscraperDetailUrl($id), $cache);
	if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";
	$resp['data'] = preg_replace('/[\r\n\t]/',' ', $resp['data']);

	// Director
	if (preg_match('/<b><i>Regie<\/i><\/b>.*?<table.*?>(.*?)<\/table>/i', $resp['data'], $ary))
    {
    	if (preg_match_all('/class="Daten"><a.*?>(.*?)<\/a>/i',$ary[1],$ary2, PREG_SET_ORDER))
		{
	    	foreach ($ary2 as $row)
			{
	        	$data['director'] .= trim($row[1]).', ';
	    	}
	    	$data['director'] = preg_replace('/, $/', '', $data['director']);
    	}
    }

    // Cast
	if (preg_match('/<b><i>Darsteller<\/i><\/b>.*?<table.*?>(.*)<\/table>/', $resp['data'], $ary))
    {
    	// dirty workaround for (.*?) failed on very long match groups issue (tested at PHP 5.2.5.5)
    	// e.g.: ofdb:7749-111320 (Angel - J‰ger der Finsternis)
    	$ary[1] = preg_replace('#</table.*#','',$ary[1]);

    	if (preg_match_all('/class="Daten"><a(.*?)">(.*?)<\/a>.*?<\/td>  <td.*?<\/td>  <td[^>]*>(.*?)<\/td>/i',$ary[1],$ary2, PREG_SET_ORDER))
		{
	    	foreach ($ary2 as $row)
			{
	        	$actor = trim(strip_tags($row[2]));

	        	$actorid = "";
				if (!empty($row[1]))
	        	{
	        		if (preg_match('#href="view.php\?page=person&id=([0-9]*)#i', $row[1], $idAry))
    				{
    					$actorid = $ofdbscraperIdPrefix.$idAry[1];
    				}
	        	}

	        	$character = "";
	        	if (!empty($row[3]))
	        	{
	        		if (preg_match('#class="Normal">... ([^<]*)<#i', $row[3], $charAry))
    				{
    					$character = trim(strip_tags($charAry[1]));
    				}
	        	}
                $data['cast'] .= "$actor::$character::$actorid\n";
	    	}
    	}
    }

    // Genres
    $genres = array(
        'Amateur' => '',
        'Eastern' => '',
        'Experimentalfilm' => '',
        'Mondo' => '',
        'Kampfsport' => 'Sport',
        'Biographie' => 'Biography',
        'Katastrophen' => 'Thriller',
        'Krimi' => 'Crime',
        'Science-Fiction' => 'Sci-Fi',
        'Kinder-/Familienfilm' => 'Family',
        'Dokumentation' => 'Documentary',
        'Action' => 'Action',
        'Drama' => 'Drama',
        'Abenteuer' => 'Adventure',
        'Historienfilm' => 'History',
        'Kurzfilm' => 'Short',
        'Liebe/Romantik' => 'Romance',
        'Heimatfilm' => 'Romance',
        'Grusel' => 'Horror',
        'Horror' => 'Horror',
        'Erotik' => 'Adult',
        'Hardcore' => 'Adult',
        'Sex' => 'Adult',
        'Musikfilm' => 'Musical',
        'Animation' => 'Animation',
        'Fantasy' => 'Fantasy',
        'Trash' => 'Horror',
        'Komˆdie' => 'Comedy',
        'Krieg' => 'War',
        'Mystery' => 'Mystery',
        'Thriller' => 'Thriller',
        'Tierfilm' => 'Documentary',
        'Western' => 'Western',
        'TV-Serie' => '',
        'TV-Mini-Serie' => '',
        'Sportfilm' => 'Sport',
        'Splatter' => 'Horror',
        'Manga/Anime' => 'Animation'
    );
    if (preg_match('/>Genre\(s\)\:.*?<b>(.*?)<\/b>/i', $resp['data'], $ary))
    {
	    if (preg_match_all('/<a.*?>(.*?)<\/a>/i',$ary[1],$ary2, PREG_SET_ORDER))
	    {
		    foreach($ary2 as $row) {
		        $genre = trim(html_entity_decode($row[1]));
		        $genre = strip_tags($genre);
		        if (!$genre) continue;
		        if (isset($genres[$genre])) $data['genres'][] = $genres[$genre];
		    }
	    }
    }

	// Fetch Version
	$resp = httpClient(ofdbscraperVersionUrl($id, $vid), $cache);
	if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";
	$resp['data'] = preg_replace('/[\r\n\t]/',' ', $resp['data']);

	// FSK
    $fsks = array(
        'FSK o.A.' => '0',
        'FSK 6' => '6',
        'FSK 12' => '12',
        'FSK 16' => '16',
        'FSK 18' => '18',
        'Keine Jugendfreigabe' => '18',
        'SPIO/JK' => '18',
        'juristisch gepr¸ft' => '',
        'ungepr¸ft' => ''
    );
	if (preg_match('/>Freigabe:<.*?<b>(.*?)<\/tr>/i', $resp['data'], $ary))
    {
    	$fsk = trim(html_entity_decode($ary[1]));
        $fsk = strip_tags($fsk);
		if (isset($fsks[$fsk])) $data['fsk'] = $fsks[$fsk];
    }

    // Languages
    // Languages (as Array)
    $laguages = array(
        'arabisch' => 'arabic',
        'bulgarisch' => 'bulgarian',
        'chinesisch' => 'chinese',
        'tschechisch' => 'czech',
        'd‰nisch' => 'danish',
        'hol‰ndisch' => 'dutch',
        'englisch' => 'english',
        'franzˆsisch' => 'french',
        'deutsch' => 'german',
        'griechisch' => 'greek',
        'ungarisch' => 'hungarian',
        'isl‰ndisch' => 'icelandic',
        'indisch' => 'indian',
        'israelisch' => 'israeli',
        'italienisch' => 'italian',
        'japanisch' => 'japanese',
        'koreanisch' => 'korean',
        'norwegisch' => 'norwegian',
        'polnisch' => 'polish',
        'portugisisch' => 'portuguese',
        'rum‰nisch' => 'romanian',
        'russisch' => 'russian',
        'serbisch' => 'serbian',
        'spanisch' => 'spanish',
        'schwedisch' => 'swedish',
        'thail‰ndisch' => 'thai',
        't¸rkisch' => 'turkish',
        'vietnamesisch' => 'vietnamese',
        'kantonesisch' => 'cantonese',
        'katalanisch' => 'catalan',
        'zypriotisch' => 'cypriot',
        'zyprisch' => 'cypriot',
        'esperanto' => 'esperanto',
        'g‰lisch' => 'gaelic',
        'hebr‰isch' => 'hebrew',
        'hindi' => 'hindi',
        'j¸disch' => 'jewish',
        'lateinisch' => 'latin',
        'mandarin' => 'mandarin',
        'serbokroatisch' => 'serbo-croatian',
        'somalisch' => 'somali'
    );
    $lang_list = array();

	// Runtime
   	if (preg_match('/>Laufzeit:<.*?<b>(.*?)\s*Min/i', $resp['data'], $ary))
   	{
   		$ary[1] = preg_replace('/:.*/','', $ary[1]);
        $data['runtime']  = trim($ary[1]);
    }

	return $data;
}


/**
 * Get Url to visit OFDB for a specific actor
 *
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @param   string  $name   The actor's name
 * @param   string  $id The actor's external id
 * @return  string  The visit URL
 */
function ofdbscraperActorUrl($name, $id)
{
    global $ofdbscraperServer;
    global $ofdbscraperIdPrefix;

    if ($id) {
    	$id = preg_replace('/^'.$ofdbscraperIdPrefix.'/', '', $id);
    } else {
    	$id = ofdbscraperGetActorId($name);
    }

    // now we have for shure an id
    return ($id!=0) ? $ofdbscraperServer.'/view.php?page=person&id='.$id : '';
}

/**
 * Parses Actor-Details
 *
 * Find image and detail URL for actor.
 *
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @param   string  $name  Name of the actor
 * @param   string  $id    Prefixed ofdb actor id
 * @return  array          array with Actor-URL and Thumbnail
 */
function ofdbscraperActor($name, $id)
{
    global $ofdbscraperServer;

    if ($id) {
    	$id = preg_replace('/^'.$ofdbscraperIdPrefix.'/', '', $id);
    } else {
    	$id = ofdbscraperGetActorId($name);
    }

    // now we have for shure an id
    $folderId = ($id < 1000) ? 0 : substr($id,0,strlen($id)-3);

    $imgUrl = $ofdbscraperServer.'/images/person/'.$folderId.'/'.$id.'.jpg';

    $ary    = array();
    $ary[0][0] = ofdbscraperActorUrl($name, $id);
    $ary[0][1] = $imgUrl;
    return $ary;
}

function ofdbscraperGetActorId($name)
{
	global $ofdbscraperServer;

    // try to guess the id -> first actor found with this name
    $url = $ofdbscraperServer.'/view.php?page=liste&Name='.urlencode(html_entity_decode_all($name));
    $resp = httpClient($url, $cache);
	if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

    $resp['data'] = preg_replace('/[\r\n\t]/',' ', $resp['data']);

    return (preg_match('#view.php?page=person&id=([0-9]+)#i', $resp['data'], $ary)) ? $ary[1] : 0;
}


/**
 * Get an array of all previous prefixes for the ImdbId
 *
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @return  array     Associative array with ImdbId prefixes
 */
function ofdbscraperImdbIdPrefixes()
{
	global $ofdbscraperIdPrefix;
    return array($ofdbscraperIdPrefix);
}

function ofdbscraperDbg($text,$append = true)
{
	file_append('debug.txt', $text, $append);
}
?>
