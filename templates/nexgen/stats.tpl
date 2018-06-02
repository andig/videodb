{*
  Statistics template
  $Id: stats.tpl,v 1.4 2013/03/13 15:27:16 andig2 Exp $
*}

<!-- {$smarty.template} -->

<div class="row">
	<div class="small-12 large-10 columns small-centered">


    <h3 class="subheader">{$lang.statistics}</h3>

    {if $owners}
    <h6 class="subheader">{$lang.statistics_for}</h6>
    <form action="stats.php">
    	{html_options name=owner options=$owners selected=$owner class="autosubmit"}
    </form>
    {/if}


    <div class="row">
    	<div class="small-12 large-6 columns">

          <table class="small-12">
            <tr>
              <td><h6 class="subheader">{$lang.totalfiles}:</h6></td>
              <td>{$stats.count_all}</td>
            </tr>
            <tr>
              <td><h6 class="subheader">{$lang.tv_episodes}:</h6></td>
              <td><a href="search.php?q=1&fields=istv">{$stats.count_tv}</a></td>
            </tr>
            <tr>
              <td><h6 class="subheader">{$lang.numberdisks}:</h6></td>
              <td>{$stats.count_disk}</td>
            </tr>
            <tr>
              <td><h6 class="subheader">{$lang.videobygen}:</h6>{$lang.multiple}</td>
              <td>
              	<div class="small-cols-1 large-cols-2">
          				<ul>
          					{foreach item=row from=$stats.count_genre}
          					<li>{$row.count} <a href="search.php?q=&genres[]={$row.id}">{if $lang.{$row.name}} {$lang.{$row.name}} {else} {$row.name} {/if}</a></li>
          					{/foreach}
          				</ul>
                </div>
              </td>
            </tr>
            <tr>
              <td><h6 class="subheader">{$lang.year}:</h6></td>
              <td>
                <table class="small-12">
                  <tr>
                    <td valign="bottom"><small>{$stats.first_year|spacify:"<br/>"}</small></td>
                    <td colspan="2" valign="bottom">
                      {foreach key=year item=count from=$stats.count_year}<a href="search.php?q={$year}&amp;fields=year&amp;nowild=1"><img src="images/bar.gif" style="width:7px; height:{if $count==0}0{else}{math equation='max(round(100/y*x),1)' x=$count y=$stats.max_count}{/if}px;" title="{$year}: {$count}" alt="{$year}: {$count}" /></a>{/foreach}
                    </td>
                    <td><small>{$stats.last_year|spacify:"<br/>"}</small></td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>

    	</div><!-- col -->

    	<div class="small-12 large-6 columns">

          <table class="small-12">
            <tr>
              <td><h6 class="subheader">{$lang.averageruntime}:</h6></td>
              <td>{$stats.avg_time} min</td>
            </tr>
            <tr>
              <td><h6 class="subheader">{$lang.totalruntime}:</h6></td>
              <td>{$stats.sum_time} h</td>
            </tr>
            <tr>
              <td><h6 class="subheader">{$lang.totalseen}:</h6></td>
              <td>{$stats.seen_time} h</td>
            </tr>
            <tr>
              <td><h6 class="subheader">{$lang.languages}:</h3></td>
              <td>
              	<div class="small-cols-1 large-cols-2">
              	<ul>
                	{foreach item=row from=$stats.count_lang}
                	{if $row.language}
    				      <li>{$row.count} <a href="search.php?q={$row.language|escape:url}&amp;fields=language">{$row.language}</a></li>
                  {/if}
                	{/foreach}
                </ul>
                </div>
              </td>
            </tr>
            <tr>
              <td><h6 class="subheader">{$lang.videobymedia}:</h6></td>
              <td>
              	<div class="small-cols-1 large-cols-2">
          				<ul>
          					{foreach item=row from=$stats.count_media}
          					<li>{$row.count} <a href="search.php?q='{$row.name|escape:url}'&amp;fields=mediatype&amp;nowild=1">{$row.name}</a></li>
          					{/foreach}
          				</ul>
                </div>
              </td>
            </tr>
            <tr>
              <td><h6 class="subheader">{$lang.averagefilesize}:</h6></td>
              <td>{$stats.avg_size} mb</td>
            </tr>
            <tr>
              <td><h6 class="subheader">{$lang.totalsize}:</h6></td>
              <td>{$stats.sum_size} gb</td>
            </tr>
            <tr>
              <td><h6 class="subheader">{$lang.videobyvcodec}:</h3></td>
              <td>
              	<div class="small-cols-1 large-cols-2">
    				<ul>
    					{foreach item=row from=$stats.count_vcodec}
    					{if $row.video_codec}
    					<li>{$row.count} <a href="search.php?q={$row.video_codec|escape:url}&amp;fields=video_codec&amp;nowild=1">{$row.video_codec}</a></li>
    					{/if}
    					{/foreach}
    				</ul>
                </div>
              </td>
            </tr>
            <tr>
              <td><h6 class="subheader">{$lang.videobyacodec}:</h3></td>
              <td>
              	<div class="small-cols-1 large-cols-2">
          				<ul>
          					{foreach item=row from=$stats.count_acodec}
          					{if $row.audio_codec}
          					<li>{$row.count} <a href="search.php?q={$row.audio_codec|escape:url}&amp;fields=audio_codec&amp;nowild=1">{$row.audio_codec}</a></li>
          					{/if}
          					{/foreach}
          				</ul>
                </div>
              </td>
            </tr>
          </table>

    	</div><!-- col -->
    </div><!-- row -->

  </div><!-- col -->
</div><!-- row -->
