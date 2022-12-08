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

use GuzzleHttp\Psr7 as Psr7;

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
 * Extract source encoding from HTML code or HTTP header otherwise
 */
function get_response_encoding($response)
{
    $header = $encoding = null;

    // response array from cache
    if (is_array($response)) {
        if (isset($response['header']['Content-Type'])) {
            $header = $response['header']['Content-Type'];
        }
    }
    else {
        // Psr response
        $header = $response->getHeader('Content-Type');
    }

    if ($header) {
        $parsed = Psr7\parse_header($header);
        $encoding = strtolower($parsed[0]['charset']);
    }

    if (!$encoding)
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
    static $referer = 'https://www.imdb.com/search/';
    global $config;
    $client = new GuzzleHttp\Client();

    $requestConfig = [];
    $headers = '';  // additional HTTP headers, used for post data

    if ($para['cookies'])
    {
        $jar = new GuzzleHttp\Cookie\CookieJar();
        $requestConfig += ['cookies' => $jar];
    }

    $method  = 'GET';

    $post = isset($para['post']) ? $para['post'] : '';
    if ($post)
    {
        $method = 'POST';
        $requestConfig += ['headers' => ['Content-Type' => 'application/x-www-form-urlencoded']];
        $requestConfig += ['body' => $post];
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

    // proxy setup
    if (!empty($config['proxy_host']) && !$para['no_proxy'])
    {
        $server = $config['proxy_host'];
        if (!($port = @$config['proxy_port']))
        {
            $port = 8080;
        }
        $requestConfig += ['proxy' => sprintf('tcp://%s:%d', $server, $port)];
    }

    // additional request headers
    if ($para['header'])
    {
        $requestConfig += ['headers' => $para['header']];
    }

    if (empty($requestConfig['headers']['Accept'])) $requestConfig['headers']['Accept'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
    if (empty($requestConfig['headers']['Accept-Language'])) $requestConfig['headers']['Accept-Language'] = filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE'); // @todo make this configurable
    if (empty($requestConfig['headers']['DNT'])) $requestConfig['headers']['DNT'] = '1';
    if (empty($requestConfig['headers']['User-Agent'])) $requestConfig['headers']['User-Agent'] = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
    if (empty($requestConfig['headers']['Referer'])) $requestConfig['headers']['Referer'] = $referer;

    $resp = $client->request($method, $url, $requestConfig);

    $response['error'] = '';
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

    // verify status code
    if ($resp->getStatusCode() != 200)
    {
        $response['error'] = 'Server returned wrong status: ' . $resp->getStatusCode();
        $response['error'] .= " Reason: " . $resp->getReasonPhrase();
        return $response;
    }

    $response['success'] = true;
    // @todo i'm not sure on the side-effects of setting the previous requested URL as referer
    //        for the next, so disabled for now. might be something to investigate...
    //$referer = $url;

    // commit successful request to cache
    if ($cache)
    {
        putHTTPcache($url.$post, $response);
    }

    return $response;
}


/**
 * Print all header info using echo
 * @param response    Object homepage Psr7\Response
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