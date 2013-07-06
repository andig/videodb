<?php
/**
 * filmstarts.de JSON API Parser
 *
 * retrieves data from the filmstarts.de (german)
 *
 * @package Engines
 * @author  Loic Devaux   <devloic@gmail.com>
 */

require_once dirname(__FILE__).'./inc/allocine_lib.inc.php';
 
$GLOBALS['ac_url_api']='http://api.filmstarts.de/rest/v3/';
$GLOBALS['ac_partner_id']='100076894460';
$GLOBALS['ac_partner_key']='b9a1eb52eded459a99d7ba3b9a5d2245';
$GLOBALS['ac_server']='http://www.filmstarts.de/kritiken/XXXX.html';
$GLOBALS['ac_idPrefix']= 'filmstarts:';
$GLOBALS['ac_actor']= 'http://www.filmstarts.de/personen/XXXX-.html';



/**
 * Get meta information about the engine
 *
 * @todo    Include image search capabilities etc in meta information
 */

function filmstartsMeta()
{
    return array('name' => 'Filmstarts (de)');
}



/**
 * Get Url to search Filmstarts for a movie
 *
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string    The search string
 * @return  string    The search URL (GET)
 */
function filmstartsSearchUrl($title)
{
	
		return searchUrl($title);

}

/**
 * Get Url to visit Filmstarts for a specific movie
 *
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string    $id    The movie's external id
 * @return  string        The visit URL
 */
function filmstartsContentUrl($id)
{
   
	return contentUrl($id);
}


/**
 * Search a Movie
 *
 * Searches for a given title on Filmstarts and returns the found links in
 * an array
 *
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Tiago Fonseca <t_r_fonseca@yahoo.co.uk>
 * @author  Charles Morgan <cmorgan34@yahoo.com>
 * @param   string    The search string
 * @return  array     Associative array with id and title
 */
function filmstartsSearch($title)
{
		$result=search($title);
		return $result;

}

/**
 * Fetches the data for a given Filmstarts-ID
 *
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Tiago Fonseca <t_r_fonseca@yahoo.co.uk>
 * @param   int   imdb-ID
 * @return  array Result data
 */
function filmstartsData($imdbID)
{
    	$data=data($imdbID);
		
		return $data;

}

/**
 * Get Url to visit filmstarts for a specific actor
 *
 * @param   string  $name   The actor's name
 * @param   string  $id The actor's external id
 * @return  string      The visit URL
 */
function filmstartsActorUrl($name, $id)
{
  
    //bug with filmstarts.de , wrong actor ID so we remove the code so that the videodburl doesn't get generated
	//$actorUrl=actorUrl($name, $id);
	$actorUrl=	urlencode('http://www.filmstarts.de/suche/5/?q='.$name);
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
function filmstartsActor($name, $actorid)
{
     $actor = actor($name, $actorid);
	 return $actor;
}

?>
