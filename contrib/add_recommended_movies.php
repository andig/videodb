<?php
/**
 * Add recommended movies.
 *
 * @package Contrib
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @version $Id: add_recommended_movies.php,v 1.8 2014/02/02 12:00:00 kec2 Exp $
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
    $required_rating = (is_numeric($required_rating)) ? (float) $required_rating : '';
    $required_year = (is_numeric($required_year)) ? (int) $required_year : '';

    // get list of all videos
    $SQL = 'SELECT * FROM '.TBL_DATA;
    if (empty($wishlist)) $SQL .= ' WHERE mediatype != '.MEDIA_WISHLIST;
    $result = runSQL($SQL);

    foreach ($result as $video)
    {
        if (empty($video['imdbID'])) continue;
        $engine = strtoupper(engineGetEngine($video['imdbID']));
        echo "Fetching recommendations for <b>{$video['title']}</b> ($engine Id {$video['imdbID']})<br/>";

        $data = engineGetRecommendations($video['imdbID'], $required_rating, $required_year, 'imdb');
        if (!empty($CLIENTERROR))
        {
            echo $CLIENTERROR."<br/>";
            continue;
        }

        if (empty($data))
        {
            // sometimes there are no recommendations for a movie. This is true for Underworld: imdbId 0320691
            echo "No recommendations for {$video['title']}.<br/><br/>";
            continue;
        }

        echo '<table border="1">';
        echo "    <tr>";
        echo "        <th>Title</th> <th>Year</th> <th>Rating</th> <th>Id</th>";
        echo "    </tr>";

        foreach ($data as $recommended)
        {
            $available = (count(runSQL("SELECT * FROM ".TBL_DATA." WHERE imdbID like '%".$recommended['id']."'")) > 0);

            if (!$available)
            {
                $recommended['title'] = '<a class="green" href="../edit.php?save=1&mediatype='.MEDIA_WISHLIST.'&lookup=1&imdbID='.$recommended['id'].
                             '&title='.urlencode($recommended['title']).'" target="_blank">'.$recommended['title'].' <img src="../images/add.gif" border="0"/></a>';
            }

            echo "<tr>";
            echo "<td align=left  width=\"65%\">{$recommended['title']}</td>";
            echo "<td align=right width=\"10%\">{$recommended['year']}</td>";
            echo "<td align=right width=\"10%\">{$recommended['rating']}</td>";
            echo "<td align=right width=\"15%\">{$recommended['id']}</td>";
            echo "</tr>";

            if ($download && !$available) engineGetData($recommended['id']);
        }
        echo "</table>";
        echo "<br/>";
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
