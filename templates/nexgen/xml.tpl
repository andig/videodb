<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<!--[if lt IE 7]><html class="lt-ie9 lt-ie8 lt-ie7" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"><![endif]-->
<!--[if IE 7]><html class="lt-ie9 lt-ie8" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"><![endif]-->
<!--[if IE 8]><html class="lt-ie9" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"><![endif]-->
<!--[if gt IE 8]><!--><html xmlns="http://www.w3.org/1999/xhtml"><!--<![endif]-->
<head>
	<title>videoDB{if $title} - {$title}{/if}</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width" />
	<meta name="description" content="VideoDB" />
	<link rel="shortcut icon" type="image/ico" href="images/icons/1-favicon.ico" />

	{if $rss}<link rel="alternate" type="application/rss+xml" title="VideoDB RSS" href="index.php?export=rss" />{/if}

	<!-- css -->
	<link rel="stylesheet" type="text/css" href="lib/foundation4/css/normalize.css" />
	<link rel="stylesheet" type="text/css" href="lib/foundation4/css/foundation.min.css" />
	<link rel="stylesheet" type="text/css" href="lib/foundation4/fonts/general_foundicons.css" />
	<link rel="stylesheet" type="text/css" href="{$style}" />
<!--[if lt IE 9]><link rel="stylesheet" type="text/css" href="lib/foundation4/css/ltie9.css" /><![endif]-->

	<link rel='stylesheet' href='http://fonts.googleapis.com/css?family=Exo:600' type='text/css'>

	<!-- js -->
	<script>document.write('<script src="lib/foundation4/js/vendor/' + ('__proto__' in {} ? 'zepto' : 'jquery') + '.js"><\/script>');</script> 
	<script type="text/javascript" src="lib/foundation4/js/vendor/custom.modernizr.js"></script>
	<script src="lib/foundation4/js/foundation.min.js"></script>
	<script type="text/javascript" src="{$template}js/app.js"></script>
	{literal}<script>$(document).ready(function(){$(document).foundation()});</script>{/literal}
</head>
