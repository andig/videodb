<?php
/**
 * allocine.fr JSON API Parser
 *
 * retrieves data from the allocine.fr
 *
 * @package Engines
 * @author  Loïc Devaux   <devloic@gmail.com>
 */

require_once dirname(__FILE__).'/inc/allocine_lib.inc.php';
 
$GLOBALS['ac_url_api']='http://api.allocine.fr/rest/v3/';
$GLOBALS['ac_partner_id']='100043982026';
$GLOBALS['ac_partner_key']='29d185d98c984a359e6e6f26a0474269';
$GLOBALS['ac_idPrefix']='allocine:';
$GLOBALS['ac_server']= 'http://www.allocine.fr/film/fichefilm_gen_cfilm=XXXX.html';
$GLOBALS['ac_actor']= 'http://www.allocine.fr/personne/fichepersonne_gen_cpersonne=XXXX.html';





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
 * Get Url to search Allocine for a movie
 *
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string    The search string
 * @return  string    The search URL (GET)
 */
function allocineSearchUrl($title)
{
	return searchUrl($title);
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
	
   return contentUrl($id);
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

	
    return search($title);
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
	$data=data($imdbID);
	return $data;
}


/**
 * Get Url to visit allocine for a specific actor
 *
 * @param   string  $name   The actor's name
 * @param   string  $id The actor's external id
 * @return  string      The visit URL
 */
function allocineActorUrl($name, $id)
{
   
    $actorUrl=actorUrl($name, $id);

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
function allocineActor($name, $actorid)
{
    return actor($name, $actorid);
}

?>
