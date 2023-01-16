{if !$DEBUG}<?xml version="1.0" encoding="{$lang.encoding}"?>{/if}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$langcode}" lang="{$langcode}" dir="ltr">
<head>
    <title>VideoDB{if $title} - {$title}{/if}</title>
    <meta http-equiv="Content-Type" content="text/html; charset={$lang.encoding}" />
    {if isset($trace_meta)} {$trace_meta} {/if}
    {if isset($delete_meta)} {$delete_meta} {/if}
    <meta name="description" content="VideoDB" />
    <link rel="stylesheet" href="{$style}" type="text/css" />
    {if $rss}<link rel="alternate" type="application/rss+xml" title="VideoDB RSS" href="index.php?export=rss" />
    {/if}<link rel="shortcut icon" href="images/icons/1-favicon.ico" type="image/ico" />
    <script src="./javascript/prototype/prototype.js" type="text/javascript"></script>
    <script src="./javascript/scriptaculous/scriptaculous.js?load=effects,controls" type="text/javascript"></script>
{*
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <script type="text/javascript">if (typeof jQuery == 'undefined') { document.write(unescape("%3Cscript src='./javascript/jquery/jquery-1.9.0.min.js' type='text/javascript'%3E%3C/script%3E"));}</script>
*}
{*    {if $config.browserid}<script src="https://browserid.org/include.js" type="text/javascript"></script>{/if} *}
</head>
