{*
  Output of search results/browselist
  $Id: list.tpl,v 2.18 2013/03/14 17:17:27 andig2 Exp $
*}

<table width="100%" class="tableborder">
  {foreach item=video from=$list}
    {cycle values="even,odd" assign=CLASS print=false}
    {if $video.who != ''}
      {assign var=CLASS value="lent"}
    {/if}

    <tr class="{$CLASS}">

      {if $video.imgurl != ''}
        <td width="37" align="center">
          <a class="list_title" href="show.php?id={$video.id}">
            <img src="{$video.imgurl}" border="0" width="35" height="48" align="top" alt="" />
          </a>
        </td>
      {/if}

      <td>
        <a class="list_title" href="show.php?id={$video.id}">
          {$video.title}
          {if $video.subtitle}
          - {$video.subtitle}
          {/if}
        </a>

        [<span class="list_info">{if $video.year}<a href="search.php?q={$video.year}&fields=year&nowild=1">{$video.year}</a>{/if}{if $video.director && $video.year}; {/if}{if $video.director != ""}<a href="search.php?q=%22{$video.director}%22&isname=Y">{$video.director}</a>{/if}</span>]
        <br />

        <span class="list_plot">
          {$video.plot|truncate:250}
          <a class="list_title" href="show.php?id={$video.id}">{$lang.more}</a>
        </span>
      </td>

      <td align="center" nowrap="nowrap" style="text-align:center">
        <span class="list_diskid"><a href="search.php?q={$video.diskid}&fields=diskid&nowild=1">{$video.diskid}</a></span>
        <br />
    {foreach item=itemlang from=$video.language}
        {if $itemlang}<a href="search.php?q={$itemlang|escape:url}&fields=language">
            {if $video.flagfile[$itemlang]}
                <img src="{$video.flagfile[$itemlang]}" border="0" alt="{$itemlang}"/>
            {else}
                {$itemlang}
            {/if}</a>
        {/if}
    {/foreach}
        <br />
        {if $video.seen}
          <span class="list_seen">{$lang.seen}</span>
        {/if}

      </td>

      <td align="center" nowrap="nowrap" style="text-align:center">
        {if $video.editable}
          <a href="edit.php?id={$video.id}">{$lang.edit}</a><br />
          <a href="delete.php?id={$video.id}" onclick="return(confirm('{$video.title|escape:javascript|escape}: {$lang.really_del}?'))">{$lang.delete}</a>
        {/if}
      </td>

    </tr>
  {foreachelse}
    <tr>
      <td align="center" style="text-align:center">
        <br />
        {$lang.l_nothing}
        <br />
        <br />
      </td>
    </tr>
  {/foreach}
</table>
