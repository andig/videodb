<?php
/**
 * Template functions
 *
 * These functions prepare the data for assignment to the template engine
 *
 * @todo    comment callback functions
 *
 * @author  Andreas Goetz <cpuidle@gmx.de>
 * @package videoDB
 * @version $Id: trace.php,v 2.64 2013/03/13 15:27:02 andig2 Exp $
 */

require_once './core/functions.php';
require_once './core/httpclient.php';

// identifier for URL parameter
$urlid      = 'videodburl';
$striptags  = array('iframe','object','embed','ads','html','head','body','!DOCTYPE');

/**
 * Figures out which part of a given URI is server and path
 * Result in global $base_server and $base_path variables
 *
 * @param  string  $url      URI
 */
function get_base($url)
{
	global $uri;

	$uri = parse_url($url);
	if (!$uri['scheme']) $uri['scheme'] = 'http';
	if (!$uri['host']) $uri['host'] = 'localhost';
	if (!$uri['path']) $uri['path'] = '/';
	$uri['server'] = $uri['scheme'].'://'.$uri['host'];

	// remove filename from path if recognized file type
        $uri['path'] = preg_replace("/^(.*\/)(.*)$/i", '\\1', $uri['path']);

        // append trailing / if missing
	if (!preg_match("/\/$/", $uri['path'])) $uri['path'] .= '/';
}

/**
 * Figures out fully qualified, absolute URI for given (relative) URI,  
 * using global $base_server and $base_path variables
 *
 * @param  string  $url (relative) URI
 * @return string       fully qualified, absolute URI
 */
function get_full_url($url)
{
    global $uri, $config;

    // fully qualified?
    if (preg_match("/^https?:\/\//", $url)) return $url;

    // local absolute path?
    if (preg_match("/^\//", $url)) {
        $url = $uri['server'].$url;
    } else {
        $url = preg_replace("/^\.\//", '', $url);
        $url = $uri['server'].$uri['path'].$url;
    }
	return $url;
}

/**
 * Helper function to enable http(s) redirects
 *
 * @return string       protocol scheme (http or https)
 */
function getScheme()
{
    #  || strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5)) != 'https'
    return (!isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != 'on') ? 'http' : 'https';
}

/**
 * array_delete removes elements from array
 *
 * @param  array   $a       input array
 * @param  string  $i       index to delete from
 * @param  string  $count   number of items to delete
 * @return array            resulting array
 */
function array_delete($a, $i, $count = 1)
{
	$result = array_slice($a, 0, $i);

	foreach (array_slice($a, $i+$count) as $val)
	{
		array_push($result, $val);
	}

	return $result;
}

/**
 * Check if this item is already in the database
 */
function is_known_item($id, &$sp_id, &$sp_diskid)
{
    $SQL = "SELECT imdbID, id, diskid
              FROM ".TBL_DATA."
             WHERE imdbID = '".addslashes($id)."'
             ORDER BY diskid DESC";
    $result = runSQL($SQL);

    // do we know this movie?
    if (count($result) && isset($result[0]['imdbID']) && adultcheck($result[0]['id']) && check_videopermission(PERM_READ, $result[0]['id']))
    {
        $sp_id      = $result[0]['id'];
        $sp_diskid  = $result[0]['diskid'];
        if (!$sp_diskid) $sp_diskid = 'no_diskid';
        
        return true;
    }
    
    return false;
}

function _replace_enclosed_tag_traced($matches)
{
	global $urlid, $config, $uri, $page, $iframe;

	// quotes
	$matches = array_delete($matches, 2);

	// get fully qualified url
	$url = get_full_url($matches[2]);
	$url = preg_replace("/&".session_name()."=[\d|\w]+$/", '', $url);

	// show anchor translation if debugging
	$note = ($config['debug']) ? "($matches[2] -> $url)" : '';

	// enable _top navigation for iframe mode
	$top = ($iframe) ? ' target="_top"' : '';

	$options = '';
	$title = strip_tags($matches[4]);

    // what's our host?
    $engine = (preg_match('/(imdb|amazon|filmweb)/i', $uri['host'], $m)) ? $m[1] : '';
    
    if ($engine == 'imdb')
    {
        // imdb
        // fix for IMDB speciality: 2nd href inside first one
        if (preg_match("/http.*?http/i", $url)) 
        {
            return implode('', array_slice($matches,1));
        }

        // title link?
        // either /Title?0328828	(old-style or tiger-redirect)
        // or /title/tt0306734/	(new-style)
        if (preg_match("#/[Tt]itle(\?|/tt)(\d+)/?(\?|$)#", $url, $m) && $title)
        {
            // don't link images to avoid matching the imdb page flicker
            if (!preg_match("/<img\s+/i", $matches[4]))
            {
                $append = ' <a href="edit.php?save=1&amp;lookup=2&amp;imdbID='.
                          urlencode("imdb:".$m[2]).'"'.$top.'><img src="'.img('add.gif').
                          '" valign="absmiddle" border="0" alt="Add Movie"/></a>';

                if (is_known_item('imdb:'.$m[2], $sp_id, $sp_diskid))
                {
                    $append.= ' <a href="show.php?id='.$sp_id.'"'.$top.'><img src="'.img('existing.gif').
                              '" title='.$sp_diskid.' valign="absmiddle" border="0" alt="Show Movie"/></a>';
                }
            }
        }
        // amend url for seasons/year the path for previous and next season/year url's at bottom of eposides page
        if (preg_match("#(=(.*?)\&ref_=ttep_ep_sn_(pv|nx))|(=(.*?)\&ref_=ttep_ep_yr_(pv|nx))#",$matches[2],$mymatches))
        {    
//          echo "<BR> in matches"; var_dump($matches);
//          echo "mymatches 1"; var_dump($mymatches);     
//          echo '<BR> $url - '.$url;
            if (!preg_match('#(\/episodes\/\?season=)|(\/episodes\/\?year=)#',$url,$mymatches))
            {
//              echo "<BR> mymatches 2"; var_dump($mymatches);
                $patterns = array ('#(\?season)#','#(\?year)#');
                $replacements = array('episodes?season','episodes?year');
                $url = preg_replace($patterns,$replacements,$url);
//              echo '<BR> $url after - '.$url;
            }
                // remove _ajax in url will be added by js. 
           if (preg_match('#\/episodes\/_ajax\/#',$url,$mymatches))
            {
//              echo "<BR> mymatches 3"; var_dump($mymatches);
                $url = preg_replace('#\/episodes\/_ajax#','',$url);
//              echo '<BR> $url after ajax - '.$url;
            }
//          echo '<BR> $url - end '.$url;
        }
    }
    elseif ($engine == 'amazon')
	{
		// amazon
		if (preg_match("#exec/obidos/tg/detail/-/([0-9A-Z]{10,})/#", $url, $m)) 
		{
			$id = $m[1];
			$append .= ' <a href="edit.php?save=1&amp;lookup=2&amp;imdbID='.urlencode('amazon:'.$id).
				       '"><img src="'.img('add.gif').'" valign="absmiddle" border="0" alt="Add Movie"/></a>';
		}
	}
    elseif ($engine == 'filmweb')
	{
		// filmweb.pl
		if (preg_match("/[Ff]ilm[\?\.]id=(\d+)/i", $url, $m) && $title) 
		{
			$append = " <a href=\"edit.php?save=1&amp;engine=filmweb&amp;lookup=1&amp;imdbID=".
			          urlencode('filmweb:'.$m[1])."&amp;title="."\">".
			          '<img src="'.img('add.gif').'" valign="absmiddle" border="0" alt="Add Movie"/></a>';
		}
	}

	$url = getScheme().'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?$urlid=".urlencode($url);
	if ($iframe) $url .= "&iframe=".$iframe;

	return $matches[1].$url.$matches[3].$matches[4].$note.$matches[5].$append;
}

function _replace_tag($matches)
{
	global $urlid, $striptags, $iframe;
	
	$url = get_full_url($matches[4]);
	if (in_array('ads', $striptags) && (strtolower($matches[2]) == 'img') && preg_match("/\/ads?\//i", $url)) return '';

	// switch on tag
	switch (strtolower($matches[2])) {
		// attn: order is crucial as $url needs be saved to get overwritten
		case 'form' :	$append = "<input type='hidden' name='$urlid' value='$url'/>";
						$url = getScheme().'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
						if ($iframe) $append .= "<input type='hidden' name='iframe' value='$iframe'/>";
						break;
		case 'area' :	$parameters = "?$urlid=".urlencode($url);
						$url = getScheme().'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
						if ($iframe) $parameters .= "&iframe=".$iframe;
						break;
	}

	return $matches[1].$url.$parameters.$matches[5].$append;
}

function _remove_tag($matches)
{
	return '';
}

function imdb_replace_title_callback($matches)
{
	global $uri;

	$result = $matches[1].$matches[2];

	if (preg_match("#/title/tt(.+)/#", $uri['path'], $m) ) 
	{
        $result .= ' <a href="edit.php?save=1&amp;lookup=2&amp;imdbID='.
                    urlencode("imdb:".$m[1]).'"><img src="'.img('add.gif').
                    '" valign="absmiddle" border="0" alt="Add Movie"/></a>';

        if (is_known_item('imdb:'.$m[1], $sp_id, $sp_diskid))
        {
            $result .= ' <a href="show.php?id='.$sp_id.'"><img src="'.img('existing.gif').
                         '" title='.$sp_diskid.' valign="absmiddle" border="0" alt="Show Movie"/></a>';
	}
	}

	$result .= $matches[3];

	return $result;
}

/**
 * HTML Code Conversion
 *
 * Converts HTML elements to allow proxying through trace.php
 * e.g. all href's are converted to trace.php?url=href links
 *
 * When a link of the IMDB movie title format is discovered, 
 * the "Add Disc" icon is appended, linking to edit.php with the correct disk id
 *
 * Note: this function is far from complete, more help is desired
 *
 * @param   string  $html   input HTML code including relative links etc.
 * @return  string          output HTML code with absolute proxied links, forms image maps etc.
 */
function fixup_HTML($html)
{
	global $striptags, $config, $uri;

	// base
	if (preg_match("/<base\s+href=(\"|')(.*?)\\1/i", $html, $matches)) get_base($matches[2]);

	// replace unwanted tags
	$html = preg_replace_callback("/(<\/?(".join("|",$striptags).")(\s+.*?)?>)/is", '_remove_tag', $html);

	// link, map/area
	$html = preg_replace_callback("/(<(link|area|base)\s+[^>]*?href\s*=\s*(\"|'))([^>]*?)(\\3.*?>)/is", '_replace_tag', $html);
	$html = preg_replace_callback("/(<(link|area|base)\s+[^>]*?href\s*=\s*([^\"']))([\d\w\.\/\+\%-:=&_]+?)(\s*[^>]*?>)/is", '_replace_tag', $html);
	// image, frame, script
	$html = preg_replace_callback("/(<(ima?ge?|frame|iframe|script)\s+[^>]*?src\s*=\s*(\"|'))([^>]*?)(\\3.*?>)/is", '_replace_tag', $html);
	$html = preg_replace_callback("/(<(ima?ge?|frame|iframe|script)\s+[^>]*?src\s*=\s*([^\"']))([\d\w\.\/\+\%-:=&_]+?)(\s*[^>]*?>)/is", '_replace_tag', $html);
	// form  
        //<input type="hidden" name="ref_" value="nv_sr_sm"/>
        $html = preg_replace_callback('#<input type="hidden" name="ref_" value="nv_sr_sm"/>#', '_remove_tag', $html);        
	$html = preg_replace_callback("/(<(form)\s+[^>]*?action\s*=\s*(\"|'))([^>]*?)(\\3[^>]*?>)/is", '_replace_tag', $html);
	$html = preg_replace_callback("/(<(form)\s+[^>]*?action\s*=\s*([^\"']))([\d\w\.\/\+\%-:=&_]+?)(\s*[^>]*?>)/is", '_replace_tag', $html);
	// href
	$html = preg_replace_callback("/(<a\s+[^>]*?href\s*=\s*(\"|'))([^>]*?)(\\2[^>]*?>)(.*?)(<\/a\s*>)/is", '_replace_enclosed_tag_traced', $html);
        $html = preg_replace_callback("/(<a\s+[^>]*?href\s*=\s*())([\d\w\.\/\+\%-:=&_]+)(\s*[^>]*?>)(.*?)(<\/a\s*>)/is", '_replace_enclosed_tag_traced', $html);
                     
	// title
    if (stristr($uri['host'], 'imdb'))
    {
	// this line maybe redundent with imdb now using webpack JS
        $html = preg_replace_callback("#(<h1 textlength.*?>)(.*?)(</h1>)#si", 'imdb_replace_title_callback', $html);

        // imdb form does not accept utf8
        $html = preg_replace("/(form\s+.*?)(>)/i", '\\1 accept-charset="ISO-8859-1" \\2', $html );
    }  
 
    return $html;
}

function request($urlonly=false)
{
	global $urlid, $url;
	
	// get or post?
	$pass = ($_POST) ? $_POST : $_GET;

	// don't use $_REQUEST or cookies will screw up the query	
	foreach ($pass as $key => $value) 
    {
		switch ($key) 
        {
			case $urlid:
				$url = html_entity_decode(urldecode($value));
				break;				
			case session_name():
			case 'videodbreload':
			case 'iframe':
				break;
//                        case 'q':	
//                            $url = 'http://www.imdb.com/find';  // quick fix for search 
			default:
				if ($request) $request .= "&";
				$request .= "$key=$value";
		}
	}

	// going directly to trace.php without options?
	if (!$url) $url = 'http://www.imdb.com';

	// remove session identifier before request is sent or caching will not work
	$url = preg_replace("/&".SID."$/", "", $url);
	// workaround for fishy IMDB URLs
	$url = preg_replace("/\&amp;/", "&", $url);

	// don't fetch, just find target
	if ($urlonly) return($url);

	// append request parameters
        if ($_POST) {
		$post = $request;
	} elseif ($request) {
            if (preg_match("#\?#",$url,$matches))
            {
                $url .= "&".$request;
            }
            else
            {
		$url .= "?".$request;
            }
	}

    // encode possible spaces, use %20 instead of +
	$url = preg_replace('/ /','%20', $url);
        
    $response = httpClient($url, $_GET['videodbreload'] != 'Y', array('post' => $post));

	// url after redirect
	get_base($response['url']);

	if ($response['success'] != true)
    {
		$page = 'Error: '.$response['error'];
		if ($response['header']) $page .= '<br/>Header:<br/>'.nl2br($response['header']);
	}
    else
    {
		if (!$cache) putHTTPcache($url.$post, $response);
		$page = $response['data'];
	}
	return $page;
}

function fixup_javascript($html)
{
    global $uri;
    
    if (stristr($uri['host'], 'imdb') === false)
    {
        return $html;
    }

    // get cache folder
    $cachefolder = cache_get_folder('javascript');  //get cache root folder
    $error = cache_create_folders($cachefolder, 0); // ensure folder exists
    // empty javascript cache as imdb keep changing things
    array_map('unlink', glob($cachefolder."/*.*"));

    // find all imdb javascript files
    preg_match_all('#[\"\']\s*\Khttps?:[^\"\']+?\.js#',
               $html,
               $matches_all);
//echo "<br> list all js files - "; var_dump($matches_all);
    //  for performance reduce matches by excluding all duplicate files
    $unique_matches = array_unique($matches_all[0]);
//echo "<br> list all js files - "; var_dump($unique_matches);
    // loop thru files
    $x = 0;
    $season_year_done = false;
    $search_bar_done = false;
    $add_movie_done = false;
    $fix_href_done = false;
    $fix_href_cast_done = False;
    
    foreach ($unique_matches as $js_file)
    {
//echo "<br>x is ".$x."  file name - ".$js_file;
        $js_file_data = file_get_contents($js_file);
        
        if (!$season_year_done)
        {
            // for season, year change drop down list on episode list
            $find_string = 'if(d!==c){var e="/title/"';
            $pattern = preg_quote('#'.$find_string.'#');  // add escape delimiters
            if (preg_match($pattern, $js_file_data, $matches) )
            {
                $html = replace_javascript_seasonyear ($html,$js_file,$js_file_data,$cachefolder);
                $season_year_done = true;
            }
        }
        
        if (!$search_bar_done)
        {        
            // for search bar and interactive search list
            $find_string = 'hiddenFields:[{name:"ref_",val:"nv_sr_sm"}]';
            $pattern = preg_quote('#'.$find_string.'#');  // add escape delimiters
            if (preg_match($pattern, $js_file_data, $matches)  )
            {
                $html = replace_javascript_search ($html,$js_file,$js_file_data,$cachefolder);
                $search_bar_done = true;
            }
        }

        if (!$add_movie_done)
        { 
            // add add/show movie links    
            $find_string = 'titleData.productionStatus';
            $pattern = preg_quote('#'.$find_string.'#');  // add escape delimiters
            if (preg_match($pattern, $js_file_data, $matches) )
            {
                $html = replace_javascript_addmovie ($html,$js_file,$js_file_data,$cachefolder);
                $add_movie_done = true;
            }
        }
        
        if (!$fix_href_done)
        {
            // on main series page for season, year select drop down list on browse episodes
            $find_string = 'return window.location.href="SEE_ALL"';
            $pattern = preg_quote('#'.$find_string.'#');  // add escape delimiters
            // fix title href
            $pattern_1 = '#HEADER:function\(.\){return#';
            if (preg_match($pattern, $js_file_data, $matches) || 
                preg_match($pattern_1, $js_file_data, $matches_1) )
            {
                $html = replace_javascript_fix_href ($html,$js_file,$js_file_data,$cachefolder);
                $fix_href_done = true;
            }   
        }

        if (!$fix_href_cast_done)
        {
            // on main series page fix title href's all cast & crew, creator, director, writer
            $find_string = '"/title/".concat(b.id,"/fullcredits")';
            $pattern = preg_quote('#'.$find_string.'#');  // add escape delimiters
            if (preg_match($pattern, $js_file_data, $matches))
            {
                $html = replace_javascript_fix_href_cast ($html,$js_file,$js_file_data,$cachefolder);
                $fix_href_cast_done = true;
            }   
        }

        // release file data from memory to avoid memory exceeeded error 
        $js_file_data = '';     
        unset($js_file_data);
        
        // check if all needed files have been processed and break out
        if ($season_year_done && $search_bar_done && 
            $add_movie_done   && $fix_href_done   &&
            $fix_href_cast_done )
        { 
            break;
        }
        $x++ ;
    }
    return ($html);
}

function fixup_json($json)
{
    // insert / after title no - fixes issue with title no being deleted in $uri in function get_base
    // "id":"tt5996792" allow for different digits in page
    $pattern = '#(\"id\":\"tt)(\d+)(\")#';
    preg_match_all($pattern,$json, $matches_all);
    for ($x = 0;$x < count($matches_all[2]);$x++)
    {
        $tt_no = preg_quote($matches_all[2][$x]);
        $pattern = '#\"id\":\"tt'.$tt_no.'\"#';
//preg_match_all($pattern,$json, $matches_pre_replace);
        $json = preg_replace($pattern,
                             '"id":"tt'.$tt_no.'/"',            
                             $json);
    }
// testing code
//$pattern = '#\"id\":\"tt.*?.\"#';
//preg_match_all($pattern,$page, $mm_after);
    
    return $json;  
}

function replace_javascript_fix_href_cast ($html,$js_file_name,$js_file_data,$cachefolder)
{
    global $iframe;
    // allow for iframe templates
    if ($iframe) $iframe_val = "&iframe=".$iframe;
    
    // find_string  "/title/".concat and  "/name/".concat
    $pattern = '#(")(/title/|/name/)(")(\.concat)#';
//echo "<BR> pre-match return - ".preg_match_all($pattern, $js_file_data, $matches);
//echo "<br> js file - search/title href"; var_dump($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace_callback($pattern, 
                                              function ($matches) 
                                              { return '"http://".concat(window.location.host).concat(window.location.pathname).concat("?'.$iframe_val.'&videodburl=https://www.imdb.com'.$matches[2].'")'.$matches[4];
                                              },
                                              $js_file_data);
    }

    $file_path = './'.$cachefolder.'imdb-clone-'.'href-cast-override'.'.js';
    //add comment line to file and save to cache (overwritten if present) 
    file_put_contents($file_path, '/* this files original name - '.$js_file_name.' */');
    // save js data file to cache
    file_put_contents($file_path, $js_file_data, FILE_APPEND);

//echo "<BR> - $js_file_name-".$js_file_name;
    $pattern = preg_quote('#'.$js_file_name.'#');  // escape all delimitters in file name
//echo "<BR> - pattern-".$pattern;
    $html = preg_replace($pattern,$file_path,$html);

    return $html;
}

function replace_javascript_fix_href ($html,$js_file_name,$js_file_data,$cachefolder)
{
    global $iframe;
    // allow for iframe templates
    if ($iframe) $iframe_val = "&iframe=".$iframe;
    
    // drop down for seasopn no and year
    //string -    "https://".concat(window.location.host)
    $pattern = '#("https://")(\.concat\(window\.location\.host\))#';
//echo "<br>".$pattern;
    if (preg_match($pattern, $js_file_data, $matches))
    {
//echo "<br> js file - find for season/year drop down"; var_dump($matches);
        $js_file_data = preg_replace($pattern,
                                     '"http://"'.$matches[2].'.concat(window.location.pathname).concat("?'.$iframe_val.'&videodburl=https://www.imdb.com")',
                                     $js_file_data); 
    }
    
    // title href
    // string - {return"/title/" - occurs in multiple places
    $pattern = '#({return")(/title/)(")#';
//echo "<br>".$pattern;
    if (preg_match($pattern, $js_file_data, $matches))
    {
//echo "<br> js file - find for href with title"; var_dump($matches);
        $js_file_data = preg_replace($pattern,
                                     $matches[1].'http://".concat(window.location.host).concat(window.location.pathname).concat("?'.$iframe_val.'&videodburl=https://www.imdb.com'.$matches[2].'")',
                                     $js_file_data);
    }

       // find_string  "/search/title/?series=".concat        
    $pattern = '#(")(/search/title/\?.*?\=)(")(\.concat)#';
//echo "<BR> pre-match return - ".preg_match_all($pattern, $js_file_data, $matches);
//echo "<br> js file - search/title href"; var_dump($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace_callback($pattern, 
                                              function ($matches) 
                                              { return '"http://".concat(window.location.host).concat(window.location.pathname).concat("?'.$iframe_val.'&videodburl=https://www.imdb.com'.$matches[2].'")'.$matches[4];
                                              },
                                              $js_file_data);
    }

    $file_path = './'.$cachefolder.'imdb-clone-'.'browse-episodes-override'.'.js';
    //add comment line to file and save to cache (overwritten if present) 
    file_put_contents($file_path, '/* this files original name - '.$js_file_name.' */');
    // save js data file to cache
    file_put_contents($file_path, $js_file_data, FILE_APPEND);

//echo "<BR> - $js_file_name-".$js_file_name;
    $pattern = preg_quote('#'.$js_file_name.'#');  // escape all delimitters in file name
//echo "<BR> - pattern-".$pattern;
    $html = preg_replace($pattern,$file_path,$html);

    return $html;
}

function replace_javascript_addmovie ($html,$js_file_name,$js_file_data,$cachefolder)
{
    global $uri, $iframe;
    // allow for iframe templates
    if ($iframe) $iframe_val = "&iframe=".$iframe;

// test code to debug if statement match
//preg_match("#/title/tt(\d+)#", $uri['path'], $m);
//echo "<br> title - addmovie"; var_dump($m); 
    if (preg_match("#/title/tt(\d+)#", $uri['path'], $m)) 
    {
        // look for  rating)&&ba(l.InlineListItem,null,ba(va,{text: - char ba & va change randomly
        $pattern = "#rating\)&&(.*?.\(l\.InlineListItem,null,.*?.,\{text:)#";
        preg_match($pattern, $js_file_data, $matches);
//echo "<br> js data - function names"; var_dump($matches);
        $append = ','.$matches[1].'"Add Movie",href:"edit.php?save=1&lookup=2&imdbID=imdb:'.$m[1].'"}))';
        if (is_known_item('imdb:'.$m[1], $sp_id, $sp_diskid))
        {
            $diskid = "";
            if ($sp_diskid <> "no_diskid") 
            {
                $diskid = " (Diskid:".$sp_diskid.")";
            }
            $append.= ','.$matches[1].'"Show Movie'.$diskid.'",href:"show.php?id='.$sp_id.'"}))';
        }
        //  string to find for in replace  - format:"{hours} {minutes}",unitDisplay:"narrow"})))}
        $pattern = '#(format:"{hours} {minutes}",unitDisplay:"narrow"}\)\))(\)\})#';
        preg_match($pattern, $js_file_data, $matches);
//echo "<br> js file - addmovie"; var_dump($matches);
        $js_file_data = preg_replace($pattern,
                                     $matches[1].$append.$matches[2],            
                                     $js_file_data);
    }
    
    // fix hrefs - title
    //            var n="/title/".concat(e.titleData.id,"/fullcredits/")
    $pattern = '#(var n=")(/title/)(")(\.concat\(e\.titleData.id,"/fullcredits/"\))#';
//echo preg_match($pattern, $js_file_data, $matches);
//echo "<br> js data - function names"; var_dump($matches);            
    if (preg_match($pattern, $js_file_data, $matches))
    {
//echo "<br> js file - find for href with title"; var_dump($matches);
        $js_file_data = preg_replace($pattern,
                                     $matches[1].'http://".concat(window.location.host).concat(window.location.pathname).concat("?'.$iframe_val.'&videodburl=https://www.imdb.com'.$matches[2].'")'.$matches[4],
                                     $js_file_data);
    }
    
    // fix hrefs - name
    //  href:"/name/".concat
    $pattern = '#(href:")(/name/)(")(\.concat)#';
//echo "<BR> - pattern - ".$pattern;
//echo "<BR> - match - ".preg_match($pattern, $js_file_data, $matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
//echo "<br> js data - /names/"; var_dump($matches);
    $js_file_data = preg_replace($pattern,
                                 $matches[1].'http://".concat(window.location.host).concat(window.location.pathname).concat("?'.$iframe_val.'&videodburl=https://www.imdb.com'.$matches[2].'")'.$matches[4],
                                 $js_file_data);
    }
    
    $file_path = './'.$cachefolder.'imdb-clone-'.'addmovie-override'.'.js';
    //add comment line to file and save to cache (overwritten if present) 
    file_put_contents($file_path, '/* this files original name - '.$js_file_name.' */');
    // save js data file to cache
    file_put_contents($file_path, $js_file_data, FILE_APPEND);
    
    $pattern = preg_quote('#'.$js_file_name.'#');  // escape all delimitters in file name
//echo "<BR> - pattern-".$pattern;
    $html = preg_replace($pattern,$file_path,$html);
    
    return $html; 
}

function replace_javascript_search ($html,$js_file_name,$js_file_data,$cachefolder)
{
     global $iframe;
    $url = getScheme().'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
    $file_path = './'.$cachefolder.'imdb-clone-'.'search-override'.'.js';
    
    // look for   search:{searchEndpoint:"https://v2.sg.media-imdb.com/suggestion",queryTemplate:"%s%s/%s.json",formAction:"/find",formMethod:"get",inputName:"q",hiddenFields:[{name:"ref_",val:"nv_sr_sm"}]},
    $pattern = '#(search:\{searchEndpoint:")(.*?)(",queryTemplate:")(.*?)((".*?formAction:")(.*?)(".*?hiddenFields:\[))(.*?\]\},)#';
    preg_match($pattern, $js_file_data, $matches);
//echo "<br> search js matches - "; var_dump($matches); var_dump($url);
    if ($iframe) $iframe_val = '{name:"iframe",val:"'.$iframe.'"},';
    $replace_val = $matches[1].$url.$matches[3].'?videodburl='.$matches[2]."/".$matches[4].$matches[5].'{name:"videodburl",val:"http://www.imdb.com'.$matches[7].'"},'.$iframe_val.$matches[9];
//echo "<br> search replace val - "; var_dump($replace_val);
    $js_file_data = preg_replace($pattern,$replace_val, $js_file_data);

    //"search-result--const",href:e.url} and "search-result--video",href:e.url} and "search-result--link",href:e.url}
    $pattern = '#(",href:)(e\.url\})#';
    preg_match($pattern, $js_file_data, $matches);
//echo "<br> interactive search js matches - "; var_dump($matches);
    $iframe_val = '';
    if ($iframe) $iframe_val = "&iframe=".$iframe;    
    $replace_val = $matches[1].'"'.$url.'?'.$iframe_val.'&videodburl=https://www.imdb.com"'.'+'.$matches[2];
//echo var_dump($replace_val);
    $js_file_data = preg_replace($pattern,$replace_val, $js_file_data);            
    // save file to cache (overwritten if present)
    //add comment line to file and save to cache (overwritten if present) 
    file_put_contents($file_path, '/* this files original name - '.$js_file_name.' */');
    // save js data file to cache
    file_put_contents($file_path, $js_file_data, FILE_APPEND);
    
    $pattern = preg_quote('#'.$js_file_name.'#');  // escape all delimitters in file name
//echo $pattern;
    $html = preg_replace($pattern,$file_path,$html);

    return $html;
}
function replace_javascript_seasonyear ($html,$js_file_name,$js_file_data,$cachefolder)
{
    global $iframe;
    
//echo "<br> in replace_javascript";
//echo "<br>".$js_file_name; echo "   ".$cachefolder;
    // allow for iframe templates
    if ($iframe) $iframe_val = "&iframe=".$iframe;
    
    $file_path = './'.$cachefolder.'imdb-clone-seasonyear-change.js';
    //string -    if(d!==c){var e="/title/"
    $pattern = '#(if\(d!==c\){var e=)(\"/title/\")#';
//echo "<br>".$pattern;
    preg_match($pattern, $js_file_data, $matches);
//echo "<br> js file - find for season"; var_dump($matches);
    $js_file_data = preg_replace($pattern,
                                 $matches[1].'"trace.php?'.$iframe_val.'&videodburl=https://www.imdb.com"+'.$matches[2],
                                 $js_file_data);
    //add comment line to file and save to cache (overwritten if present) 
    file_put_contents($file_path, '/* this files original name - '.$js_file_name.' */');
    // save js data file to cache
    file_put_contents($file_path, $js_file_data, FILE_APPEND);
    
    $pattern = preg_quote('#'.$js_file_name.'#');  // escape all delimitters in file name
    $html = preg_replace($pattern,$file_path,$html);
//echo "<BR> - pattern-".$pattern;
    return $html;
}

// make sure this is a local access
if (!preg_match('/^https?:\/\/'.$_SERVER['SERVER_NAME'].'/i', $_SERVER['HTTP_REFERER']))
{
	// errorpage('Access denied', 'Access to trace.php is allowed for local scripts only. Please make sure to send a referer to allow verification!');
}

/**
 * iframe modes
 *  0: "classic" mode - no use of iframes
 *  1: "iframe" mode 
 *		used to display template containing iframe
 *  2: "iframe" mode 
 *		used to display iframe contents
 */

if ($iframe == 1)
{
	// mode 1: display template with url
	$url = request(true);
}
else
{
	// mode 0 or 2: fetch data for display

    // fetch URL
    $fetchtime = time();
    $page = request();
    
 //testing code page from call to imdb
 //$file_path = './cache/pagedata-html-before-processing.txt';
 //file_put_contents($file_path, $page);
    
    $fetchtime = time() - $fetchtime;

	// convert HTML for output
    $page = fixup_HTML($page);
    $page = fixup_javascript($page);
    
//testing code page after our processing
//$file_path = './cache/pagedata-html-after-processing.txt';
//file_put_contents($file_path, $page);
}

if (    $iframe == 2 || 
        preg_match('#\/_ajax#', $videodburl, $matches_ajax) || 
        preg_match('#\.json#', $videodburl, $matches_json)  ||
        preg_match('#\_json#', $videodburl, $matches_json_1)
    )
{
    if ($matches_json)
    {
//testing code
//$file_path = './cache/pagedata-json-before-processing.txt';
//file_put_contents($file_path, $page);
        
        $page = fixup_json($page);

//testing code page after json amended
//$file_path = './cache/pagedata-json-after-processing.txt';
//file_put_contents($file_path, $page);
    }
    elseif ($matches_json_1)
    {
//$current_time = date("Y-m-d")." T".date("H-i-s");  
//$file_path = './cache/pagedata-json_1-no-processing_json-'.$current_time.'.txt';
//file_put_contents($file_path, $page);        
    }
    elseif ($matches_ajax)
    {
//$current_time = date("Y-m-d")." T".date("H-i-s");
//$file_path = './cache/pagedata-ajax-no-processing_ajax-'.$current_time.'.txt';
//file_put_contents($file_path, $page);        
    }
    
    // mode 2: display data into iframe
    // ajax call: dissplay data from imdb (no head)
    //testing code save page before send to browser
    //$file_path = './cache/pagedataframe.txt';
    // file_put_contents($file_path, $page);
    echo($page);
    exit();
}

// mode 0 or 1: prepare templates
tpl_page('imdbbrowser');
    //testing code save page before send to browser
    //$file_path = './cache/pagedata.html';
    //file_put_contents($file_path, $page);
$smarty->assign('url', $url);
$smarty->assign('page', $page);
$smarty->assign('fetchtime', $fetchtime);

// display templates
tpl_display('trace.tpl');

?>
