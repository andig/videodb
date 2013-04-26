<?php
/**
 * Add TV series
 *
 * This version has been modified to allow users to add partial 
 * or entire series to their videodb database.
 *
 * @package Contrib
 *
 * @author  Adam <precarious_panther@bigpond.com>
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @version $Id: add_tv_series.php,v 1.6 2008/02/08 20:06:42 chinamann Exp $
 */

// move out of contrib for includes
chdir('..');

require_once './core/functions.php';
require_once './engines/engines.php';

localnet_or_die();
permission_or_die(PERM_ADMIN);

?>

<html>
<head>
    <title>Add TV series in part, or in whole</title>
</head>
<body>
<?
if($_POST[save])
{
    save();
}
else
{
    printStartForm();
    
    if (!empty($tomeurl))
    {
        fetchTomeInfos($tomeurl);
    }
}
?>

</body>
</html>


<?
// -------------- Functions follow -------------

function printStartForm()
{
    global $tomeurl;
?>
  <p>This script has been developed by Adam Benson (precarious_panther@bigpond.com), based on the tvtome script by Andreas Gohr.</p>
  <p>You can use it to add television series in part, or in whole to your videodb collection (Place this file in your 'contrib' folder). It will find the episode information, including an episode image (which you can alternatively specify yourself), and automatically add all the checked episodes to your database with correct Cast, Plot, Image, and etc.</p>
  <p>This is Version 1.0 of this script, and therefore may have some bugs that need ironing out. Next version I will solve the hassle of having to manually add disk id's.</p>
  <p>To add a series, enter the TVTOME root episode address in the box below.</p>
  <form method="post">
  <table>
    <tr>
      <td>TV-Tome Episode Root</td>
      <td><input type="text" name="tomeurl" value="<?php echo formvar($tomeurl)?>"></td>
      <td>e.g. <code>http://www.tvtome.com/StargateSG1/</code> (See <a href="http://www.tvtome.com" target="_blank">tvtome.com</a>)</td> 
    </tr>
    <tr>
      <td colspan="3" align="center">
        <input type="submit" value="Look Up Episodes!">
      </td>
    </tr>
  </table>
  </form>
  
  <hr/>
<?
}


function getEpBody($url)
{
    $resp = httpClient($url, true);

    //get the main body
    preg_match('/<td width="589" align="center" valign="top">.*?<table width="580" border="0" cellspacing="2" cellpadding="2">(.*?)<\/table>\n<center> Season:/si',$resp[data],$matches);

    $body = $matches[1];
    //remove season headings
    $body = preg_replace('/<tr><td><a name=".*?"><h2>.*?<\/h2><\/a><\/td><\/tr>/si', '', $body);

    //get episodes
    preg_match_all('/(<tr><td><b>.*?<a name=.*?)<tr><td><hr><\/td><\/tr>/si', $body, $matches, PREG_PATTERN_ORDER);

    $episodes = array();
    
    //get infos;
    foreach ($matches[1] as $ep)
    {
        $episode = array();

        preg_match_all('/<tr>(.*?)<\/tr>/si', $ep, $fields);

        //title and episodenumber
        preg_match('/<a name="ep(\d*?)" href=".*?">(.*?)<\/a>/', $fields[1][0], $match);
        $episode['number']  = $match[1];
        $episode['title']   = $match[2];

        //gueststars
        preg_match_all('/<a href="\/tvtome\/servlet\/PersonDetail\/personid.*?>(.*?)<\/a> \((.*?)\)/si',$fields[1][1],$gss,PREG_SET_ORDER);

        $cast = '';
        foreach($gss as $gs)
        {
            $cast .= $gs[1];
            $cast .= '::';
            if(preg_match('/Voice of /',$gs[2]))
            {
                $gs[2] = preg_replace('/Voice of /','',$gs[2]);
                $gs[2] .= ' (Voice)';
            }
            $cast .= $gs[2];
            $cast .= "\n";
        }
        $episode[cast] = trim($cast);

        //plot
        $episode[plot] = trim(strip_tags($fields[1][2]));

        //year
        preg_match('/<i>b<\/i>: \d{1,2}-.{3}-(\d{4})$/mi',$fields[1][3],$match);
        $episode[year] = $match[1];

        //Director
        preg_match('/<i>d<\/i>:\s+<a href="\/tvtome\/servlet\/PersonDetail\/personid.*?>(.*?)<\/a>/si',$fields[1][3],$match);
        $episode['director'] = $match[1];

        $episodes[] = $episode;
    }
    
    return $episodes;
}


function fetchTomeInfos($url)
{
    if (!stristr($url, 'http://')) $url = 'http://www.tvtome.com/'.ucfirst($url).'/';

    //Firstly, look up the episode lists.
    $eplist = $url . "eplist.html";
    $ephome = $url . "index.html";

    echo "Retrieving Episode List... "; 
    flush();
    
    $resp = httpClient($eplist, true);

    preg_match_all('/<tr align="center"><td align="right" valign=top class="small">.*?<\/td><td nowrap valign=top class="small">(.*?)<\/td><td valign=top class="small">.*?<\/td><td valign=top align="right" class="small" nowrap>(.*?)<\/td><td>&nbsp;<\/td><td class="small" align="left" valign=top><a href=".*?">(.*?)<\/a><\/td><\/tr>/si',$resp[data],$matches,PREG_PATTERN_ORDER);

    // 1 - Epnum, 2 - Air Date, 4 - Ep Name
    $epdata = array();
    for($x=0; $x<count($matches[1]);$x++)
    {
        $epnums = explode("-", $matches[1][$x]);
        $epdata[$x]['season']   = trim($epnums[0]);
        $epdata[$x]['episode']  = trim($epnums[1]);
        $epdata[$x]['date']     = trim($matches[2][$x]);
        $epdata[$x]['title']    = trim($matches[3][$x]);
    }   
    echo "Done. \n <br />"; 
    echo "Retrieving Series Image... "; 
    flush();

    //Then we grab the shows image from the shows home page.
    $resp = httpClient($ephome, true);
    if (preg_match('/<img src="(\/images\/shows.+?)"/i', $resp['data'], $matches))
    {
        $spic = "http://www.tvtome.com".$matches[1];
        echo "Done. \n <br /><hr />"; 
    }    
    flush();


    //Finally, generate the form using this data. (Episode details are retrieved using tvtome.php by Andreas Gohr)
    $lastsea    = $epdata[count($epdata)-1]['season'];
    $lastep     = $epdata[count($epdata)-1]['episode'];

?> 
    <form method="post" name="list" id="list">

    <input type="hidden" name="seasons" value="<?php echo $lastsea ?>" />
    <input type="hidden" name="bodyurl" value="<?php echo $url ?>guide.html" />
    <input type="hidden" name="episodes" value="<?php echo $lastep ?>" />
    <center><table cellpadding="3" border="1" cellspacing="1" style="background-color:#dddddd; width:90%;">
        <tr>
            <td colspan="3"><center>Series Picture:</center></td>
            <td colspan="2"><center><img src="<?php echo $spic ?>" /><br /> <input type="text" name="s_pic" value="<?php echo $spic ?>" style="width:100%;" /></center></td>
        </tr>
        <tr>
            <td colspan="3">Series Title: (i.e Stargate SG-1)</td>
            <td colspan="2"><input type="text" name="s_title" style="width:100%;" /></td>

        </tr>
        <tr>
            <td><a href="#" onClick="javascript:toggle()">Add To Collection</a></td>
            <td>Season</td>
            <td>Episode</td>
            <td>Title</td>
            <td>Air-Date</td>
        </tr>

<?php
    $ids = array();
    
    foreach($epdata as $ep)
    {
        $id     = 'c_'.trim($ep[season]) .'x' . trim($ep[episode]);
        $ids[]  = $id;
?>
        <tr>
            <td><input type="checkbox" name="<?php echo $id?>" id="<?php echo $id?>" /></td>
            <td><?php echo $ep['season'];?></td>
            <td><?php echo $ep['episode'];?></td>
            <td><?php echo $ep['title'];?><input type="hidden" value="<?php echo $ep['title'];?>" name="t_<?php echo $ep[season] .'x' . $ep[episode]?>" /></td>
            <td><?php echo $ep['date'];?><input type="hidden" value="<?php echo $ep['date'];?>" name="d_<?php echo $ep[season] .'x' . $ep[episode]?>" /></td>
        </tr>
<?php
    }
    
?>

    <script language="javascript">
    function toggle()
    {
<?php
    foreach ($ids as $id)
    {
        echo "document.forms['list'].elements['$id'].checked = !document.forms['list'].elements['$id'].checked;\n";
    }
?>
        return false;
    }
    </script>

    </table><br />
    <input type="hidden" name="save" value="1">
    <input type="submit" value="Add Selected Episodes" /></center>
    </form>

<?php
}


function save()
{
	$scount = $_POST['seasons'];
	$ecount = $_POST['episodes'];
	$simage = $_POST['s_pic'];
	$stitle = $_POST['s_title'];
	$bodyurl= $_POST['bodyurl'];
    
	echo "Retrieving Episode Plot/Actor Details...\n <br /><hr />\n"; 
    flush();
	$epdetails = getEpBody($bodyurl);
  
	echo "Updating Video Database...\n <br /><hr />\n"; 
    flush();

	for ($s = 1; $s <= $scount; $s++)
    {
		$e      = 1;
		$curEp  = $s ."x". $e;
        
		while ($_POST['t_'.$curEp])
        {
			$ccheck = $_POST['c_'.$curEp];
            
			if ($ccheck == "on")
            {
				$ctitle     = $_POST['t_'.$curEp];
				$cdate      = $_POST['d_'.$curEp];
				$subTitle   = $curEp . " - " . $ctitle;
				echo "Currently Processing: " . $subTitle . "\n <br />"; 
                flush();

				foreach ($epdetails as $ep)
                {
					if(trim($ep['title']) == $ctitle)
                    {
						$cast   = addslashes($ep['cast']);
						$plot   = addslashes($ep['plot']);
						$year   = addslashes($ep['year']);
					}
				}		

				$SQL = "INSERT INTO `".TBL_DATA."` (`title`,`subtitle`,`imgurl`,`istv`,`owner_id`,`actors`,`plot`,`year`,`comment`) 
                             VALUES ('$stitle','$subTitle','$simage', 1, 1,'$cast','$plot','$year','Original Air-Date: $cdate');";
				runSQL($SQL);
			}
			$e++;	
			$curEp = $s."x".$e;
		}
	}

    print "done.<br>";
    print '<p><a href="..">back to videodb</a></p>';
}


?>