{*
  Template for the video detail view/episodes chapter
  $Id: episodes.tpl,v 2.2 2005/05/20 10:24:28 andig2 Exp $
*}

<table width="100%" class="show_plot" cellspacing="0" cellpadding="0">
{counter start=0 print=false name=videocount}

{foreach item=episode from=$video.episodes}

	{if !$count}
		{cycle values="even,odd" assign=CLASS print=false}
		<tr class="{$CLASS}">
	{/if}

	<td class="list_episode">
{*
		<table width="100%" class="show_plot">
		  <tr>
			<td style="text-align:justify">
*}
				<b>
					<a href="show.php?id={$episode.id}">{$episode.title}</a>
					{if $episode.episode}
						[{$episode.season}-{$episode.episode}]
					{/if}
				</b><br/>
{*
			  <b>{$lang.plot}:</b><br/>{$episode.plot}<br/>
*}
			  {$episode.plot}
{*
			</td>
		  </tr>
		</table>
*}
	</td>

	{counter assign=count name=videocount}

	{if $count == $listcolumns}
		{counter start=0 print=false name=videocount}
		</tr>
	{/if}
{/foreach}

{if $count}
	{section name="columnLoop" start=$count loop=$listcolumns}
		<td class="{$CLASS}">&nbsp;</td>
	{/section}
	</tr>
{/if}

</table>