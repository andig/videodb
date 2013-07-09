{*
  Output of search results/browselist
  $Id: list.tpl,v 1.22 2013/03/14 17:16:36 andig2 Exp $
*}

<!-- {$smarty.template} -->

{if $listcolumns == 1}
    {assign var=IMGWIDTH value="35"}
    {assign var=IMGHEIGHT value="48"}
{else}
    {assign var=IMGWIDTH value="97"}
    {assign var=IMGHEIGHT value="144"}
{/if}

{*include file="protoflow.tpl"*}
{*include file="covereffect.tpl"*}

<div id="content">
{counter start=0 print=false name=videocount}
{foreach $list as $video}
{if $count == 0}
    {cycle values="listeven,listodd" assign=CLASS print=false}
    <div class="{$CLASS}">
        <table width="100%" class="collapse">
        <tr>
{/if}
            <td {if $video.who}class="lent"{elseif $video.mediatype==$smarty.const.MEDIA_WISHLIST}class="wanted"{/if} width="{floor(100/$listcolumns)}%">

            <div class="list_item">
                {if $video.imgurl}{html_image file=$video.imgurl link="show.php?id="|cat:$video.id align=left max_width=$IMGWIDTH max_height=$IMGHEIGHT class="cover"}{/if}
{*
            <div class="boxgrid caption">
                {if $video.imgurl}{html_image file=$video.imgurl link="show.php?id="|cat:$video.id align=left max_width=$IMGWIDTH max_height=$IMGHEIGHT class="cover"}{/if}

                <div class="cover boxcaption" style="top: 200px">
					<h3>{$video.title}{if $video.subtitle} - {$video.subtitle}{/if}</h3>
					<h4>
					{if $video.director}<a href="search.php?q=%22{$video.director|escape:url}%22&amp;isname=Y">{$video.director}</a>{/if}
					{if $video.year}(<a href="search.php?q={$video.year}&amp;fields=year&amp;nowild=1">{$video.year}</a>){/if}
					{if $video.diskid}<a href="search.php?q={$video.diskid}&fields=diskid&nowild=1">{$video.diskid}</a>{/if}
					</h4>
					{if $video.plot}<div>{$video.plot|truncate:100}</div>{/if}
				</div>
			</div>
*}

{if $listcolumns < 4}
                <div class="list_item_more">
                    {if $video.editable}
                    <div class="list_item_buttons">
                        <form action="edit.php" method="get">
                            <input type="hidden" name="id" value="{$video.id}"/>
                            <input type="submit" class="button" value="{$lang.edit}" />
                        </form>
                        <form action="delete.php" method="get">
                            <input type="hidden" name="id" value="{$video.id}"/>
                            <input type="submit" class="button" value="{$lang.delete}" onclick="return(confirm('{$video.title|escape:javascript|escape}: {$lang.really_del|escape:javascript|escape}?'))" />
                        </form>
                    </div>
                    {/if}

{* For quicker searching ond oneddl.com -obsolete- *}									
{*					{if $video.mediatype==$smarty.const.MEDIA_WISHLIST}
					<div class="list_imdbid">
						tt{$video.imdbID|replace:"imdb:":""}
					</div>
					{/if}
*}					
                  	<div class="list_rating">
                  	{if $video.rating}{$lang.rating}: {$video.rating}{/if}
                    </div>
{*                    <div class="list_diskid">
                        <a href="search.php?q={$video.diskid}&fields=diskid&nowild=1">{$video.diskid}</a>
                    </div>
*}
                    <div class="list_language">
                        {foreach $video.language as $itemlang}
                            {if $itemlang}<a href="search.php?q={$itemlang|escape:url}&amp;fields=language">
                                {if $video.flagfile[$itemlang]}
                                    <img src="{$video.flagfile[$itemlang]}" alt="{$itemlang}"/>
                                {else}
                                    {$itemlang}
                                {/if}</a>
                            {/if}
                        {/foreach}
                    </div>
                    
                       <div class="list_mediatype" style=width:"18">
                        {foreach $video.mediatypename as $mediatype}
                            {if $mediatype}<a href="search.php?q={$mediatype|escape:url}&amp;fields=mediatype">
                                {if $video.mediatypename[$mediatype]}
                                    <img src="./images/media/{$mediatype}.png" alt="{$mediatype}" width="40"/>
                                {else}
                                    {$mediatype}
                                {/if}</a>
                            {/if}
                        {/foreach}
					</div>

                   {if $video.seen}
                    <div class="list_seen"><!--<a href="index.php?filter=seen">--><img src="{$template}images/eye.gif" filter="seen" alt="{$lang.seen}"/><!--</a>--></div>
                    {/if}
                </div>
{/if}
                
                <div class="list_item_content">
                    <div class="list_title"><a href="show.php?id={$video.id}">{$video.title}{if $video.subtitle} - {$video.subtitle}{/if}</a></div>

                    {if $video.year || $video.director}
                    <div class="list_info">
                        [{if $video.year}<a href="search.php?q={$video.year}&amp;fields=year&amp;nowild=1">{$video.year}</a>{/if}{if $video.director}{if $video.year}; {/if}<a href="search.php?q=%22{$video.director|escape:url}%22&amp;isname=Y">{$video.director}</a>{/if}]
                    </div>
                    {/if}

                    <div class="list_plot">
                        {$video.plot|truncate:250}
                        <a href="show.php?id={$video.id}">{$lang.more}</a>
                    </div>
                </div>
            </div>

            </td>

{counter assign=count name=videocount}
{if $count == $listcolumns}
    {counter start=0 print=false name=videocount}
        </tr>
        </table>
    </div>
{/if}
{/foreach}

{if $count != 0}
    {section name="columnLoop" start=$count loop=$listcolumns}
            <td>&nbsp;</td>
    {/section}
        </tr>
        </table>
    </div>
{/if}

</div>
<!-- /content -->
