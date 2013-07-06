<?php
/**
 * Allocine & Clones common functions to access API
 *
 * check http://wiki.gromez.fr/dev/api/allocine_v3 for details of API
 *
 * @package Engines
 * @author  Loïc Devaux   <devloic@gmail.com>
 * @author  Douglas Mayle   <douglas@mayle.org>
 * @author  Andreas Gohr    <a.gohr@web.de>
 * @author  tedemo          <tedemo@free.fr>
 */


function createURL($route, $tokens) {

    global $ac_url_api,$ac_partner_id,$ac_partner_key;


	$sed = date("Ymd");
    $tokens[] = "partner=" . $ac_partner_id;
    $tokens[] = "count=25";
    $tokens[] = "page=1";
    $tokens[] = "format=json";
    sort($tokens);
    $tokensUrl = implode("&", $tokens);
    $sig = rawurlencode(base64_encode(sha1($ac_partner_key . $tokensUrl.'&sed='.$sed, true)));
    
    return $ac_url_api . $route . '?' . $tokensUrl . "&sed=" . $sed . "&sig=" . $sig;
}

/**
 * retrieve data with curl simulating an android user agent
 * @author  Loïc Devaux   <devloic@gmail.com>
 * @param string	url
 * @return mixed	json object
 */
function recup($url) {
    $curl = curl_init($url);
	
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$useragent="Dalvik/1.6.0 (Linux; U; Android 4.0.".rand(11, 20)."; SGH-T989 Build/IML74K)";
    curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
    $return = curl_exec($curl);
	curl_close($curl);
	//print_r(curl_getinfo($curl, CURLINFO_HTTP_CODE));
    return json_decode($return, true);
}



/**
 * Get Url to search for a movie 
 *
 * @author  Loïc Devaux   <devloic@gmail.com>
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string    The search string
 * @return  string    The search URL (GET)
 */
function searchUrl($title)
{
	$url = createURL("search", array("q=" . urlencode($title), "filter=movie"));
	return $url;
}

/**
 * Get url to visit for a specific movie
 *
 * @author  Loïc Devaux   <devloic@gmail.com>
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string    $id    The movie's external id
 * @return  string        The visit URL
 */
function contentUrl($id)
{
   $id = preg_replace('/^'.$GLOBALS['ac_idPrefix'].'/', '', $id);
   $contentUrl=str_replace('XXXX', $id, $GLOBALS['ac_server']);
   return $contentUrl;
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
function search($title)
{
   
    // The removeAccents function is added here
    $resp = recup(searchUrl($title));
 
        

    $data = array();

	if (isset($resp['feed']['movie'])){
		foreach ($resp['feed']['movie'] as $row)

	   
		{
			$info['id']     = $GLOBALS['ac_idPrefix'].$row['code'];

			$info['title']  = html_clean_utf8( isset($row['title']) ? $row['title']: $row['originalTitle']);
			
		   
			// add year (helpful in case of multiple matches)
			if (isset($row['productionYear'])) {$info['year'] = html_clean_utf8($row['productionYear']);}

			// add director (helpful in case of multiple matches)
			if (isset($row['castingShort']['directors'])) {
			  $info['director'] = html_clean_utf8($row['castingShort']['directors']);
			  
			}

			$data[]          = $info;
		}
	}
	
    return $data;
}

/**
 * Fetches the data for a given movie ID (ex: allocine:190299 , filmstarts:190299 )
 *
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Tiago Fonseca <t_r_fonseca@yahoo.co.uk>
 * @param   int   videodbRef
 * @return  array Result data
 */
function data($movieID)
{
    
	 
	 $movieIDshort = preg_replace('/^'.$GLOBALS['ac_idPrefix'].'/', '', $movieID);
		$urlAPI = createURL("movie", array("code=$movieIDshort","profile=large"));

		$movie = recup($urlAPI);
		$movie=$movie['movie'];

   
	$data   = array(); // result
    $ary    = array(); // temp

   $data['encoding']='utf-8';
   
   
   
    $data['id'] = $GLOBALS['ac_idPrefix'].$movieIDshort;
    $data['title']    = $movie['title'];
	//todo ?
    $data['subtitle'] = ''; //$movie['title'];
	

    /*
      Year
    */
    $data['year'] = $movie['productionYear'];


    /*
      Release Date
        added to the comments
    */
    $release_date = "";
    $release_date = $movie['release']['releaseDate'];

    /*
      Cover URL
    */
    $data['coverurl'] = $movie['poster']['href'];


    /*
      Runtime
    */
    #Durée : 02h13min

    $data['runtime']  = $movie['runtime']/60;


    /*
      Director
    */
    $data['director'] =$movie['castingShort']['directors'];

	$castMembers=$movie['castMember'];
	$cast='';
	
	foreach ($castMembers as $castMember){
	
		if ($castMember['activity']['code']==8001){
			$cast.= $castMember['person']['name'].'::'.$castMember['role'].'::'.$GLOBALS['ac_idPrefix'].$castMember['person']['code']."\n";
		}
	}
	$data['cast'] = $cast;
	

    /*
      Rating
	  todo : keep track of different ratings: page, users, press
    */
    $data['rating'] = round($movie['statistics']['userRating'],2);
	

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
	
	foreach ( $movie['nationality'] as $country){
		$data['country']  .=' '.$country['$'];
	}
     

    /*
      Plot
    */
    $data['plot'] = $movie['synopsis'];
		
   

   

		 
    foreach ($movie['genre'] as $genre){
		$data['genres'][] =allocineGenreId2videodbGenreId($genre['code']);
	}
	

    /*
      Original Title
    */
  
    $data['origtitle'] = $movie['originalTitle'];

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

	// Return the data collected
	
	return $data;
}

function allocineGenreId2videodbGenreId($alloGenreId){

$allo2vd=array("13025"=>"1",
"13001"=>"2",
"13026"=>"3",
"13006"=>"3",
"13005"=>"4",
"13018"=>"5",
"13007"=>"6",
"13008"=>"7",
"13054"=>"7",
"13036"=>"8",
"13012"=>"9",
"13009"=>"11",
"13013"=>"12",
"13024"=>"14",
"13021"=>"15",
"13023"=>"17",
"13014"=>"18",
"13019"=>"19",
"13043"=>"21",
"13027"=>"22",
"13015"=>"23",
"13050"=>"24",
"13016"=>"101",
"13047"=>"102",
"13040"=>"103",
"13002"=>"104",
"13049"=>"105",
"13017"=>"106",
"13010"=>"107",
"13022"=>"108",
"13033"=>"109",
"13031"=>"110",
"13048"=>"111",
"13028"=>"112",
"13051"=>"113",
"13053"=>"106"
);


$genre=$allo2vd[$alloGenreId];

return $genre;

}


function actorUrl($name, $id)
{	
	
	$actorUrl=str_replace('XXXX', $id, $GLOBALS['ac_actor']);
	
    return $actorUrl;
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
function actor($name, $actorid)
{


    if (empty ($actorid)) {
        return;
    }

   $url= createURL("person", array("code=$actorid","profile=small"));
	
	$actor = recup($url);
	
	
   
        $ary[0][1]=$actor['person']['picture']['href'];
        $ary[0][0]=$actor['person']['picture']['name'];
		
        return $ary;
   
}

?>
