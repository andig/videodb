{*
  List template
  $Id: list.tpl,v 1.6 2013/03/21 16:27:57 andig2 Exp $
*}

<!-- {$smarty.template} -->

{if $deleted}
<div class="alert-box alert sticky">
	{$lang.deleted}
	<a href="#" class="close">&times;</a>
</div>
{/if}

<div class="row">
	<div class="small-12 columns small-centered">

		{assign var=max_width value=220}
		{assign var=max_height value=400}

		<ul class="small-block-grid-2 large-block-grid-{$listcolumns} itemlist">
			{foreach $list as $video name=col}
			<li>
				<a href="show.php?id={$video.id}" class="th radius">
{* 
					<!-- Uncomment this if you want title/subtitle to be shown above the cover image: -->
					<div>{$video.title}{if $video.subtitle} - {$video.subtitle}{/if}</div>
*}
					{if $video.imgurl}{html_image file=$video.imgurl max_width=$max_width max_height=$max_height}{/if}
				</a>
			</li><!--col-->
			{/foreach}
		</ul><!--row-->

	</div><!--column-->
</div><!--row-->
