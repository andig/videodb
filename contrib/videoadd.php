<?php
/**
 * Add movies on a file system to the DB
 *
 * Please change following variables
 *
 * movie_dirs      -- list of directories to search for a new files
 * update_missing  -- if set to TRUE , the movie with a file in DB but not on a disk will be set as wanted
 * update_moved    -- if set to TRUE , if file was moved to a different location on a disk ,the location in a DB will be updated to a new path
 * clean_file_name -- is a substring in a name of the file you want to be removed when setting movie name
 * skip_folders    -- a list of subfolders to be skipped
 *
 * @package pages
 * @author Alexander Mondshain <alex_mond@yahoo.com>
 * @version $Id: videoadd.php,v 1.2 2009/03/17 13:02:11 andig2 Exp $
 */
chdir('..');

require './core/functions.php';
#require './core/output.php';

?>

<html>

<head>
<title>Add and move files</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="description" content="VideoDB" />
<link rel="stylesheet" href="../<?php echo $config['style'] ?>" type="text/css" />
</head>

<body>
<b>Scaning...</b>
<br />
<?php


$movies = null;
$doubles = array();
$notfound = null;
$changed = null;

//NOTE : as a minimum change the following movie_dirs variable to point to a movi directory $movie_dirs = array("/moviedir1","/moviedir2);
$movie_dirs = array();
$movie_exts = "avi|mpg|bin|mpeg|ogm|bin|mkv";
$mplayer = '/usr/bin/mplayer';
// NOTE:update_missing, update missing files and associeted movies as wanted
// NOTE:update_moved. update moved files with a new file location
$update_missing = FALSE;
$update_moved = FALSE;


$clean_file_name="(.avi|.mkv|XVID|XviD|XViD|Xvid|CD1)";
$skip_folders="/^\.|^CVS|^lost|^photos|^music|^staff/";

function filesize_linux($file) {
	@exec("stat -c %s \"$file\"",$out,$ret);
	if ( $ret <> '0' ) return FALSE;
	else return($out[0]);
}

function filemtime_linux($file){
	@exec("stat -c %Y \"$file\"",$out,$ret);
	if ( $ret <> '0' ) return FALSE;
	else return($out[0]);
}

function recurse_dir($dir,$ext,$skip) {
	#  echo "In DIR $dir<br>";
	global $movies, $doubles;
	if ($dh = opendir($dir)) {
		while ($file = readdir($dh)){
			// Exclude all dot file, CVS and lost+found directories
			if ( preg_match($skip,$file) )
			continue;
			// If the file is a movie, we add it to the list
			if ( preg_match("/\.($ext)$/",$file) ){
				if (isset($movies[$file])) {
					array_push($doubles,$movies[$file]);
					array_push($doubles,"$dir/$file");
				}
				else
				$movies[$file] = "$dir/$file";
			}
			// If this is a directory we search it
			else if (is_dir("$dir/$file"))
			recurse_dir("$dir/$file",$ext,$skip);
		}
		closedir($dh);
	}
	return false;
}

// #$movie_exts=split('::',$config[movie_ext]);
if ( count($movie_dirs) == 0){
	echo "<b>PLEASE edit the script and set at least one folder in \$movie_dirs variable </b><br/>";
	exit;
}
foreach ($movie_dirs as $dir){
	recurse_dir($dir,$movie_exts,$skip_folders);
}

asort($movies);
$totalfs = sizeof($movies);

echo "<b>Total movie files found on disk:</b> $totalfs<br/>";

$SELECT = "SELECT id,filename FROM ".TBL_DATA." WHERE mediatype < 50 ORDER BY filename";
// # OLD $query = "select * from ".$MYSQL{'table'}." order by title";
$filenames = runSQL($SELECT);
$total = count($filenames);
echo "<b>Total movie files found in DB:</b>   $total <br/> ";

// #loading_message(localize_string("Comparing results."));
echo "Comparing results.<br>";
foreach ($filenames as $filename) {
	$name = split( "\/", $filename[filename] );
	// # The path does not match the path found in the database
	if ( $movies[end($name)] != $filename[filename] ) {
		if ( !isset($movies[end($name)]) ){
			$notfound[$filename['id']] = end($name);
			echo "<b>MISSING</b> - <a href='../show.php?id=".$filename['id']."'>".end($name)."</a><br />";
			if( $update_missing ){
				$UPDATE = "UPDATE  ".TBL_DATA." SET mediatype='50' WHERE id='".$filename['id']."'";
				runSQL($UPDATE);
			}
		}
		else {
			$changed[$filename['id']] = $movies[end($name)];
			echo "<b>MOVED</b> -  <a href='../show.php?id=".$filename['id']."'>".end($name)."</a><br />";
			if ($update_moved){
				$UPDATE = "UPDATE ".TBL_DATA." SET filename='".addslashes($movies[end($name)])."' WHERE id='".$filename['id']."'";
				runSQL($UPDATE);
			}
		}
	}
	else unset($movies[end($name)]);
}

echo "<b>New files found:</b>".sizeof($movies)."<br/>";

//remove all movies added last time and not updated with real information
$UPDATE = "DELETE FROM  ".TBL_DATA." WHERE mediatype='51'";
runSQL($UPDATE);

foreach ($movies as $movie){
	$name = split( "\/", $movie );
	if ( preg_match("/CD2/i",$movie) ){
		unset($movies[end($name)]);
	}
	if ( preg_match("/CD3/i",$movie) ){
		unset($movies[end($name)]);
	}
	if ( preg_match("/CD4/i",$movie) ){
		unset($movies[end($name)]);
	}
}
echo "<b>New files fond with no CD[2-3-4]:</b>".sizeof($movies)."<br/>";

echo "<table class='collapse'><tr>
<td>Filename</td>
<td>Title</td>
<td>Filesize</td>
<td>Audio Codec</td>
<td>Video Codec</td>
<td>Video Width</td>
<td>Video Height</td>
<td>Filedate</td>
</tr>";

foreach ($movies as $movie){

	$name = split( "\/", $movie );
	$newname=preg_replace($clean_file_name,"",$name);
	// remove extra characters
	$newname=str_replace("."," ",$newname);
	$newname=str_replace("-"," ",$newname);
	$newname=str_replace("_"," ",$newname);

	$filesize = filesize_linux($movies[end($name)]);

	if( preg_match("/CD1/i",$movies[end($name)] )){
		$newfile=preg_replace("/(CD)(\d)/i",'${1}2',$movies[end($name)]);
		$filesize_a = filesize_linux($newfile);
		$filesize = $filesize+$filesize_a;
		$newfile=preg_replace("/(CD)(\d)/i","${1}3",$movies[end($name)]);
		$filesize_a = filesize_linux($newfile);
		$filesize = $filesize+$filesize_a;
		$newfile=preg_replace("/(CD)(\d)/i","${1}4",$movies[end($name)]);
		$filesize_a = filesize_linux($newfile);
		$filesize = $filesize+$filesize_a;
  }

  $filedate=filemtime_linux($movies[end($name)]);

  $output = array();
  $return_var = 0;
  //  $command = $mplayer." -vo null -ao null -frames 0 -identify \"".$movies[end($name)]."\" 2>/dev/null";
  $filename_p=$movies[end($name)];
  $command = $mplayer." -vo null -ao null -frames 0 -identify \"".$filename_p."\"";

  exec($command, $output, $return_var);
  // parse mplayer output
  foreach ($output as $line){
  	trim($line);
  	if (ereg("^ID_",$line)){
  		if (ereg("^ID_VIDEO_FORMAT=(.*)",$line,$regs)){
  			$video_codec = $regs[1];
  			if ($video_codec == '0x10000001')   $video_codec = 'MPEG1' ;
  			if ($video_codec == '0x10000002')   $video_codec = 'MPEG2' ;
  			if ($video_codec == 'MPG4')         $video_codec = 'MPEG4';
  			if ($video_codec == 'DIV3')         $video_codec = 'DivX3';
  			if ($video_codec == 'div3')         $video_codec = 'DivX3';
  			if ($video_codec == 'DIV4')         $video_codec = 'DivX4';
  			if ($video_codec == 'DIVX')         $video_codec = 'DivX4';
  			if ($video_codec == 'divx')         $video_codec = 'DivX4';
  			if ($video_codec == 'DX50')	    $video_codec = 'DivX5';
  			if ($video_codec == 'XVID')         $video_codec = 'XviD';
  		}
  		elseif (ereg("^ID_VIDEO_WIDTH=(.*)",$line,$regs)){
  			$video_width = $regs[1];
  		}
  		elseif (ereg("^ID_VIDEO_HEIGHT=(.*)",$line,$regs)){

  			$video_height = $regs[1];
  		}
  		elseif (ereg("^ID_AUDIO_CODEC=(.*)",$line,$regs)){
  			$audio_codec = $regs[1];
  			if ($audio_codec == 'mad')      $audio_codec = 'MP3';
  			if ($audio_codec == 'mp3')      $audio_codec = 'MP3';
  			if ($audio_codec == 'a52')      $audio_codec = 'AC3' ;
  			if ($audio_codec == 'ffvorbis') $audio_codec = 'Vorbis';
  			if ($audio_codec == 'pcm')      $audio_codec = 'PCM';
  			if ($audio_codec == 'ffwmav2')  $audio_codec = 'WMA2';
  			if ($audio_codec == 'ffwmav1')  $audio_codec = 'WMA1' ;	# just a guess, needs confirmation
  		}
  		//        elsif ($line =~ m/^ID_LENGTH=(.*)/)
  		//        {
  		//           $runtime = $1;
  		//           $runtime = sprintf("%d", $runtime / 60);
  		//        }
  	}
  }
  echo "<tr>
<td>".$movies[end($name)]."</td>
<td>".end($newname)."</td>
<td>$filesize</td>
<td>$audio_codec</td>
<td>$video_codec</td>
<td>$video_width</td>
<td>$video_height</td>
<td>$filedate</td>
</tr>";

  $UPDATE = "INSERT ".TBL_DATA." SET filename='".addslashes($movies[end($name)])."'".",
                          mediatype='51'".",
                          title='".addslashes(end($newname))."', 
                          filesize=$filesize,
                          audio_codec = '$audio_codec',
                          video_codec = '$video_codec',
                          video_width = $video_width,
                          video_height = $video_height,
                          filedate = FROM_UNIXTIME($filedate)";

  runSQL($UPDATE);

}
echo "</table>";


$SELECT = "SELECT id,title FROM  ".TBL_DATA." WHERE mediatype='51' ORDER BY title";
$filenames = runSQL($SELECT);

echo "<br><br>New movies added ( click to edit ):<br>";
foreach ($filenames as $filename) {
	echo "<a href='../edit.php?id=".$filename['id']."'>".$filename[title]."</a><br />";
}

?>
<b>All Done</b>
</body>
</html>
