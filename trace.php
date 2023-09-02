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
 * input
 */
$iframe = req_int('iframe');
$videodburl = req_string('videodburl'); // #18
$videodbreload = req_int('videodbreload');

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
             WHERE imdbID = '".escapeSQL($id)."'
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
			$append = ' <a href="edit.php?save=1&amp;lookup=2&amp;imdbID='.urlencode('amazon:'.$id).
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
    $request = '';
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
    foreach ($unique_matches as $js_file_name)
    {
        $partfilename = '';
//echo "<br>x is ".$x."  file name - ".$js_file;
        $js_file_data = file_get_contents($js_file_name);
        
        // add add/show to main title on episode list page @ aug 2023
        $pattern = '#'.preg_quote('mainPageHref:', '#').'#';  // add escape delimiters
       if (preg_match($pattern, $js_file_data, $matches) )
       {
           $js_file_data = replace_javascript_eposidelistmain ($js_file_data, $html);
           $partfilename .= '-eposidelistmain';
       }        

        // add add/show to New version of episode list page @ aug 2023
        $pattern = '#'.preg_quote('SeasonsTab="tab-seasons"', '#').'#';  // add escape delimiters
        if (preg_match($pattern, $js_file_data, $matches) )
        {
            list($js_file_data, $html) = replace_javascript_episodelist ($js_file_data, $html);
            $partfilename .= '-eposidelist';
        }

        // for season, year change drop down list on episode list
        $pattern = '#bySeason#';
        if (preg_match($pattern, $js_file_data, $matches) )
        {
            $js_file_data = replace_javascript_seasonyear ($js_file_data);
            $partfilename .= '-seasonyear';
        }
        
        // for search bar and interactive search list
        $find_string = 'hiddenFields:[{name:"ref_",val:"nv_sr_sm"}]';
        $pattern = '#'.preg_quote($find_string, '#').'#';  // add escape delimiters
        if (preg_match($pattern, $js_file_data, $matches)  )
        {
            $js_file_data = replace_javascript_search ($js_file_data);
            $partfilename .= '-search';
        }
        
        // for add/show movie links    
        $find_string = 'displayableProperty.value.plainText}),(0';
        $pattern = '#'.preg_quote($find_string, '#').'#';  // add escape delimiters
        if (preg_match($pattern, $js_file_data, $matches) )
        {
            $js_file_data = replace_javascript_addmovie ($js_file_data);
            $partfilename .= '-addmovie';
        }
 
        // on main series page for season, year select drop down list on browse episodes
        $find_string = 'return window.location.href="SEE_ALL"';
        $pattern = '#'.preg_quote($find_string, '#').'#';  // add escape delimiters
        // fix title href
        $pattern_1 = '#HEADER:function\(.\){return#';
        if (preg_match($pattern, $js_file_data, $matches) || 
            preg_match($pattern_1, $js_file_data, $matches_1) )
        {
            $js_file_data = replace_javascript_fix_href ($js_file_data);
            $partfilename .= '-href';
        }   
        
        // on main series page fix title href's all cast & crew, creator, director, writer
        // string  '"/title/".concat(h.id,"/fullcredits")'; h is variable
        $pattern = '#"/title/"\.concat\(.*?\.id,"/fullcredits"\)#';  // add escape delimiters
        if (preg_match($pattern, $js_file_data, $matches))
        {
            $js_file_data = replace_javascript_fix_href_cast ($js_file_data);
            $partfilename .= '-hrefcast';
        }
        
        if ($partfilename <> '')
        {
            $file_path = './'.$cachefolder.'imdb-clone'.$partfilename.'.js';
            //add comment line to file and save to cache (overwritten if present) 
            file_put_contents($file_path, '/* Processed by - replace_javascript_('.$partfilename.') : this files original name - '.$js_file_name.' */');
            // save js data file to cache
            file_put_contents($file_path, $js_file_data, FILE_APPEND);

            $pattern = '#'.preg_quote($js_file_name, '#').'#';  // escape all delimitters in file name
            $html = preg_replace($pattern,$file_path,$html);
        }
        // release file data from memory to avoid memory exceeeded error 
        $js_file_data = '';     
        unset($js_file_data);
        
        $x++ ;
    }
    return ($html);
}

function fixup_json($json)
{
/*  section commented out as not needed as of june 2022 - code left incase needed in future
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
 */
// testing code
//$pattern = '#\"id\":\"tt.*?.\"#';
//preg_match_all($pattern,$page, $mm_after);
    
    return $json;  
}

function replace_javascript_fix_href_cast ($js_file_data)
{
    global $iframe;
    // allow for iframe templates
    $iframe_val = '';
    if ($iframe) $iframe_val = "&iframe=".$iframe;
    
    // find_string  "/title/".concat and  "/name/".concat
    $pattern = '#(")(/title/|/name/)(")(\.concat)#';
//echo "<BR> pre-match return - ".preg_match_all($pattern, $js_file_data, $matches);
//echo "<br> js file - search/title href"; var_dump($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace_callback($pattern, function ($matches) use ($iframe_val) {
            return '"http://".concat(window.location.host).concat(window.location.pathname).concat("?'.$iframe_val.'&videodburl=https://www.imdb.com'.$matches[2].'")'.$matches[4];
        }, $js_file_data);
    }

    return $js_file_data;
}

function replace_javascript_fix_href ($js_file_data)
{
    global $iframe;
    // allow for iframe templates
    $iframe_val = '';
    if ($iframe) $iframe_val = "&iframe=".$iframe;
    
    // drop down for season no and year on main series page
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
        $js_file_data = preg_replace_callback($pattern, function ($matches) use ($iframe_val) {
            return '"http://".concat(window.location.host).concat(window.location.pathname).concat("?'.$iframe_val.'&videodburl=https://www.imdb.com'.$matches[2].'")'.$matches[4];
        }, $js_file_data);
    }

    return $js_file_data;
}

function replace_javascript_addmovie ($js_file_data)
{
    global $uri, $iframe;
    // allow for iframe templates
    $iframe_val = '';
    if ($iframe) $iframe_val = "&iframe=".$iframe;

// test code to debug if statement match
//preg_match("#/title/tt(\d+)#", $uri['path'], $m);
//echo "<br> title - addmovie"; var_dump($m); 
    if (preg_match("#/title/tt(\d+)#", $uri['path'], $m)) // $m[1] is imdb tltle no
    {
        // look for &&S.push({text:p.displayableProperty.value.plainText}),(0,r.jsx)   S p and r can change
        $pattern = "#&&(.?\.push\(\{text\:)(.?\.displayableProperty\.value\.plainText\}\),\(0,.?\.jsx\))#";
        //              111111111111111111  22222222222222222222222222222222222222222222222222222222222
        preg_match($pattern, $js_file_data, $matches);
//echo "<br> js data - function names"; var_dump($matches);
        $append = $matches[1].'"Add Movie", link: "edit.php?save=1&lookup=2&imdbID=imdb:'.$m[1].'"}),';
        if (is_known_item('imdb:'.$m[1], $sp_id, $sp_diskid))
        {
            $diskid = "";
            if ($sp_diskid <> "no_diskid") 
            {
                $diskid = " (Diskid:".$sp_diskid.")";
            }
            $append.= $matches[1].'"Show Movie'.$diskid.'", link: "show.php?id='.$sp_id.'"}),';
        }
        $pattern = "#(&&.?\.push\(\{text\:.?\.displayableProperty\.value\.plainText\}\),)(\(0,.?\.jsx\))#"; 
        //            111111111111111111111111111111111111111111111111111111111111111111  2222222222222
        preg_match($pattern, $js_file_data, $matches_1);
//echo "<br> js file - addmovie"; var_dump($matches_1);
        $js_file_data = preg_replace($pattern,
                                     $matches_1[1].$append.$matches_1[2],            
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
    
    return $js_file_data; 
}

function replace_javascript_search ($js_file_data)
{
    global $iframe;
    $url = getScheme().'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
    
    // look for   search:{searchEndpoint:"https://v2.sg.media-imdb.com/suggestion",queryTemplate:"%s%s/%s.json",formAction:"/find",formMethod:"get",inputName:"q",hiddenFields:[{name:"ref_",val:"nv_sr_sm"}]},
    $pattern = '#(search:\{searchEndpoint:")(.*?)(",queryTemplate:")(.*?)((".*?formAction:")(.*?)(".*?hiddenFields:\[))(.*?\]\},)#';
    preg_match($pattern, $js_file_data, $matches);
//echo "<br> search js matches - "; var_dump($matches); var_dump($url);
    if ($iframe) $iframe_val = '{name:"iframe",val:"'.$iframe.'"},';
    $replace_val = $matches[1].$url.$matches[3].'?videodburl='.$matches[2]."/".$matches[4].$matches[5].'{name:"videodburl",val:"http://www.imdb.com'.$matches[7].'"},'.$iframe_val.$matches[9];
//echo "<br> search replace val - "; var_dump($replace_val);
    $js_file_data = preg_replace($pattern,$replace_val, $js_file_data);

    //"search-result--const",href:e.url} and "search-result--video",href:e.url} and "search-result--link",href:e.url}
    $pattern = '#(",href:)(.\.url\})#';
    preg_match($pattern, $js_file_data, $matches);
//echo "<br> interactive search js matches - "; var_dump($matches);
    $iframe_val = '';
    if ($iframe) $iframe_val = "&iframe=".$iframe;    
    $replace_val = $matches[1].'"'.$url.'?'.$iframe_val.'&videodburl=https://www.imdb.com"'.'+'.$matches[2];
//echo var_dump($replace_val);
    $js_file_data = preg_replace($pattern,$replace_val, $js_file_data); 
    
    return $js_file_data;
}

function replace_javascript_seasonyear ($js_file_data)
{
    global $iframe;
    
//echo "<br> in replace_javascript_seasonyear";
//echo "<br>".$js_file_name; echo "   ".$cachefolder;
    // allow for iframe templates
    $iframe_val = '';
    if ($iframe) $iframe_val = "&iframe=".$iframe;
    
    //string -    if(d!==c){var e="/title/"
    $pattern = '#(if\(.!==.\){var .=)(\"/title/\")#';
//echo "<br>".$pattern;
    preg_match($pattern, $js_file_data, $matches);
//echo "<br> js file - find for season"; var_dump($matches);
    $js_file_data = preg_replace($pattern,
                                 $matches[1].'"trace.php?'.$iframe_val.'&videodburl=https://www.imdb.com"+'.$matches[2],
                                 $js_file_data);
    
    return $js_file_data;
}

function replace_javascript_episodelist ($js_file_data, $html)
{
    global $iframe;
//echo "<br> in replace_javascript_episodelist";
//echo "<br>".$js_file_name; echo "   ".$cachefolder;
//file_put_contents('./cache/orig_eposidelist.js', $js_file_data);

    // allow for iframe templates
    $iframe_val = '';
    if ($iframe) $iframe_val = "&iframe=".$iframe;
    
   // fix navigation
   //string -    "/title/"
    $pattern = '#(")(/title/")#';
    preg_match($pattern, $js_file_data, $matches);
    $js_file_data = preg_replace($pattern,
                                 $matches[1].'http://".concat(window.location.host).concat(window.location.pathname).concat("?'.$iframe_val.'&videodburl=https://www.imdb.com'.$matches[2].')',
                                 $js_file_data);

    // add - add and show to each episode
    // find the json data in html containing title id each episode
    preg_match('#(\<script id\="__NEXT_DATA__".*?)("episodes"\:.*?)(,"currentSeason")#',$html,$matches_1);
//file_put_contents('./cache/nextdata_episodedata.json', $matches_1[2]);  // for debugging
    // Decode the JSON file
    $ep_data = json_decode("{".$matches_1[2]."}",true);

    $x = 0;
    foreach ($ep_data['episodes']['items'] as $object) {
        $imdb_id = filter_var($object['id'], FILTER_SANITIZE_NUMBER_INT);
        $ep_data['episodes']['items'][$x]['imdbid'] = $imdb_id;
        
        $ep_data['episodes']['items'][$x]['videodbid'] = 0;
         if (is_known_item('imdb:'.$imdb_id, $sp_id, $sp_diskid))
        {
            $diskid = "";
            if ($sp_diskid <> "no_diskid") 
            {
                $diskid = " (Diskid:".$sp_diskid.")";
            }
            // add videodb id and diskid to html json
            $ep_data['episodes']['items'][$x]['videodbid'] = $sp_id;
            $ep_data['episodes']['items'][$x]['videodbdiskid'] = $diskid;
        }    
        $x  = $x + 1;
    }
    $ep_data_new = json_encode($ep_data, JSON_UNESCAPED_SLASHES );
//file_put_contents('./cache/nextdata_new_encoded.json', $ep_data_new);   // for debugging
    // strip out added delimiters added in earlier
    $ep_data_new = substr($ep_data_new, 1, -1);   
//file_put_contents('./cache/nextdata_new_trimmed.json', $ep_data_new);  // for debugging
    //update htlm with added ids in amended json
    $html = preg_replace('#\<script id\="__NEXT_DATA__".*?"episodes"\:.*?,"currentSeason"#',
                         $matches_1[1].$ep_data_new.$matches_1[3],
                         $html);
//file_put_contents('./cache/html_new.text', $html);  // for debugging

    // get js code to clone
                //{aggregateRating:s.aggregateRating,voteCount:s.voteCount},refMarker:{prefix:C}}),I&&!E&&!S&&(0,a.jsx)(vt,{onClick:function(){return g(!0)},width:"half-padding",children:x({id:"common_buttons_watchOptions",defaultMessage:"Watch options"}
                //111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111112222222222222233333333333333333333333333333333333333333333333333334444444444444445555555555555555555555555555566666666666666667777777777777777
    $pattern = '#(\{aggregateRating:..aggregate.*?&&.*?&&)(\(0,.*?\,\{)(onClick.*?padding",)(children\:.*?:)("common_buttons_watchOptions")(.*?)("Watch options"\})#';
                //111111111111111111111111111111111111111  22222222222  3333333333333333333  44444444444444  55555555555555555555555555555  666  77777777777777777
preg_match($pattern, $js_file_data, $matches_2);
    // bits needed 
    $part1 = $matches_2[2];  // (0,a.jsx)(ut,{    a and ut variable
    $part2 = $matches_2[4];    // children:x({id:   x variable and id maybe variable
    $part3 = $matches_2[6];    // ,defaultMessage:  maybe variable
    // get varaiable which holds episode data from json after processing in js 
                 //({titleId:s.id})
                 //1111111111233333
    $pattern = '#(\({titleId:)(.*?)(\.id}\))#';
                //11111111111  222  3333333
    preg_match($pattern, $js_file_data, $matches_4);
    $part_4 = $matches_4[2];   // s is variable
    
    // build add episode js code
    $append = $part1.'href:"edit.php?save=1&lookup=2&imdbID=imdb:".concat('.$part_4.'.imdbid),'.$part2.'"add_episode"'.$part3.'"Add Episode"})}),';   
    // build show episode js code
    $append.= $part_4.'.videodbid != 0 &&'.$part1.'href:"show.php?id=".concat('.$part_4.'.videodbid),'.$part2.'"show_episode"'.$part3.'"Show Episode ".concat('.$part_4.'.videodbdiskid)})}),'; 
  
    // get position to insert cloned js
              //className:"episode-item-wrapper",children:[(0,a.jsx)(qn,{href:"/title/".concat
              //111111111111111111111111111111111111111111122222222222222222222222222222222222
    $pattern = '#(className\:"episode\-item.*?children\:\[)(\(0,.*?\)\(.*?,\{href\:.*?\.concat)#';
    preg_match($pattern, $js_file_data, $matches_3);
    $js_file_data = preg_replace($pattern,
                                 $matches_3[1].$append.$matches_3[2],
                                 $js_file_data);

//file_put_contents('./cache/new_eposidelist.js', $js_file_data);
    
    return array($js_file_data,$html);
}

function replace_javascript_eposidelistmain ($js_file_data, $html)
{
    global $iframe;
//echo "<br> in replace_javascript_episodelist";
//echo "<br>".$js_file_name; echo "   ".$cachefolder;
    // allow for iframe templates
    $iframe_val = '';
    if ($iframe) $iframe_val = "&iframe=".$iframe;    

    preg_match('#(\<script id\="__NEXT_DATA__".*?\>)(.*?)(\</script\>)#',$html,$matches_1);
//$file_path = './cache/nextdata_episodedata.json';
//file_put_contents($file_path, $matches_1[2]);
    // Decode the JSON file
    $json_data = json_decode($matches_1[2],true);
    $imdb_id = filter_var($json_data["props"]["pageProps"]["contentData"]["entityMetadata"]["id"], FILTER_SANITIZE_NUMBER_INT);
     // get js codeto clone
    // ,{children:[(0,r.jsx)(X,{preIcon
    // 1111111111112222222222222333333333
    $pattern = '#(,\{children\:\[)(.*?)(preIcon)#';
    preg_match($pattern, $js_file_data, $matches_2);
    // build add episode js code
    $append = $matches_2[2].'text:"\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0"}),';   // add spaces before link
    $append.= $matches_2[2].'href:"edit.php?save=1&lookup=2&imdbID=imdb:'.$imdb_id.'","data-testid": "add_title",text:"Add Title"}),';   
    if (is_known_item('imdb:'.$imdb_id, $sp_id, $sp_diskid))
    {
        $diskid = "";
        if ($sp_diskid <> "no_diskid") 
        {
            $diskid = " (Diskid:".$sp_diskid.")";
        }
        // build show movie js code
        $append.= $matches_2[2].'text:"\xa0\xa0\xa0\xa0\xa0\xa0\xa0\xa0"}),'; //add spaces before link
        $append.= $matches_2[2].'href:"show.php?id='.$sp_id.'","data-testid": "show_title",text:"Show Title'.$diskid.'"}),';
    }

    //"data-testid":children:[e.subtitle,
    $pattern = '#"data-testid"\:.*?Subtitle,children\:\[.*?subtitle,#';
    preg_match($pattern, $js_file_data, $matches_3);
    $js_file_data = preg_replace($pattern,
                                 $matches_3[0].$append,
                                 $js_file_data); 

    return ($js_file_data);
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
 //$file_path = './cache/'.date("Y-m-d")." T".date("H-i-s").' - pagedata-html-before-processing.log';
 //file_put_contents($file_path, $page);
    
    $fetchtime = time() - $fetchtime;

	// convert HTML for output
    $page = fixup_HTML($page);
    $page = fixup_javascript($page);
    
//testing code page after our processing
//$file_path = './cache/'.date("Y-m-d")." T".date("H-i-s").' - pagedata-html-after-processing.log';
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
    //$file_path = './cache/'.date("Y-m-d")." T".date("H-i-s").' - pagedata-html-before-sent-to-browser.log';
    //file_put_contents($file_path, $page);
$smarty->assign('url', $url);
$smarty->assign('page', $page);
$smarty->assign('fetchtime', $fetchtime);

// extract meta element to pass to header
//                <meta name="next-head-count" content="nn"/>
if (preg_match('#\<meta name\="next\-head\-count" content\="\d+"/\>#',$page,$m1))
{
    $smarty->assign('trace_meta', $m1[0]);
}

// display templates
tpl_display('trace.tpl');

