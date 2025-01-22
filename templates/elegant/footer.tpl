{*
  This is the footer which is displayed on bottom of every page
  $Id: footer.tpl,v 1.7 2008/10/19 10:45:28 andig2 Exp $
*}

<!-- {$smarty.template} -->

<div id="footer" style="clear:both;">

    <div id="footerimage">
        {if !empty($pdf)}
            <a href="{$pdf}export=pdf&amp;ext=.pdf"><img src="images/pdfexport.png" /></a>
        {/if}
        {if !empty($xls)}
            <a href="{$xls}export=xls&amp;ext=.xls"><img src="images/xlsexport.png" /></a>
        {/if}
        {if !empty($xml)}
            <a href="{$xml}export=xml" target="_blank"><img src="images/xmlexport.png" /></a>
        {/if}
        {if !empty($rss)}
            <a href="{$rss}export=rss" target="_blank"><img src="images/rssexport.png" /></a>
        {/if}
    </div>

    <div id="footerversion">
        <a href="https://github.com/andig/videodb.git">v{$version|strip|replace:"_":"."}</a>
    </div>

    <div id="footerpages">
        <a href="#top"><img src="images/top.gif" alt=""/></a>

        {if !empty($pageno) && !empty($maxpageno)}
            {if $pageno != 1}<a href="?pageno={$pageno-1}">&#171;</a>{/if}
            Page <span id="pageno">{$pageno}</span> of <span id="maxpageno">{$maxpageno}</span>
            {if $pageno != $maxpageno}<a href="?pageno={$pageno+1}">&#187;</a>{/if}
            &nbsp;
        {/if}
        {if !empty($totalresults)}<span id="count">{$totalresults}</span> {$lang.records}.{/if}
    </div>
{*
    {if $loggedin}<span> {$lang.loggedinas} {$loggedin}</span>{/if}
*}
</div>
<!-- /footer -->

</div>
<!-- /container -->

{$DEBUG}

</body>
</html>