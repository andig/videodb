<?php
/**
 * Refresh whole IMDB info (rund from command line)
 *
 * This script should be executed from command line
 * Look for the NOTE: comments to change behavior
 * The script should be placed under videodb/contrib and run as "php fetch_imdb_all.php"
 *
 * @package Contrib
 * @author  Alex Mondshain <alex_mond@yahoo.com>
 */

chdir('..');
require_once './engines/engines.php';
require_once './core/functions.php';
require_once './core/genres.php';
require_once './core/custom.php';
require_once './core/edit.core.php';

//Id is imdb id
//lookup is either 1 (add missing) or 2 (overwrite)
function FetchSaveMovie($id,$lookup)
{
	$debug = 0;

	$video = runSQL('SELECT * FROM '.TBL_DATA.' WHERE id = '.$id);
	// get fields (according to list) from db to be saved later

	if ($debug){
		echo "\n=================== Video DB Data ============================\n";
		print_r( $video[0]);
		echo "\n=================== Video DB Data ============================\n";
	}

	$imdbID = $video[0]['imdbID'];
	echo "Movie/imdb -- ".$video[0]['title']."/".$video[0]['imdbID']."\n";


	if (empty($imdbID)) {
		echo "No imdbID\n";
		return;
	}

	if (empty($engine)) $engine = engineGetEngine($imdbID);

	if ($debug) {
		echo "IMDBID = $imdbID, engine = $engine\n";
	}

	$imdbdata = engineGetData($imdbID, $engine);
	# removed due to performance issues of is_utf8
	// fix erroneous IMDB encoding issues
	if (!is_utf8($imdbdata)) {
		echo "Applying encoding fix\n";
		$imdbdata = fix_utf8($imdbdata);
	}

	if (empty($imdbdata[title])) {
		echo "Fetch failed , try again...\n";
		$imdbdata = engineGetData($imdbID, $engine);
	}

	if (empty($imdbdata[title])) {
		echo "Fetch failed again , next movie";
		return;
	}

	if ($debug) {
		echo "\n===================  IMDB Data ============================\n";
		print_r($imdbdata);
		echo "\n===================  IMDB Data ============================\n";
	}

	if (!empty($imdbdata[title])) {
		//
		// NOTE: comment out any of the following lines if you do not want them updated
		//
//		$video[0][title]=$imdbdata[title];
//		$video[0][subtitle]=$imdbdata[subtitle];
		$video[0][year]=$imdbdata[year];
		$video[0][imgurl]=$imdbdata[coverurl];
		$video[0][runtime]=$imdbdata[runtime];
		$video[0][director]=$imdbdata[director];
		$video[0][rating]=$imdbdata[rating];
		$video[0][country]=$imdbdata[country];
//		$video[0][language]=$imdbdata[language];
		$video[0][actors]=$imdbdata[cast];
		$video[0][plot]=$imdbdata[plot];
	}

	if (count($genres) == 0 || ($lookup > 1))
	{
		$genres = array();
		$gnames = $imdbdata['genres'];
		if (isset($gnames))
		{
			foreach ($gnames as $gname)
			{
				// check if genre is found- otherwise fail silently
				if (is_numeric($genre = getGenreId($gname))) {
					$genres[] = $genre;
				} else {
					echo "MISSING GENRE $gname\n";
				}
			}
		}
	}

	// custom filds , not working for now
	for ($i=1; $i<=4; $i++)
	{
		$custom = 'custom'.$i;
		$type   = $config[$custom.'type'];
		if (!empty($type))
		{
			// copy imdb data into corresponding custom field
			$video[0][$custom]=$imdbdata[$type];
			echo "CUSTOM $custom $type = $imdbdata[$type]\n";
		}
	}

	//  -------- SAVE

	$SETS = prepareSQL($video[0]);

	if ($debug) {
		echo "\n===================  Final Data ============================\n";
		echo "SETS = $SETS \n";
		echo  "\n===================  Final Data ============================\n";
	}

	$id = updateDB($SETS, $id);

	// save genres
	setItemGenres($id, $genres);

	// set seen for currently logged in user
	set_userseen($id, $seen);
}

// NOTE: Edit this line if you want to update specific set of files by adding WHERE statment
$allids = runSQL('SELECT id FROM '.TBL_DATA);

foreach ($allids as $id) {
	#if ($id['id'] <= 2113) continue;
	echo "Updating ID:".$id['id']."\n";
	FetchSaveMovie($id['id'],3);
}
// for testing
// FetchSaveMovie(1,3);

?>
