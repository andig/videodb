<?php
/**
 * Genre functions
 *
 * Contains functions for working with video genres
 * moved from functions.php to genres.php
 *
 * @package Core
 * @author  Andreas Gohr    <a.gohr@web.de>
 * @author  Andreas Götz    <cpuidle@gmx.de>
 * @version $Id: genres.php,v 1.14 2008/01/29 10:59:52 veal Exp $
 */

/**
 * Map movie genres to versions existing in db
 *
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   array $genres   A list of input genres
 * @return  array           The mapped genres result array
 */
function mapGenres($genres)
{
    global $dbgenres;

    // load genres from DB once
    if (empty($dbgenres)) 
    {
        $dbgenres = array();
        foreach (runSQL('SELECT id, name FROM '.TBL_GENRES.' ORDER BY name') as $row) 
        {
            $dbgenres[] = $row['name'];
        }
    }

    foreach ($genres as $in_genre)
    {
        $mapped_genre  = '';
        $mapped_percent  = 0;

		$in_genre = trim($in_genre);
		
        // direct match?
        if (in_array($in_genre, $dbgenres)) 
        {
            $gens[] = $in_genre;
        }
        else
        {
            // possible approximate match
            foreach ($dbgenres as $genre_name) 
            {
                // calculate similiarity and find best match
                $chars = similar_text($in_genre, $genre_name, $percent);
                if ($percent >= 50) 
                {
                    if (stristr($in_genre, $genre_name)) $percent += 10;
                    if ($percent > $mapped_percent) 
                    {
                        $mapped_genre   = $genre_name;
                        $mapped_percent = $percent;
                    }
                }
            }
            if ($mapped_genre) $gens[] = $mapped_genre;
        }
    }

    return array_unique($gens);
}

/**
 * returns the genreID for a given name from the 'genres' table
 *
 * @todo                  check if this can be moved to edit.php
 * @param  string  $name  the name of the genre
 * @return integer $genre the genre id
 */
function getGenreId($name)
{
	$name   = addslashes($name);
    $result = runSQL("SELECT id FROM ".TBL_GENRES." WHERE LCASE(name) = LCASE('".$name."')");
	return $result[0]['id'];
}

/**
 * retrieve genre ids/ genres of a video
 *
 * @param   integer $id     ID of the video
 * @param   boolean $names  include genre names in output
 * @return  array           genre id's OR
 * @return  array           associative array of genre ids and names
 */
function getItemGenres($id, $names = false)
{
    $genres = array();
    if (empty($id)) return $genres;
    
    $SELECT = 'SELECT genres.id, genres.name
                 FROM '.TBL_GENRES.' AS genres, '.TBL_VIDEOGENRE.' AS videogenre
                WHERE genres.id = videogenre.genre_id
                  AND videogenre.video_id = '.$id;
    $result = runSQL($SELECT);

    if ($names) return $result;
    
    foreach ($result as $row)
    {
        $genres[] = $row['id'];
    }    

    return $genres;
}

/**
 * save genres for a movie
 *
 * @todo                  check if this can be moved to edit.php
 * @param integer $id     ID of the video
 * @param array   $genres genre IDs
 */
function setItemGenres($id, $genres)
{
    if (count($genres))
    {
        runSQL('DELETE FROM '.TBL_VIDEOGENRE.' WHERE video_id = '.$id);
        $genres = array_unique($genres);

        foreach($genres as $genre)
        {
            runSQL('INSERT INTO '.TBL_VIDEOGENRE.' SET video_id = '.$id.', genre_id = '.$genre);
        }
    }
}

?>
