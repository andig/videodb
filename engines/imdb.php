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
// Only used in contrib/add_recommended_movies.php
function imdbRecommendations($id, $required_rating, $required_year)
{
    global $CLIENTERROR;

    $url = imdbContentUrl($id);
    $resp = httpClient($url, true);

    $recommendations = array();
    preg_match_all('/<div class="rec_item" data-info=".*?" data-spec=".*?" data-tconst="tt(\d+)">/si', $resp['data'], $ary, PREG_SET_ORDER);

    foreach ($ary as $recommended_id) {
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
    if ($data['istv']) { // @todo this is always false
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
    preg_match('/<span class="AggregateRatingButton__RatingScore-.+?">(.+?)<\/span>/si', $resp['data'], $ary);
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

    $url = $imdbServer.'/find?q='.urlencode($title);
    if ($aka) $url .= ';s=tt;site=aka';

    $resp = httpClient($url, $cache);
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

    $data = array();

    // add encoding
    $data['encoding'] = $resp['encoding'];

    // direct match (redirecting to individual title)?
    // @todo i don't think this gets called anymore, investigate
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
    else if (preg_match_all('#div class="ipc-metadata-list-summary-item__tc".*href="/title/tt(\d+)/.*>([^\<]+)</a>.*<ul.*>(.*)</ul>.*</div>#Uism', $resp['data'], $multi, PREG_SET_ORDER))
    {
        foreach ($multi as $row)
        {
            $info = [
                'id' => $imdbIdPrefix.$row[1],
                'title' => $row[2],
                'year' => null
            ];
            if (preg_match_all('#<label.*>([^\<]+)</label>#Uism', $row[3], $labels, PREG_PATTERN_ORDER))
            {
                foreach ($labels[1] as $label)
                {
                    if (preg_match('#^(\d{4})$#i', $label)) $info['year'] = $label;
                    if (preg_match('#^.*(episode|series)$#i', $label)) $info['title'] .= ' ('.$label.')';
                }
            }
            $data[] = $info;
        }
    } elseif (preg_match_all('/<div class="col-title">.+?<a href="\/title\/tt(\d+)\/\?ref_=adv_li_tt".+?>(.+?)<\/a>.+?<span .+?>\((\d+).*?\)<\/span>/is', $resp['data'], $ary, PREG_SET_ORDER)) {
             foreach ($ary as $row) {
                 $info           = array();
                 $info['id']     = $imdbIdPrefix.$row[1];
                 $info['title']  = $row[2];
                 $info['year']   = $row[3];
                 $data[]         = $info;
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
    #testing code save resp data from imdb
    #file_put_contents('./cache/httpclient-php_imdbData_title.html', $resp['data']);  // write page data to file
    
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

    // extract json data from page
    if (preg_match('#(\<script id\="__NEXT_DATA__".*?\>)(.*?)(\</script\>)#',$resp['data'],$matches))
    {
        #file_put_contents('./cache/nextdata.json', $matches[2]);  // write json data to file
        $json_data = json_decode($matches[2],true);
        #file_put_contents('./cache/nextdata-decoded.json', print_r($json_data, true));  // write formated json data to file
    } 

    // add encoding
    $data['encoding'] = $resp['encoding'];

    // Check if it is a TV series episode
    if (preg_match('/<title>.+?\(TV (Episode|Series|Mini-Series).*?<\/title>/si', $resp['data'])) {
        $data['istv'] = 1;

        # find id of Series
        preg_match('/<meta property="imdb:pageConst" content="tt(\d+)"\/>/si', $resp['data'], $ary);
        $data['tvseries_id'] = trim($ary[1]);
    }

    // Titles and Year
    // See for different formats. https://contribute.imdb.com/updates/guide/title_formats
    if ($data['istv']) {
        if (preg_match('/<title>&quot;(.+?)&quot;(.+?)\(TV Episode (\d+)\) - IMDb<\/title>/si', $resp['data'], $ary)) {
            # handles one episode of a TV serie
            $data['title'] = trim($ary[1]);
            $data['subtitle'] = trim($ary[2]);
            $data['year'] = $ary[3];
        } else if (preg_match('/<title>(.+?)\(TV (?:Series|Mini-Series) (\d+).+?\) - IMDb<\/title>/si', $resp['data'], $ary)) {
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
    preg_match('/<div class="originalTitle">(.+?)<span class="description"> \(original title\)<\/span><\/div>/si', $resp['data'], $ary);
    $data['origtitle'] = trim($ary[1]);

    // Cover URL
    $data['coverurl'] = imdbGetCoverURL($resp['data']);

    // MPAA Rating
    $data['mpaa'] = "";
    $data['mpaa'] = $json_data["props"]["pageProps"]["aboveTheFoldData"]["certificate"]["rating"];

    // Runtime
    if (filter_var($json_data["props"]["pageProps"]["aboveTheFoldData"]["runtime"]["seconds"], FILTER_SANITIZE_NUMBER_INT) > 0) {
        # use the runtime from the next_data json data
        $data['runtime'] = filter_var($json_data["props"]["pageProps"]["aboveTheFoldData"]["runtime"]["seconds"], FILTER_SANITIZE_NUMBER_INT) / 60;
    } else if (preg_match('/<li role="presentation" class="ipc-inline-list__item">(\d+)(?:<!-- --> ?)+(?:h|s).*?(?:(?:<!-- --> ?)+(\d+)(?:<!-- --> ?)+.+?)?<\/li>/si', $resp['data'], $ary)) {
        # handles Hours and maybe minutes. Some movies are exactly 1 hours.
        $minutes = intval($ary[2]);
    	if (is_numeric($ary[1])) {
    		$minutes += intval($ary[1]) * 60;
    	}

    	$data['runtime'] = $minutes;
    } else if (preg_match('/<li role="presentation" class="ipc-inline-list__item">(\d+)(?:<!-- --> ?)+m.*?<\/li>/si', $resp['data'], $ary)) {
        # handle only minutes
    	$data['runtime'] = $ary[1];
    } else if (preg_match('/<div class="ipc-metadata-list-item__content-container">(\d+)(?:<!-- --> ?)+m.*?<\/div>/si', $resp['data'], $ary)) {
        # handle only minutes
        # Handles the case where runtime is only in the technical spec section.
        $data['runtime'] = $ary[1];
    }

    // Director
    $data['director'] = "";
    // TODO: Update templates to use multiple directors
    if ($json_data["props"]["pageProps"]["mainColumnData"]["directors"]["0"]["totalCredits"] > 0)
    {
        foreach ($json_data["props"]["pageProps"]["mainColumnData"]["directors"]["0"]["credits"] as $directordata)
        {
            $directorarray[] = trim($directordata["name"]["nameText"]["text"]);
        }
        $data['director'] = trim(join(', ',$directorarray));
    } 
    
    // Rating
    preg_match('/<div data-testid="hero-rating-bar__aggregate-rating__score" class="sc-.+?"><span class="sc-.+?">(.+?)<\/span><span>\/<!-- -->10<\/span><\/div>/si', $resp['data'], $ary);
    $data['rating'] = trim($ary[1]);

    // Countries
    preg_match_all('/href="\/search\/title\/\?country_of_origin.+?>(.+?)<\/a>/si', $resp['data'], $ary, PREG_PATTERN_ORDER);
    $data['country'] = trim(join(', ', $ary[1]));

    // Languages
	preg_match_all('/<a class=".+?" href="\/search\/title\?title_type=feature&amp;primary_language=.+?&amp;sort=moviemeter,asc&amp;ref_=tt_dt_ln">(.+?)<\/a>/', $resp['data'], $ary, PREG_PATTERN_ORDER);
    $data['language'] = trim(strtolower(join(', ', $ary[1])));

    // Genres (as Array)
    preg_match_all('/class="ipc-chip__text">(.+?)<\/span><\/a>/si', $resp['data'], $ary, PREG_PATTERN_ORDER);
    foreach($ary[1] as $genre) {
        $data['genres'][] = trim($genre);
    }

    // for Episodes - try to get some missing stuff from the main series page
    if ( $data['istv'] and (!$data['runtime'] or !$data['country'] or !$data['language'] or !$data['coverurl'])) {
        $sresp = httpClient($imdbServer.'/title/tt'.$data['tvseries_id'].'/', $cache);
        if (!$sresp['success']) $CLIENTERROR .= $resp['error']."\n";

        # runtime
        if (preg_match('/<li role="presentation" class="ipc-inline-list__item">(\d+)(?:<!-- --> ?)+(?:h|s).*?(?:(?:<!-- --> ?)+(\d+)(?:<!-- --> ?)+.+?)?<\/li>/si', $resp['data'], $ary)) {
            # handles Hours and maybe minutes. Some movies are exactly 1 hours.
            $minutes = intval($ary[2]);
            if (is_numeric($ary[1])) {
                $minutes += intval($ary[1]) * 60;
            }

            $data['runtime'] = $minutes;
        } else if (preg_match('/<li role="presentation" class="ipc-inline-list__item">(\d+)(?:<!-- --> ?)+m.*?<\/li>/si', $resp['data'], $ary)) {
            # handle only minutes
            $data['runtime'] = $ary[1];
        } else if (preg_match('/<div class="ipc-metadata-list-item__content-container">(\d+)(?:<!-- --> ?)+m.*?<\/div>/si', $resp['data'], $ary)) {
            # handle only minutes
            # Handles the case where runtime is only in the technical spec section.
            $data['runtime'] = $ary[1];
        }

        # country
        if (!$data['country']) {
            preg_match_all('/href="\/search\/title\/\?country_of_origin.+?>(.+?)<\/a>/si', $sresp['data'], $ary, PREG_PATTERN_ORDER);
            $data['country'] = trim(join(', ', $ary[1]));
        }

        # language
        if (!$data['language']) {
	        preg_match_all('/<a class=".+?" rel="" href="\/search\/title\?title_type=feature&amp;primary_language=.+?&amp;sort=moviemeter,asc&amp;ref_=tt_dt_ln">(.+?)<\/a>/', $sresp['data'], $ary, PREG_PATTERN_ORDER);
            $data['language'] = trim(strtolower(join(', ', $ary[1])));
        }

        # cover
        if (!$data['coverurl']) {
            $data['coverurl'] = imdbGetCoverURL($sresp['data']);
        }
    }

    // Plot
    if (array_key_exists('plainText', $json_data["props"]["pageProps"]["aboveTheFoldData"]["plot"]["plotText"]) )
    {
        $data['plot'] = stripslashes($json_data["props"]["pageProps"]["aboveTheFoldData"]["plot"]["plotText"]["plainText"]);
    }
    
    // Fetch credits
    $resp = imdbFixEncoding($data, httpClient($imdbServer.'/title/tt'.$imdbID.'/fullcredits', $cache));
    if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

    // Cast
    #testing code save resp data from imdb
    #file_put_contents('./cache/httpclient-php_imdbData_cast.html', $resp['data']);  // write page data to file    

    // Increase the PCRE backtrack limit for a potentially large regex operation
    $origBacktrackLimit = ini_get('pcre.backtrack_limit');
    $newBacktrackLimit  = '10000000';
    ini_set('pcre.backtrack_limit', $newBacktrackLimit);
    
    // extract json data from page
    if (preg_match('#(\<script id\="__NEXT_DATA__".*?\>)(.*?)(\</script\>)#s',$resp['data'],$matches))
    {
        #file_put_contents('./cache/nextdata.json-cast', $matches[2]);  // write json data to file
        $json_data_cast = json_decode($matches[2],true);
        #file_put_contents('./cache/nextdata-decoded.json-cast', print_r($json_data_cast, true));  // write formated json data to file
    }
    //revert the PCRE limits back to their original values after regex operation,
    ini_set('pcre.backtrack_limit', $origBacktrackLimit);
    
    $cast = '';
    if (isset($json_data_cast['props']['pageProps']['contentData']['categories']) &&
        is_array($json_data_cast['props']['pageProps']['contentData']['categories'])) 
    {
        foreach ($json_data_cast['props']['pageProps']['contentData']['categories'] as $index => $category) 
        {
            // Make sure 'id' exists for this category, then check if it equals "cast"
            if (isset($category['id']) && $category['id'] === "cast") 
            {
                // Ensure that 'section' and its 'items' exist and are an array
                if (isset($category['section']['items']) && is_array($category['section']['items'])) 
                {
                    $pageSize = $category['pagination']['queryVariables']['first'];
                    $total_cast = $category['section']['total'];

                    if ($total_cast > $pageSize) 
                    {
                        $cast = imdbCastExtra($imdbID);
                    }
                    else
                    {
                        $cast = imdbCast($category['section']['items']);
                    }
                }
                // break out of the cast data
                break;
            }
        }
    }

    $data['cast'] = $cast;

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
    if (preg_match('/<a class="ipc-lockup-overlay ipc-focusable" href="(\/title\/tt\d+\/mediaviewer\/\??rm.+?)" aria-label=".*?Poster.*?"><div class="ipc-lockup-overlay__screen"><\/div><\/a>/s', $data, $ary))
    {
        // Fetch the image page
        $resp = httpClient($imdbServer.$ary[1], $cache);

        if ($resp['success'])
        {
            // get big cover image.
            preg_match('/<div style=".+?" class=".+?"><img src="(.+?)"/si', $resp['data'], $ary);
            // If you want the image to scaled to a certain size you can do this.
            // UX800 sets the width of the image to 800 with correct aspect ratio with regard to height.
			// UY800 set the height to 800 with correct aspect ratio with regard to width.
            // return str_replace('.jpg', 'UY800_.jpg', $ary[1]);
            return trim($ary[1]);
        }
        $CLIENTERROR .= $resp['error']."\n";
        return '';
    }
    // src look somthing like: src="https://images-na.ssl-images-amazon.com/images/M/MV5BMTc0MDMyMzI2OF5BMl5BanBnXkFtZTcwMzM2OTk1MQ@@._V1_UX214_CR0,0,214,317_AL_.jpg"
    // The last part ._V1_UX214.....jpg seams to be an function that scales the image. Just remove that we want the full size.
    else if (preg_match('/<div.*?class="poster".*?<img.*?src="(.*?\.)_v.*?"/si', $data, $ary))
    {
        $img_url = $ary[1]."jpg";
        // Replace the https wtih http.
        $img_url = str_replace("https://images-na.ssl-images-amazon.com", "http://ecx.images-amazon.com", $img_url);
        return $img_url;
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
    //testing code save resp data from imdb
    //$file_path = './cache/httpclient-php_imdbActor_call_1.html';
    //file_put_contents($file_path, $resp['data']);

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
        //testing code save resp data from imdb
        //$file_path = './cache/httpclient-php_/_imdbActor_call_2.html';
        //file_put_contents($file_path, $resp['data']);
    }

    // now we should have loaded the best match

    // only search in img_primary <td> - or we get far to many useless images
    preg_match('/<div class="ipc-poster.*?>(.*?)<\/a><\/div>/si', $resp['data'], $match);

    $ary = array();
    if (preg_match('/.+?src="(.+?)".+?<a.*?href="(\/name\/nm\d+\/).+?/si', $match[1], $m))
    {
        $ary[0][0] = $m[2];
        $ary[0][1] = $m[1];
    }

    return $ary;
}

function imdbCast(array $items)
{
    global $imdbIdPrefix;
    
    // Loop through each item in the items array
    foreach ($items as $item) 
    {
        // Check if the required keys exist.
        $actorid   = isset($item['id']) ? $item['id'] : "";
        $actor     = isset($item['rowTitle']) ? $item['rowTitle'] : "";
        // Build the $character string from characters and attributes
        if (isset($item['characters']) && is_array($item['characters']) && !empty($item['characters'])) 
        {
            // Join all characters if available
            $character = implode(" / ", $item['characters']);
            // Append attributes if present
            if (isset($item['attributes']) && !empty($item['attributes'])) 
            {
                $character .= " " . $item['attributes'];
            }
        }   
        elseif (isset($item['attributes']) && !empty($item['attributes'])) 
            {
                // Use only attributes if characters are not set or empty
                $character = $item['attributes'];
            } 
            else 
            {
                // Default to an empty string if neither field is available
                $character = "";
            }
        // Append episodic credit data if available
        if (isset($item['episodicCreditData']) && is_array($item['episodicCreditData'])) 
        {
            $episodicParts = [];
            if (isset($item['episodicCreditData']['episodesText']) && !empty($item['episodicCreditData']['episodesText'])) {
                $episodicParts[] = $item['episodicCreditData']['episodesText'];
            }
            if (isset($item['episodicCreditData']['tenureText']) && !empty($item['episodicCreditData']['tenureText'])) {
                $episodicParts[] = $item['episodicCreditData']['tenureText'];
            }
            if (!empty($episodicParts)) {
                $character .= " " . implode(", ", $episodicParts);
            }
        }
        // Append the current actor's details
        $cast .= "$actor::$character::$imdbIdPrefix$actorid\n";
    }
    
    return $cast;
}

function imdbCastExtra($imdbID)
{
    global $imdbIdPrefix;
    global $CLIENTERROR;
    global $cache;

    $param = ['header' => ['Accept' => 'application/json',
                           'User-Agent' => 'Mozilla/5.0',
                           'Content-Type' => 'application/json',
                          ]
             ];
    $after = '';
    $cast = '';
    
    do 
    {
        $url = 'https://caching.graphql.imdb.com/?operationName=TitleCreditSubPagePagination&variables={"after":"'.$after.'","category":"cast","const":"tt'.$imdbID.'","first":250,"locale":"en-US","originalTitleText":false,"tconst":"tt'.$imdbID.'"}&extensions={"persistedQuery":{"sha256Hash":"716fbcc1b308c56db263f69e4fd0499d4d99ce1775fb6ca75a75c63e2c86e89c","version":1}}';

        $resp = httpClient($url, $cache, $param);
        if (!$resp['success']) $CLIENTERROR .= $resp['error']."\n";

        // Cast
        #testing code save resp data from imdb
        #file_put_contents('./cache/httpclient-php_imdbData_castextra.html', $resp['data']);  // write page data to file    
        #file_put_contents('./cache/json-castextra', $resp['data']);  // write json data to file
        $json_data_castextra = json_decode( $resp['data'],true);
        #file_put_contents('./cache/jsonDecoded-castextra', print_r($json_data_castextra, true));  // write formated json data to file

        if (isset($json_data_castextra['data']['title']['credits']) &&
            is_array($json_data_castextra['data']['title']['credits'])) 
        {
            $credits = $json_data_castextra['data']['title']['credits'];
            // Loop through each item in the items array
            foreach ($credits['edges'] as $edge) 
            {
                // Check if the required keys exist.
                $actorId   = isset($edge['node']['name']['id']) ? $edge['node']['name']['id'] : "";
                $actor     = isset($edge['node']['name']['nameText']['text']) ? $edge['node']['name']['nameText']['text'] : "";
                // Build the $character string from characters and attributes

                if (is_array($edge['node']['characters'])) 
                {
                    $characterNames = array_map(function ($char) 
                                                {
                                                    return $char['name'];
                                                }, $edge['node']['characters']);
                    $role = implode(' / ', $characterNames);

                    if ($edge['node']['attributes']) 
                    {
                        foreach($edge['node']['attributes'] as $attr) 
                        {
                            $role .= " (" . $attr['text'] . ")";
                        }
                    }
                } 
                else 
                {
                    $role = $edge['node']['attributes']['text'];
                }
                if ($edge['node']['episodeCredits'] && $edge['node']['episodeCredits']['total'] > 0) 
                {
                    $total = $edge['node']['episodeCredits']['total'];
                    $from = $edge['node']['episodeCredits']['yearRange']['year'];
                    $to = $edge['node']['episodeCredits']['yearRange']['endYear'];

                    $role .= ", $total episodes, $from";
                    if ($to) 
                    {
                        $role .= "-$to";
                    }
                }
                // Append the current actor's details
                $cast .= "$actor::$role::$imdbIdPrefix$actorId\n";
            }
        }
    
        $after = $credits['pageInfo']['endCursor'];
    } while ($credits['pageInfo']['hasNextPage']);
    
    return $cast;
}
