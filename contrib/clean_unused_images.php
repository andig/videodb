<?php
/**
 * Cleanup utility to remove unused images from cache folders
 *
 * @package Contrib
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @modified  Constantinos Neophytou   <jaguarcy@gmail.com>
 */

// move out of contrib for includes
chdir('..');

require_once './core/functions.php';
require_once './core/setup.core.php';

?>

<html>

<head>
    <title>Cleanup Image Cache</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="description" content="VideoDB" />
<!--
    <link rel="stylesheet" href="../templates/modern/compact.css" type="text/css" />
-->    
</head>

<body>

<?php

error_reporting(E_ALL ^ E_NOTICE);

// get list of all used images
//$SQL = "SELECT imgurl FROM videodata UNION SELECT imgurl FROM actors";
//$result = runSQL($SQL);

$coverSQL = "SELECT imgurl FROM ".TBL_DATA;
$actorSQL = "SELECT imgurl FROM ".TBL_ACTORS;
$coverResult = runSQL($coverSQL);
$actorResult = runSQL($actorSQL);

#dump($coverResult);

// find covers in cache
foreach ($coverResult as $val) 
{
    $url = $val['imgurl'];
    if (preg_match("/\.(jpe?g|gif|png)$/i", $url, $matches)) 
    {
        // get the cache file name, honor manually uploaded files
    	if (preg_match('#^cache#i', $url))
    		$cache_file = $url;
    	else
	        cache_file_exists($url, $cache_file, CACHE_IMG, $matches[1]);

        $covers[] = $cache_file;
        $images[] = $cache_file;
    }
}

// find actor images in cache
foreach ($actorResult as $val) 
{
    $url = $val['imgurl'];
    if (preg_match("/\.(jpe?g|gif|png)$/i", $url, $matches)) 
    {
        // get the cache file name, honor manually uploaded files
    	if (preg_match('#^cache#i', $url))
    		$cache_file = $url;
    	else
	        cache_file_exists($url, $cache_file, CACHE_IMG, $matches[1]);
        
        $actors[] = $cache_file;
        $images[] = $cache_file;
    }
}

$size   	= 0;
$coverSize 	= 0;
$actorSize 	= 0;
$unused 	= 0;
$coverNum 	= 0;
$actorNum 	= 0;

// get list of all images currently in cache
list($total, $foo, $files) = analyzeCacheFolder('cache/img', true);

if ($submit) dump('Deleting:');

// loop over cache files
foreach ($files as $file)
{
    if (!in_array($file, $images))
    {
        $size += filesize($file);
        $unused++;
        
        if ($submit)
        {
        	unlink($file);
			dump($file);
		}	
    }
	elseif (in_array($file, $covers))
	{
		$coverSize += filesize($file);
		$coverNum++;
	}
	elseif (in_array($file, $actors))
	{
		$actorSize += filesize($file);
		$actorNum++;
	}
}

echo sprintf("
    $coverNum out of %d files with a size of %.2fMB are used for covers<br/>
    $actorNum out of %d files with a size of %.2fMB are used for headshots<br/>
    $unused out of %d files with a size of %.2fMB are currently unused<br/>", 
    count($files), $coverSize/(1024*1024),
    count($files), $actorSize/(1024*1024),
    count($files), $size/(1024*1024));

if ($unused)
{
    if ($submit) 
    {
        echo "$unused files with a size of ".round($size/(1024*1024),2)."Mb have been deleted<br/>";
    }
?>
    <form action=<?php echo $PHP_SELF?>>
        <input type="submit" name="submit" value="Delete" />
    </form>
<?php
}
?>

</body>
</html>
