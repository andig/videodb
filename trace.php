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
        // amend url for seasons/year the path for previous and next season/year url's at bottom of episodes page
        if (preg_match("#(=(.*?)\&ref_=ttep_ep_sn_(pv|nx))|(=(.*?)\&ref_=ttep_ep_yr_(pv|nx))#",$matches[2],$mymatches))
        {    
            if (!preg_match('#(\/episodes\/\?season=)|(\/episodes\/\?year=)#',$url,$mymatches))
            {
                $patterns = array ('#(\?season)#','#(\?year)#');
                $replacements = array('episodes?season','episodes?year');
                $url = preg_replace($patterns,$replacements,$url);
            }
            // remove _ajax in url will be added by js. 
            if (preg_match('#\/episodes\/_ajax\/#',$url,$mymatches))
            {
                $url = preg_replace('#\/episodes\/_ajax#','',$url);
            }
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

/**
 * @param   string  $html   input HTML code including relative links etc.
 * @return  string          output HTML code with absolute proxied links, forms image maps etc.
 */
function fixup_javascript($html)
{
    global $uri, $debug_trace, $trace_dirs;

    if (stristr($uri['host'], 'imdb') === false)
    {
        return $html;
    }

    // get cache folder for overridden js files
    $cachefolder = cache_get_folder('javascript');  //get cache root folder
    $error = cache_create_folders($cachefolder, 0); // ensure folder exists
    array_map('unlink', glob($cachefolder."/*.*")); // delete files

    // find all imdb javascript files
    preg_match_all('#[\"\']\s*\Khttps?:[^\"\']+?\.js#',
               $html,
               $matches_all);

    //  for performance reduce matches by excluding all duplicate files
    $unique_matches = array_unique($matches_all[0]);

    // loop thru files
    $x = 0;
    foreach ($unique_matches as $js_file_name)
    {
        $partfilename = '';
        $js_file_data = file_get_contents($js_file_name);

        // testing/debugging only - use to get copy of all javascript before cloning
        if ($debug_trace)
        { 
            $file_path = $trace_dirs['preclone'].'pre_'.$x.'.js';
            file_put_contents($file_path, $js_file_data); 
        }
        
        $pattern = '#'.preg_quote('fragment BaseTitleCard on Title', '#').'#';  // add escape delimiters
        if ( preg_match($pattern, $js_file_data, $matches))
        {
            $js_file_data = replace_javascript_title ($js_file_data, $html);
            $partfilename .= '-title';
        }   

        // add add/show to main title on episode list page @ aug 2023
        // amended pattern @Sept 24
        $pattern = '#'.preg_quote('defaultMessage:"View episode guide"}', '#').'#';  // add escape delimiters
        if ( preg_match($pattern, $js_file_data, $matches))
        {
           $js_file_data = replace_javascript_episodemain ($js_file_data, $html);
           $partfilename .= '-episodemain';
        }        

        // add add/show to New version of episode list page @ aug 2023
        $pattern = '#'.preg_quote('SeasonsTab="tab-seasons"', '#').'#';  // add escape delimiters
        if (preg_match($pattern, $js_file_data, $matches) )
        {
            list($js_file_data, $html) = replace_javascript_episodelist ($js_file_data, $html);
            $partfilename .= '-episodelist';
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
        
        // for qlnk  links
        $find_string = 'defaultMessage:"Cast & crew"';
        $pattern = '#'.$find_string.'#';
        if (preg_match($pattern, $js_file_data, $matches) )
        {
            $js_file_data = replace_javascript_qlnk  ($js_file_data);
            $partfilename .= '-qlnk';
        }
        
        // breadcrum links
        $find_string = 'defaultMessage:"Back"';
        $pattern = '#'.$find_string.'#';
        if (preg_match($pattern, $js_file_data, $matches) )
        {
            $js_file_data = replace_javascript_brcrumb  ($js_file_data);
            $partfilename .= '-brcrumb';
        }        
      
        // for search result page
        $find_string = 'defaultMessage:"Exact matches"';
        $pattern = '#'.$find_string.'#';
        if (preg_match($pattern, $js_file_data, $matches) )
        {
            list($js_file_data, $html) = replace_javascript_srchlist  ($js_file_data, $html);
            $partfilename .= '-srchlist';
        }  
      
        if ($partfilename <> '')
        {
            $file_path = './'.$cachefolder.'imdb-clone-'.$x.$partfilename.'.js';
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

/**
 * @param   string  $json   input json data
 * @return  string          output json with title data.
 */
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

/**
 * @param   string  $js_file_data   imdb supplied javascript
 * @return  string  amended javascript.
 */
function replace_javascript_title ($js_file_data)
{
    global $iframe;
    // allow for iframe templates
    $iframe_val = '';
    if ($iframe) $iframe_val = "&iframe=".$iframe;
    
    //    let r=(e.localePrefix??"")+"/title/{tconst}/"
    //    let r=""+"/title/{tconst}/faq/"
    //    let r=""+"/title/{tconst}/fullcredits/"
    //    let r=""+"/title/{tconst}/plotsummary/"
    //    let r=""+"/title/{tconst}/taglines/"
    //    let r=""+"/title/{tconst}/trivia/"
    //    let r=""+"/title/{tconst}/reviews/"
    $pattern = '#(let .\=""\+"|let r\=\(..localePrefix\?\?""\)\+")(/title/\{tconst\}.*?")#';
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace_callback($pattern, function ($matches) use ($iframe_val) 
        {
           return $matches[1].'?'.$iframe_val.'&videodburl=https://www.imdb.com'.$matches[2];
        }, $js_file_data);
    }    

    //   let r = (e.localePrefix ?? "") + "/name/{nconst}/"
    //   let r=""+"/name/{nconst}/awards/"
    //   let r=""+"/name/{nconst}/quotes/"
    //   let r=""+"/name/{nconst}/triva/"
    //   let r=""+"/name/{nconst}/videogallery/"
    //   let r=""+"/name/{nconst}/bio/"
    //   let r=""+"/name/{nconst}/externalsites/"
    //   let r=""+"/name/{nconst}/faq/"
    //   let r=""+"/name/{nconst}/mediaindex/"
    //   let r=""+"/name/{nconst}/mediaviewer/"
    //   let r=""+"/name/{nconst}/news/"
    //   let r=""+"/name/{nconst}/otherworksawards/"
    //   let r=""+"/name/{nconst}/publicity/"
    $pattern = '#(let .\=""\+"|let .\=\(..localePrefix\?\?""\)\+")(/name/\{nconst\}.*?")#';
    unset($mataches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace_callback($pattern, function ($matches) use ($iframe_val) 
        {
           return $matches[1].'?'.$iframe_val.'&videodburl=https://www.imdb.com'.$matches[2];
        }, $js_file_data);
    }
    
    //   let r=""+"/interest/{inconst}/"
    $pattern = '#(let .\=""\+")(/interest/\{inconst\}/")#';
    unset($mataches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace_callback($pattern, function ($matches) use ($iframe_val) 
        {
           return $matches[1].'?'.$iframe_val.'&videodburl=https://www.imdb.com'.$matches[2];
        }, $js_file_data);
    }
    
    
    return $js_file_data;  
}

/**
 * @param   string  $js_file_data   imdb supplied javascript
 * @return  string  amended javascript.
 */
function replace_javascript_addmovie ($js_file_data)
{
    global $uri, $iframe;
    // allow for iframe templates
    $iframe_val = '';
    if ($iframe) $iframe_val = "&iframe=".$iframe;

    if (preg_match("#/title/tt(\d+)#", $uri['path'], $m)) // $m[1] is imdb tltle no
    {
        // look for &&S.push({text:p.displayableProperty.value.plainText}),(0,r.jsx)   S p and r can change
        $pattern = "#&&(.?\.push\(\{text\:)(.?\.displayableProperty\.value\.plainText\}\),\(0,.?\.jsx\))#";
        //              111111111111111111  22222222222222222222222222222222222222222222222222222222222
        preg_match($pattern, $js_file_data, $matches);
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
        unset($matches);
        preg_match($pattern, $js_file_data, $matches);
        $js_file_data = preg_replace($pattern,
                                     $matches[1].$append.$matches[2],            
                                     $js_file_data);
    }
        
    // href=`https://${window.location.host}  "#href\=`https\://\$\{window\.location\.host\}#"
    $pattern = '#(href\=`)(https\://)(\$\{window\.location\.host\})#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace($pattern,
                                     $matches[1].'http://'.'${window.location.host}'.'${window.location.pathname}',
                                     $js_file_data);
    } 
    
   // find_string  `/title/ or  `/name/
    $pattern = '#(`)(/title/|/name/)#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace_callback($pattern, function ($matches) use ($iframe_val) {
            return $matches[1].'?'.$iframe_val.'&videodburl=https://www.imdb.com'.$matches[2];
        }, $js_file_data);
    }

    // links for actor real name
    //"data-testid":"title-cast-item__actor",href:
    $pattern = '#"data-testid":"title-cast-item__actor",href:#';
    unset($matches);
    preg_match($pattern, $js_file_data, $matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace($pattern,
                                     $matches[0].'"?'.$iframe_val.'&videodburl=https://www.imdb.com"+',
                                     $js_file_data);
    }
 
//    // links for principals names
//    //"data-testid":"title-pc-principal-credit",labelTitle:t.category.text,labelLinkAriaLabel:a,labelLink:t.totalCredits>t.credits.length?s:void 0,listContent:t.credits.filter(e=>!!e.name.nameText).map((e,t)=>{let{name:a}=e;return{href:
    $pattern = '#"data-testid":"title-pc-principal-credit".*?href:#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace($pattern,
                                     $matches[0].'"?'.$iframe_val.'&videodburl=https://www.imdb.com"+',
                                     $js_file_data);
    }
    
    // link to character  profile  
    // find string href:c,className:"title-cast-item__char" 
    //             1111122222222222222222222222222222222222
    $pattern = '#(href\:)(.,className\:"title\-cast\-item__char")#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace($pattern,
                                     $matches[1]."'"."?$iframe_val&videodburl=https://www.imdb.com"."'"."+".$matches[2],
                                     $js_file_data);
    }    
   
    //"data-testid":"title-cast-allcast-link",labelTitle:s,labelLink:C,
    //11111111111111111111111111111111111111111111111111111111111111122
     $pattern = '#("data-testid":"title-cast-allcast-link",labelTitle:.,labelLink:)(.,)#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace($pattern,
                                     $matches[1]."'"."?$iframe_val&videodburl=https://www.imdb.com"."'"."+".$matches[2],
                                     $js_file_data);
    }        

    // top cast
    //"data-testid":"title-cast",children:[(0,l.jsx)(aX.O,{title:r,editHref:h,subtitleProps:{href:C,subText:
    //111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111112222222222
    $pattern = '#("data\-testid":"title\-cast",children:\[\(.,..jsx\)\(....,{title:.,editHref:.,subtitleProps:{href:)(.,subText:)#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace($pattern,
                                     $matches[1]."'"."?$iframe_val&videodburl=https://www.imdb.com"."'"."+".$matches[2],
                                     $js_file_data);
    } 
    
    // plot summary
    //w.InlineListItem,{children:(0,l.jsx)(w.TextLink,{text:s.formatMessage(t),href:i,inline:
    //111111111111111111111111111111111111111111111111111111111111111111111111111111222222222
    $pattern = '#(..InlineListItem,{children:\(.,..jsx\)\(..TextLink,{text:..formatMessage\(.\),href:)(.,inline:)#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace($pattern,
                                     $matches[1]."'"."?$iframe_val&videodburl=https://www.imdb.com"."'"."+".$matches[2],
                                     $js_file_data);
    } 

    //  back cervon on eposide page to return to main series page
    //refSuffix:B.Cd.SERIES});return(0,l.jsx)(tL,{children:(0,l.jsx)(tR,{href:
    $pattern = '#refSuffix:.....SERIES}\);return\(.,..jsx\)\(..,{children:\(.,..jsx\)\(..,{href:#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace($pattern,
                                     $matches[0]."'"."?$iframe_val&videodburl=https://www.imdb.com"."'"."+",
                                     $js_file_data);
    }
      
    // various lnks - director writer ....
    $pattern = '#text:..name.nameText\?.text\|\|"",href:#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace($pattern,
                                     $matches[0]."'?$iframe_val&videodburl=https://www.imdb.com"."'"."+",
                                     $js_file_data);
    }

    // interest lnks
    //"data-testid":"interests",arrowBackgroundColorShade:"shade3",children:[E&&c?.map((e,t)=>l.jsx(w.Chip,{label:e.text,href:
    // o({refSuffix:{t: B.Cd.GENRE,n:t+1},query:{genres:e.id.toLowerCase(),explore:"title_type,genres"}})},e.id)),p&&p.map((e,t)=>(0,l.jsx)(w.Chip,{label:e.node.primaryText?.text,href:
    $pattern = '#("data-testid":"interests".*?href:)(.*?href:)#';
    preg_match($pattern, $js_file_data, $matches);
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace($pattern,
                                     $matches[1]."'?$iframe_val&videodburl=https://www.imdb.com"."'"."+"
                                    .$matches[2]."'?$iframe_val&videodburl=https://www.imdb.com"."'"."+",
                                     $js_file_data);
    }
    
    return $js_file_data; 
}

/**
 * @param   string  $js_file_data   imdb supplied javascript
 * @return  string  amended javascript.
 */
function replace_javascript_search ($js_file_data)
{
    global $iframe;
    $url = getScheme().'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
    $iframe_val = '';
    if ($iframe) 
    {
        $iframe_val = '{name:"iframe",val:"'.$iframe.'"},';
        $iframe_val_1 = "&iframe=".$iframe;
    }
    
    // fix link for looking glass in search bar
    // look for   search:{searchEndpoint:"https://v2.sg.media-imdb.com/suggestion",queryTemplate:"%s%s/%s.json",formAction:"/find",formMethod:"get",inputName:"q",hiddenFields:[{name:"ref_",val:"nv_sr_sm"}],
    //            11111111111111111111111 222222222222222222222222222222222222222 33333333333333334444444444445555555555555566666777777777777777777777777777777777777777777777778888888888888888888888888888888
    $pattern = '#(search:\{searchEndpoint:")(.*?)(",queryTemplate:")(.*?formAction:")(.*?)(".*?hiddenFields:\[)(.*?\],)#';
               // 1111111111111111111111111  222  33333333333333333  4444444444444444444  555  6666666666666666666  777777
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $replace_val = $matches[1].$url.$matches[3].'?videodburl='.$matches[2]."/".$matches[4].'?videodburl='.$matches[5].$matches[6].'{name:"videodburl",val:"http://www.imdb.com'.$matches[5].'"},'.$iframe_val.$matches[7];
        $js_file_data = preg_replace($pattern,$replace_val, $js_file_data);
    }
    
    // fix link for drop down list in search bar
    //"search-result--const",href:e.url} and "search-result--video",href:e.url} and "search-result--link",href:e.url}
    //                     1111111222222                          1111111222222                         1111111222222  
    $pattern = '#(",href:)(.\.url\})#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $replace_val = $matches[1].'"'.$url.'?'.$iframe_val_1.'&videodburl=https://www.imdb.com"'.'+'.$matches[2];
        $js_file_data = preg_replace($pattern,$replace_val, $js_file_data); 
    }
    return $js_file_data;
}

/**
 * @param   string  $js_file_data  imdb supplied javascript
 * @param   string  $html          html data        
 * @return  string  $js_file_data   amended javascript and html.
 * @return  string  $html            amended  html.
 */
function replace_javascript_srchlist ($js_file_data, $html)
{
    global $iframe, $debug_trace, $trace_dirs;
    
    $url = getScheme().'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
    $iframe_val = '';
    if ($iframe) 
    {
        $iframe_val = "&iframe=".$iframe;
    }   
        
    // title name interest links
    // {titleMainLinkBuilder:_}=(0,E.WOb)(),M=[i?Q.C.TITLE:Q.C.ALL,{t: Q.C.TITLE,n:r}],C=
    // {interestSingleLinkBuilder:p}=(0,E.WOb)(),u=
    // {nameMainLinkBuilder:g}=(0,E.WOb)(),f=
    $pattern = '#({titleMainLinkBuilder:.}=.*?TITLE.*?='
              . '|{nameMainLinkBuilder:.}=.*?='
              . '|{interestSingleLinkBuilder:.}=.*?=)#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace_callback($pattern, function ($matches) use ($iframe_val) 
                                                {return $matches[0]."'?".$iframe_val."&videodburl=https://www.imdb.com'"."+";
                                                }, $js_file_data);
    }
    
    // exact match or not exact match lnks
    // find_string  TextButton,{href:
    $pattern = '#TextButton,{href:#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace($pattern,
                                     $matches[0]."'"."?$iframe_val&videodburl=https://www.imdb.com"."'"."+",
                                     $js_file_data);
    }
    
    // lnk for refining to movie, series ... etc
    // label:g,href:
    $pattern = '#label:.,href:#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace_callback($pattern, function ($matches) use ($iframe_val) 
                                                        {return $matches[0]."'?$iframe_val&videodburl=https://www.imdb.com'+";
                                                        }, $js_file_data);
    }
    
    // defaultMessage:"Quotes"}),href:
    // defaultMessage:"Plot Summaries"}),href:
    // defaultMessage:"Biographies"}),href:
    // defaultMessage:"Movies, TV & more"}),href:
    // defaultMessage:"People"}),href:
    // defaultMessage:"Collaborations"}),href:
    $pattern = '#defaultMessage:'
              . '("Quotes"}\),href:'
              . '|"Plot Summaries"}\),href:'
              . '|"Biographies"}\),href:'
              . '|"Movies, TV & more"}\),href:'
              . '|"People"}\),href:'
              . '|"Collaborations"}\),href:)#';   
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace_callback($pattern, function ($matches) use ($iframe_val) 
                                                        {return $matches[0]."'?$iframe_val&videodburl=https://www.imdb.com'+";
                                                        }, $js_file_data);
    }

    // "data-testid":"advanced-search-link-genres",href:
    // "data-testid":"advanced-search-link-keywords",href:
        $pattern = '#"data-testid":'
              . '("advanced-search-link-genres",href:'
              . '|"advanced-search-link-keywords",href:)#';   
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace_callback($pattern, function ($matches) use ($iframe_val) 
                                                        {return $matches[0]."'?$iframe_val&videodburl=https://www.imdb.com'+";
                                                        }, $js_file_data);
    }
    // add add title and show title for all except episodes
    // add variables to data blob
    if ($debug_trace)
    {     
        preg_match('#(\<script id\="__NEXT_DATA__".*?\>)(.*?)(\<\/script\>)#',$html,$matches); // for debugging
        file_put_contents($trace_dirs['srchlst'].'allBefore.json', $matches[2]);  // for debugging
    }

    unset($matches);
    preg_match('#(\<script id\="__NEXT_DATA__".*?)("titleResults"\:\{"results"\:.*?)(,"companyResults"\:)#',$html,$matches);

    if ($debug_trace)
    { 
        file_put_contents($trace_dirs['srchlst'].'part.json', $matches[2]);  // for debugging
    }
    // Decode the JSON file - add { for syntax
    $title_data = json_decode("{".$matches[2]."}",true);
    $x = 0;
    foreach ($title_data['titleResults']['results'] as $object) 
    {
            $imdb_id = filter_var($object['id'], FILTER_SANITIZE_NUMBER_INT);
            $title_data['titleResults']['results'][$x]['imdbid'] = $imdb_id;
        
        $title_data['titleResults']['results'][$x]['videodbid'] = 0;
         if (is_known_item('imdb:'.$imdb_id, $sp_id, $sp_diskid))
        {
            $diskid = "";
            if ($sp_diskid <> "no_diskid") 
            {
                $diskid = " (Diskid:".$sp_diskid.")";
            }
            // add videodb id and diskid to html json
            $title_data['titleResults']['results'][$x]['videodbid'] = $sp_id;
            $title_data['titleResults']['results'][$x]['videodbdiskid'] = $diskid;
        } 
        $x = $x +1;     
    }
    $title_data_new = json_encode($title_data, JSON_UNESCAPED_SLASHES );
    // strip out added delimiters '{' '}' added in earlier
    $title_data_new = substr($title_data_new, 1, -1);
    
    if ($debug_trace)
    { 
        file_put_contents($trace_dirs['srchlst'].'new_encoded.json', $title_data_new);   // for debugging
        file_put_contents($trace_dirs['srchlst'].'new_js.js', $matches[1].$title_data_new.$matches[3]);   // for debugging
    }
    //update htlm with added ids in amended json
    $html = preg_replace('#(\<script id\="__NEXT_DATA__".*?)("titleResults"\:\{"results"\:.*?)(,"companyResults"\:)#',
                         $matches[1].$title_data_new.$matches[3],
                         $html);
    
    if ($debug_trace)
    { 
        preg_match('#(\<script id\="__NEXT_DATA__".*?\>)(.*?)(\<\/script\>)#',$html,$matches); // for debugging
        file_put_contents($trace_dirs['srchlst'].'allAfter.json', $matches[2]);  // for debugging
    }

    // assign my fields to a var
    // let{id:t,titleNameText:a,hasSearchType:i,index:r,
    $pattern = '#(let{)(id:.,titleNameText:.,)#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace($pattern,
                                     $matches[1]."imdbid:imdbid,videodbid:videodbid,videodbdiskid:videodbdiskid,".$matches[2],
                                     $js_file_data);
    }

    // get place to insert js data
    // pattern for all except episodes
    //return n&&T.push({text:n}),l&&T.push({text:l}),(0,s.jsx)(c.MetaDataListSummaryItem
    //1111111111111111111111111111112222222233333344455555555555555555555555555555555555               
    //            code to insert    XXXXXXXXXXXXXXXX  
    // pattern for episodes
    //text:e.join(".")}),l&&T.push({text:l}),(0,s.jsx)(c.MetaDataListSummaryItem,
    //111111111111111111111122222222333333444555555555555555555555555555555555555 
    $pattern = '#(return .&&..push\({text:.}\),.&&'
                . '|'
                . 'text:..join\("."\)}\),.&&)'
              . '(..push\({)'
              . '(text:.)'
              . '(}\),)'
              . '(\(.,..jsx\)\(..MetaDataListSummaryItem)#';
    unset($matches);
    preg_match($pattern,$js_file_data,$matches);
    $add = $matches[2]."text:'Add Title',href:'edit.php?save=1&lookup=2&imdbID=imdb:'+imdbid}),";
    $show = "videodbid != 0 &&".$matches[2]."text:'Show Title '+videodbdiskid,href:'show.php?id='+videodbid}),";

    $js_file_data = preg_replace_callback($pattern, function ($matches) use($add, $show)
                                                    {return $matches[1].$matches[2].$matches[3].$matches[4].$add.$show.$matches[5];
                                                    }, $js_file_data);

    return array($js_file_data,$html);
}

/**
 *  @param   string  $js_file_data  imdb supplied javascript
 * @param   string  $html          html data        
 * @return  string  $js_file_data   amended javascript and html.
 * @return  string  $html            amended  html.
 */
function replace_javascript_episodelist ($js_file_data, $html)
{
    global $iframe, $debug_trace, $trace_dirs;
 
    // allow for iframe templates
    $iframe_val = '';
    if ($iframe) $iframe_val = "&iframe=".$iframe;
    
    // find_string  `/title/ 
    $pattern = '#(`)(/title/)#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace_callback($pattern, function ($matches) use ($iframe_val) {
             return $matches[1].$_SERVER['PHP_SELF'].'?'.$iframe_val.'&videodburl=https://www.imdb.com'.$matches[2];
        }, $js_file_data);
    }

    // add - add and show to each episode
    // find the json data in html containing title id each episode
    unset($matches);
    preg_match('#(\<script id\="__NEXT_DATA__".*?)("episodes"\:\{"items"\:.*?)(,"currentSeason")#',$html,$matches);
    
    if ($debug_trace)
    { 
        file_put_contents($trace_dirs['episodelst'].'all.json', $matches[0]);  // for debugging
        file_put_contents($trace_dirs['episodelst'].'seasons.json', "{".$matches[2]."}");  // for debugging
    }

    // Decode the JSON file
    $ep_data = json_decode("{".$matches[2]."}",true);

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
        
    if ($debug_trace)
    { 
        file_put_contents($trace_dirs['episodelst'].'new_encoded.json', $ep_data_new);   // for debugging
    }
    // strip out added delimiters added in earlier
    $ep_data_new = substr($ep_data_new, 1, -1);
        
    if ($debug_trace)
    { 
        file_put_contents($trace_dirs['episodelst'].'new_trimmed.json', $ep_data_new);  // for debugging
    }
    //update htlm with added ids in amended json
    $html = preg_replace('#\<script id\="__NEXT_DATA__".*?"episodes"\:\{"items"\:.*?,"currentSeason"#',
                         $matches[1].$ep_data_new.$matches[3],
                         $html);
    if ($debug_trace)
    { 
        file_put_contents($trace_dirs['episodelst'].'html_new.text', $html);   // for debugging
    }
    // get js code to clone
                //{aggregateRating:s.aggregateRating,voteCount:s.voteCount},refMarker:{prefix:C}}),I&&!E&&!S&&(0,a.jsx)(vt,{onClick:function(){return g(!0)},width:"half-padding",children:x({id:"common_buttons_watchOptions",defaultMessage:"Watch options"}
                //111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111112222222222222233333333333333333333333333333333333333333333333333334444444444444445555555555555555555555555555566666666666666667777777777777777
    $pattern = '#(\{aggregateRating:..aggregate.*?&&.*?&&)(\(0,.*?\,\{)(onClick.*?padding",)(children\:.*?:)("common_buttons_watchOptions")(.*?)("Watch options"\})#';
                //111111111111111111111111111111111111111  22222222222  3333333333333333333  44444444444444  55555555555555555555555555555  666  77777777777777777
    unset($matches);
    preg_match($pattern, $js_file_data, $matches);
    // bits needed 
    $part1 = $matches[2];  // (0,a.jsx)(ut,{    a and ut variable
    $part2 = $matches[4];    // children:x({id:   x variable and id maybe variable
    $part3 = $matches[6];    // ,defaultMessage:  maybe variable
    // get varaiable which holds episode data from json after processing in js 
                 //({titleId:s.id})
                 //1111111111233333
    $pattern = '#(\({titleId:)(.*?)(\.id}\))#';
                //11111111111  222  3333333
    unset($matches);
    preg_match($pattern, $js_file_data, $matches);
    $part_4 = $matches[2];   // s is variable
    
    // build add episode js code
    $append = $part1.'href:"edit.php?save=1&lookup=2&imdbID=imdb:".concat('.$part_4.'.imdbid),'.$part2.'"add_episode"'.$part3.'"Add Episode"})}),';   
    // build show episode js code
    $append.= $part_4.'.videodbid != 0 &&'.$part1.'href:"show.php?id=".concat('.$part_4.'.videodbid),'.$part2.'"show_episode"'.$part3.'"Show Episode ".concat('.$part_4.'.videodbdiskid)})}),'; 
  
    // get position to insert cloned js
              //className:"episode-item-wrapper",children:[(0,a.jsx)(qn,{href:"/title/".concat old
              //111111111111111111111111111111111111111111122222222222222222222222222222222222  old
              //className:"episode-item-wrapper",children:[(0,c.jsx)(eG.Z,{href:`
              //11111111111111111111111111111111111111111112222222222222222222222
    unset($matches);
    $pattern = '#(className\:"episode\-item.*?children\:\[)(\(0,.*?\)\(.*?,\{href\:`)#';
    preg_match($pattern, $js_file_data, $matches);
    $js_file_data = preg_replace($pattern,
                                 $matches[1].$append.$matches[2],
                                 $js_file_data);
    
    return array($js_file_data,$html);
}

/**
 * @param   string  $js_file_data   imdb supplied javascript
 * @param   string  $html    html data
 * @return  string  $js_file_data   amended javascript
 */
function replace_javascript_episodemain ($js_file_data, $html)
{
    global $iframe;
    // allow for iframe templates
    $iframe_val = '';
    if ($iframe) $iframe_val = "&iframe=".$iframe;    

    // find_string  `/title/ or  `/name/
    $pattern = '#(`)(/title/|/name/)#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace_callback($pattern, function ($matches) use ($iframe_val) {
            return $matches[1].'?'.$iframe_val.'&videodburl=https://www.imdb.com'.$matches[2];
        }, $js_file_data);
    }
    
    // do link to episode listing from episode main page
    // find string TMD_Hero_EpisodeCount?.total||0),f=i({tconst:o??"",refSuffix
    //             111111111111111111111111111111111112222222222222222222222222
    $pattern = '#(TMD_Hero_EpisodeCount\?\.total\|\|0\),.\=)(.\(\{tconst\:.\?\?"",refSuffix)#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace($pattern,
                                     $matches[1]."'"."?$iframe_val&videodburl=https://www.imdb.com"."'"."+".$matches[2],
                                     $js_file_data);
    }
    
    // lnk for all episodes
    //defaultMessage:"View all episodes"}),u=t?i({tconst:
   $pattern = '#defaultMessage:"View all episodes"}\),.\=.\?#';
   unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace($pattern,
                                     $matches[0]."'"."?$iframe_val&videodburl=https://www.imdb.com"."'"."+",
                                     $js_file_data);
    } 
   
    // prevoius and next cevrons around all episodes
    //previousEpisode?.id,p=u?
    //nextEpisode?.id,c=f?
    $pattern = '#(previousEpisode\?.id,.\=.\?'
              . '|nextEpisode\?.id,.\=.\?)#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace_callback($pattern, function ($matches) use ($iframe_val) {
             return $matches[0]."'"."?$iframe_val&videodburl=https://www.imdb.com"."'"."+";
        }, $js_file_data);
    }
    
    return ($js_file_data);
}

/**
 * @param   string  $js_file_data   imdb supplied javascript
 * @return  string  $js_file_data   amended javascript
 */
function replace_javascript_qlnk ($js_file_data)
{
    global $iframe;
    // allow for iframe templates
    $iframe_val = '';
    if ($iframe) $iframe_val = "&iframe=".$iframe;    

    // do links upper right of episode main page  
    // Cast & crew
    // user reviews
    // Trivia
    // FAQ
    $pattern = '#(defaultMessage:"Cast & crew"}\),href:'
              . '|defaultMessage:"User reviews"}\),href:'
              . '|defaultMessage:"Trivia"}\),href:'
              . '|defaultMessage:"FAQ"}\),href:)#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace_callback($pattern, function ($matches) use ($iframe_val) 
                        {
                           return $matches[0]."'"."?$iframe_val&videodburl=https://www.imdb.com"."'"."+";
                        }, $js_file_data);
    }    

    // various links of eposide list page
    // defaultMessage:"Videos"},href:(e,t)=>
    // defaultMessage:"Cast & crew"},href:(e,t)=>
    // defaultMessage:"Trivia"},href:(e, t)=>
    // defaultMessage:"Photos"},href:(e, t)=>
    $pattern = '#defaultMessage:"Trivia"\},href:\(.,.\)\=>'
             . '|defaultMessage:"Videos"\},href:\(.,.\)\=\>'
             . '|defaultMessage:"Photos"\},href:\(.,.\)\=>'
             . '|defaultMessage:"Cast & crew"\},href:\(.,.\)\=\>'
             . '|defaultMessage:"Taglines"\},href:\(.,.\)\=\>#';

    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
        {
            $js_file_data = preg_replace_callback($pattern, function ($matches) use ($iframe_val) 
                            {
                               return $matches[0]."'"."?$iframe_val&videodburl=https://www.imdb.com"."'"."+";
                            }, $js_file_data);
        } 

    return ($js_file_data);
}

/**
 * @param   string  $js_file_data   imdb supplied javascript
 * @return  string  $js_file_data   amended javascript
 */
function replace_javascript_brcrumb ($js_file_data)
{
    global $iframe;
    // allow for iframe templates
    $iframe_val = '';
    if ($iframe) $iframe_val = "&iframe=".$iframe;    
 
    // back button of episode list page
    //    href:Q,"data-testid": n.BackButton
    $pattern = '#(href:)(.,"data\-testid":..BackButton)#';
    unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace($pattern,
                                     $matches[1]."'"."?$iframe_val&videodburl=https://www.imdb.com"."'"."+".$matches[2],
                                     $js_file_data);
    }
  
    // lnk in photo 
    // loading:"eager"},dynamicWidth:!0,href:f?u({tconst:d,refSuffix:[A.C.HERO,A.C.POSTER]}):
    // 11111111111111111111111111111111111111112222222222222222222222222222222222222222222222
    $pattern = '#(loading:"eager".,dynamicWidth:..,href:.\?)(.*?POSTER...:)#';
      unset($matches);
    if (preg_match($pattern, $js_file_data, $matches))
    {
        $js_file_data = preg_replace($pattern,
                                     $matches[1]."'"."?$iframe_val&videodburl=https://www.imdb.com"."'"."+"
                                    .$matches[2]."'"."?$iframe_val&videodburl=https://www.imdb.com"."'"."+",
                                     $js_file_data);
    }

    return ($js_file_data);
}

/**
 * @parm    array    $dirs
 * @return  none
 */
function setup_debug_trace_folders ($debug, $dirs)
{
    foreach($dirs as $dir)
    {
        if ($debug)
        {            
            $error = cache_create_folders($dir, 0); // ensure folder exists
            array_map('unlink', glob($dir."/*.*")); // delete files   
        }
        else
        {
            if (is_dir($dir))
            {
                array_map('unlink', glob($dir."/*.*")); //delete files
                rmdir($dir);                           // remove directory
            }
        }
    }
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
    /*
    $file_path = './cache/'.date("Y-m-d")." T".date("H-i-s").' - pagedata-html-before-processing.log';
    file_put_contents($file_path, $page);
    */
    
    $fetchtime = time() - $fetchtime;

    // trace dirs
    $debug_trace = 0;   // @todo move to config inc ???
    $trace_dirs=array('preclone' => cache_get_folder('trace_javascript_preclone'),
                      'srchlst' => cache_get_folder('trace_nextdata_srchlst'),
                      'episodelst' => cache_get_folder('trace_nextdata_episodelst'));
    setup_debug_trace_folders ($debug_trace, $trace_dirs);   

    // convert HTML for output
    $page = fixup_HTML($page);
    $page = fixup_javascript($page);
    
    //testing code page after our processing
    /*
    $file_path = './cache/'.date("Y-m-d")." T".date("H-i-s").' - pagedata-html-after-processing.log';
    file_put_contents($file_path, $page)
    */
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
        /*
        $file_path = './cache/pagedata-json-before-processing.txt';
        file_put_contents($file_path, $page);
        */
        
        $page = fixup_json($page);

        //testing code page after json amended
        /*
        $file_path = './cache/pagedata-json-after-processing.txt';
        file_put_contents($file_path, $page);
        */
    }
    elseif ($matches_json_1)
    {
        /*
        $current_time = date("Y-m-d")." T".date("H-i-s");  
        $file_path = './cache/pagedata-json_1-no-processing_json-'.$current_time.'.txt';
        file_put_contents($file_path, $page);  
        */      
    }
    elseif ($matches_ajax)
    {
        /*
        $current_time = date("Y-m-d")." T".date("H-i-s");
        $file_path = './cache/pagedata-ajax-no-processing_ajax-'.$current_time.'.txt';
        file_put_contents($file_path, $page);    
        */    
    }
    
    // mode 2: display data into iframe
    // ajax call: dissplay data from imdb (no head)
    //testing code save page before send to browser
    /*
    $file_path = './cache/pagedataframe.txt';
    file_put_contents($file_path, $page);
    */
    echo($page);
    exit();
}

// mode 0 or 1: prepare templates 
tpl_page('imdbbrowser');
//testing code save page before send to browser
/*
$file_path = './cache/'.date("Y-m-d")." T".date("H-i-s").' - pagedata-html-before-sent-to-browser.log';
file_put_contents($file_path, $page);
 */
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

