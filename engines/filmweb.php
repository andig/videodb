<?php
/**
 * FilmWeb Parser (pl)
 *
 * Parses data from the Polish FilmWeb site
 *
 * @package Engines
 * @author  Marek Domaniuk <domanm@o2.pl>
 * @author  Victor La <cyridian@users.sourceforge.net>
 * @desc    Original filmweb.php by Marek Domaniuk, rewritten by Victor La
 * @desc    using Andreas Goetz's imdb.php as a template
 * @link    http://www.filmweb.com  Internet Movie Database
 * @version $Id: filmweb.php,v 1.4 2007/08/08 18:28:15 andig2 Exp $
 */

$GLOBALS['filmwebServer']	= 'http://www.filmweb.pl';
$GLOBALS['filmwebIdPrefix'] = 'filmweb:';

/**
 * Get meta information about the engine
 *
 * @todo    Include image search capabilities etc in meta information
 */
function filmwebMeta()
{
    return array('name' => 'FilmWeb (pl)', 'stable' => 1);
}

/**
 * Get Url to search FilmWeb for a movie
 *
 * @author  Marek Domaniuk <domark@o2.pl>
 * @param   string    The search string
 * @return  string    The search URL (GET)
 */
function filmwebSearchUrl($title)
{
	global $filmwebServer;
	return $filmwebServer.'/Find?query='.urlencode($title).'&category=1&submit=szukaj';
}

/**
 * Get Url to visit FilmWeb for a specific movie
 *
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @author  Marek Domaniuk <domark@o2.pl>
 * @author  Victor La <cyridian@users.sourceforge.net>
 * @param   string	$id	The movie's external id
 * @return  string		The visit URL
 */
function filmwebContentUrl($id)
{
	global $filmwebServer;
	global $filmwebIdPrefix;
	$id = preg_replace('/^'.$filmwebIdPrefix.'/', '', $id);
	return $filmwebServer.'/Film?id='.$id;
}

/**
 * Get Url for actor on FilmWeb
 *
 * @author  Victor La <cyridian@users.sourceforge.net>
 * @param   string    $name  Name of the Actor
 * @return  string    The actor URL (GET)
 */
function filmwebActorUrl($name, $actorid)
{
    global $filmwebServer;
    $url    = ($actorid) ? '/Person,id='.urlencode($actorid) : '/szukaj?q='.urlencode($name).'&alias=person';
    return $filmwebServer.$url;
}

/**
 * Search a Movie
 *
 * Searches for a given title on the FilmWeb and returns the found links in
 * an array
 *
 * @author  Marek Domaniuk <domark@o2.pl>
 * @author  Victor La <cyridian@users.sourceforge.net>
 * @param   string    The search string
 * @return  array     Associative array with id and title
 */
function filmwebSearch($title)
{
    global $filmwebServer;
    global $filmwebIdPrefix;
    global $CLIENTERROR;

    $resp = httpClient(filmwebSearchUrl($title), 1);
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

	preg_match_all('/<a class="searchResultTitle" href="(.+?)">(.+?)<\/a>.+?\((.+?)\)/si', $resp['data'], $data, PREG_SET_ORDER);
    foreach ($data as $row) 
    {
        $info['id']     = trim($row[1]);
		
        $row[2] = preg_replace('/<b>/','', $row[2]);
        $row[2] = preg_replace('/<\/b>/','', $row[2]);
        $info['title']  = trim($row[2]);

        // add year (helpful in case of multiple matches)
        $info['title']  = $info['title'].' ('.$row[3].')';
		
        //Check URL to see if the movie ID is in it, if not, load the page and grab it from that new page
        if (preg_match('/id=(\d+)/', $info['id'], $single))
        {
            $info['id'] = $single[1];
        }
        elseif  ((strpos($info['id'], 'id=')) != true)
        {			    
            $subResp = httpClient($info['id'],1);
            if (!$subResp['success']) $CLIENTERROR .= $subResp['error']."\n";
            //<a class="n" href="http://www.filmweb.pl/AddFilmFavourite?film.id=108130">dodaj do ulubionych</a>
            preg_match('/,id=(\d+)">/i', $subResp['data'], $single);
            $info['id'] = $single[1];
        }
        $info['id'] = $filmwebIdPrefix.$info['id'];
	
        $ary[] = $info;
    }

    return $ary;
}

/**
 * Fetches the data for a given FilmWeb-ID
 *
 * @author  Marek Domaniuk <domark@o2.pl>
 * @author  Victor La <cyridian@users.sourceforge.net>
 * @param   int   FilmWeb-ID
 * @return  array Result data
 */
function filmwebData($filmwebID) 
{
    global $filmwebServer;
    global $filmwebIdPrefix;
    global $CLIENTERROR;

    $filmwebID = preg_replace('/^'.$filmwebIdPrefix.'/', '', $filmwebID);
    $data= array();	// result
    $ary = array();	// temp

    // fetch mainpage
    $resp = httpClient(filmwebContentUrl($filmwebID), 1);
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

    // Titles - Fixed
    preg_match('/<div id="filmTitle">(.*?)<span class="otherTitle">(.+?)<\/span>/is', $resp['data'], $ary);
    $data['title'] = trim($ary[1]);

    //DOUBLE CHECK THIS SECTION OF CODE FOR THE SUBTITLE!
    if (!preg_match('/<a href/i', $ary[2], $tempary)) {
        $data['subtitle'] = trim($ary[2]);
    }
    if (preg_match('/\(AKA (.+?)\)/i', $ary[2], $tempary)) {
        $data['subtitle'] = trim($tempary[1]);
    }

    // Year - Fixed
    preg_match('/\(([1-2][0-9][0-9][0-9])\)/i', $resp['data'], $ary);
    $data['year']     = trim($ary[1]);

    // Cover-URL - Fixed
    //<img  src="http://gfx.filmweb.pl/f/94745/po.6954459.jpg"
    //http://gfx.filmweb.pl/f/520/520.jpg
    preg_match('/<div id="filmPhoto">.+?<img border="0" src="(.+?)"/si', $resp['data'], $ary);
    $data['coverurl'] = trim($ary[1]);

    // MPAA Rating
    $data['mpaa'] = '';

    // UK BBFC Rating
    $data['bbfc'] = '';
    
    // Runtime
    preg_match('/czas trwania.+?([0-9,]+)\s/si', $resp['data'], $ary);
    $data['runtime']  = preg_replace('/,/', '', trim($ary[1]));

    // Director - Fixed
    //re¿yseria				<a class="n" title="Kerry Conran: filmografia [Filmweb.pl]" //href="http://www.filmweb.pl/Kerry,Conran,filmografia,Person,id=138676">Kerry Conran</a>
    preg_match('/re.yseria.+?title="(.+?)- filmografia.+?"/i', $resp['data'], $ary);
    $data['director'] = trim($ary[1]);

    // Rating - Fixed
    preg_match('/<b class="rating">(.+?)<\/b>\/10/i', $resp['data'], $ary);
    $data['rating']   = trim($ary[1]);

    // Countries - Fixed
    preg_match_all('/<a href="http:\/\/www.filmweb.pl\/szukaj\/film\?countryids=\d.+?">(.+?)<\/A>/i', $resp['data'], $ary, PREG_PATTERN_ORDER);
    $data['country']  = trim(join(', ', $ary[1]));

    // Languages - DOES THIS NEED TO BE FIXED?
    //$data['language'] = '';

    // Plot (movies in their early stages have the plot here but not yet in plotsummary?) - Fixed 4-5-07
    // Not necessary for FilmWeb?

    // Genres (as Array) - Fixed
    $genres = array(
	'Przygodowy' => 'Adventure',
	'Akcja' => 'Action',
	'Komedia' => 'Comedy',
	'Familijny' => 'Family',
	'Muzyka' => 'Music',
	'Western' => 'Western',
	'Dla doros³ych' => 'Adult',
	'Krymina³' => 'Crime',
	'Fantasy' => 'Fantasy',
	'Musical' => 'Musical',
	'Muzyczny' => 'Musical',
	'Krótkometra¿owy' => 'Short',
	'Dokumentalny' => 'Documentary',
	'Film-Noir' => 'Film-Noir',
	'Mystery' => 'Mystery',
	'Thriller' => 'Thriller',
	'Dreszczowiec' => 'Thriller',
	'Animacja' => 'Animation',
	'Dramat' => 'Drama',
	'Melodramat' => 'Drama',
	'Dramat historyczny' => 'History',
	'Historyczny' => 'History',
	'Dramat obyczajowy' => 'Drama',
	'Dramat s±dowy' => 'Drama',
	'Dramat spo³eczny' => 'Drama',
	'Horror' => 'Horror',
	'Romans' => 'Romance',
	'Wojenny' => 'War',
	'Biograficzny' => 'Biographic',
	'Erotyczny' => 'Adult',
	'Komedia kryminalna' => 'Comedy',
	'Komedia obycz.' => 'Comedy',
	'Komedia rom.' => 'Comedy',
	'Komedia' => 'Comedy',
	'Czarna komedia' => 'Comedy',
	'Dla dzieci' => '',
	'Obyczajowy' => '',
	'Bibilijny' => '',
	'Sensacja' => 'Action',
	'Sensacyjny' => 'Action',
	'Fabularyzowany dok.' => 'Documentary',
	'Psychologiczny' => ''
    );
    preg_match_all('/genreIds=\d.+?">(.+?)</i', $resp['data'], $ary, PREG_PATTERN_ORDER);
    foreach($ary[1] as $genre)
    {
        $genre = trim($genre);
        if (!($genre)) continue;
        if (isset($genres[$genre])) $genre = $genres[$genre];
        if (!($genre)) continue;
        $data['genres'][] = $genre;
    }

    // Cast - Fixed
    preg_match_all('/<a class="filmActor" HREF="(.+?)".+?>(.+?)<\/a>.+?((<br\/>).|(<div class="filmRoleSeparator">:<\/div>.+?<div class="filmRole">(.+?)<\/div>))/si', $resp['data'], $ary,PREG_PATTERN_ORDER);
    $count = 0;
    while (isset($ary[1][$count])) 
    {
        $actor  = trim(strip_tags($ary[2][$count]));
        $role   = trim(strip_tags($ary[6][$count]));
        $role = preg_replace('/&nbsp;/',' ', $role);
        $role = trim($role);

        //$actorid= $ary[1][$count]; //, $m)) ? $m[1] : '';
        preg_match('/.+?Person\,id=(\d+)/i', $ary[1][$count], $ary2);

        if (!empty($ary2[1]))
        {
            $actorid= $ary2[1];
        }
        else
        {
            $resp = httpClient($ary[1][$count], 1);
            if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

            preg_match('/,Person.+?,id=(\d+)">/i', $resp['data'], $ary2);
            if (!empty($ary2[1])) $actorid = $ary2[1];
        }


        $cast  .= "$actor::$role::$filmwebIdPrefix$actorid\n";
        $count++;
    }
    $data['cast'] = trim($cast);
    
    // fetch Plot
    $resp = httpClient($filmwebServer.'/FilmDescriptions?id='.$filmwebID, 1);
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

    // Plot
    preg_match('/<li><div.+?">(.+?)<\/div><\/li>/is', $resp['data'], $ary);
    if (!empty($ary[1])) $data['plot'] = trim($ary[1]);
    $data['plot'] = preg_replace('/[\n\r]/',' ', $data['plot']);
    $data['plot'] = preg_replace('/  /',' ', $data['plot']);
    $data['plot'] = trim($data['plot']);

    return $data;
}

/**
 * Parses Actor-Details
 *
 * Find image and detail URL for actor, not sure if this can be made
 * a one-step process?
 *
 * @author  Victor La <cyridian@users.sourceforge.net>
 * @param  string  $name  Name of the Actor
 * @return array          array with Actor-URL and Thumbnail
 */
function filmwebActor($name, $actorid)
{
    global $filmwebServer;

    // search directly by id or via name?
    $resp   = httpClient(filmwebActorUrl($name, $actorid), 1);

    $ary    = array();
    
    if (preg_match('/<a class="searchResultTitle" href="(.+?)">/i', $resp['data'], $m)) {
        $resp = httpClient($m[1], true);
    }

	// now we should have loaded the best match    
    if (preg_match('/<img src="http:\/\/gfx.filmweb.pl\/p\/(.+?)"/i', $resp['data'], $m))
    {
        $ary[0][0] = 'http://gfx.filmweb.pl/p/'.$m[1];
        $ary[0][1] = 'http://gfx.filmweb.pl/p/'.$m[1];
        return $ary;
    } 
    else return null;
}

?>