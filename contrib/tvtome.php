<?php
/**
 * TV.com importer
 *
 * @package Contrib
 * @author  Andreas Gohr <agohr@web..de>
 * @version $Id: tvtome.php,v 2.24 2008/02/08 20:11:28 chinamann Exp $
 */

// move out of contrib for includes
chdir('..');

require_once './core/functions.php';
require_once './core/httpclient.php';
require_once './core/genres.php';
require_once './engines/engines.php';
require_once './engines/tvcom.php';

localnet_or_die();
permission_or_die(PERM_ADMIN);

error_reporting(E_ALL^E_NOTICE);

?>

<html>
<head>
  <title>TV.com importer</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <script language="JavaScript" type="text/javascript">
    function invertSelection() {
        for (var i = 0; i < document.forms["results"].length; i++) {
            if (document.forms["results"].elements[i].type == "checkbox") {
                   document.forms["results"].elements[i].checked =
                       !document.forms["results"].elements[i].checked;
            }
        }
    }
  </script>
</head>
<body>

<?php
fetchConfiguration();
if ($save)
{
    save();
}
else
{
    printStartForm();
    if (!empty($tomeshowid) && !empty($titlesql))
    {
        printEpisodes();
    }
}
?>

</body>
</html>


<?php
// -------------- Functions follow -------------

function fetchConfiguration()
{
	global $cfg_rating_col;

	// get rating column from config table
	$SQL = "SELECT opt FROM ". TBL_CONFIG ." WHERE opt LIKE 'custom_type' AND value = 'rating'";
	$result = runSQL($SQL);
	$score = $result[0][opt];
	if (!empty($score))
	{
		$cfg_rating_col = preg_replace('/type$/', '', $score);
	}
}


function printStartForm()
{
  global $tomeshowid;
  global $tomeseason;
  global $titlesql;
  global $subtitlesql;
  global $languagesql;
  global $showext;
  global $fastmode;

  if ($tomeseason == 0) $tomeseason = 1;

  ?>
  <form name="search" method="post">
  <table>
    <tr>
      <td nowrap>TV.com show id and season</td>
      <td>
        <input type="text" name="tomeshowid" size="5" value="<?php echo formvar($tomeshowid)?>">
        <input type="text" name="tomeseason" size="2" value="<?php echo formvar($tomeseason)?>">
      </td>
      <td>e.g. Futurama has showid 249: http://www.tv.com/futurama/show/249/episode_listings.html
        (See <a href="http://www.tv.com" target="_blank">tv.com</a>)</td>
    </tr>
    <tr>
      <td nowrap>VideoDB title search</td>
      <td><input type="text" name="titlesql" value="<?php echo formvar($titlesql)?>"></td>
      <td>e.g. <code>Futurama</code> - Very simple search use * and ? as wildcards.</td>
    </tr>
    <tr>
      <td nowrap>VideoDB sub-title search</td>
      <td><input type="text" name="subtitlesql" value="<?php echo formvar($subtitlesql)?>"></td>
      <td>e.g. <code>3x</code> to search for 3rd season.</td>
    </tr>
    <tr>
      <td nowrap>VideoDB language search</td>
      <td><input type="text" name="languagesql" value="<?php echo formvar($languagesql)?>"></td>
      <td>Empty field will include any language.</td>
    </tr>
    <tr>
      <td>Fast mode</td>
  <?php
    if (stristr(formvar($fastmode), 'fastmode')) {
  ?>
      <td><input type="checkbox" name="fastmode" value="fastmode" checked></td>
  <?php
    } else {
  ?>
      <td><input type="checkbox" name="fastmode" value="fastmode"></td>
  <?php
    }
  ?>
      <td>Only fetch the id from tv.com.</td>
    </tr>
    <tr>
      <td>Show details</td>
  <?php
    if (stristr(formvar($showext), 'showext')) {
  ?>
      <td><input type="checkbox" name="showext" value="showext" checked></td>
  <?php
    } else {
  ?>
      <td><input type="checkbox" name="showext" value="showext"></td>
  <?php
    }
  ?>
      <td>Only affects output, database will be updated anyway.</td>
    </tr>
  </table>
  <br>
  <div align="center">
    <input type="submit" value="Search">
  </div>
  </form>
  
  <br><hr>
  <?php
}

function printEpisodes()
{
    global $tomeshowid;
    global $tomeseason;
    global $titlesql;
    global $subtitlesql;
    global $languagesql;
    global $showext;
    global $fastmode;

    // check mandatory fields
    if (!$tomeshowid) {
        print "Mandatory fields: showid, titlesql!<br>\n";
        return;
    }
    $tomeurl = 'http://www.tv.com/show/'.$tomeshowid.'/episode_listings.html?season='.$tomeseason;

    // fetch data
    $episodes = fetchTomeSeasonInfos($tomeurl);
    $videos = getVideoIDs($titlesql, $subtitlesql, $languagesql);

  ?>
  <form name="results" method="post">
    <input type="hidden" name="save" value="1"/>
    <input type="hidden" name="form_fastmode" value="<?php echo ($fastmode) ? 1 : 0?>"/>
    
  <table width="94%" style="margin-left:3%; margin-right:3%;">
  <?php
    $row=0;
    foreach ($episodes as $ep) {
      if (!$fastmode) {
        $ep = fetchTomeEpisodeSummary($ep);
      } else {
        if ($tomeseason > 0)
          $ep['season'] = $tomeseason;
      }
  ?>
    <tr>
      <td>
        <input id="<?php echo $ep[tvcomid]?>" type="checkbox" name="form_eps[]" value="<?php echo $row?>">
      </td>
      <td colspan="2">
        <?php echo $ep[season]?>x<?php if ($ep[number] < 10) print "0"; echo $ep[number]?>: <b><?php echo $ep[subtitle]?></b>
      </td>
      <td align="right">
        <select name="form_id[<?php echo $row?>]">
          <?php showSelect($videos,$ep[subtitle])?>
        </select>
      </td>
    </tr>
  <?php
    if ($showext) {
  ?>
    <tr>
      <td/>
      <td>Id:</td>
      <td><?php echo substr($ep[tvcomid],0,32)?></td>
    </tr>
  <?php
      if (!$fastmode) {
  ?>
    <tr>
      <td/>
      <td>Episode:</td>
      <td><?php echo substr($ep[episode],0,32)?></td>
    </tr>
    <tr>
      <td/>
      <td>Year:</td>
      <td><?php echo substr($ep[year],0,32)?></td>
    </tr>
    <tr>
      <td/>
      <td>Score:</td>
      <td><?php echo substr($ep[rating],0,32)?></td>
    </tr>
    <tr>
      <td/>
      <td>Director:</td>
      <td><?php echo substr($ep[director],0,128)?></td>
    </tr>
    <tr>
      <td/>
      <td nowrap>Cover URL:</td>
      <td colspan="2"><?php echo substr($ep[coverurl],0,128)?></td>
    </tr>
    <tr>
      <td/>
      <td>Genres:</td>
      <td colspan="2"><?php echo substr($ep[genres],0,128)?></td>
    </tr>
    <tr>
      <td/>
      <td valign="top">Actors:</td>
      <td colspan="2"><?php echo substr($ep[cast],0,1024)?></td>
    </tr>
    <tr>
    </tr>
    <tr>
      <td/>
      <td valign="top">Plot:</td>
      <td colspan="2"><?php echo substr($ep[plot],0,1024)?></td>
    </tr>
  <?php
      }
  ?>
    <tr>
      <td colspan="4"><hr></td>
    </tr>
  <?php
    }
  ?>
    <tr>
      <td>
        <input type="hidden" name="form_tvcomid[<?php echo $row?>]" value="<?php echo formvar($ep[tvcomid])?>">
        <input type="hidden" name="form_subtitle[<?php echo $row?>]" value="<?php echo formvar($ep[subtitle])?>">
        <input type="hidden" name="form_plot[<?php echo $row?>]" value="<?php echo formvar($ep[plot])?>">
        <input type="hidden" name="form_year[<?php echo $row?>]" value="<?php echo formvar($ep[year])?>">
        <input type="hidden" name="form_director[<?php echo $row?>]" value="<?php echo formvar($ep[director])?>">
        <input type="hidden" name="form_cast[<?php echo $row?>]" value="<?php echo formvar($ep[cast])?>">
        <input type="hidden" name="form_rating[<?php echo $row?>]" value="<?php echo formvar($ep[rating])?>">
        <input type="hidden" name="form_coverurl[<?php echo $row?>]" value="<?php echo formvar($ep[coverurl])?>">
        <input type="hidden" name="form_genres[<?php echo $row?>]" value="<?php echo formvar($ep[genres])?>">
      </td>
    </tr>
  <?php
      $row++;
    }
  ?>
  <?php
    if (!$showext) {
  ?>
    <tr>
      <td colspan="4"><hr></td>
    </tr>
  <?php
    }
  ?>
    <tr>
      <td style="text-align:left;" colspan="3">
        <input type="button" value="Invert Selection" onclick="invertSelection();">
      </td>
      <td style="text-align:right;">
        <input type="submit" value="Save">
      </td>
    </tr>
  </table>
  </form>
  <?php
}

function similarity($string1,$string2)
{
  $string1 = preg_replace('/[^a-zA-Z ]/','',$string1);
  $string2 = preg_replace('/[^a-zA-Z ]/','',$string2);
  $string1 = strtolower($string1);
  $string2 = strtolower($string2);
  similar_text($string1,$string2,$sim);
  if (strstr($string1,$string2)) return 100;
  if (strstr($string2,$string1)) return 100;
  return number_format($sim, 0);
}

function showSelect($videos,$select)
{

  print '<option value=""></option>';
  $maxsim = 0;
  foreach($videos as $vid){
    $thissim = 0;
    $sim = similarity($select,$vid[title]);
    if ($sim > $thissim) $thissim = $sim;
    $sim = similarity($select,$vid[subtitle]);
    if ($sim > $thissim) $thissim = $sim;
    $sim = similarity($select,$vid[filename]);
    if ($sim > $thissim) $thissim = $sim;
    
    if ($thissim > $maxsim ){
      $SEL = "selected";
      $maxsim = $thissim;
    }else{
      $SEL = "";
    }
    
    print '<option value="'.$vid[id].'" '.$SEL.'>';
    print $vid[title];
    print ' - ';
    print $vid[subtitle];
    print '   ('.$thissim.'%)';
    print '</option>';
  }
}

function save()
{
  global $form_eps;
  global $form_tvcomid;
  global $form_id;
  global $form_subtitle;
  global $form_plot;
  global $form_year;
  global $form_director;
  global $form_cast;
  global $form_rating;
  global $form_coverurl;
  global $form_genres;
  global $form_fastmode;
  global $cfg_rating_col;
  
  print '<h2>Updating database...</h2>';
  
  $fastmode = formvar($form_fastmode);
  
  foreach($form_eps as $ep){
    $id    = $form_id[$ep];    
    if (empty($id)) continue;
    $tvcomid = addslashes($form_tvcomid[$ep]);
    $subtitle = addslashes($form_subtitle[$ep]);
    $plot  = addslashes($form_plot[$ep]);
    $year  = addslashes($form_year[$ep]);
    $director = addslashes($form_director[$ep]);
    $cast = addslashes($form_cast[$ep]);
    $rating = addslashes($form_rating[$ep]);
    $coverurl = addslashes($form_coverurl[$ep]);
    if (!$fastmode)
      $genres = mapGenres(explode(", ", addslashes($form_genres[$ep])));

    print $form_subtitle[$ep].'... ';
    $SQL = "UPDATE " . TBL_DATA . "
               SET imdbID = '$tvcomid',
                   istv = 1,
                   lastupdate = NOW()";
    if (!$fastmode) {
      $SQL .= ",   plot = '$plot',
                   year = '$year',
                   director = '$director',
                   actors = '$cast'";
      if (!empty($cfg_rating_col)) {
        $SQL .= ", $cfg_rating_col = '$rating'";
      }
      if (!empty($coverurl)) {
        $SQL .= ", imgurl = '$coverurl'";
      }
    }
    $SQL .= " WHERE id = $id";
    runSQL($SQL);

    // Genres
    if (!$fastmode && !empty($genres)) {
      $genre_ids = array();
      foreach ($genres as $g) {
        if ($gid = getGenreId($g))
          $genre_ids[] = $gid;
      }
      setItemGenres($id, $genre_ids);
    }

    print "done.<br>\n";
  }
  
  print '<p>back to <a href="tvtome.php">the importer</a> or to <a href="../index.php">the movies</a></p>';
}

function getVideoIDs($title, $subtitle, $language)
{
  $title = addslashes($title);
  $title = preg_replace('/\*/','%',$title);
  $title = preg_replace('/\?/','_',$title);
  $subtitle = addslashes($subtitle);
  $subtitle = preg_replace('/\*/','%',$subtitle);
  $subtitle = preg_replace('/\?/','_',$subtitle);
  $language = addslashes($language);
  $language = preg_replace('/\*/','%',$language);
  $language = preg_replace('/\?/','_',$language);
  $SQL = "SELECT id, title, subtitle, filename
            FROM " . TBL_DATA . "
           WHERE LOWER(title) LIKE LOWER('%$title%')
           AND LOWER(subtitle) LIKE LOWER('%$subtitle%')";
  if (!empty($language)) {
    $SQL .= " AND language LIKE '%$language%'";
  }

  $SQL .= "ORDER BY title, subtitle";
  $result = runSQL($SQL);
  return $result;
}

function fetchTomeSeasonInfos($url)
{
  $response = httpClient($url, true);
  if (!$response['success']) $CLIENTERROR .= $resp['error']."\n";

  // get show id
  if (preg_match('|/show/(\d*)/episode_listings\.html|', $url, $match))
    $showid = $match[1];

  //get the main body
  preg_match('|<div class="table-styled">(.*)</div>\s*<div class="table-nav"|si', $response[data], $matches);

  $body = $matches[1];

  //get episodes
  preg_match_all('|<td class="f-bold">[^<]*<a href="([^"]*summary\.html[^"]*">[^<]*)</a>[^<]*</td>[^<]*<td class="ta-c"|si',$body,$matches);

  $ep = 1;
  $episodes=array();

  //get infos;
  foreach($matches[1] as $episode) {
    // Episode in season
    $episodes[$ep][number] = $ep;

    //title and url
    preg_match_all('/(http.*summary.html)[^"]*">(.*)/si',$episode,$fields);
    $episodes[$ep][subtitle] = $fields[2][0];

    // URL code
    preg_match('|/episode/(\d*)/|si',$fields[1][0],$match);
    $episodes[$ep][tvcomid] = 'tvcom:'.$showid.'-'.$match[1];

    $ep++;
  }

  return $episodes;
}

function fetchTomeEpisodeSummary($episode)
{
  $ep = tvcomData($episode['tvcomid']);
  if (!empty($ep))
  {
    $ep['number'] = $episode['number'];
    $ep['subtitle'] = $episode['subtitle'];
    $ep['tvcomid'] = $episode['tvcomid'];
	$genres = implode(", ", $ep['genres']);
	$ep['genres'] = $genres;
    $episode = $ep;
  }
  
  return $episode;
}

?>
