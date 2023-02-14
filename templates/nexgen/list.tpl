{*
  List template
  $Id: list.tpl,v 1.6 2013/03/21 16:27:57 andig2 Exp $
*}

<!-- {$smarty.template} -->

{if !empty($deleted)}
<div class="alert-box alert sticky">
	{$lang.deleted}
	<a href="#" class="close">&times;</a>
</div>
{/if}

<div class="row">
	<div class="small-12 columns small-centered">

		{assign var=max_width value=300}
		{assign var=max_height value=500}
                {if !empty($list)}
		<ul class="small-block-grid-2 large-block-grid-{$listcolumns} itemlist">
			{foreach $list as $video name=col}
			<li>
				<a href="show.php?id={$video.id}" class="th radius" title="{if $video.title}{$video.title}{if $video.subtitle} - {$video.subtitle}{/if}{else}(Empty title){/if}">
{if $config.browse_include_title eq top || $config.browse_include_title eq both}
					<div style="margin-top:-5px;">{if $video.title}{$video.title}{if $video.subtitle} - {$video.subtitle}{/if}{else}(Empty title){/if}</div>
{/if}
{*
					<!-- Uncomment this if you want to use lazy-load together with full-scale images (requires larger bandwidth - don't use for moile access) -->
					{if $video.imgurl}<img class="lazy" src="{$video.imgurl}"/>{/if}
*}
					<!-- Uncomment this if you want to use lazy-load together with image thumbnails - suited for mobile access -->
					{if $video.imgurl}<img class="lazy" src="templates/nexgen/images/nocover.png" data-original="{html_image file=$video.imgurl max_width=$max_width max_height=$max_height path_only=1}"/>{/if}
{if $config.browse_include_title eq bottom || $config.browse_include_title eq both}
					<div>{if $video.title}{$video.title}{if $video.subtitle} - {$video.subtitle}{/if}{else}(Empty title){/if}</div>
{/if}
				</a>
			</li><!--col-->
			{/foreach}
		</ul><!--row-->
                {/if}
	</div><!--column-->
</div><!--row-->
