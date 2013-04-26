<?php
/**
 * HTTP client functions
 *
 * @todo    Encapsulate httpClient and Cache as separate classes
 *
 * @package Core
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * @author  Andreas Gohr    <a.gohr@web.de>
 * @author  Chinamann       <chinamann@users.sourceforge.net>
 * @version $Id: httpclient.php,v 1.21 2013/04/26 15:09:35 andig2 Exp $
 */

require_once 'core/cache.php';

/**
 * Reads a saved HTTP response from a cachefile.
 * If caching is globally disabled ($config['IMDBage'] <= 0), file is not loaded.
 *
 * @param   string $url URL of the cached response
 * @return  mixed       HTTP Response, false on errors
 */
function getHTTPcache($url)
{
    global $config;

    if (@$config['cache_pruning'])
    {
        $cache_file = cache_get_filename($url, CACHE_HTML);
        cache_prune_folder(dirname($cache_file).'/', $config['IMDBage']);
    }
    
    return cache_get($url, CACHE_HTML, $config['IMDBage'], true);
}

/**
 * Saves a HTTP resonse to a cachefile
 * If caching is globally disabled ($config['IMDBage'] <= 0), file is not saved.
 *
 * @param  string $url  URL of the response
 * @param  mixed  $resp HTTP Response
 */
function putHTTPcache($url, $data)
{
    global $config;

    // for debugging purposes track there the request originated
    $data['source'] = $url;
    
    cache_put($url, $data, CACHE_HTML, $config['IMDBage'], true);
}

/**
 * httpClient helper function to convert array of cookies to http header
 */
function cookies2header($cookies)
{
    global $request_cookies;

    // concatenate cookie string
    foreach ($cookies as $key => $val)
    {
        // remember cookies for next request
        $request_cookies[$key] = $val;

        if ($headers) $headers .= '; ';
        $headers .= $key.'='.$val;
    }

    // build header
    if ($headers) $headers = 'Cookie: '.$headers."\r\n";
    return $headers;
}

/**
 * Extract all headers of a specific type from the request
 */
function http_get_headers($response, $header)
{
    preg_match_all('/'.$header.':\s*(.+)/', $response['header'], $matches, PREG_PATTERN_ORDER);
    return $matches[1];
}

/**
 * Collect cookies from httpclient response and add them to an existing array
 *
 * @param  mixed    $response   HTTP response
 * @param  array    $oldcookies old cookies
 * @return array                new and old cookies
 */
function get_cookies_from_response($response, $oldcookies = null) 
{
    if (preg_match_all('/Set-Cookie:\s*(.+?);/', $response['header'], $_cookies, PREG_PATTERN_ORDER))
    {
        foreach ($_cookies[1] as $cookie)
        {
            // limit split to 2 elements (key/value)
            list($key, $value) = explode('=', $cookie, 2);
            $oldcookies[$key] = $value;
        }
    }
    
    return $oldcookies;
}

/**
 * Extract source encoding from HTML code or HTTP header otherwise
 */
function get_response_encoding(&$resp)
{
    // <meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
    if (preg_match('#<meta.+?\scontent.+?charset=\s*([a-zA-Z0-9-]+)#is', $resp['data'], $m))
    {
        $encoding = strtolower($m[1]);
    }
    else if ($resp['header']) 
    {   
        // Content-Type: text/html; charset=UTF-8
        if (preg_match('#charset=\s*([a-zA-Z0-9-]+)#is', $resp['header'], $m))  
            $encoding = strtolower($m[1]);
        else // no charset implies default charset
            $encoding = 'iso-8859-1';
    }

#   dump('get_response_encoding: '.$encoding);
    return $encoding;
}

/**
 * HTTP Client
 *
 * Returns the raw data from the given URL, uses proxy when configured
 * and follows redirects
 *
 * @author Andreas Goetz <cpuidle@gmx.de>
 * @param  string  $url      URL to fetch
 * @param  bool    $cache    use caching? defaults to false
 * @param  string  $post     POST data, if nonempty POST is used instead of GET
 * @param  integer $timeout  Timeout in seconds defaults to 15
 * @return mixed             HTTP response
 */
function httpClient($url, $cache = false, $para = null, $reload = false)
{
    global $config;

    // use this as workaround for php bug http://bugs.php.net/bug.php?id=22526 session_start/popen hang
    # session_write_close(); // <-- We don't use popen! But sometimes we need the session afterwards! (Chinamann)

    $method  = 'GET';
    $headers = '';  // additional HTTP headers, used for post data
    
    $post    = $para['post'];
    if (is_array($post)) $post = http_build_query($post);
    
    if (!empty($post))
    {
        // POST request
        $method   = 'POST';
        $headers .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $headers .= "Content-Length: ".strlen($post)."\r\n";
    }
    
    // get data from cache?
    if ($cache &! $reload)
    {
        $resp = getHTTPcache($url.$post);
        if ($resp !== false)
        {
            $resp['cached'] = true;
            return $resp;
        }
    }

    $response['error']  = '';
    $response['header'] = '';
    $response['data']   = '';
    $response['url']    = $url;
    $response['success']= false;

    $uri    = parse_url($url);
    $server = $uri['host'];
    $path   = $uri['path'];
    if (empty($path)) $path = '/';  
    if (!empty($uri['query'])) $path .= '?'.$uri['query'];
    $port   = @$uri['port'];

    // proxy setup
    if (!empty($config['proxy_host']) && !$para['no_proxy'])
    {
        $request_url = $url;
        $server      = $config['proxy_host'];
        $port        = @$config['proxy_port'];
        if (!$port) $port = 8080;
    }
    else
    {
        $request_url = $path;  // cpuidle@gmx.de: use $path instead of $url if HTTP/1.0
        $server      = $server;
        if (!$port) $port = 80;
    }

    // open socket
    $socket = fsockopen($server, $port);
    if (!$socket)
    {
        $response['error'] = "Could not connect to $server";
        return $response;
    }
    stream_set_timeout($socket, $para['timeout'] ? $para['timeout'] : 10);

    // build request
    $request  = "$method $request_url HTTP/1.0\r\n";
    $request .= "Host: ".$uri['host']."\r\n";
    $request .= "User-Agent: Mozilla/4.0 (compatible; MSIE 5.5; Windows 98)\r\n";
    if (extension_loaded('zlib')) $request .= "Accept-encoding: gzip\r\n";
#    $request .= "Accept-Charset: iso-8859-1, utf-8, *\r\n";
    
    // add cookies- these are expcted to be in array form
    if ($para['cookies']) $request .= cookies2header($para['cookies']);
    
    $request .= "Connection: Close\r\n";
    
    // additional request headers
    $request .= $headers;
    if ($para['header']) $request .= $para['header'];
    
    $request .= "\r\n";
    $request .= $post;

    // send request
    fputs($socket, $request);

    if (@$config['debug']) echo "request:<br>".nl2br($request)."<p>";

    // log request
    if (@$config['httpclientlog'])
    {
        $log = fopen('httpClient.log', 'a');
        fwrite($log, $request."\n");
        fclose($log);
    }

    // read headers from socket
    while (!(feof($socket) || preg_match('/\r\n\r\n$/', $response['header'])))
    {
        $read                = fgets($socket);
        $response['header'] .= $read;
        // $header_size        += strlen($read);
    }

    // chunked encoding?
    if (preg_match('/transfer\-(en)?coding:\s+chunked\r\n/i', $response['header']))
    {
        do {
            unset($chunk_size);
            do
            {
                $byte = fread($socket, 1);
                $chunk_size .= $byte;
            }
            while (preg_match('/[a-zA-Z0-9]/',$byte));  // read chunksize including \r

            $byte       = fread($socket, 1);            // read trailing \n
            $chunk_size = hexdec($chunk_size);
            $this_chunk = fread($socket, $chunk_size);
            $response['data'] .= $this_chunk;
            if ($chunk_size) $byte = fread($socket, 2); // read trailing \r\n
        }
        while ($chunk_size);
    }
    else
    {
        // read entire socket
        while (!feof($socket))
        {
            $response['data'] .= fread($socket, 4096);
        }
    }

    // close socket
    $status = socket_get_status($socket);
    fclose($socket);

    if (@$config['debug']) echo "header:<br>".nl2br($response['header'])."<p>";
    // if ($config['debug']) echo "data:<br>".htmlspecialchars($response['data'])."<p>";

    // check for timeout
    if (@$status['timed_out'])
    {
        $response['error'] = "Connection timed out";
        return $response;
    }

    // log response
    if (@$config['httpclientlog'])
    {
        $log = fopen('httpClient.log', 'a');
        fwrite($log, $response['header']."\n");
        fclose($log);
    }

    // check server status code to follow redirect
    if (preg_match("/^HTTP\/1.\d 30[12].*?\n/s", $response['header']))
    {
        preg_match("/Location:\s+(.*?)\n/is",$response['header'],$matches);
        if (empty($matches[1]))
        {
            $response['error'] = 'Redirect but no Location header found';
            return $response;
        }
        else
        {
			// in case no redirect is needed stop here and respond success
            if ($para['no_redirect'])
            {
                // save time if result is not needed
        		$response['error']   = '';
        		$response['success'] = true;
        		return $response;
        	}

            // get redirection target
            $location = trim($matches[1]);

            if (preg_match("/^\//", $location))
            {
                // local redirect
                $location = 'http://'.$uri['host'].':'.$uri['port'].$location;
            }
            elseif (!preg_match('/^http/', $location))
            {
                // local redirect without path
            	$path     = substr($uri['path'], 0, strrpos($uri['path'], '/') + 1);
            	$location = 'http://'.$uri['host'].':'.$uri['port'].$path.$location;
            }

            // don't use old headers again
            $headers	= '';

            // add new cookies from response
            $para['cookies'] = get_cookies_from_response($response, $para['cookies']);

            // perform redirected request; we must GET, not POST
            if ($para['post']) unset($para['post']);
            $response = httpClient($location, $cache, $para);

            // remember we were redirected
            $response['redirect'] = $location;

            // store response a 2nd time under the the original post attributes
            if ($response['success'] == true && $cache) putHTTPcache($url.$post, $response);

            return $response;
        }
    }

    // verify status code
    if (!preg_match("/^.*? 200 .*?\n/s", $response['header']))
    {
        $response['error'] = 'Server returned wrong status.';
        return $response;
    }

    $response['success'] = true;

    // decode data if necessary- do not modify original headers
    if (preg_match("/Content-Encoding:\s+gzip\r?\n/i", $response['header']))
    {
        $response['data'] = gzinflate(substr($response['data'], 10));
    }

    // commit successful request to cache
    if ($cache) putHTTPcache($url.$post, $response);

    return $response;
}

/**
 * Downloads an URL to the given local file
 *
 * @param   string  $url    URL to download
 * @param   string  $local  Full path to save to
 * @return  bool            true on succes else false
 */
function download($url, $local)
{
    $resp = httpClient($url);
    if (!$resp['success']) return false;

    return(@file_put_contents($local, $resp['data']) !== false);
}

?>