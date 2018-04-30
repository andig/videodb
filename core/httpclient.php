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
require_once 'vendor/autoload.php';

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
    return GuzzleHttp\Psr7\parse_header($response->getHeader($header));
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
    $cookies = GuzzleHttp\Psr7\parse_header($response->getHeader('Set-Cookie'));
    foreach ($cookies[0] as $key => $value)
    {
        $oldcookies[$key] = $value;
    }

    return $oldcookies;
}

/**
 * Extract source encoding from HTML code or HTTP header otherwise
 */
function get_response_encoding($response)
{
    $parsed = GuzzleHttp\Psr7\parse_header($response->getHeader('Content-Type'));
    $encoding = strtolower($parsed[0]['charset']);
    if (!encoding)
    {
        $encoding = 'iso-8859-1';
    }

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
    $client = new GuzzleHttp\Client();

    $headers = '';  // additional HTTP headers, used for post data

    $requestConfig;
    $requestConfig = ['timeout' => 15];
    if ($para['cookies'])
    {
        $jar = new GuzzleHttp\Cookie\CookieJar();
        $requestConfig += ['cookies' => $jar];
    }

    $post = $para['post'];
    if (is_array($post))
    {
        $post = http_build_query($post);
    }

    $method  = 'GET';
    if (!empty($post))
    {
        //  POST request
        $method = 'POST';

        $requestConfig += ['headers' => ['Content-Type' => 'application/x-www-form-urlencoded']];
        $requestConfig += ['headers' => ['Content-Length' => strlen($post)]];
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

    $uri = parse_url($url);
    $server = $uri['host'];
    $path = $uri['path'];
    if (empty($path))
    {
        $path = '/';
    }

    if (!empty($uri['query']))
    {
        $path .= '?'.$uri['query'];
    }
    $port = @$uri['port'];

    // proxy setup
    if (!empty($config['proxy_host']) && !$para['no_proxy'])
    {
        $request_url = $url;
        $server = $config['proxy_host'];
        $port = @$config['proxy_port'];
        if (!$port)
        {
            $port = 8080;
        }
    }
    else
    {
        $request_url = $url; //path;  // cpuidle@gmx.de: use $path instead of $url if HTTP/1.0
        $server = $server;
        if (!$port)
        {
            $port = 80;
        }
    }

    // build request
    $request = '';
    if (extension_loaded('zlib')) {
        $requestConfig += ['headers'        => ['Accept-Encoding' => 'gzip'],
                           'decode_content' => true];
    }

    // additional request headers
    $request .= $headers;
    if ($para['header'])
    {
        $requestConfig += ['headers' => $para[header]];
    }

    if ($config['debug']) echo "request:<br>".nl2br($request)."<p>";

    // log request
    if ($config['httpclientlog'])
    {
        $log = fopen('httpClient.log', 'a');
        fwrite($log, $request."\n");
        fclose($log);
    }

    $resp = $client->request($method, $request_url, $requestConfig);

    $response['error'] = '';
    $response['header'] = '';
    $response['data'] = '';
    $response['url'] = $url;
    $response['success'] = false;
    $response['encoding'] = get_response_encoding($resp);
    $response['header'] = $resp->getHeaders();
    $response['data'] = (string) $resp->getBody();

    if ($config['debug']) echoHeaders($response['header'])."<p>";
    if ($config['debug']) echo "data:<br>".htmlspecialchars($response['data'])."<p>";

    // log response
    if ($config['httpclientlog'])
    {
        $log = fopen('httpClient.log', 'a');
        fwrite($log, headers_to_string($response['header']));
        fclose($log);
    }

    // check server status code to follow redirect
    if ($resp->getStatusCode() == 301 || $resp->getStatusCode() == 302)
    {
        // get redirection target
        $location = getHeader($response['header'], 'Location');
        if (empty($location))
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
                $response['error'] = '';
                $response['success'] = true;
                return $response;
            }

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
            $headers = '';

            // add new cookies from response
            $para['cookies'] = get_cookies_from_response($resp, $para['cookies']);

            // perform redirected request; we must GET, not POST
            if ($para['post'])
            {
                unset($para['post']);
            }
            $response = httpClient($location, $cache, $para);

            // remember we were redirected
            $response['redirect'] = $location;

            // store response a 2nd time under the the original post attributes
            if ($response['success'] == true && $cache)
            {
                putHTTPcache($url.$post, $response);
            }

            return $response;
        }
    }

    // verify status code
    if ($resp->getStatusCode() != 200)
    {
        $response['error'] = 'Server returned wrong status: ' . $resp->getStatusCode();
        $response['error'] .= " Reason: " . $resp.getReasonPhrase();
        return $response;
    }

    $response['success'] = true;

    // commit successful request to cache
    if ($cache)
    {
        putHTTPcache($url.$post, $response);
    }

    return $response;
}


/**
 * Print all header info using echo
 * @param response    Object homepageGuzzleHttp\Psr7\Response
 */
function echoHeaders($headers)
{
    foreach ($headers as $name => $values) {
        echo $name . ': ' . implode(', ', $values) . "<br>";
    }
}

function headers_to_string($headers)
{
    $result = '';
    foreach ($headers as $name => $values) {
        $result .= $name . ': ' . implode(', ', $values) . "\n";
    }

    return $result;
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

    if (!$resp['success'])
    {
        return false;
    }

    return(@file_put_contents($local, $resp['data']) !== false);
}

?>