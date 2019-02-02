<?php
/**
 * IMDB Parser
 *
 * Parses data from the Internet Movie Database
 *
 * @package Engines
 * @author  Andreas Gohr    <a.gohr@web.de>
 * @link    http://www.imdb.com  Internet Movie Database
 * @version $Id: imdb.php,v 1.76 2013/04/10 18:11:43 andig2 Exp $
 */

$GLOBALS['imdbServer']   = 'https://www.imdb.com';
$GLOBALS['imdbIdPrefix'] = 'imdb:';

/**
 * Get meta information about the engine
 *
 * @todo    Include image search capabilities etc in meta information
 */
function imdbMeta()
{
    return array('name' => 'IMDB', 'stable' => 1);
}


/**
 * Get Url to search IMDB for a movie
 *
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string    The search string
 * @return  string    The search URL (GET)
 */
function imdbSearchUrl($title)
{
    global $imdbServer;
    return $imdbServer.'/find?s=all&amp;q='.urlencode($title);
}

/**
 * Get Url to visit IMDB for a specific movie
 *
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @param   string  $id The movie's external id
 * @return  string      The visit URL
 */
function imdbContentUrl($id)
{
    global $imdbServer;
    global $imdbIdPrefix;
    $id = preg_replace('/^'.$imdbIdPrefix.'/', '', $id);
    return $imdbServer.'/title/tt'.$id.'/';
}

/**
 * Get IMDB recommendations for a specific movie that meets the requirements
 * of rating and release year.
 *
 * @author  Klaus Christiansen <klaus_edwin@hotmail.com>
 * @param   int     $id      The external movie id.
 * @param   float   $rating  The minimum rating for the recommended movies.
 * @param   int     $year    The minimum year for the recommended movies.
 * @return  array            Associative array with: id, title, rating, year.
 *                           If error: $CLIENTERROR contains the http error and blank is returned.
 */
function imdbRecommendations($id, $required_rating, $required_year)
{
    global $CLIENTERROR;

	$url = imdbContentUrl($id);
	$resp = httpClient($url, true);

    $recommendations = array();
    preg_match_all('/<div class="rec_item" data-info=".*?" data-spec=".*?" data-tconst="tt(\d+)">/si', $resp['data'], $ary, PREG_SET_ORDER);

    foreach ($ary as $recommended_id)
    {

	$rec_resp = getRecommendationData($recommended_id[1]);
        $imdbId = $recommended_id[1];
        $title  = $rec_resp['title'];
        $year   = $rec_resp['year'];
        $rating = $rec_resp['rating'];

        // matching at least required rating?
        if (empty($required_rating) || (float) $rating < $required_rating) continue;

        // matching at least required year?
        if (empty($required_year) || (int) $year < $required_year) continue;

        $data = array();
        $data['id']     = $imdbId;
        $data['rating'] = $rating;
        $data['title']  = $title;
        $data['year']   = $year;

        $recommendations[] = $data;
    }
    return $recommendations;
}

function getRecommendationData($imdbID) {
	global $imdbServer;
    global $imdbIdPrefix;
    global $CLIENTERROR;

	$imdbID = preg_replace('/^'.$imdbIdPrefix.'/', '', $imdbID);

	// fetch mainpage
    $resp = httpClient($imdbServer.'/title/tt'.$imdbID.'/', true);     // added trailing / to avoid redirect
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

	// Titles and Year
    // See for different formats. https://contribute.imdb.com/updates/guide/title_formats
    if ($data['istv']) {
        if (preg_match('/<title>&quot;(.+?)&quot;(.+?)\(TV Episode (\d+)\) - IMDb<\/title>/si', $resp['data'], $ary)) {
            # handles one episode of a TV serie
            $data['title'] = trim($ary[1]);
            $data['year'] = $ary[3];
        } else if (preg_match('/<title>(.+?)\(TV Series (\d+).+?<\/title>/si', $resp['data'], $ary)){
            $data['title'] = trim($ary[1]);
            $data['year'] = trim($ary[2]);
        }
    } else {
        preg_match('/<title>(.+?)\((\d+)\).+?<\/title>/si', $resp['data'], $ary);
        $data['title'] = trim($ary[1]);
        $data['year'] = trim($ary[2]);
    }

	// Rating
    preg_match('/<strong title="([\d\.]+)+ based on.+?ratings"/si', $resp['data'], $ary);
    $data['rating'] = trim($ary[1]);

	return $data;
}

/**
 * Search a Movie
 *
 * Searches for a given title on the IMDB and returns the found links in
 * an array
 *
 * @author  Tiago Fonseca <t_r_fonseca@yahoo.co.uk>
 * @author  Charles Morgan <cmorgan34@yahoo.com>
 * @param   string  title   The search string
 * @param   boolean aka     Use AKA search for foreign language titles
 * @return  array           Associative array with id and title
 */
function imdbSearch($title, $aka=null)
{
    global $imdbServer;
    global $imdbIdPrefix;
    global $CLIENTERROR;
    global $cache;

    $url    = $imdbServer.'/find?q='.urlencode($title);
    if ($aka) $url .= ';s=tt;site=aka';

    $resp = httpClient($url, $cache);
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

    $data = array();

    // add encoding
    $data['encoding'] = $resp['encoding'];

    // direct match (redirecting to individual title)?
    if (preg_match('/^'.preg_quote($imdbServer,'/').'\/[Tt]itle(\?|\/tt)([0-9?]+)\/?/', $resp['url'], $single))
    {
        $info       = array();
        $info['id'] = $imdbIdPrefix.$single[2];

        // Title
        preg_match('/<title>(.*?) \([1-2][0-9][0-9][0-9].*?\)<\/title>/i', $resp['data'], $m);
        list($t, $s)        = explode(' - ', trim($m[1]), 2);
        $info['title']      = trim($t);
        $info['subtitle']   = trim($s);

        $data[]     = $info;
    }

    // multiple matches
    else if (preg_match_all('/<tr class="findResult.*?">(.*?)<\/tr>/i', $resp['data'], $multi, PREG_SET_ORDER))
    {
        foreach ($multi as $row)
        {
            preg_match('/<td class="result_text">\s*<a href="\/title\/tt(\d+).*?" >(.*?)<\/a>\s?\(?(\d+)?\)?/i', $row[1], $ary);
            if ($ary[1] and $ary[2]) {
                $info           = array();
                $info['id']     = $imdbIdPrefix.$ary[1];
                $info['title']  = $ary[2];
                $info['year']   = $ary[3];
                $data[]         = $info;
            }

#           dump($info);
        }
    }

    return $data;
}

/**
 * Fetches the data for a given IMDB-ID
 *
 * @author  Tiago Fonseca <t_r_fonseca@yahoo.co.uk>
 * @author  Victor La <cyridian@users.sourceforge.net>
 * @author  Roland Obermayer <robelix@gmail.com>
 * @param   int   IMDB-ID
 * @return  array Result data
 */
function imdbData($imdbID)
{
    global $imdbServer;
    global $imdbIdPrefix;
    global $CLIENTERROR;
    global $cache;

    $imdbID = preg_replace('/^'.$imdbIdPrefix.'/', '', $imdbID);
    $data= array(); // result
    $ary = array(); // temp

    // fetch mainpage
    $resp = httpClient($imdbServer.'/title/tt'.$imdbID.'/', $cache);     // added trailing / to avoid redirect
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

    // add encoding
    $data['encoding'] = $resp['encoding'];

    // Check if it is a TV series episode
    if (preg_match('/<title>.+?\(TV (Episode|Series|Mini-Series).*?<\/title>/si', $resp['data']))
	{
        $data['istv'] = 1;

        # find id of Series
        preg_match('/<meta property="pageId" content="tt(\d+)" \/>/si', $resp['data'], $ary);
        $data['tvseries_id'] = trim($ary[1]);
    }

    // Titles and Year
    // See for different formats. https://contribute.imdb.com/updates/guide/title_formats
    if ($data['istv']) {
        if (preg_match('/<title>&quot;(.+?)&quot;(.+?)\(TV Episode (\d+)\) - IMDB<\/title>/si', $resp['data'], $ary)) {
            # handles one episode of a TV serie
            $data['title'] = trim($ary[1]);
            $data['subtitle'] = trim($ary[2]);
            $data['year'] = $ary[3];
        } else if (preg_match('/<title>(.+?)\(TV (?:Series|Mini-Series) (\d+)\) - IMDB<\/title>/si', $resp['data'], $ary)){
            # handles a TV series.
            # split title - subtitle
            list($t, $s) = explode(' - ', $ary[1], 2);
            # no dash, lets try colon
            if ($s == false) {
                list($t, $s) = explode(': ', $ary[1], 2);
            }
            $data['title'] = trim($t);
            $data['subtitle'] = trim($s);
            $data['year'] = trim($ary[2]);
        }
    } else {
        preg_match('/<title>(.+?)\(.*?(\d+)\).+?<\/title>/si', $resp['data'], $ary);
        $data['year'] = trim($ary[2]);
        # split title - subtitle
        list($t, $s) = explode(' - ', $ary[1], 2);
        # no dash, lets try colon
        if ($s == false) {
            list($t, $s) = explode(': ', $ary[1], 2);
        }
        $data['title'] = trim($t);
        $data['subtitle'] = trim($s);
    }
    # orig. title
    preg_match('<div class="originalTitle">(.+?)<span class="description"> \(original title\)<\/span><\/div>/si', $resp['data'], $ary);
    $data['origtitle'] = trim($ary[1]);

    // Cover URL
    $data['coverurl'] = imdbGetCoverURL($resp['data']);

    // MPAA Rating
    preg_match('/<div class="subtext">(.+?)</is', $resp['data'], $ary);
    $data['mpaa'] = trim($ary[1]);

    // Runtime
    preg_match('/<time datetime="PT(\d+)M">/si', $resp['data'], $ary);
    $data['runtime'] = trim($ary[1]);

    // Director
    preg_match('/Directors?:\s*<\/h4>(.+?)<\/div>/si', $resp['data'], $ary);
    preg_match_all('/<a.*?href="\/name\/nm.+?".*?>(.+?)<\/a>/si', $ary[1], $ary, PREG_PATTERN_ORDER);
    // TODO: Update templates to use multiple directors
    $data['director']  = trim(join(', ', $ary[1]));

    // Rating
    preg_match('/<strong title="([\d\.]+)+ based on.+?ratings"/si', $resp['data'], $ary);
    $data['rating'] = trim($ary[1]);

    // Countries
    preg_match('/Country:\s*<\/h4>(.+?)<\/div>/si', $resp['data'], $ary);
    preg_match_all('/<a.+?country.+?>(.+?)<\/a>/si', $ary[1], $ary, PREG_PATTERN_ORDER);
    $data['country'] = trim(join(', ', $ary[1]));

    // Languages
    preg_match('/Languages?:\s*<\/h4>(.+?)<\/div>/si', $resp['data'], $ary);
    preg_match_all('/<a.*?href=".*?language.*?".*?>(.+?)<\/a>/si', $ary[1], $ary, PREG_PATTERN_ORDER);
    $data['language'] = trim(strtolower(join(', ', $ary[1])));

    // Genres (as Array)
    preg_match('/Genres:\s*<\/h4>(.+?)<\/div>/si', $resp['data'], $ary);
    preg_match_all('#<a href=".+?".*?>\s*(.+?)</a>#si', $ary[1], $genres, PREG_PATTERN_ORDER);
    //file_append('log.txt', $ary[1]);
    //file_append('log.txt', $genres);
    foreach($genres[1] as $genre) {
        $data['genres'][] = trim($genre);
    }

    // for Episodes - try to get some missing stuff from the main series page
    if ( $data['istv'] and (!$data['runtime'] or !$data['country'] or !$data['language'] or !$data['coverurl'])) {
        $sresp = httpClient($imdbServer.'/title/tt'.$data['tvseries_id'].'/', $cache);
        if (!$sresp['success']) $CLIENTERROR .= $resp['error']."\n";

        # runtime
        if (!$data['runtime']) {
            preg_match('/<time datetime="PT(\d+)M">/si', $sresp['data'], $ary);
            $data['runtime'] = trim($ary[1]);
        }

        # country
        if (!$data['country']) {
            preg_match('/Country:\s*<\/h4>(.+?)<\/div>/si', $sresp['data'], $ary);
            preg_match_all('/<a.+?country.+?>(.+?)<\/a>/si', $ary[1], $ary, PREG_PATTERN_ORDER);
            $data['country'] = trim(join(', ', $ary[1]));
        }

        # language
        if (!$data['language']) {
            preg_match('/Languages?:\s*<\/h4>(.+?)<\/div>/si', $sresp['data'], $ary);
            preg_match_all('/<a.*?href=".*?language.*?".*?>(.+?)<\/a>/si', $ary[1], $ary, PREG_PATTERN_ORDER);
            $data['language'] = trim(strtolower(join(', ', $ary[1])));
        }

        # cover
        if (!$data['coverurl']) {
            $data['coverurl'] = imdbGetCoverURL($sresp['data']);
        }
    }

    // Plot
    preg_match('/<h2>Storyline<\/h2>.*?<p>(.*?)</si', $resp['data'], $ary);
    $data['plot'] = $ary[1];

    // Fetch credits
    $resp = imdbFixEncoding($data, httpClient($imdbServer.'/title/tt'.$imdbID.'/fullcredits', $cache));
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

    // Cast
    if (preg_match('#<table class="cast_list">(.*)#si', $resp['data'], $match))
    {
        // no idea why it does not always work with (.*?)</table
        // could be some maximum length of .*?
        // anyways, I'm cutting it here
        $casthtml = substr($match[1], 0, strpos($match[1], '</table'));
        if (preg_match_all('#<td class=\"primary_photo\">\s+<a href=\"\/name\/(nm\d+)\/?.*?".+?<a .+?>(.+?)<\/a>.+?<td class="character">(.*?)<\/td>#si', $casthtml, $ary, PREG_PATTERN_ORDER))
        {
            for ($i=0; $i < sizeof($ary[0]); $i++)
            {
                $actorid    = trim(strip_tags($ary[1][$i]));
                $actor      = trim(strip_tags($ary[2][$i]));
                $character  = trim( preg_replace('/\s+/', ' ', strip_tags( preg_replace('/&nbsp;/', ' ', $ary[3][$i]))));
                $cast  .= "$actor::$character::$imdbIdPrefix$actorid\n";
            }
        }

        // remove html entities and replace &nbsp; with simple space
        $data['cast'] = html_clean_utf8($cast);

        // sometimes appearing in series (e.g. Scrubs)
        $data['cast'] = preg_replace('#/ ... #', '', $data['cast']);
    }

    // Fetch plot
    $resp = $resp = imdbFixEncoding($data, httpClient($imdbServer.'/title/tt'.$imdbID.'/plotsummary', $cache));
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

    // Plot
    //<li class="ipl-zebra-list__item" id="summary-ps0695557">
    //  <p>A nameless first person narrator (<a href="/name/nm0001570/">Edward Norton</a>) attends support groups in attempt to subdue his emotional state and relieve his insomniac state. When he meets Marla (<a href="/name/nm0000307/">Helena Bonham Carter</a>), another fake attendee of support groups, his life seems to become a little more bearable. However when he associates himself with Tyler (<a href="/name/nm0000093/">Brad Pitt</a>) he is dragged into an underground fight club and soap making scheme. Together the two men spiral out of control and engage in competitive rivalry for love and power. When the narrator is exposed to the hidden agenda of Tyler&#39;s fight club, he must accept the awful truth that Tyler may not be who he says he is.</p>
    //  <div class="author-container">
    //      <em>&mdash;<a href="/search/title?plot_author=Rhiannon&view=simple&sort=alpha&ref_=ttpl_pl_0">Rhiannon</a></em>
    //  </div>
    //</li>
    preg_match('/<li class="ipl-zebra-list__item" id="summary-p.\d+">\s+<p>(.+?)<\/p>/is', $resp['data'], $ary);
    if ($ary[1])
    {
        $data['plot'] = trim($ary[1]);
        $data['plot'] = preg_replace('/&#34;/', '"', $data['plot']); //Replace HTML " with "

        // removed linked actors like: <a href="/name/nm0001570?ref_=tt_stry_pl">Edward Norton</a>
        $data['plot'] = preg_replace('/<a href="\/name\/nm\d+.+?">/', '', $data['plot']);
        $data['plot'] = preg_replace('/<\/a>/', '', $data['plot']);
        $data['plot'] = preg_replace('/\s+/s', ' ', $data['plot']);
    }

    $data['plot'] = html_clean_utf8($data['plot']);

    return $data;
}

/**
 * At the moment - oct 2010 - most imdb-pages were changed to utf8,
 * but e.g. fullcredits are still iso-8859-1
 * so data is recoded here
 */
function imdbFixEncoding($data, $resp)
{
    $result = $resp;
    $pageEncoding = $resp['encoding'];

    if ($pageEncoding != $data['encoding'])
    {
        $result['data'] = iconv($pageEncoding, $data['encoding'], html_entity_decode_all($resp['data']));
    }

    return $result;
}

/**
 * Get Url of Cover Image
 *
 * @author  Roland Obermayer <robelix@gmail.com>
 * @param   string  $data   IMDB Page data
 * @return  string          Cover Image URL
 */
function imdbGetCoverURL($data) {
    global $imdbServer;
    global $CLIENTERROR;
    global $cache;

    // find cover image url
    if (preg_match('/<td.*?id="img_primary".*?<a.*?href="(\/media\/rm.*?)".*?>/si', $data, $ary))
    {
        // Fetch the image page
        $resp = httpClient($imdbServer.$ary[1], $cache);

        if ($resp['success'])
        {
            // get big cover image.
            preg_match('/<img.+?id="primary-img".*?src="(.*?)"/si', $resp['data'], $ary);
            return trim($ary[1]);
        }
        $CLIENTERROR .= $resp['error']."\n";
        return '';
    }
    // src look somthing like: src="https://images-na.ssl-images-amazon.com/images/M/MV5BMTc0MDMyMzI2OF5BMl5BanBnXkFtZTcwMzM2OTk1MQ@@._V1_UX214_CR0,0,214,317_AL_.jpg"
    // The last part ._V1_UX214.....jpg seams to be an function that scales the image. Just remove that we want the full size.
    else if (preg_match('/<div.*?class="poster".*?<img.*?src="(.*?\.)_v.*?"/si', $data, $ary))
    {
    	// image is being resized due to load times and the size of the PDF export file.
        return $ary[1]."_V1_SY600_CR0,0,600_AL_.jpg";
    }
    else
    {
        # no image
        return '';
    }
}


/**
 * Get Url to visit IMDB for a specific actor
 *
 * @author  Michael Kollmann <acidity@online.de>
 * @param   string  $name   The actor's name
 * @param   string  $id The actor's external id
 * @return  string      The visit URL
 */
function imdbActorUrl($name, $id)
{
    global $imdbServer;

    $path = ($id) ? 'name/'.urlencode($id).'/' : 'Name?'.urlencode(html_entity_decode_all($name));

    return $imdbServer.'/'.$path;
}

/**
 * Parses Actor-Details
 *
 * Find image and detail URL for actor, not sure if this can be made
 * a one-step process?
 *
 * @author                Andreas Goetz <cpuidle@gmx.de>
 * @param  string  $name  Name of the Actor
 * @return array          array with Actor-URL and Thumbnail
 */
function imdbActor($name, $actorid)
{
    global $imdbServer;
    global $cache;

    // search directly by id or via name?
    $resp = httpClient(imdbActorUrl($name, $actorid), $cache);

    // if not direct match load best match
    if (preg_match('#<b>Popular Names</b>.+?<a\s+href="(.*?)">#i', $resp['data'], $m) ||
        preg_match('#<b>Names \(Exact Matches\)</b>.+?<a\s+href="(.*?)">#i', $resp['data'], $m) ||
        preg_match('#<b>Names \(Approx Matches\)</b>.+?<a\s+href="(.*?)">#i', $resp['data'], $m))
    {
        if (!preg_match('/http/i', $m[1]))
        {
            $m[1] = $imdbServer.$m[1];
        }

        $resp = httpClient($m[1], true);
    }

    // now we should have loaded the best match

    // only search in img_primary <td> - or we get far to many useless images
    preg_match('/<td.+?id="img_primary">(.*?)<\/td>/si', $resp['data'], $match);

    $ary = array();
    if (preg_match('/.+?<a.*?href="(\/name\/nm\d+\/).+?src="(.+?)"/si', $match[1], $m))
    {
        $ary[0][0] = $m[1];
        $ary[0][1] = $m[2];
    }

    return $ary;
}

?>
