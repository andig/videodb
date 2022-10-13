<?php
/**
 * OFDB Parser
 *
 * Parses data from the OFDB
 *
 * @package Engines
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @link    http://www.ofdb.de
 * @version $Id: ofdb.php,v 1.27 2013/03/16 14:29:47 andig2 Exp $
 */

require_once './core/xml.core.php';

$GLOBALS['ofdbServer']   = 'https://www.ofdb.de';
$GLOBALS['ofdbGW']       = 'http://www.ofdbgw.org'; // defunct
$GLOBALS['ofdbIdPrefix'] = 'ofdb:';

/**
 * Get meta information about the engine
 *
 * @todo    Include image search capabilities etc in meta information
 */
function ofdbMeta()
{
    return array(
        'name' => 'OFDB (de)'
        , 'stable' => 1
        , 'supportsEANSearch' => 1
    );
}

/**
 * Get search Url for OfDB
 *
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @param   string  The search string
 * @return  string  The search URL (GET)
 */
function ofdbSearchUrl($title, $searchType = 'title')
{
    global $ofdbServer;

    // auto switch to ean Mode if title is exactly 13 digits
    if (preg_match('#^\s*[0-9]{13}\s*$#',$title)) $searchType = 'ean';

    $url = $ofdbServer.'/view.php?page=suchergebnis&SText='.urlencode($title);
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
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @param   string  $id The movie's external id
 * @return  string      The visit URL
 */
function ofdbContentUrl($id)
{
    global $ofdbServer;
    global $ofdbIdPrefix;

    $id = preg_replace('/^'.$ofdbIdPrefix.'/', '', $id);
    list($id, $vid) = explode("-", $id, 2);
    return $ofdbServer.'/view.php?page=film&fid='.$id;
}

/**
 * Get content detail URL
 *
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @param   string  $id The movie's external id
 * @return  string      The visit URL
 */
function ofdbDetailUrl($id)
{
    global $ofdbServer;
    return $ofdbServer.'/view.php?page=film_detail&fid='.$id;
}

/**
 * Get explicit version URL
 *
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @param   string  $id     The movie's external id
 * @param   string  $vid    The movie's version id
 * @return  string          The visit URL
 */
function ofdbVersionUrl($id, $vid)
{
    global $ofdbServer;
    return $ofdbServer.'/view.php?page=fassung&fid='.$id.'&vid='.$vid;
}

/**
 * Get content description URL
 *
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @param   string  $id     The movie's external id
 * @param   string  $sid    The movie's description id
 * @return  string          The visit URL
 */
function ofdbDescriptionUrl($id, $sid)
{
    global $ofdbServer;
    return $ofdbServer.'/view.php?page=inhalt&fid='.$id.'&sid='.$sid;
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
function ofdbSearch($title, $searchType = 'title')
{
    global $CLIENTERROR;
    global $cache;
    global $ofdbServer;
    global $ofdbGW;
    global $ofdbIdPrefix;

    $url    = $ofdbGW.'/search/'.$title;
    $resp   = httpClient($url, $cache);
#   dump($resp);

    if (!$resp['success']) {
        $CLIENTERROR .= $resp['error']."\n";
        return(false);
    }

    $xml = load_xml($resp['data']);
#   dump($xml);

    if ((int) $xml->status->rcode > 0) {
        // prevent caching bad data
        if ($cache) {
            $cache_file = cache_get_filename($url, CACHE_HTML);
            @unlink($cache_file);

            if ($resp['source']) {
                // TODO make sure redirects are deleted as well
                $url = $resp['source'];
                $cache_file = cache_get_filename($url, CACHE_HTML);
                @unlink($cache_file);
            }
        }
        $CLIENTERROR .= ((string) $xml->status->rcodedesc)."\n";
        return(false);
    }

    $data   = array();
    $data['encoding'] = 'utf-8';

    foreach($xml->resultat->eintrag as $item)
    {
        $data   = array();

        // Id
        $data['id']         = $ofdbIdPrefix.((string) $item->id);

        // Title
        $data['title']      = (string) $item->titel;
        $data['orgtitle']   = (string) $item->titel_orig;
        list($data['title'], $data['subtitle']) = explode(" - ", $data['title'], 2);

        // Year
        $data['year']       = (string) $item->jahr;

        // cover url for image lookup
        $data['coverurl']   = (string) $item->bild;

        $result[] = $data;
    }
#   dump($data);
    
    return($result);
}

/**
 * Fetches the data for a given OfDB id
 *
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @param   int   OfDB id
 * @return  array Result data
 */
function ofdbData($id)
{
    global $CLIENTERROR;
    global $cache;
    global $ofdbServer;
    global $ofdbGW;
    global $ofdbIdPrefix;

    // Languages
    $map_laguages = array(
        'arabisch' => 'arabic',
        'bulgarisch' => 'bulgarian',
        'chinesisch' => 'chinese',
        'tschechisch' => 'czech',
        'd�nisch' => 'danish',
        'holl�ndisch' => 'dutch',
        'englisch' => 'english',
        'franz�sisch' => 'french',
        'deutsch' => 'german',
        'griechisch' => 'greek',
        'ungarisch' => 'hungarian',
        'isl�ndisch' => 'icelandic',
        'indisch' => 'indian',
        'israelisch' => 'israeli',
        'italienisch' => 'italian',
        'japanisch' => 'japanese',
        'koreanisch' => 'korean',
        'norwegisch' => 'norwegian',
        'polnisch' => 'polish',
        'portugisisch' => 'portuguese',
        'rum�nisch' => 'romanian',
        'russisch' => 'russian',
        'serbisch' => 'serbian',
        'spanisch' => 'spanish',
        'schwedisch' => 'swedish',
        'thail�ndisch' => 'thai',
        't�rkisch' => 'turkish',
        'vietnamesisch' => 'vietnamese',
        'kantonesisch' => 'cantonese',
        'katalanisch' => 'catalan',
        'zypriotisch' => 'cypriot',
        'zyprisch' => 'cypriot',
        'esperanto' => 'esperanto',
        'g�lisch' => 'gaelic',
        'hebr�isch' => 'hebrew',
        'hindi' => 'hindi',
        'j�disch' => 'jewish',
        'lateinisch' => 'latin',
        'mandarin' => 'mandarin',
        'serbokroatisch' => 'serbo-croatian',
        'somalisch' => 'somali'
    );

    // Genres
    $map_genres = array(
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
        'Kom�die' => 'Comedy',
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

    $data   = array();
    $data['encoding']   = 'utf-8';
    $data['imdbID']     = $id;

    $id = preg_replace('/^'.$ofdbIdPrefix.'/', '', $id);
    list($id, $vid) = explode("-", $id, 2);

    $url    = $ofdbGW.'/movie/'.$id;
#   dump($url);
    $resp   = httpClient($url, $cache);

    if (!$resp['success']) {
        $CLIENTERROR .= $resp['error']."\n";
        return(false);
    }
    
    $xml = load_xml($resp['data']);
#   dump($xml);

    if ((int) $xml->status->rcode > 0) {
        // prevent caching bad data
        if ($cache) {
            $cache_file = cache_get_filename($url, CACHE_HTML);
            @unlink($cache_file);
        }
        $CLIENTERROR .= ((string) $xml->status->rcodedesc)."\n";
        return(false);
    }

    // set root
    $item = $xml->resultat;

    // Title
    $data['title']      = (string) $item->titel;
    $data['orgtitle']   = (string) $item->alternativ;
    list($data['title'], $data['subtitle']) = explode(" - ", $data['title'], 2);

    // Year
    $data['year']       = (string) $item->jahr;

    // Cover url for image lookup
    $data['coverurl']   = (string) $item->bild;

    // Plot
    $data['plot']       = (string) $item->beschreibung;
    if (!$data['plot']) $data['plot'] = (string) $item->kurzbeschreibung;

    // Rating
    $data['rating']     = round((float) $item->bewertung->note);

    // Cast
    foreach ($item->besetzung->person as $cast) {
        $data['cast'] .= "\n";
        $data['cast'] .= ((string) $cast->name);
        if ((string) $cast->rolle) $data['cast'] .= '::'.((string) $cast->rolle);
        if ((string) $cast->id) $data['cast'] .= '::'.((string) $cast->id);
    }

    // Director
    foreach ($item->regie->person as $director) {
        if ($data['director']) $data['director'] .= ', ';
        $data['director'] .= (string) $director->name;
    }

    // Country
    foreach ($item->produktionsland as $country) {
        if ($data['country']) $data['country'] .= ', ';
        $data['country'] .= (string) $country->name;
    }
    
    // Genre
    $data['genres'] = array();
    foreach ($item->genre->titel as $genre) {
        $genre = (string) $genre;
        // mapping
        if ($map_genres[$genre]) $data['genres'][] = $map_genres[$genre];
    }


    // Fetch first VID if none already selected
    if (!$vid) {
        foreach ($item->fassungen->titel as $fassung) {
            $vid = (string) $fassung->id; // 1545;210858
            break;
        }
        if ($vid) {
            // IMDB ID
            $data['imdbID'] = $ofdbIdPrefix.preg_replace('/;/', '-', $vid);
        }
    }

#   dump($data);
    return($data);
/*
    $data = array(); //result
    $ary  = array(); //temp
    $ary2 = array(); //temp2

    // Fetch Mainpage
    $resp = httpClient(ofdbContentUrl($id), $cache);
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

    // add encoding
    $data['encoding'] = $resp['encoding'];

    // add engine ID -> important for non edit.php refetch
    $data['imdbID'] = $ofdbIdPrefix.$id;

    $resp['data'] = preg_replace('/[\r\n\t]/',' ', $resp['data']);

    // Titles / Year
    preg_match('/<title>(.*?)<\/title>/i', $resp['data'], $ary);
    $ary[1] = preg_replace('/^OFDb[\s-]*!!!!!!!!!!!/', '', $ary[1]);
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
    $data['title']      = trim($t);
    $data['subtitle']   = trim($s);

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
    if (preg_match('/<br>Note:\s*([0-9\.]+)/', $resp['data'], $ary)) {
        $data['rating'] = $ary[1];
    }

    // Cover URL
    if (preg_match('#<img src="(http://img.ofdb.de/film/.*?\.jpg)"#i', $resp['data'], $ary))
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
    $data['imdbID'] = $ofdbIdPrefix."$id-$vid";

    // Fetch Plot
    if (preg_match('#href="(plot/[^"]+)"#i', $resp['data'], $ary))
    {
        $subresp = httpClient($ofdbServer.'/'.$ary[1], $cache);

        if (!$resp['success']) $CLIENTERROR .= $subresp['error']."\n";
        $subresp['data'] = preg_replace('/[\r\n\t]/',' ', $subresp['data']);

        if (preg_match('#</b><br><br>(.*?)</font></p>#i', $subresp['data'], $ary))
        {

            $ary[1] = preg_replace('/\s{2,}/s', ' ', $ary[1]);
            $ary[1] = preg_replace('#<(br|p)[ /]*>#i', "\n", $ary[1]);
            $data['plot'] = trim($ary[1]);
            //$data['plot'] = "ae����aaa�";
        }
    }

    // Fetch Details
    $resp = httpClient(ofdbDetailUrl($id), $cache);
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
        // e.g.: ofdb:7749-111320 (Angel - J�ger der Finsternis)
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
                        $actorid = $ofdbIdPrefix.$idAry[1];
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

    if (preg_match('/>Genre\(s\)\:.*?<b>(.*?)<\/b>/i', $resp['data'], $ary))
    {
        if (preg_match_all('/<a.*?>(.*?)<\/a>/i',$ary[1],$ary2, PREG_SET_ORDER))
        {
            foreach($ary2 as $row) {
                $genre = trim(html_entity_decode($row[1]));
                $genre = strip_tags($genre);
                if (!$genre) continue;
                if (isset($map_genres[$genre])) $data['genres'][] = $map_genres[$genre];
            }
        }
    }

    // Fetch Version
    $resp = httpClient(ofdbVersionUrl($id, $vid), $cache);
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
        'juristisch gepr�ft' => '',
        'ungepr�ft' => ''
    );
    if (preg_match('/>Freigabe:<.*?<b>(.*?)<\/tr>/i', $resp['data'], $ary))
    {
        $fsk = trim(html_entity_decode($ary[1]));
        $fsk = strip_tags($fsk);
        if (isset($fsks[$fsk])) $data['fsk'] = $fsks[$fsk];
    }

    $lang_list = array();
    if (preg_match('/>Tonformat:(.*?)<\/tr>/i', $resp['data'], $ary) &&
        preg_match_all('/<a.*?>(\w+?)(\s\().*?a>/si', $ary[1], $langs, PREG_PATTERN_ORDER))
    {
        foreach($langs[1] as $language) {
            $language = trim(strtolower($language));
            $language = html_entity_decode(strip_tags($language));
            $language = preg_replace('/\s+$/','',$language);
            if (!$language) continue;
            if (isset($map_laguages[$language])) $language = $map_laguages[$language];
            else continue;
            if (!$language) continue;
            $lang_list[] = $language;
        }
        $data['language'] = trim(join(', ', array_unique($lang_list)));
    }

    // Runtime
    if (preg_match('/>Laufzeit:<.*?<b>(.*?)\s*Min/i', $resp['data'], $ary))
    {
        $ary[1] = preg_replace('/:.*!!!!!!!/','', $ary[1]);
        $data['runtime']  = trim($ary[1]);
    }

    // EAN-Code
    if (preg_match('/>EAN\/UPC<\/a>:.*?<b>\s*([0-9]+)\s*<\/b>/i', $resp['data'], $ary))
    {
        $data['barcode'] = $ary[1];
    }

    return $data;
*/
}


/**
 * Get Url to visit OFDB for a specific actor
 *
 * @author  Chinamann <chinamann@users.sourceforge.net>
 * @param   string  $name   The actor's name
 * @param   string  $id The actor's external id
 * @return  string  The visit URL
 */
function ofdbActorUrl($name, $id)
{
    global $ofdbServer;
    global $ofdbIdPrefix;

    if ($id) {
        $id = preg_replace('/^'.$ofdbIdPrefix.'/', '', $id);
    } else {
        $id = ofdbGetActorId($name);
    }

    // now we have for shure an id
    return ($id) ? $ofdbServer.'/view.php?page=person&id='.$id : '';
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
function ofdbActor($name, $id)
{
    global $ofdbServer;

    if ($id) {
        $id = preg_replace('/^'.$ofdbIdPrefix.'/', '', $id);
    } else {
        $id = ofdbGetActorId($name);
    }

    // now we have for shure an id
    $folderId = ($id < 1000) ? 0 : substr($id,0,strlen($id)-3);

    $imgUrl = $ofdbServer.'/images/person/'.$folderId.'/'.$id.'.jpg';

    $ary    = array();
    $ary[0][0] = ofdbActorUrl($name, $id);
    $ary[0][1] = $imgUrl;
    return $ary;
}

function ofdbGetActorId($name)
{
    global $ofdbServer;

    // try to guess the id -> first actor found with this name
    $url = $ofdbServer.'/view.php?page=liste&Name='.urlencode(html_entity_decode_all($name));
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
function ofdbImdbIdPrefixes()
{
    global $ofdbIdPrefix;
    return array($ofdbIdPrefix);
}

?>
