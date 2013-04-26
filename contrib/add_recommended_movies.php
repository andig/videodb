<?php
/**
 * Add recommended movies via IMDB
 *
 * @package Contrib
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @version $Id: add_recommended_movies.php,v 1.8 2008/06/29 11:13:12 andig2 Exp $
 */

// move out of contrib for includes
chdir('..');

require_once './core/functions.php';
require_once './engines/engines.php';

// since we don't need session functionality, use this as workaround 
// for php bug #22526 session_start/popen hang 
session_write_close();

?>

<html>

<head>
    <title>Find Movie Recommendations</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="description" content="VideoDB" />
<!--
    <link rel="stylesheet" href="../templates/modern/compact.css" type="text/css" />
-->    
    <style>
        .green { color:green }
    </style>

</head>

<body>

<?

error_reporting(E_ALL ^ E_NOTICE);

if ($submit)
{
    // validate form data
    $required_rating    = (is_numeric($required_rating)) ? (float) $required_rating : '';
    $required_year      = (is_numeric($required_year)) ? (int) $required_year : '';

    // get list of all used images
    $SQL = 'SELECT * FROM '.TBL_DATA;
    if (empty($wishlist)) $SQL .= ' WHERE mediatype != '.MEDIA_WISHLIST;
    $result = runSQL($SQL);

    foreach ($result as $video)
    {   
        if (empty($video['imdbID'])) continue;

        $engine = engineGetEngine($video['imdbID']);
        if ($engine == 'imdb')
        {
            echo "Fetching recommendations for {$video['title']} (IMDB Id {$video['imdbID']})<br/>\n";
            flush();

            $url = 'http://uk.imdb.com/title/tt'.$video['imdbID'].'/recommendations';

            $resp = httpClient($url, true);
            if (!$resp['success']) 
            {
                echo($resp['error']."<br/>");
                continue;
            }

            preg_match_all('#<a href="/title/tt(\d+)/">(.+?)</a>#i', $resp['data'], $ary, PREG_SET_ORDER);
            foreach ($ary as $recommend)
            {
                $title  = $recommend[2];
                $id     = $recommend[1];

                if (preg_match('/<img/', $title)) continue;

                $rating = '';
                if (preg_match("#$id.+?<b>(\d+\.\d+)</b>#i", $resp['data'], $match)) 
                {
                    $rating = $match[1];
                    // matching at least required rating?
                    if (!empty($required_rating) && ((float) $rating < $required_rating)) continue;
                }

                $year = '';
                if (preg_match("#\((\d{4})\)#i", $title, $match))
                {
                    $year = $match[1];
                    // matching at least required year?
                    if (!empty($required_year) && ((int) $year < $required_year)) continue;
                }

                if (empty($rating) || ($rating >= $required_rating))
                {
                    $available = (count(runSQL("SELECT * FROM ".TBL_DATA." WHERE imdbID = '$id'")) > 0);

                    if ($available)
                    {
                        $add_movie = $title;
                    }
                    else
                    {
                        $add_movie = '<a class="green" href="../edit.php?save=1&mediatype='.MEDIA_WISHLIST.'&lookup=1&imdbID='.$id.
                                     '&title='.urlencode($title).'" target="_blank">'.$title.' <img src="../images/add.gif" border="0"/></a>';
                    }    

                    $add_movie = 'Recommended: '.$add_movie." (IMDB Id $id) $rating<br/>\n";             
                    echo $add_movie;

                    if ($download && !$available) engineGetData($id);
                }    
            }
        }
        echo "<br/>\n\n";
    }

}
else
{
?>
    <form action="<?php echo $_SERVER['PHP_SELF']?>">
        <table>
        <tr valign="top">
        <td>
            Limit to movies after
        </td>
        <td>
            <input type="text" name="required_year" id="required_year" value="1980" />
        </td>
        </tr>

        <tr valign="top">
        <td>
            At least require this rating
        </td>
        <td>
            <input type="text" name="required_rating" id="required_rating" value="7.0" />
        </td>
        </tr>
        </table>

        <label for="wishlist">
            <input type="checkbox" name="wishlist" id="wishlist" value="1" />
            Include wishlist
        </label>
        <br />

        <label for="download">
            <input type="checkbox" name="download" id="download" value="1" />
            Download recommendations if movie is not in videoDB
        </label>
        <br />

        <input type="submit" name="submit" value="Search" />
    </form>
<?
}
?>

</body>
</html>
