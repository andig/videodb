<?php
/**
 * Allocine Parser
 *
 * Parses data from the Allocine.fr
 *
 * @package Engines
 * @author  Douglas Mayle   <douglas@mayle.org>
 * @author  Andreas Gohr    <a.gohr@web.de>
 * @author  tedemo          <tedemo@free.fr>
 * @link    http://www.allocine.fr  Internet Movie Database
 * @version $Id: allocine.php,v 1.17 2011/06/24 23:08:06 robelix Exp $
 */

$GLOBALS['allocineServer']	    = 'http://www.allocine.fr';
$GLOBALS['allocineIdPrefix']    = 'allocine:';

/**
 * Get meta information about the engine
 *
 * @todo    Include image search capabilities etc in meta information
 */

function allocineMeta()
{
    return array('name' => 'Allocine (fr)');
}

/**
 * Encode title search to allow results with accentued characters
 * @author Martin Vauchel <martin@vauchel.com>
 * @param string	The search string
 * @return string	The search string with no accents
 */
function removeAccents($title)
{
	$accentued = array("à","á","â","ã","ä","ç","è","é","ê","ë","ì",
	"í","î","","ï","ñ","ò","ó","ô","õ","ö","ù","ú","û","ü","ý","ÿ",
	"À","Á","Â","Ã","Ä","Ç","È","É","Ê","Ë","Ì","Í","Î","Ï","Ñ","Ò",
	"Ó","Ô","Õ","Ö","Ù","Ú","Û","Ü","Ý");
	$nonaccentued = array("a","a","a","a","a","c","e","e","e","e","i","i",
	"i","i","n","o","o","o","o","o","u","u","u","u","y","y","A","A","A",
	"A","A","C","E","E","E","E","I","I","I","I","N","O","O","O","O","O",
	"U","U","U","U","Y");

	$title = str_replace($accentued, $nonaccentued, $title);

	return $title;
}

/**
 * Get Url to search Allocine for a movie
 *
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string    The search string
 * @return  string    The search URL (GET)
 */
function allocineSearchUrl($title)
{
	global $allocineServer;
	// The removeAccents function is added here
	return $allocineServer.'/recherche/?q='.urlencode(removeAccents($title));
}

/**
 * Get Url to visit Allocine for a specific movie
 *
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string    $id    The movie's external id
 * @return  string        The visit URL
 */
function allocineContentUrl($id)
{
   global $allocineServer;
   global $allocineIdPrefix;

   $allocineID = preg_replace('/^'.$allocineIdPrefix.'/', '', $id);
   return $allocineServer.'/film/fichefilm_gen_cfilm='.$allocineID.'.html';
}


/**
 * Search a Movie
 *
 * Searches for a given title on Allocine and returns the found links in
 * an array
 *
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Tiago Fonseca <t_r_fonseca@yahoo.co.uk>
 * @author  Charles Morgan <cmorgan34@yahoo.com>
 * @param   string    The search string
 * @return  array     Associative array with id and title
 */
function allocineSearch($title)
{
    global $allocineServer;
    global $CLIENTERROR;

    // The removeAccents function is added here
    $resp = httpClient(allocineSearchUrl($title), 1);
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

    $data = array();

    #echo '<pre>';
    #dump(htmlspecialchars($resp['data']));
    #echo '</pre>';

    // add encoding
    $data['encoding'] = $resp['encoding'];

    // direct match (redirecting to individual title)?
    // no longer needed??
    $single = array();
    if (preg_match('#^'.preg_quote($allocineServer,'/').'/film/fichefilm_gen_cfilm=(\d+)\.html#', $resp['url'], $single))
    {
        $data[0]['id']   = 'allocine:'.$single[2];
        $data[0]['title']= $title;
        return $data;
    }

    // multiple matches
    // We remove all the multiples spaces and line breakers
	$resp['data'] = preg_replace('/[\s]{2,}/','',$resp['data']);
	// To have the result zone
	#$debutr  = strpos($resp['data'], '<table class="totalwidth noborder purehtml">')+strlen('<table class="totalwidth noborder purehtml">');
	#$finr    = strpos($resp['data'], '</table>', $debutr);
	#$chaine  = substr($resp['data'], $debutr, $finr-$debutr);

    preg_match('#<h2>\s*?Films\s*?</h2>(.*?)<h2>#si',$resp['data'],$ary);

    $chaine = $ary[1];
    # contains some pretty random <b></b>
    $chaine = preg_replace('/<b>/','',$chaine);
    $chaine = preg_replace('/<\/b>/','',$chaine);

    /*
    <tr><td style=" vertical-align:top;">
    <a href='/film/fichefilm_gen_cfilm=57999.html'><img
    src='http://images.allocine.fr/r_75_106/medias/nmedia/18/36/26/78/18759563.jpg'
    alt='Clerks II' /></a>
    </td><td style=" vertical-align:top;" class="totalwidth"><div><div style="margin-top:-5px;">
    <a href='/film/fichefilm_gen_cfilm=57999.html'>
    Clerks II</a>
    <br />
    <span class="fs11">
    2006<br />
    de Kevin Smith<br />
    avec Brian O'Halloran, Jeff Anderson<br />
    <div>
    <div class="spacer vmargin10"></div>
    </span> <!-- /fs11 -->
    */

    preg_match_all('#<a href=\'/film/fichefilm_gen_cfilm=(\d+).html\'>\s*?(.*?)</a>\s*?<br />\s*?<span class=\"fs11\">\s*?(\d+)<br />\s*?de (.*?)\s*?/#si', $chaine, $m, PREG_SET_ORDER);

    foreach ($m as $row)
    {
        $info['id']     = 'allocine:'.$row[1];

        $info['title']  = html_clean_utf8(strip_tags($row[2]));
        $info['title']  = str_replace("(", " (", $info['title']);

        // add year (helpful in case of multiple matches)
        if (isset($row[3])) {$info['year'] = html_clean_utf8($row[3]);}

        // add director (helpful in case of multiple matches)
        if (isset($row[4])) {
          $info['director'] = html_clean_utf8($row[4]);
          $info['director'] = preg_replace("/^de\s/", "", $info['director']);
        }

        $data[]          = $info;
    }

    return $data;
}

/**
 * Fetches the data for a given Allocine-ID
 *
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Tiago Fonseca <t_r_fonseca@yahoo.co.uk>
 * @param   int   imdb-ID
 * @return  array Result data
 */
function allocineData($imdbID)
{
    global $allocineServer;
    global $allocineIdPrefix;
    global $CLIENTERROR;

    $allocineID = preg_replace('/^'.$allocineIdPrefix.'/', '', $imdbID);

    // fetch mainpage
    $resp = httpClient($allocineServer.'/film/fichefilm_gen_cfilm='.$allocineID.'.html', 1);		// added trailing / to avoid redirect
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

    $data   = array(); // result
    $ary    = array(); // temp

    // add encoding
    $data['encoding'] = $resp['encoding'];

    // Allocine ID
    $data['id'] = "allocine:".$allocineID;

    // We remove all the multiples spaces and line breakers
    $resp['data'] = preg_replace('/[\s]{2,}/','',$resp['data']);

    /*
      Title and subtitle
    */
    preg_match('#<h1.*?>(.*?)</h1>#si', $resp['data'], $ary);
    list($t, $s)	  = explode(" - ",trim($ary[1]),2);
    // Some bugs when using html_clean function --> using html_clean_utf8
    $data['title']    = html_clean_utf8($t);
    $data['subtitle'] = html_clean_utf8($s);


    /*
      Year
    */
    preg_match('/<a.*? href="\/film\/tous\/decennie.*?year=(\d+)">(\d+)<\/a>/i', $resp['data'], $ary);
    if (!empty($ary[1])) {$data['year'] = trim($ary[1]);}


    /*
      Release Date
        added to the comments
    */
    preg_match('#<a.*? href="/film/agenda\.html\?week=\d+\-\d+\-\d+">(.*)</a>#i',$resp['data'], $ary);
    $release_date = "";
    if (!empty($ary[1])) {$release_date = "\r\nDate de sortie cinéma : ".html_clean_utf8($ary[1]);}

    /*
      Cover URL
    */
    preg_match('#<div class="colleft">\s*?<div class="vmargin20b">\s*?<div class=\"poster\">\s*?<em class=\"imagecontainer\">\s*?<a .*?>\s*?<img.*?src=\'(.*?)\'.*?>#si', $resp['data'], $ary);
    $data['coverurl'] = trim($ary[1]);


    /*
      Runtime
    */
    #Durée : 02h13min

    preg_match('/Durée :\s*?(\d+)h(\d+)\s*?min/i', $resp['data'], $ary);
    $hours  = preg_replace('/,/', '', trim($ary[1]));
    $minutes  = preg_replace('/,/', '', trim($ary[2]));
    $data['runtime']  = $hours * 60 + $minutes;


    /*
      Director
    */
    preg_match('#Réalisé par\s*<span.*?><a.*?rel="v:directedBy".*?href=\'/personne/fichepersonne_gen_cpersonne=\d+\.html\' title=\'.*\'>(.*)</a></span>#i', $resp['data'], $ary);
    $data['director'] = trim($ary[1]);


    /*
      Rating
    */
    preg_match('#<p class="withstars"><a.*?href="/film/critiquepublic_gen_cfilm=\d+\.html"><img.*?class="stareval.*?".*?<span class=\"moreinfo\">\((.*)\)</span></p>#i', $resp['data'], $ary);
    $data['rating'] = trim($ary[1]);
    $data['rating'] = str_replace(",", ".", $data['rating']);
    // Allocine rating is based on 5, imdb is based on 10
    $data['rating'] = $data['rating'] * 2;


    /*
      Countries
    */
	 // Countries in English
    $map_countries = array(
  		'allemand'			=> 'Germany',
  		'américain'			=> 'USA',
  		'arménien'      	=>  'Armenia',
  		'argentin'      	=>  'Argentina',
  		'sud-africain'  	=>  'South Africa',
      	'australien'		=> 'Australia',
  		'belge'				=> 'Belgium',
  		'britannique'		=> 'UK',
  		'bulgare'			=> 'Bulgaria',
  		'canadien'			=> 'Canada',
  		'chinois'			=> 'China',
  		'coréen'			=> 'South Korea',
  		'danois'			=> 'Denmark',
  		'espagnol'			=> 'Spain',
  		'français'			=> 'France',
  		'grec'				=> 'Greece',
  		'hollandais'		=> 'Netherlands',
  		'hong-kongais'		=> 'Hong-Kong',
  		'hongrois'			=> 'Hungary',
  		'indien'			=> 'India',
  		'irlandais'			=> 'Republic of Ireland',
  		'islandais'			=> 'Iceland',
  		'israëlien'			=> 'Israel',
  		'italien'			=> 'Italy',
  		'japonais'			=> 'Japan',
  		'luxembourgeois'	=> 'Luxembourg',
  		'mexicain'			=> 'Mexico',
  		'norvégien'			=> 'Norge',
  		'néo-zélandais'		=> 'New Zealand',
  		'polonais'			=> 'Poland',
  		'portugais'			=> 'Portugal',
  		'roumain'			=> 'Romania',
  		'russe'				=> 'Russia',
  		'serbe'				=> 'Serbia',
  		'suédois'			=> 'Sweden',
  		'taïwanais'			=> 'Taiwan',
  		'tchèque'			=> 'Czech Republic',
  		'thaïlandais'		=> 'Thailand',
  		'turc'				=> 'Turkey',
  		'ukrainien'			=> 'Ukraine',
  		'vietnamien'		=> 'Vietnam');

    if (preg_match_all('#Long\-métrage\s*?<a.*?href=".*?">(.*?)</a>#si', $resp['data'], $ary, PREG_PATTERN_ORDER) > 0)
    {
		$originlist  = explode(",",trim(join(', ', $ary[1])));
		foreach ($originlist as $origin)
		{
			$mapped_country_found = '';

			foreach ($map_countries as $pattern_c => $mapped_country)
			{
				if (preg_match_all('/'.$pattern_c.'/i', $origin, $junk, PREG_PATTERN_ORDER) > 0)
				{
					$mapped_country_found = $mapped_country;
					break;
				}
			}

			if($data['country'] == '') {$data['country'] = $mapped_country_found;}
			elseif(stristr($data['country'], $mapped_country_found) == TRUE)
			{
				$data['country'] = $data['country'];
			}
			else
			{
				$data['country'] = $data['country'] . ', ' . $mapped_country_found;
			}
		}
	}

    /*
      Plot
    */
    preg_match('#<div id="synopsis_full">\s*?<p>\s*?<span class=\"bold\">Synopsis \: </span>\s*?<span property="v:summary">(.*?)</span>#is', $resp['data'], $ary);
    if (!empty($ary[1])) {
		$data['plot'] = $ary[1];
		$data['plot']= html_clean_utf8($data['plot']);

		// And cleanup
		$data['plot'] = trim($data['plot']);
		$data['plot'] = preg_replace('/[\n\r]/',' ', $data['plot']);
		$data['plot'] = preg_replace('/  /',' ', $data['plot']);
    }

    /*
     Genres (as Array)
    */
    $map_genres = array(
          'Action'            	=> 'Action',
          'Animation'         	=>  'Animation',
          'Arts Martiaux'     	=>  'Action',
          'Aventure'            => 'Adventure',
          'Biopic'              => 'Biography',
          'Bollywood'           =>  'Musical',
          'Classique'           => '-',
          'Comédie Dramatique'  => 'Drama',
          'Comédie musicale'    =>  'Musical',
          'Comédie'             => 'Comedy',
          'Dessin animé'        => 'Animation',
          'Divers'              => '-',
          'Documentaire'        => 'Documentary',
          'Drame'               => 'Drama',
          'Epouvante-horreur'   => 'Horror',
          'Erotique'            =>  'Adult',
          'Espionnage'          => '-',
          'Famille'             => 'Family',
          'Fantastique'         => 'Fantasy',
          'Guerre'              => 'War',
          'Historique'          => 'History',
          'Horreur'             =>  'Horror',
          'Musique'             => 'Musical',
          'Policier'            => 'Crime',
          'Péplum'              => 'History',
          'Romance'             => 'Romance',
          'Science fiction'     => 'Sci-Fi',
          'Thriller'            => 'Thriller',
          'Western'             =>  'Western');

    if (preg_match_all('#Genre :(.*?)</a>\s*?<br#si', $resp['data'], $ary, PREG_PATTERN_ORDER) > 0)
    {
      $genrelist = explode(",", trim(join(', ', $ary[1])));

      foreach ($genrelist as $genre)
      {
        $mapped_genre_found = '';
        foreach ($map_genres as $pattern => $mapped_genre)
        {
          if (preg_match_all('/'.$pattern.'/i', $genre, $junk, PREG_PATTERN_ORDER) > 0)
          {
            $mapped_genre_found = $mapped_genre;
            break;
          }
        }
        $data['genres'][] = ($mapped_genre_found != '-') ? $mapped_genre_found : trim($genre);
      }
    }

    /*
      Original Title
    */
    preg_match('#Titre original : <span class=\"purehtml\"><em>(.*)</em></span>#', $resp['data'], $ary);
    $data['origtitle'] = trim($ary[1]);

    /*
      Title and Subtitle
      If sub-title is blank, we'll try to fill in the original title for foreign films.
    */
    if (empty($data['subtitle']))
    {
        if ($data['origtitle'])
        {
            $data['subtitle'] = $data['title'];
            $data['title']  = $data['origtitle'];
        }
    }

    /*
      CREDITS AND CAST
    */
    // fetch credits
    // Another HTML page
    $resp = httpClient($allocineServer.'/film/casting_gen_cfilm='.$allocineID.'.html', 1);
    if (!$resp['success']) {$CLIENTERROR .= $resp['error']."\n";}

    // We remove all the multiples spaces and line breakers
    $resp['data'] = preg_replace('/[\s]{2,}/','',$resp['data']);

    if (preg_match('#<h2>Acteurs, rôles, personnages</h2>(.*?)<div class="titlebar">\s*?<a class="anchor" id=\'actors\'></a>\s*?<h2>#is', $resp['data'], $Section))
    {

        # the big ones with image
        /*
        <div class="titlebar">
        <h3>
        <a href="/personne/fichepersonne_gen_cpersonne=5568.html">Liam Neeson</a>
        </h3>
        </div>
        <p>
        Rôle : Qui-Gon Jinn
        </p>
        <div class="spacer"></div>
        */
        preg_match_all('#<div class="titlebar">\s*?<h3>\s*?<a href="/personne/fichepersonne_gen_cpersonne=(\d+?).html">(.*?)</a>\s*?</h3>\s*?</div>\s*?<p>\s*Rôle : (.*?)\s*</p>#is', $Section[1], $ary, PREG_PATTERN_ORDER);

        $count = 0;
        while (isset($ary[1][$count]))
        {
            $cast .= $ary[2][$count]."::".$ary[3][$count]."::allocine:".$ary[1][$count]."\n";
            $count++;
        }

        # extended cast - without image
        /*
        <tr class="odd">
        <td>
        Shmi Skywalker
        </td>
        <td>
        <a href="/personne/fichepersonne_gen_cpersonne=14279.html">Pernilla August</a>
        </td>
        </tr>
        */
        preg_match_all('#<tr.*?>\s*?<td>\s*(.*?)\s*</td>\s*?<td>\s*?<a href="/personne/fichepersonne_gen_cpersonne=(\d+).html">(.*?)</a>\s*?</td>#si', $Section[1], $ary, PREG_PATTERN_ORDER);

        $count = 0;
        while (isset($ary[1][$count]))
        {
            $cast .= $ary[3][$count]."::".$ary[1][$count]."::allocine:".$ary[2][$count]."\n";
            $count++;
        }
        $data['cast'] = trim($cast);

    }



    /*
      Comments
    */
    // By default
    $data['language'] = 'french';

    // Another HTML page
    $resp = httpClient($allocineServer.'/film/fichefilm-'.$allocineID.'/technique/', 1);
    if (!$resp['success']) {$CLIENTERROR .= $resp['error']."\n";}

    // We remove all the multiples spaces and line breakers
	$resp['data'] = preg_replace('/[\s]{2,}/','',$resp['data']);

	 // Technical informations as comment
    preg_match('#<div class=\"rubric\">\s*?<div class=\"vpadding20b\">\s*(.*?)\s*</div>\s*?</div>#si', $resp['data'], $ary);
    if (!empty($ary[1]))
    {
        $data['comment'] = $ary[1];

        $data['comment'] = str_replace("Tourné en :", "Tourné en : ", $data['comment']);

        // Adding the release date in theater
        $data['comment'] = $data['comment'] . $release_date;

        // Search the language
        // Default language
        $data['language'] = "french";

        if (preg_match('#<p>\s*?<span class=\"bold\">Tourné en :</span>\s*(.*?)\s*</p>#si', $resp['data'], $ary))
        {
            $data['language'] = $ary[1];

            // Converting languages from french to english
            $map_languages = array(
            'Anglais'             =>  'english',
            'Français'            =>  'french',
            'Allemand'            =>  'german',
            'Italien'             =>  'italian',
            'Espagnol'            =>  'spanish',
            'Coréen'              =>  'Korean',
            'Roumain'             =>  'romanian',
            'Autre'               =>  'french',
            'Hindi'               =>  'hindi',
            'Arabe'               =>  'arabic',
            'Thaï'                =>  'thai',
            'Danois'              =>  'danish',
            'Suédois'             =>  'swedish',
            'Tchèque'             =>  'czech',
            'Japonais'            =>  'japanese',
            'Portugais'           =>  'portuguese',
            'Norvégien'           =>  'norwegian',
            'Bulgare'             =>  'bulgarian',
            'Grec'                =>  'greek',
            'Hongrois'            =>  'hungarian',
            'Turc'                =>  'turkish',
            'Islandais'			=>  'icelandic',
            'Polonais'			=>  'polish',
            'Russe'				=>  'russian',
            'Ukrainien'			=>  'ukrainian',
            'Serbe'				=>  'serbian',
            'Vietnamien'		    =>  'vietnamese',
            'Afrikaans' 		    =>  'afrikaans'
            );

            foreach($map_languages as $pattern => $map_lang)
            {
                $data['language'] = str_replace($pattern, $map_lang, $data['language']);
            }
        }
    }

	// Return the data collected
	return $data;
}

/**
 * Parses Actor-Details
 *
 * Find image and detail URL for actor, not sure if this can be made
 * a one-step process?  Completion waiting on update of actor
 * functionality to support more than one engine.
 *
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author                Andreas Goetz <cpuidle@gmx.de>
 * @param  string  $name  Name of the Actor
 * @return array          array with Actor-URL and Thumbnail
 */
function allocineActor($name, $actorid)
{
    global $allocineServer;

    if (empty ($actorid)) {
        return;
    }

    $url = 'http://www.allocine.fr/personne/fichepersonne_gen_cpersonne='.urlencode($actorid).'.html';
    $resp = httpClient($url, 1);

    $single = array();
    if (preg_match ('/src="([^"]+allocine.fr\/acmedia\/medias\/nmedia\/[^"]+\/[0-9]+\.jpg)[^>]+width="120"/', $resp['data'], $single)) {
        $ary[0][0]=$url;
        $ary[0][1]=$single[1];
        return $ary;
    } else {
	    return null;
    }
}

?>
