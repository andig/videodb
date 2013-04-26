{*
  Inline ajax response display
  $Id: lookup_ajax.tpl,v 1.3 2009/04/04 16:21:33 andig2 Exp $
*}

<!-- {$smarty.template} -->

{if $searchtype == 'purchase'}

{foreach item=match from=$imdbresults}
    <div class="thumbnail" >
        <a href="{$match.url}" target="_blank" title="Purchase">
            <img src="{$match.imgsmall}" url="{$match.coverurl}" align="left" width="60" height="90" /><br />
            {$match.title}{if $match.subtitle} - {$match.subtitle}{/if}
            {if $match.sl}({$match.sl}){/if}
            {if $match.price}({$match.price}){/if}
        </a>
    </div>
{/foreach}

{elseif $searchtype == 'download'}

<ul>
{foreach item=match from=$imdbresults}
	<li><a href="{$match.url}" target="_blank">{$match.title}</a>{if $match.subtitle} - {$match.subtitle}{/if}
		{if $match.plot && 0}<br/>{$match.plot}{/if}
	</li>
{/foreach}
</ul>

{elseif $searchtype == 'trailer'}

{foreach item=match from=$imdbresults}
	<span>
		<object width="425" height="350">
			<param name="movie" value="{$match}"></param>
			<param name="wmode" value="transparent"></param>
			<embed src="{$trailerid}" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></embed>
		</object>
	</span>
{/foreach}

{else}

{foreach item=match from=$imdbresults}
    <div class="thumbnail" >
        <a href="javascript:void(returnImage('{$match.coverurl|escape:"javascript"}'))" url="{$match.coverurl}" title="Select image">
            <img src="{$match.imgsmall}" url="{$match.coverurl}" align="left" width="60" height="90" /><br />
            {$match.title}
        </a>
    </div>
{/foreach}

{/if}