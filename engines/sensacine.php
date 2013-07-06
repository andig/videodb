<?php
/**
 * Sensacine Parser
 *
 * retrieves data from the sensacine.com (spanish)
 *
 * @package Engines
 * @author  Loic Devaux   <devloic@gmail.com>
 */

require_once dirname(__FILE__).'./inc/allocine_lib.inc.php';
 
$GLOBALS['ac_url_api']='http://api.sensacine.com/rest/v3/';
$GLOBALS['ac_partner_id']='100058896081';
$GLOBALS['ac_partner_key']='1d6b9b4006444e19bc788350342a9c66';
$GLOBALS['ac_idPrefix']= 'sensacine:';
$GLOBALS['ac_server']= 'http://www.sensacine.com/peliculas/pelicula-XXXX/';
$GLOBALS['ac_actor']= 'http://www.sensacine.com/actores/actor-XXXX/';





/**
 * Get meta information about the engine
 *
 * @todo    Include image search capabilities etc in meta information
 */

function sensacineMeta()
{
    return array('name' => 'Sensacine (es)');
}



/**
 * Get Url to search Sensacine for a movie
 *
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string    The search string
 * @return  string    The search URL (GET)
 */
function sensacineSearchUrl($title)
{
	
		return searchUrl($title);

}

/**
 * Get Url to visit Sensacine for a specific movie
 *
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string    $id    The movie's external id
 * @return  string        The visit URL
 */
function sensacineContentUrl($id)
{
   
	return contentUrl($id);
}

/**
 * Get Url to visit sensacine for a specific actor
 *
 * @param   string  $name   The actor's name
 * @param   string  $id The actor's external id
 * @return  string      The visit URL
 */
function sensacineActorUrl($name, $id)
{
   
    $actorUrl=actorUrl($name, $id);

    return $actorUrl;
}


/**
 * Search a Movie
 *
 * Searches for a given title on Sensacine and returns the found links in
 * an array
 *
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Tiago Fonseca <t_r_fonseca@yahoo.co.uk>
 * @author  Charles Morgan <cmorgan34@yahoo.com>
 * @param   string    The search string
 * @return  array     Associative array with id and title
 */
function sensacineSearch($title)
{

        return search($title);

}

/**
 * Fetches the data for a given Sensacine-ID
 *
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author  Tiago Fonseca <t_r_fonseca@yahoo.co.uk>
 * @param   int   imdb-ID
 * @return  array Result data
 */
function sensacineData($imdbID)
{
    	return data($imdbID);

}

/**
 * Parses Actor-Details
 *
 * Find image and detail URL for actor
 * @author  Douglas Mayle <douglas@mayle.org>
 * @author                Andreas Goetz <cpuidle@gmx.de>
 * @param  string  $name  Name of the Actor
 * @return array          array with Actor-URL and Thumbnail
 */
function sensacineActor($name, $actorid)
{
     return actor($name, $actorid);
}

?>
