{*
  Template for browsing IMDB through VideoDB
  $Id: trace.tpl,v 2.10 2005/05/20 10:02:17 andig2 Exp $
*}

<table width="100%" class="tableborder">
  <tr>
    <td>URL: </span><a href="{$url}" target="_blank">{$url}</a></td>
    <td nowrap="nowrap" align="right" style="text-align:right">{if $fetchtime}{$lang.fetchtime}: {$fetchtime}s{/if}
    <td align="right" style="text-align:right;">
      <form action="trace.php" method="get" style="margin:0px; padding:0px;">
        <input type="hidden" name="videodburl" value="{$url}" />
        <input type="hidden" name="videodbreload" value="Y" />
        <input type="submit" value="Reload" />
      </form>
    </td>
  </tr>
</table>

<br />

<table width="100%" class="tableborder">
<tr><td style="background-color:#ffffff">
{$page}
</td></tr>
</table>
