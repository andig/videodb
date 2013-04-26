<?php
/**
 * HTTP caching functions
 *
 * Enable use of HTTP 304 headers for unmodified content to save bandwidth
 *
 * @package Core
 * @author  Andreas Götz    <cpuidle@gmx.de>
 * @version $Id: httpcache.php,v 1.5 2010/11/05 10:38:47 andig2 Exp $
 */

/**
 * Start output buffering
 */
function httpCacheCaptureStart()
{
    ob_start();
}

/**
 * Stop output buffering
 *
 * @param   string  MD5 hash of content
 */
function httpCacheCaptureEnd()
{
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}

/**
 * Get last modified data for given etag
 * Checks session for known etag and timestamp
 */
function httpCacheCheckTag($template, $etag)
{
    if ($etag != $_SESSION['vdb'][$template]['etag'])
    {
        $lastmod = time();
        $_SESSION['vdb'][$template]['etag'] = $etag;
        $_SESSION['vdb'][$template]['time'] = $lastmod;
    }
    else
    {
        $lastmod = $_SESSION['vdb'][$template]['time'];
    }
    return($lastmod);
}

/**
 * Output 304 Not Modified header
 * Require browser to re-check on next request
 */
function httpCacheHeaders($etag, $expires)
{
    header(php_sapi_name() == 'cgi' ? 'Status: 304 Not Modified' : 'HTTP/1.x 304 Not Modified');
    header("ETag: {$etag}");
    header("Cache-Control: private, max-age={$expires}, pre-check=0, post-check=0");
    header("Content-Length: 0");

    header("Content-Type: !invalid");
    exit();
}

/**
 * Check if output was modified since last request
 * If unmodifed, output 304 Not Modified header
 * Otherwise add additional ETag and LastModified headers
 */
function httpCacheOutput($template, $content)
{
    $etag = '"'.md5($content).'"';

    // check if 'sending' is necessary (using cache functions)
    $sendbody = true;
    $expires  = 0;

    $lastmod  = httpCacheCheckTag($template, $etag);

    // check 'If-Modified-Since' header
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && gmdate('D, d M Y H:i:s', $lastmod)." GMT" == trim($_SERVER['HTTP_IF_MODIFIED_SINCE']))
    {
        httpCacheHeaders($etag, $expires);
    }

    // check 'If-None-Match' header (ETag)
    if ($sendbody && isset($_SERVER['HTTP_IF_NONE_MATCH']))
    {
        $inm = explode(',', $_SERVER['HTTP_IF_NONE_MATCH']);
        foreach ($inm as $i)
        {
            if (trim($i) != $etag) continue;
            httpCacheHeaders($etag, $expires);
        }
    }

    // send with caching headers (enable cache for one day)
    $exp_gmt = gmdate('D, d M Y H:i:s', time() + $expires).' GMT';
    $mod_gmt = gmdate('D, d M Y H:i:s', $lastmod).' GMT';
    header("Expires: {$exp_gmt}");
    header("Last-Modified: {$mod_gmt}");
    header("Cache-Control: private, max-age={$expires}, pre-check=0, post-check=0");
    header("Pragma: !invalid");
    header("ETag: {$etag}");

    echo $content;
}

?>