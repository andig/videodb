<?php
/**
 * Add movies on a file system to the DB
 *
 * Pre-requsisions:
 * mplayer:
 * Test if installed with this command: mplayer -v
 * If not installed use this command (Ubuntu) to install: sudo apt install mplayer
 *
 * Please change following variables
 *
 * movie_dirs      -- list of directories to search for a new files
 * update_missing  -- if set to TRUE, the movie with a file in DB but not on a disk will be set as wanted
 * update_moved    -- if set to TRUE, if file was moved to a different location on a disk, the location in a DB will be updated to a new path
 * clean_file_name -- is a substring in a name of the file you want to be removed when setting movie name
 * skip_folders    -- a list of subfolders to be skipped
 *
 * @package pages
 * @author Alexander Mondshain <alex_mond@yahoo.com>
 * @author Alexander Mondshain <klaus_edwin@hotmail.com>
 * @version $Id: videoadd.php,v 1.3 2018/09/23 13:02:11 kec2 Exp $
 */
chdir('..');

require './core/functions.php';

// Movies on the file system.
// File name is key and full path is value.
$moviesFS = array();
// duplicate file on file system.
// File name is key and full path is value.
$doubles = array();

// NOTE : as a minimum change the following movie_dirs variable to point to a movie directory $movieDirs = array("/moviedir1","/moviedir2);
$movieDirs = array();
$movieExts = '(avi|mpg|bin|mpeg|ogm|bin|mkv|m2ts|iso)';
$cleanFileName = '(.avi|.mkv|.iso|.m2ts|XVID|XviD|XViD|Xvid|CD1)';
$skipFolders = '/^\.|^CVS|^lost|^photos|^music|^staff/';

// NOTE:update_missing, update missing files and associeted movies as wanted
// NOTE:update_moved. update moved files with a new file location
$update_missing = false;
$update_moved = false;

/**
 * Check if MPlayer is installed.
 *
 * @return boolean True if it is installed.
 */
function isMPlayerInstalled()
{
    $out = null;
    $ret = - 1;
    @exec("mplayer -v", $out, $ret);
    return $ret == '0';
}

/**
 * If a movies consits of multiple file then all but one will be removed.
 * fx. Thor_CD1.avi, Thor_CD2.avi and Thor_CD3.avi => Thor_CD1.avi.
 */
function removeCDNumbers($moviesFS)
{
    foreach ($moviesFS as $movie) {
        $filePathParts = preg_split("/\//", $movie);
        $fileName = end($filePathParts);

        if (preg_match("/CD2/i", $movie)) {
            unset($moviesFS[$fileName]);
        } else if (preg_match("/CD3/i", $movie)) {
            unset($moviesFS[$fileName]);
        } else if (preg_match("/CD4/i", $movie)) {
            unset($moviesFS[$fileName]);
        }
    }
    return $moviesFS;
}

function getFileSizeLinux($file)
{
    $out = null;
    $ret = - 1;
    @exec("stat -c %s \"$file\"", $out, $ret);
    if ($ret != '0') {
        return FALSE;
    } else {
        return ($out[0]);
    }
}

function getFileTimeLinux($file)
{
    $out = null;
    $ret = - 1;
    @exec("stat -c %Y \"$file\"", $out, $ret);
    if ($ret != '0') {
        return FALSE;
    } else {
        return ($out[0]);
    }
}

/**
 *
 * Get all movie files requrcily starting from \$dir.
 *
 * @param String $dir
 *            The starting point.
 * @param String $ext
 *            Files with these file extensions are or included in the result.
 * @param String $skip
 *            Directories to skip.
 */
function recurseDir($dir, $ext, $skip)
{
    // echo "In DIR $dir<br>";
    global $moviesFS, $doubles;
    if ($dh = opendir($dir)) {
        while ($file = readdir($dh)) {
            // Exclude all dot file, CVS and lost+found directories
            if (preg_match($skip, $file)) {
                continue;
            }

            // If the file is a movie, we add it to the list
            if (preg_match("/\.$ext$/", $file)) {
                if (isset($moviesFS[$file])) {
                    array_push($doubles, $moviesFS[$file]);
                    array_push($doubles, "$dir/$file");
                } else {
                    $moviesFS[$file] = "$dir/$file";
                }
            }

            // If this is a directory we search it
            if (is_dir("$dir/$file")) {
                recurseDir("$dir/$file", $ext, $skip);
            }
        }
        closedir($dh);
    }
}

/**
 * Get combined file size of a movies with multiple files.
 * fx. Thor_CD1.avi (100k), Thor_CD2.avi (200k) and Thor_CD3.avi (150k) => 450k.
 *
 * @param string $filePath
 *            Path to the first file.
 * @return integer The size of the file(s).
 */
function getFileSize($filePath)
{
    $fileSize = getFileSizeLinux($filePath);

    if (preg_match("/CD1/i", $filePath)) {
        $newfile = preg_replace("/(CD)(\d)/i", '${1}2', $filePath);
        $fileSize += getFileSizeLinux($newfile);

        $newfile = preg_replace("/(CD)(\d)/i", '${1}3', $filePath);
        $fileSize += getFileSizeLinux($newfile);

        $newfile = preg_replace("/(CD)(\d)/i", '${1}4', $filePath);
        $fileSize += getFileSizeLinux($newfile);
    }

    return $fileSize;
}

/**
 * Get metadata of a movie.
 * Data include video codec, audio codec, video width and video height.
 * MPlayer is used to retrive these data.
 *
 * @param String $filePath
 *            File path to the movie.
 * @return array An assosiative array with keys: videoCodec, audioCodec, videoHeight and videoWidth.
 */
function getMetadata($filePath)
{
    $metadata = array();
    $command = "mplayer -vo null -ao null -frames 3 -identify \"$filePath\"";
    // echo "command: ".$command."<br>";
    $output = array();
    $return_var = 0;

    $match = array();
    exec($command, $output, $return_var);
    // parse mplayer output
    foreach ($output as $line) {
        trim($line);

        if (preg_match("/ID_VIDEO_CODEC=(.*)/", $line, $match)) {
            $videoCodec = strtolower($match[1]);
            switch ($videoCodec) {
                case 'ffh264':
                    $metadata['videoCodec'] = 'H.264';
                    break;
                case 'ffvc1':
                    $metadata['videoCodec'] = 'VC1';
                    break;
                case '0x10000001':
                    $metadata['videoCodec'] = 'MPEG1';
                    break;
                case 'ffmpeg2':
                    $metadata['videoCodec'] = 'MPEG2';
                    break;
                case '0x10000002':
                    $metadata['videoCodec'] = 'MPEG2';
                    break;
                case 'mpg4':
                    $metadata['videoCodec'] = 'MPEG4';
                    break;
                case 'div3':
                    $metadata['videoCodec'] = 'DivX3';
                    break;
                case 'div4':
                    $metadata['videoCodec'] = 'DivX4';
                    break;
                case 'divx':
                    $metadata['videoCodec'] = 'DivX4';
                    break;
                case 'dx50':
                    $metadata['videoCodec'] = 'DivX5';
                    break;
                case 'xvid':
                    $metadata['videoCodec'] = 'XviD';
                    break;
                default:
                    $metadata['videoCodec'] = 'Unknown';
                    echo 'Unknown Video Codec: ' . $match[1] . '<br>';
            }
        } else if (preg_match("/ID_AUDIO_CODEC=(.*)/", $line, $match)) {
            $audioCodec = strtolower($match[1]);
            switch ($audioCodec) {
                case 'fftruehd':
                    $metadata['audioCodec'] = 'Dolby TrueHD';
                    break;
                case 'ffdca':
                    $metadata['audioCodec'] = 'DTS-HD Master Audio';
                    break;
                case 'ffac3':
                    $metadata['audioCodec'] = 'AC-3';
                    break;
                case 'a52':
                    $metadata['audioCodec'] = 'AC3';
                    break;
                case 'pcm':
                    $metadata['audioCodec'] = 'Uncompressed PCM';
                    break;
                case 'dvdpcm':
                    $metadata['audioCodec'] = 'Uncompressed DVD/VOB LPCM';
                    break;
                case 'mad':
                    $metadata['audioCodec'] = 'MP3';
                    break;
                case 'mp3':
                    $metadata['audioCodec'] = 'MP3';
                    break;
                case 'ffvorbis':
                    $metadata['audioCodec'] = 'Vorbis';
                    break;
                case 'ffwmav1':
                    $metadata['audioCodec'] = 'WMA1';
                    break;
                case 'ffwmav2':
                    $metadata['audioCodec'] = 'WMA2';
                    break;
                default:
                    $metadata['audioCodec'] = 'Unknown';
                    echo 'Unknown Audio Codec: ' . $match[1] . '<br>';
            }
        } else if (preg_match("/ID_VIDEO_WIDTH=(.*)/", $line, $match)) {
            $metadata['videoWidth'] = $match[1];
        } else if (preg_match("/ID_VIDEO_HEIGHT=(.*)/", $line, $match)) {
            $metadata['videoHeight'] = $match[1];
        }
    }

    return $metadata;
}
?>

<html>
<head>
<title>Add and move files</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="description" content="VideoDB" />
<link rel="stylesheet" href="../<?php echo $config['style'] ?>"
	type="text/css" />
</head>
<body>
<?php

if (!isMPlayerInstalled()) {
    echo '<h1 style="color:red;">mplayer is not installed! Please install it.</h1><br>';
    exit();
}

if (count($movieDirs) == 0) {
    echo '<h1 style="color:red;">PLEASE edit the script and set at least one folder in \$movie_dirs variable </h1><br>';
    exit();
}

// get all files. Result is in $moviesFS and $doubles;
foreach ($movieDirs as $dir) {
    recurseDir($dir, $movieExts, $skipFolders);
}

// Get all movies that are not on the whishlist (50) or inserted by this tool (51)
$SELECT = 'SELECT id, filename FROM ' . TBL_DATA . ' WHERE mediatype < 50 ORDER BY filename';
$rows = runSQL($SELECT);

echo '<h3>Scanning...</h3>';
echo '<b>Total movie files found on disk:</b> ' . sizeof($moviesFS) . '<br>';
echo '<b>Total movie files found in DB:</b> ' . count($rows) . '<br><br>';
echo '<h3>Comparing results.</h3>';

foreach ($rows as $row) {
    $filePathParts = preg_split("/\//", $row['filename']);
    $fileName = end($filePathParts);
    $id = $row['id'];

    // The path does not match the path found in the database
    if ($moviesFS[$fileName] != $row['filename']) {
        if (! isset($moviesFS[$fileName])) {
            echo "<b>MISSING</b> - <a href='../show.php?id={$id}'>{$fileName}</a><br>";
            if ($update_missing) {
                $UPDATE = "UPDATE " . TBL_DATA . " SET mediatype='" . MEDIA_WISHLIST . "' WHERE id='{$id}'";
                runSQL($UPDATE);
            }
        } else {
            echo "<b>MOVED</b> -  <a href='../show.php?id={$id}'>{$fileName}</a><br>";
            if ($update_moved) {
                $UPDATE = "UPDATE " . TBL_DATA . " SET filename='" . addslashes($moviesFS[$fileName]) . "' WHERE id='{$id}'";
                runSQL($UPDATE);
            }
        }
    } else {
        unset($moviesFS[$fileName]);
    }
}

echo '<br><b>New files found:</b> ' . sizeof($moviesFS) . '<br>';
asort($moviesFS);
$moviesFS = removeCDNumbers($moviesFS);
echo '<b>New files found with no CD[2-3-4]:</b> ' . sizeof($moviesFS) . '<br>';

// remove all movies added last time and not updated with real information
// mediatype 51 indicates that the movie was inserted/modified by this tool.
$UPDATE = "DELETE FROM " . TBL_DATA . " WHERE mediatype='51'";
runSQL($UPDATE);

echo '<h3>Data on movies to add.</h3>';
echo "<table class='collapse'>
          <tr>
              <th>File Name</th>
              <th>Title</th>
              <th>File Size</th>
              <th>Audio Codec</th>
              <th>Video Codec</th>
              <th>Video Width</th>
              <th>Video Height</th>
              <th>File Date</th>
          </tr>";

$currentUserId = get_current_user_id();

foreach ($moviesFS as $movie) {
    $filePathParts = preg_split("/\//", $movie);
    $fileName = end($filePathParts);
    $title = preg_replace($cleanFileName, '', $fileName);
    $title = str_replace('_', ' ', $title);

    $filePath = $moviesFS[end($filePathParts)];
    $fileSize = getFileSize($filePath);
    $fileDate = getFileTimeLinux($filePath);

    $metadata = getMetadata($filePath);
    $audioCodec = $metadata['audioCodec'];
    $videoCodec = $metadata['videoCodec'];
    $videoWidth = $metadata['videoWidth'];
    $videoHeight = $metadata['videoHeight'];

    echo "<tr>
              <td>$filePath</td>
              <td>$title</td>
              <td>$fileSize</td>
              <td>$audioCodec</td>
              <td>$videoCodec</td>
              <td>$videoWidth</td>
              <td>$videoHeight</td>
              <td>$fileDate</td>
          </tr>";

    $UPDATE = "INSERT " . TBL_DATA . " SET filename='" . addslashes($filePath) . "',
        mediatype = 51,
        title = '$title',
        filesize = $fileSize,
        audio_codec = '$audioCodec',
        video_codec = '$videoCodec',
        video_width = $videoWidth,
        video_height = $videoHeight,
        owner_id = $currentUserId,
        filedate = FROM_UNIXTIME($fileDate)";

    runSQL($UPDATE);
}
echo '</table>';

$SELECT = "SELECT id, title FROM " . TBL_DATA . " WHERE mediatype='51' ORDER BY title";
$rows = runSQL($SELECT);

echo '<br><br><h3><New movies added (click to edit):</h3>';
foreach ($rows as $row) {
    echo "<a href='../edit.php?id={$row['id']}'>{$row[title]}</a><br>";
}

?>
<h3>All Done</h3>
</body>
</html>
