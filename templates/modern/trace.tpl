{*
  Template for IMDB Online browsing
  $Id: trace.tpl,v 2.11 2005/10/13 19:30:55 andig2 Exp $
*}

<table width="100%" cellspacing="0" cellpadding="0">
<tr><td>
    <table width="100%" class="tablefilter" cellspacing="5">
    <tr><td width="100%">
        <span class="filterlink">URL: </span><a href="{$url}" target="_blank">{$url}</a>
    </td>
    <td align="right">
        <nobr>{if $fetchtime}<span class="filterlink">{$lang.fetchtime}: </span>{$fetchtime}s{else}&nbsp;{/if}{if $md5} {$md5}{/if}</nobr>
    </td>
    <td align="right">
        <form action="trace.php" method="get">
            <input type="hidden" name="videodburl" value="{$url}"/>
            <input type="hidden" name="videodbreload" value="Y"/>
            <input type="submit" value="Reload" class="button"/>
        </form>
    </td></tr>
    </table>
</td></tr>
</table>
{$page}
