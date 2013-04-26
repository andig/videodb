{*
  This is the footer which is displayed on bottom of every page
  $Id: footer.tpl,v 2.25 2008/10/19 10:45:28 andig2 Exp $
*}

{$DEBUG}

<table class="tablefooter">
<tr>
	<td><a href="#top"><img src="images/top.gif" alt=""/></a></td>
	<td style="text-align:center" nowrap="nowrap">
		<span class="version">
		{if $pageno && $maxpageno}
			{if $pageno != 1}<a href="?pageno={$pageno-1}">&#171;</a>{/if}
			Page {$pageno} of {$maxpageno}
			{if $pageno != $maxpageno}<a href="?pageno={$pageno+1}">&#187;</a>{/if}
			&nbsp;
		{/if}
			<span id="count">{$totalresults}</span> {$lang.records}.
		</span>
	</td>

	<td align="right" style="text-align:right" nowrap="nowrap">
		{if $pdf}
			<a href="{$pdf}export=pdf&ext=.pdf"><img src="images/pdfexport.png" style="float:right;margin-left:3px;"/></a>
		{/if}
		{if $xls}
			<a href="{$xls}export=xls&ext=.xls"><img src="images/xlsexport.png" style="float:right;margin-left:3px;"/></a>
		{/if}
		{if $xml}
			<a href="{$xml}export=xml" target="_blank"><img src="images/xmlexport.png" style="float:right;margin-left:3px;"/></a>
		{/if}
		{if $rss}
			<a href="{$rss}export=rss" target="_blank"><img src="images/rssexport.png" style="float:right;margin-left:3px;"/></a>
		{/if}
		<a href="http://www.videodb.net" class="splitbrain">v.{$version|strip}</a>{if $loggedin}<span class="version">, {$lang.loggedinas} {$loggedin}</span>{/if}
	</td>
</tr>
</table>
</body>
</html>