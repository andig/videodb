{*
  Template for the statistics page
  $Id: stats.tpl,v 2.11 2008/11/13 17:56:05 andig2 Exp $
*}

<table width="100%" class="tableborder">
  <tr>
    <td align="center">

      <table>
        {if $owners}
        <tr>
          <td><b>{$lang.owner}:</b></td>
          <td><form action="stats.php">{html_options name=owner options=$owners selected=$owner onchange="submit()"}</form></td>
        </tr>
        {/if}
        <tr>
          <td><b>{$lang.totalfiles}:</b></td>
          <td>{$stats.count_all}</td>
        </tr>
        <tr>
          <td><b>{$lang.tv_episodes}:</b></td>
          <td><a href="search.php?q=1&fields=istv">{$stats.count_tv}</a></td>
        </tr>
        <tr>
          <td><b>{$lang.numberdisks}:</b></td>
          <td>{$stats.count_disk}</td>
        </tr>
        <tr>
          <td><b>{$lang.videobygen}:</b><br />{$lang.multiple}</td>
          <td>
            {foreach item=row from=$stats.count_genre}
              <a href="search.php?q=&genres[]={$row.id}">{if $lang.{$row.name}} {$lang.{$row.name}} {else} {$row.name} {/if}</a> : {$row.count}<br />
            {/foreach}
          </td>
        </tr>
        <tr>
          <td><b>{$lang.year}:</b></td>
          <td>
            <table cellpadding="2">
              <tr>
                <td valign="bottom" align="left" style="text-align:left"><small>{$stats.first_year|spacify:"<br />"}</small></td>
                <td  colspan="2" valign="bottom" align="left" class="odd">
                {foreach key=year item=count from=$stats.count_year}<a href="search.php?q={$year}&fields=year&nowild=1"><img src="images/bar.gif" width="3" height="{$count}" title="{$year}: {$count}" border="0" alt="" /></a>{/foreach}
                </td>
                <td valign="bottom" align="right" style="text-align:right"><small>{$stats.last_year|spacify:"<br />"}</small></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
    <td align="center">
      <table>
        <tr>
          <td><b>{$lang.averagefilesize}:</b></td>
          <td>{$stats.avg_size} mb</td>
        </tr>
        <tr>
          <td><b>{$lang.totalsize}</b></td>
          <td>{$stats.sum_size} gb</td>
        </tr>
        <tr>
          <td><b>{$lang.averageruntime}:</b></td>
          <td>{$stats.avg_time} min</td>
        </tr>
        <tr>
          <td><b>{$lang.totalruntime}:</b></td>
          <td>{$stats.sum_time} h</td>
        </tr>
        <tr>
          <td><b>{$lang.totalseen}:</b></td>
          <td>{$stats.seen_time} h</td>
        </tr>
        <tr>
          <td><b>{$lang.languages}:</b><br /></td>
          <td>
            <table cellspacing="0">
            {foreach item=row from=$stats.count_lang}
              {if $row.language}<tr><td><a href="search.php?q={$row.language|escape:url}&fields=language">{$row.language}</a>:</td><td>{$row.count}</td></tr>{/if}
            {/foreach}
            </table>
          </td>
        </tr>
        <tr>
          <td><b>{$lang.videobymedia}:</b></td>
          <td>
            <table cellspacing="0">
            {foreach item=row from=$stats.count_media}
              <tr><td><a href="search.php?q={$row.name|escape:url}&fields=mediatype&nowild=1">{$row.name}</a>:</td><td>{$row.count}</td></tr>
            {/foreach}
            </table>
          </td>
        </tr>
        <tr>
          <td><b>{$lang.videobyvcodec}:</b><br /></td>
          <td>
            <table cellspacing="0">
            {foreach item=row from=$stats.count_vcodec}
              {if $row.video_codec}<tr><td><a href="search.php?q={$row.video_codec|escape:url}&fields=video_codec&nowild=1">{$row.video_codec}</a>:</td><td>{$row.count}</td></tr>{/if}
            {/foreach}
            </table>
          </td>
        </tr>
        <tr>
          <td><b>{$lang.videobyacodec}:</b><br /></td>
          <td>
            <table cellspacing="0">
            {foreach item=row from=$stats.count_acodec}
              {if $row.audio_codec}<tr><td><a href="search.php?q={$row.audio_codec|escape:url}&fields=audio_codec&nowild=1">{$row.audio_codec}</a>:</td><td>{$row.count}</td></tr>{/if}
            {/foreach}
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
