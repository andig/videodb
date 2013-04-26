<?php
/**
 * mass_add.php
 *
 * Form for batch importing entries based on imdb id's.
 *
 * Changelog:
 * vv0.000000.1e-100000000 Initial version by Branko Kokanovic
 * v0.2 seen attribute now stored in userseen table - Alrik Bronsema
 *
 * @todo Optional check for duplicate entries.
 *
 * @package Contrib
 * @author Branko Kokanovic
 * @author Alrik Bronsema <alrikb@gmail.com>
 * @version $Id: mass_add.php,v 1.2 2007/07/27 10:09:07 andig2 Exp $
 */

chdir('..');

require_once './core/functions.php';
require_once './core/genres.php';
require_once './core/custom.php';
require_once './core/security.php';
?>

<html>

<head>
    <title>Mass IMDB movie add</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>
<body>

<?php

//slightly modified VideoDB function that does pretty much the same thing
function InsertMovie($imdb_id,&$ret_title,$seen,$mediatype){
	
	$imdb_set_fields    = array('md5','title','subtitle','language','diskid','mediatype','comment','disklabel',
                            'imdbID','year','imgurl','director','actors','runtime','country','plot','filename',
                            'filesize','filedate','audio_codec','video_codec','video_width','video_height','istv',
                            'custom1','custom2','custom3','custom4');
			    
	//fetching all the data
	$imdbdata=engineGetData($imdb_id);
	if ($imdbdata['title']=='') return 0;
	//sorting needed things
	
	//genres--------------------------
        $genres = array();
        $gnames = $imdbdata['genres'];
        if (isset($gnames))
        {
            foreach ($gnames as $gname)
            {
                // check if genre is found- otherwise fail silently
                if (is_numeric($genre = getGenreId($gname)))
                {
                    $genres[] = $genre;
                }
            }
        }
	//--------------------------------
	
	//actors
	$actors = $imdbdata['cast'];
	
	//movie owner---------------------
	if (check_permission(PERM_WRITE, $_COOKIE['VDBuserid'])){
		$owner_id = $_COOKIE['VDBuserid'];
	}else{
		$owner_id=0;
	}
	//--------------------------------

	//cover
	$imgurl = $imdbdata['coverurl'];
	
    	// lookup all other fields
    	foreach (array_keys($imdbdata) as $name){
		if (in_array($name, array('coverurl', 'genres', 'cast', 'id'))) continue;
		$$name = $imdbdata[$name];
	}

	//year
	if (empty($year)) $year = '0000';
	
	// set owner
    	if (!empty($owner_id))
        	$SETS = 'owner_id = '.addslashes($owner_id);

	$imdbID=$imdb_id;
    	// update all fields according to list
   	foreach ($imdb_set_fields as $name){
        	// sanitize input
        	$$name = removeEvilTags($$name);

        	// make sure no formatting contained in basic data
        	if (in_array($name, array('title', 'subtitle'))){
            		$$name = trim(strip_tags($$name));

            		// string leading articles?
            		if ($config['removearticles']){
                		foreach ($articles as $article){
                    			if (preg_match("/^$article+/i", $$name)){
                        			$$name = trim(preg_replace("/(^$article)(.+)/i", "$2, $1", $$name));
                        			break;
                    			}
                		}
            		}
        	}

        	$SET = "$name = '".addslashes($$name)."'";

        	if (empty($$name)){
            		if (in_array($name, $db_null_fields))
               		$SET = "$name = NULL";
            	elseif (in_array($name, $db_zero_fields))
                	$SET = "$name = 0";
        	}
		
        	if ($SETS) $SETS .= ', ';
        	$SETS .= $SET;
    	}

	//inserting into database--------------------
	$INSERT = 'INSERT INTO '.TBL_DATA.' SET '.$SETS.', created = NOW()';
	//print_r($INSERT);
	//echo "<br><br>";
	$id = runSQL($INSERT);
	// save genres
        setItemGenres($id, $genres);
	//-------------------------------------------
	
	// insert userseen data
	$INSERTSEEN = 'INSERT INTO `userseen` (`video_id`, `user_id`) VALUES ('.$id.','.$owner_id.')';
	runSQL($INSERTSEEN);
	$ret_title=$title;
	return 1;
}

if ((isset($_POST['Submit'])) && (is_uploaded_file($_FILES['id_list']['tmp_name']))){
	//lets set time limit of we can
	set_time_limit(30000);
	$filename = $_FILES['id_list']['tmp_name'];
	echo "File uploaded. Starting fetching and inserting into database...<br>";
	ob_flush();
	flush();
	//get seen field from form
	$seen=isset($_POST['seen'])?1:0;
	//get mediatype id from form
	$mediatype=$_POST['mediatype'];
	$lines=file($filename);
	//iterate for all lines in uploaded file
	foreach( $lines as $line){
		//trim \n from end of line
		$line=rtrim($line);
		//if line is empty, go to the next line
		if ($line=="") continue;
		//if we import by title, first get id from best matcing title
		if ($_POST['import_type']=='title'){
			$all=engineSearch($line);
			$id=$all[0][id];
		}
		else{
			$id=$line;
		}
		if (strpos($id,"imdb:")===false){
			$id="imdb:".$id;
		}
		//try to insert movie
		if (InsertMovie($id,&$title,$seen,$mediatype)==1){
			echo "<a href=\"http://www.imdb.com/title/tt".substr($id,-7)."/\">$title</a> inserted ";
			if ($_POST['import_type']=='title'){
				echo "(additional info - title form file was $line)";
			}
			echo "<br>";
		}
		else{ //ops, error
			echo "Error while inserting ID - ".$id." (additional info - ";
			if ($_POST['import_type']=='title')
				echo "title from file was $line) <br>";
			else{
				echo "id from file was $line) <br>";
			}
		}
		ob_flush();
		flush();
	}
}

?>

<h1>Mass IMDB movie add v0.2</h1><br>
Ok, here is the thing:<br>

1. Browse for file with movie data (imdb ids or titles), hit Submit and pray:)
<br>
2. Script will try to set time limit, but this also depends from PHP configuration, so if script stops before all movies has been entered, check for PHP config.
<br>
3. File you need to browse is in form - one line per movie. So, if you want to import imdb ids, it should be something like:
<br>
<center>
0088247
<br>
0088248
<br>
or
<br>
imdb:0088247
<br>
imdb:0088248
<br>
</center>
and for titles:
<br>
<center>
terminator
<br>
terrible joe moran
<br>
</center>
In second case (import by titles), first matching title will be imported.
<br>
4. You must be logged to VideoDB as user who has write access (i.e. you can add movies on your own). All imported movies will be assigned to you. If you don't have write access or you are not logged, owner id will be 0.
<br>
5. Backup your data, make sure nothing can be lost, no responsibility, blah, blah...you already know it all.
<br>
<br>
<form action="" method="post" enctype="multipart/form-data" name="form" id="form">
<input name="import_type" type="radio" value="id" checked="checked" /> Import by ids
<br>
<input name="import_type" type="radio" value="title" /> Import by titles
<br>
<input type="checkbox" name="seen" value="1" /> Set all movies as seen
<br>
Media type:
<select name="mediatype">
<?php
	$first=false;
	$ret=runSQL("SELECT * FROM mediatypes");
	foreach($ret as $mediatype){
		if ($first==false){
			$first=true;
			echo "<option value=\"$mediatype[id]\" selected=\"selected\">$mediatype[name]</option>";
		}
		else{
			echo "<option value=\"$mediatype[id]\">$mediatype[name]</option>";
		}
	}
?>
</select>
<br>
Import list:<input type="file" name="id_list" />
<br>
<input type="submit" name="Submit" value="Mass add" />
</form>
<br>
</body>
</html>




