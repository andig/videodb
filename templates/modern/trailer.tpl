{*
  Trailer popup
  $Id: trailer.tpl,v 1.5 2012/08/10 16:07:30 andig2 Exp $
*}
{include file="xml.tpl"}

<body>

<script language="JavaScript" type="text/javascript" src="javascript/lookup.js"></script>

<div class="tablemenu">
	<div style="height:7px; font-size:1px;"></div>
    <span class="tabActive"><a href="#">YouTube Trailers</a></span>
</div>

<div id="topspacer"></div>

{if $trailer}
<table width="100%" cellspacing="0" cellpadding="0">

{assign var="id" value="1"}

{foreach item=match from=$trailer}
	<tr>
		<td>
            <iframe id="ytplayer{$id++}" type="text/html" width="425" height="350"
                src="http://www.youtube.com/embed/{$match.id}?autoplay=0"
                frameborder="0"/>

		</td>
	</tr>
{/foreach}

</table>
{/if}

</body>
</html>
