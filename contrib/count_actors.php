<?php
/**
 * Produce a count of all actors in the database
 * 
 * Code structure based on add_recommended_movies.php by Andreas Goetz
 *
 * @package Contrib
 * @author  Constantinos Neophytou   <jaguarcy@gmail.com>
 * @version $Id: count_actors.php,v 1.4 2007/09/08 09:17:16 andig2 Exp $
 */

// move out of contrib for includes
chdir('..');

require_once './core/functions.php';
?>

<html>

<head>
	<title>List actor counts</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<meta name="description" content="VideoDB" />
</head>

<body>

<?

if ($submit)
{	
	// validate form data
	$maxcount = (is_numeric($maxcount)) ? (int) $maxcount : 0;
	
	// Build query - ignore duplicate imdbID fields
    $query = 'SELECT DISTINCT `imdbID`, `director`, `actors` FROM '.TBL_DATA;
    if (empty($wishlist)) $query .= ' WHERE mediatype != '.MEDIA_WISHLIST;
	
	$result = runSQL($query);
	
	$includeDirectors = !empty($director);

	$actors = array();  // Actor array
	
	// If we are counting the directors separately than the actors, create the array
	if (empty($notseparate) && $includeDirectors) {
		$directors = array();
		$displayDirectorCount = true;
	} else {
		// Otherwise, use the actor array for directors as well.
		$directors = &$actors;
		$displayDirectorCount = false;
	}
	
	
	foreach ($result as $row) 
    {
		$cast = split("\r?\n", $row['actors']);
		
		// Counting actors
		foreach ($cast as $actor) {
			$actorary = split('::', $actor);
			if (!isset($actors[$actorary[0]])) {
				// Use actor name as array index so all counts are attributed to the same name
				$actors[$actorary[0]] = 0;
			}
			$actors[$actorary[0]]++;
		}
		
		// Director count
		if ($includeDirectors) {
			if (!isset($directors[$row['director']])) {
				$directors[$row['director']] = 0;
			}
			$directors[$row['director']]++;
		}
	}
	
	// Sort array by actor appearances in reverse order (high to low)
	arsort($actors);	

	$i = 1;
	foreach ($actors as $key=>$val) 
    {
		if ($val > $maxcount) 
        {			
			// Build name search url
			$url = "../search.php?q=%22" . htmlentities(urlencode($key)) . "%22&isname=Y";
			
			// Text for director counts
			$dirText = '';
			if ($displayDirectorCount && $directors[$key]) {
				$dirText = ", " . $directors[$key] . " director entries";
			}
			
			echo "$i - <a href='$url'>$key</a>: $val actor entries" . $dirText . "<br />";
			$i++;
		}
	}
} else {
?>
	<form action="<?php echo $_SERVER['PHP_SELF']?>">
		
		<label for="maxcount">
			Only show names with more than
			<input type="text" name="maxcount" id="maxcount" value="3" size="2" maxlength="2" />
			actor entries
		</label>
		<br />
		<label for="director">
			<input type="checkbox" name="director" id="director" value="1" checked />
			Count director entries (will appear next to the actor entries, will not be sorted)
		</label>
		<br />
		<label for="notseparate">
			<input type="checkbox" name="notseparate" id="notseparate" value="1" />
			Count director entries as "actor" (i.e. don't separate them)
		</label>
		<br />
		<label for="wishlist">
			<input type="checkbox" name="wishlist" id="wishlist" value="1" />
			Include wishlist
		</label>
		<br />
		<br />
		<input type="submit" name="submit" value="List" />
	</form>
	<br />
	<small>Note: Duplicate movie entries (determined by imdbID) will not be counted.</small>
<?
}
?>

</body>
</html>
