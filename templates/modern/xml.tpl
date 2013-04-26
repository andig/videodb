{*
commented out to avoid IE switching to quirksmode, see http://www.quirksmode.org/css/quirksmode
{if !$DEBUG}<?xml version="1.0" encoding="{$lang.encoding}"?>{/if}
*}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$langcode}" lang="{$langcode}" dir="ltr">
<head>
    <title>VideoDB{if $title} - {$title}{/if}</title>
    <meta http-equiv="Content-Type" content="text/html; charset={$lang.encoding}" />
    {php}if (stristr($_SERVER['PHP_SELF'], "delete.php")) echo '<meta http-equiv="refresh"; content="1; url='.session_get('listview', 'index.php').'?'.strip_tags(SID).'">';{/php}
    <meta name="description" content="VideoDB" />
    <link rel="stylesheet" href="{$style}" type="text/css" />
    {if $rss}<link rel="alternate" type="application/rss+xml" title="VideoDB RSS" href="index.php?export=rss" />
    {/if}<link rel="shortcut icon" href="images/icons/1-favicon.ico" type="image/ico" />
</head>
