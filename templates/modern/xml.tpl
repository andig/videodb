{*
commented out to avoid IE switching to quirksmode, see http://www.quirksmode.org/css/quirksmode
{if !$DEBUG}<?xml version="1.0" encoding="{$lang.encoding}"?>{/if}
*}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$langcode}" lang="{$langcode}" dir="ltr">
<head>
    <title>VideoDB{if $title} - {$title}{/if}</title>
    <meta http-equiv="Content-Type" content="text/html; charset={$lang.encoding}" />
    {if !empty($trace_meta)} {$trace_meta} {/if}
    {if !empty($delete_meta)} {$delete_meta} {/if}
    <meta name="description" content="VideoDB" />
    <link rel="stylesheet" href="{$style}" type="text/css" />
    {if $rss}<link rel="alternate" type="application/rss+xml" title="VideoDB RSS" href="index.php?export=rss" />
    {/if}<link rel="shortcut icon" href="images/icons/1-favicon.ico" type="image/ico" />
</head>
