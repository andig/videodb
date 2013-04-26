<?php
/**
 * Dvdfr Parser
 *
 * Parses data from www.dvdfr.com (french site)
 * 2006-08-12 Update Sebastien Koechlin <seb.videodb@koocotte.org>
 *
 * @package Engines
 * @author  tedemo  <tedemo@free.fr>
 * @link    http://www.dvdfr.com
 * @version $Id: dvdfr.php,v 1.7 2011/06/23 12:27:28 robelix Exp $
 */

require_once './core/compatibility.php';

$GLOBALS['dvdfrServer']	  = 'http://www.dvdfr.com';
$GLOBALS['dvdfrIdPrefix'] = 'dvdfr:';

/**
 * Get meta information about the engine
 *
 * @todo    Include image search capabilities etc in meta information
 */
function dvdfrMeta()
{
    return array('name' => 'Dvdfr (fr)', 'stable' => 0);
}


/**
 * Clean a string
 *
 * @param   string    The string to clean
 * @return  string    The cleaned string
 */
function dvdfrCleanStr($str)
{
    // Remove spaces
    $str = trim($str);

    // Translate strange (MS-Word?) quotes
    $str = preg_replace( '/&#039;/', '\'', $str );

    // Remove HTML entities
    $str = html_entity_decode($str);

    return $str;
}

/**
 * Get Url to search Dvdfr for a movie
 *
 * @param   string    The search string
 * @return  string    The search URL (GET)
 */
function dvdfrSearchUrl($title)
{
	global $dvdfrServer;
	return $dvdfrServer.'/api/search.php?title='.urlencode(mb_convert_encoding($title,'ISO-8859-15','UTF-8'));
}

/**
 * Get Url to visit Dvdfr for a specific movie
 *
 * @param   string	$id	The movie's external id
 * @return  string		The visit URL
 */
function dvdfrContentUrl($id)
{
    list($engineword, $dvdfrID) = explode(':',$id,2);
	global $dvdfrServer;
	return $dvdfrServer.'/api/dvd.php?id='.$dvdfrID;
	#return 'http://koocotte.org/DVDMARK';
}

/**
 * Search a Movie
 *
 * Searches for a given title on Dvdfr and returns the found links in
 * an array
 *
 * @return  array     Associative array with id and title
 */
function dvdfrSearch($title)
{
    global $dvdfrServer;
    global $CLIENTERROR;

    $para['useragent'] = 'VideoDB (http://www.videodb.net/)';

    $resp = httpClient(dvdfrSearchUrl($title), 1, $para);
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

    // Encoding
    $ary['encoding'] = get_response_encoding($resp);

/* No more direct match with XLM API

    // direct match (redirecting to individual title)?
    $single = array();
    if (preg_match('/\/dvd\/dvd\.php\?id=(\d+)/', $resp['url'], $single))
    {
        $ary[0]['id']   = 'dvdfr:'.$single[1];
        preg_match('/<td><div class=\"dvd_title\">([^<]+)<\/div>[^<]*<div class=\"dvd_titlevo\">([^<]+)<\/div>[^<]*<div class=\"dvd_titleinfo\">([^<]+)</is', $resp['data'], $single);
        $ary[0]['title']= $single[1].' ('.$single[2].'/'.$single[3].')';
        return $ary;
    }
*/
    // multiple matches
  /*
          <dvd>
            <id>16892</id>					<= $1
            <media>DVD</media>					<= $2
            <titres>
              <fr>Star Wars - Clone Wars - Vol. 1</fr>		<= $3
              <vo>Star Wars: Clone Wars</vo>			<= $4
              <alternatif></alternatif>
              <alternatif_vo></alternatif_vo>
            </titres>
            <annee>2003</annee>					<= $5
            <edition></edition>					<= $6
            <editeur>20th Century Fox</editeur>			<= $7
            <stars>
              <star type="RÃ©alisateur" id="49661">Genndy Tartakovsky</star>
            </stars>
          </dvd>
  */

    preg_match_all('#<dvd>\s*<id>(\d+)</id>\s*<media>(\w+)</media>\s*<titres>\s*<fr>(.+?)</fr>\s*<vo>(.*?)</vo>.*?<annee>(.*?)</annee>\s*<edition>(.*?)</edition>\s*<editeur>(.*?)</editeur>\s*.*?</dvd>#is', $resp['data'], $data, PREG_SET_ORDER);
    foreach ($data as $row)
    {
        $info['id']     = 'dvdfr:'.$row[1];
        $title  = dvdfrCleanStr($row[3]);
        // add native title
        if( !empty($row[4]) ) $title .= " / " . dvdfrCleanStr($row[4]);

        if( !empty($row[5]) and !empty($row[6]) and !empty($row[7]) ) {
          $title .= ' (';
          // add year (helpful in case of multiple matches)
          if( !empty($row[5]) ) $title .= dvdfrCleanStr($row[5]);
          $title .= '/';
        // add edition and editor
          if( !empty($row[6]) ) $title .= dvdfrCleanStr($row[6]);
          $title .= '/';
          if( !empty($row[7]) ) $title .= dvdfrCleanStr($row[7]);
          $title .=')';
        }

        // Add record
        $info['title']  = $title;
        $ary[]          = $info;
    }

    return $ary;
}

/**
 * Fetches the data for a given Dvdfr-ID
 *
 * @param   int   IMDB-ID
 * @return  array Result data
 */
function dvdfrData($imdbID)
{
    global $dvdfrServer;
    global $CLIENTERROR;

    $data= array();	// result
    $ary = array();	// temp

    $para['useragent'] = 'VideoDB (http://www.videodb.net/)';

    // fetch mainpage
    $resp = httpClient(dvdfrContentUrl($imdbID), 1, $para);		// added trailing / to avoid redirect
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

    // add encoding
    $data['encoding'] = get_response_encoding($resp);

    // See http://www.dvdfr.com/api/dvd.php?id=2869 for output

    // Titles
    preg_match('#<titres>\s*<fr>(.+?)</fr>\s*<vo>(.+?)</vo>#is', $resp['data'], $ary);
    $data['title']    = mb_convert_case(dvdfrCleanStr($ary[1]), MB_CASE_TITLE, $data['encoding']);
    $data['subtitle'] = mb_convert_case(dvdfrCleanStr($ary[2]), MB_CASE_TITLE, $data['encoding']);

    // I found: <div class="dvd_titleinfo">USA, Royaume-Uni , 2004<br />R&D TV, Sky TV, USA Cable Entertainment</div>
    preg_match('#<listePays>\s*<pays.*?>(.+?)</pays>#is', $resp['data'], $ary);
    $data['country'] = dvdfrCleanStr($ary[1]);
    preg_match('#<annee>(\d+)</annee>#is', $resp['data'], $ary);
    $data['year'] = dvdfrCleanStr($ary[1]);

    // Cover URL
    preg_match('#<cover>(.*?)</cover>#i', $resp['data'], $ary);
    $data['coverurl'] = trim($ary[1]);

    // Runtime
    preg_match('#<duree>(\d+)</duree>#i', $resp['data'], $ary);
    $data['runtime']  = $ary[1];

    // Director (only the first one)
    preg_match('#<star type="R.*?alisateur" id="\d+">(.*?)</star>#i', $resp['data'], $ary);
    $data['director'] = dvdfrCleanStr($ary[1]);

    // Plot
    preg_match('#<synopsis>(.*?)</synopsis>#is', $resp['data'], $ary);
    if (!empty($ary[1])) {
      $data['plot'] = $ary[1];
      // And cleanup
      $data['plot'] = preg_replace('/[\n\r]/',' ', $data['plot']);
      $data['plot'] = preg_replace('/\s+/',' ', $data['plot']);
      $data['plot'] = dvdfrCleanStr($data['plot']);
    }

    // maps dvdfr category ids to videodb category names
    $category_map = array
    (
        "1" => "Action",
        "2" => "Animation",
        "61" => "", //  "Autres séries"
        "3" => "Adventure",
        "72" => "", //"Beaux-Arts"
        "81" => "Musical", //"Bollywood"
        "4" => "Comedy",
        "5" => "Drama", // "Comédie dramatique"
        "6" => "Musical", //"Comédie musicale"
        "74" => "Romance", // "Comédie romantique"
        "7" => "Music", //"Concert"
        "8" => "" , //"Conte"
        "9" => "Short", //"Court-Métrage"
        "10" => "Documentary", //"Culture"
        "78" => "Documentary", //"Culture Gay"
        "11" => "Music", //"Danse"
        "12" => "", //"Divers"
        "13" => "Documentary", //"Documentaire"
        "14" => "Drama", //"Drame"
        "73" => "Drama", //"Emotion"
        "15" => "Adult", //"Erotique"
        "16" => "Action", //"Espionnage"
        "17" => "Sci-Fi", //"Fantastique"
        "30" => "Musical", //"Film musical"
        "83" => "Sport", //"Freefight"
        "18" => "War", //"Guerre"
        "19" => "Musical", //"Hard-rock"
        "20" => "History", //"Historique"
        "21" => "Horror", //"Horreur"
        "22" => "Comedy", //"Humour"
        "23" => "Animation", //"Japanimation"
        "24" => "Adult", //"Japanimation érotique"
        "25" => "Music", //"Jazz &amp; Blues"
        "79" => "", //"Jeux"
        "26" => "Music", //"Karaoke"
        "27" => "Action", //"Kung Fu"
        "28" => "", //"Méthode"
        "57" => "", //"Mini-series / Feuilletons"
        "29" => "Documentary", //"Muet"
        "32" => "Music", //"Musique Classique"
        "71" => "Music", //"Musiques du monde"
        "31" => "Music", //"Opéra"
        "33" => "War", //"Péplum"
        "34" => "Crime", //"Policier"
        "54" => "", //"Pour enfants"
        "76" => "Music", //"R&amp;B &amp; Soul"
        "55" => "Music", //"Rap"
        "56" => "Sci-Fi", //"Science Fiction"
        "60" => "", //"Série Anime / OAV"
        "75" => "", //"Série d'animation enfants"
        "58" => "", //"Série TV"
        "59" => "", //"Sitcom"
        "62" => "", //"Spectacle"
        "63" => "Sport",
        "82" => "Sport", //"Sports mécaniques"
        "64" => "Music", //"Techno / Electro"
        "65" => "", //"Theatre"
        "66" => "Thriller",
        "67" => "Music", //"Variété française"
        "68" => "Music", //"Variété internationale"
        "69" => "Documentary", //"Voyages"
        "70" => "Western",
        "Science Fiction" => "Sci-Fi",
    );

    // Genres (as Array)
    if (preg_match_all('#<categorie>(.*?)</categorie>#i', $resp['data'], $ary, PREG_PATTERN_ORDER) > 0)
    {
        $count = 0;
    	while (isset($ary[1][$count]))
    	{
              $data['genres'][]  = $category_map[dvdfrCleanStr($ary[1][$count])];
              $count ++;
        }
    }

    // Cast
    if( preg_match('#<stars>(.*)</stars>#is', $resp['data'], $Section) ) {
      preg_match_all('#<star type="Acteur" id="(\d+)">(.*?)</star>#i', $Section[1], $ary,PREG_PATTERN_ORDER);

        for ($i=0; $i < sizeof($ary[0]); $i++)
        {
          $cast .= dvdfrCleanStr($ary[2][$i]) . '::::dvdfr' . dvdfrCleanStr($ary[1][$i]) . "\n";
          #$cast  .= "$actor::$character::$imdbIdPrefix$actorid\n";
        }
        $data['cast'] = dvdfrCleanStr($cast);
    }

    #// Convert ISO to UTF8
    #$encoding = $data['encoding'];
    #foreach( $data as $k => $v ) {
    #  $data[$k] = mb_convert_encoding(trim($v),'UTF-8',$encoding);
    #}

    return $data;
}

/**
 * Parses Actor-Details
 *
 * Find image and detail URL for actor, not sure if this can be made
 * a one-step process?  Completion waiting on update of actor
 * functionality to support more than one engine.
 *
 * @param  string  $name  Name of the Actor
 * @return array          array with Actor-URL and Thumbnail
 */
function dvdfrActor($name, $actorengineid)
{
    global $dvdfrServer;

    return;
}

?>
