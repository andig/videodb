{*
  Show template
  $Id: show.tpl,v 1.9 2013/03/21 16:27:57 andig2 Exp $
*}

<!-- {$smarty.template} -->

<script type="text/javascript">

function showTrailer(title) {
	open('trailer.php?title=' + escape(title), 'trailer',
		 'width=500,height=500,menubar=no,resizable=yes,scrollbars=yes,status=yes,toolbar=no');
}

{if $config.boxeeHost}
function boxeePlay(id) {
	$.getJSON("show.php?method=boxeePlay&id="+id);
}
{/if}

</script>

<div class="row header">
	<div class="small-12 columns">
		<div class="right">
			<ul class="button-group">
				<li><a class="button small {if !$video.copyable}disabled{/if}" href="#" data-reveal-id="delete-modal">{$lang.delete}</a></li>
			</ul><!-- button-group -->
			<ul class="button-group">
				<li><a class="button small {if !$video.copyable}disabled{/if}" href="edit.php?copyid={$video.id}&amp;save=1">{$lang.copy}</a></li>
				<li><a class="button small {if !$video.editable}disabled{/if}" href="borrow.php?id={$video.id}&amp;diskid={$video.diskid}">{$lang.borrow}</a></li>
			</ul><!-- button-group -->
			<ul class="button-group">
				<li><a class="button small {if !$video.copyable}disabled{/if}" href="edit.php?id={$video.id}">{$lang.edit}</a></li>
			</ul><!-- button-group -->
			{if $config.boxeeHost}
			<ul class="button-group">
				<li><a class="button small {if !$video.filename}disabled{/if}" href="#" onclick="javascipt:boxeePlay({$video.id})">{$lang.play}</a></li>
			</ul><!-- button-group -->
			{/if}
		</div>

		<ul class="button-group hide-for-small">
			<li><a class="button small secondary {if !$video.prev_id}disabled{/if}" href="show.php?id={$video.prev_id}">&laquo;</a></li>
			<li><a class="button small secondary {if !$video.next_id}disabled{/if}" href="show.php?id={$video.next_id}">&raquo;</i></a></li>
		</ul><!-- button-group -->
<!--
		{if $engines.trailer || $engines.purchase || $engines.download}
		<ul class="button-group hide-for-small">
			{if $engines.trailer}<li id="youtube"><a class="button small" href="#" onclick='showTrailer("{$video.title|escape:javascript|escape:html}"); return false;'><i class="foundicon-video"></i></a></li>{/if}
			{if $engines.purchase}<li id="purchase"><a href="#" class="button small" onclick="toggler('purchases');return false;">X</a></li>{/if}
			{if $engines.download}<li id="torrent"><a href="#" class="button small" onclick="toggler('torrents');return false">X</a></li>{/if}
		</ul>
		{/if}
-->
	</div><!-- nav-bar -->
</div>

<div class="row">
	<div class="small-12 large-3 columns show-cover">
		<h2>{$video.title}</h2>
        {if $link && $config.imdbBrowser}
        	{assign var="link" value=$link|escape:url}
			{assign var="link" value="trace.php?iframe=1&amp;videodburl=$link"}
        {/if}
        {if $video.imgurl}
			{html_image file=$video.imgurl link=$link title=$lang.visit}
        {/if}

		<h3 class="subheader">{$video.subtitle}</h3>

		{if $video.diskid}
		<h3><a href="search.php?q={$video.diskid}&amp;fields=diskid&amp;nowild=1">&lt;{$video.diskid}&gt;</a></h3>
		{/if}

		{if $video.who}
		<div class="alert-box">
			{$lang.notavail} {$video.who}
		</div>
		{/if}
	</div><!-- col -->

	<!-- main block -->
	<div class="small-12 large-9 columns show-details">
		<h4 class="subheader">{$lang.main_details}</h4>

		<div class="row">
			<div class="small-12 large-6 columns">
				<div class="row"><!-- clear helper -->
					<div class="small-6 columns">
						<p>{$lang.director}:
							{assign var=d value="/\s*,\s*/"|preg_split:$video.director}
							{foreach $d as $director name=loop}
							<a href="search.php?q=%22{$director|escape:url}%22&amp;isname=1">{$director}</a>{if $smarty.foreach.loop.index < $smarty.foreach.loop.total-1}, {/if}
							{/foreach}
						</p>
					</div><!-- col -->

					<div class="small-6 columns">
						<p>{$lang.year}:
							{if $video.year}<a href="search.php?q={$video.year}&amp;fields=year&amp;nowild=1">{$video.year}</a>{/if}
						</p>
					</div><!-- col -->
				</div><!-- col -->
			</div><!-- row -->

			<div class="small-12 large-6 columns">
				<div class="row"><!-- clear helper -->
					<div class="small-6 columns">
						<p>{$lang.country}:
							{foreach $video.country as $country name=loop}
							<a href="search.php?q=%22{$country|escape:url}%22&amp;fields=country">{$country}</a>{if $smarty.foreach.loop.index < $smarty.foreach.loop.total-1}, {/if}
							{/foreach}
						</p>
					</div><!-- col -->

					<div class="small-6 columns">
						<p>{$lang.runtime}: {if $video.runtime}{$video.runtime}min{/if}</p>
					</div><!-- col -->
				</div><!-- row -->
			</div><!-- col -->
		</div><!-- row -->

		<div class="row">
			<div class="small-12 large-6 columns">
				<div class="row"><!-- clear helper -->
					<div class="small-6 columns">
						<p>{$lang.genres}:
							{foreach $genres as $genre name=loop}
							<a href="search.php?q=&amp;genres[]={$genre.id}">{if $lang.{$genre.name}} {$lang.{$genre.name}} {else} {$genre.name} {/if}</a>{if $smarty.foreach.loop.index < $smarty.foreach.loop.total-1}, {/if}
							{/foreach}
						</p>
					</div><!-- col -->

					<div class="small-6 columns">
						<p>{$lang.language}:
							{foreach $video.language as $language name="loop"}
							<a href="search.php?q=%22{$language|escape:url}%22&amp;fields=language">{$language|capitalize}</a>{if $smarty.foreach.loop.index < $smarty.foreach.loop.total-1}, {/if}
							{/foreach}
						</p>
					</div><!-- col -->
				</div><!-- col -->
			</div><!-- row -->

			<div class="small-12 large-6 columns">
				<div class="row"><!-- clear helper -->
					<div class="small-6 columns">
						<p>{$lang.mediatype}:
							<a href="search.php?q=%22{$video.mediatype|escape:url}%22&amp;fields=mediatype&amp;nowild=1">{$video.mediatype}</a>
						</p>
					</div><!-- col -->

					<div class="small-6 columns">
						<p>{$lang.rating}: {if $video.rating}{$video.rating}{/if}</p>
					</div><!-- col -->
				</div><!-- row -->
			</div><!-- col -->
		</div><!-- row -->

		<div class="row">
			{if $video.owner}
			<div {if $video.istv || $video.seen || $video.3d}class="small-6 large-3 columns"{else}class="small-12 large-12 columns"{/if}>
				<p>{$lang.owner}: <a href="search.php?q={$video.owner|escape:url}&amp;fields=owner&amp;nowild=1">{$video.owner}</a>
					{if $loggedin && $video.email && $video.owner != $loggedin && $video.who == '' && $video.diskid}
					<a href="javascript:void(open('borrowask.php?id={$video.id|escape:url}&amp;diskid={$video.diskid|escape:url}','borrowask','width=300,height=300,menubar=no,resizable=yes,scrollbars=yes,status=yes,toolbar=no'))" class="button tiny">{$lang.borrowask}</a>
					{/if}
				</p>
			</div><!-- col -->
			{/if}

			{if $video.istv || $video.seen || $video.3d}
			<div {if $video.owner}class="small-6 large-9 columns"{else}class="small-12 large-12 columns"{/if}>
				{if $video.istv}
				<dl class="sub-nav inline" input-checkbox>
					<dd {if $video.istv}class="active"{/if}><a href="istv" value="1">{$lang.tvepisode}</a></dd>
				</dl>
				{/if}
				{if $video.seen}
				<dl class="sub-nav inline" input-checkbox>
					<dd {if $video.seen}class="active"{/if}><a href="seen" value="1">{$lang.seen}</a></dd>
				</dl>
				{/if}
				{if $video.3d}
				<dl class="sub-nav inline" input-checkbox>
					<dd {if $video.3d}class="active"{/if}><a href="3d" value="1">{$lang.3d}3D</a></dd>
				</dl>
				{/if}
			</div><!-- col -->
			{/if}
		</div><!-- row -->


		{if $video.plot}
		<h4 class="subheader">{$lang.synopsis}</h4>

		<div class="row">
			<div class="small-12 columns">
				<div {if $video.plot|strlen > 200}class="small-cols-1 large-cols-3"{/if}>
					<p>{$video.plot|escape}</p>
				</div><!-- col -->
			</div><!-- col -->
		</div><!-- row -->
		{/if}


		{if $video.filename}
		<h4 class="subheader">{$lang.file_details}</h4>

		<div class="row">
			<div class="small-6 large-4 columns">
				<p>{$lang.filename}: {$video.filename}</p>
			</div><!-- col -->

			<div class="small-6 large-4 columns">
				<p>{$lang.filesize}: {if $video.filesize}{$video.filesize}mb{else}-{/if}</p>
			</div><!-- col -->

			<div class="small-6 large-4 hide-for-small columns">
				<p>{$lang.filedate}: {if $video.filedate}{$video.filedate}{else}-{/if}</p>
			</div><!-- col -->
		</div><!-- row -->

		<div class="row hide-for-medium-up">
			<div class="small-6 columns">
				<p>{$lang.filedate}: {if $video.filedate}{$video.filedate}{else}-{/if}</p>
			</div><!-- col -->

			<div class="small-6 columns">
				<p>{$lang.dimension}: {$video.video_width}x{$video.video_height}</p>
			</div><!-- col -->
		</div><!-- row -->

		<div class="row">
			<div class="small-6 large-4 columns">
				<p>{$lang.audiocodec}:
					<a href="search.php?q=%22{$video.audio_codec|escape:url}%22&amp;fields=audio_codec&amp;nowild=1">{if $video.audio_codec}{$video.audio_codec|escape}{else}-{/if}</a>
				</p>
			</div><!-- col -->

			<div class="small-6 large-4 columns">
				<p>{$lang.videocodec}:
					<a href="search.php?q=%22{$video.video_codec|escape:url}%22&amp;fields=video_codec&amp;nowild=1">{if $video.video_codec}{$video.video_codec|escape}{else}-{/if}</a>
				</p>
			</div><!-- col -->

			<div class="small-6 large-4 hide-for-small columns">
				<p>{$lang.dimension}: {$video.video_width}x{$video.video_height}</p>
			</div><!-- col -->
		</div><!-- row -->
		{/if}


		{if $video.comment}
		<h4 class="subheader">{$lang.comment}</h4>

		<div class="row">
			<div class="small-12 columns">
				<p>{$video.comment|escape}</p>
			</div><!-- col -->
		</div><!-- row -->
		{/if}


		{if $video.custom1name || $video.custom2name || $video.custom3name || $video.custom4name}
		<h4 class="subheader">{$lang.custom_details}</h4>

		<div class="row">
			<div class="small-12 columns">
				<ul class="small-block-grid-4">
					{if $video.custom1name}
					<li>{$video.custom1name}: {$video.custom1out}</li>{/if}

					{if $video.custom2name}
					<li>{$video.custom2name}: {$video.custom2out}</li>{/if}

					{if $video.custom3name}
					<li>{$video.custom3name}: {$video.custom3out}</li>{/if}

					{if $video.custom4name}
					<li>{$video.custom4name}: {$video.custom4out}</li>{/if}
				</ul>
			</div><!-- col -->
		</div><!-- row -->
		{/if}


		{if $video.cast|@count}
		<h4 class="subheader">{$lang.cast}</h4>

		<div class="row">
			<div class="small-12 columns">
				<ul class="small-block-grid-1 large-block-grid-4">
					{foreach $video.cast as $actor name=col}
					<li>
						<div class="row collapse">
							<div class="small-4 columns">
								{if $actor.imgurl}
									{assign var="link" value=$actor.imdburl}
									{if $config.imdbBrowser}
										{assign var="link" value=$link|escape:url}
										{assign var="link" value="trace.php?iframe=1&amp;videodburl=$link"}
									{/if}
									<a href="{$link}">{html_image file=$actor.imgurl max_width=60 max_height=90}</a>
								{/if}
							</div>
							<div class="small-8 columns">
								<a href="search.php?q=%22{$actor.name|escape:url}%22&amp;isname=1">{$actor.name}</a>
								{foreach $actor.roles as $role name="loop"}<br/>{$role}{/foreach}
							</div>
						</div>
					</li><!-- col -->
					{/foreach}
				</ul><!-- row -->
			</div><!-- col -->
		</div><!-- row -->
		{/if}

	</div><!-- col -->
</div><!-- row -->

<!-- modal dialogs -->
<div id="delete-modal" class="reveal-modal large">
	<form action="delete.php" method="post">
		<input type="hidden" name="redirect" value="1" />
		<input type="hidden" name="id" value="{$video.id}" />

		<div class="row">
			<div class="small-3 columns">
				{if $video.imgurl}
				{html_image file=$video.imgurl link=$link title=$lang.visit}
				{/if}
			</div>
			<div class="small-9 columns">
				<h2>{$video.title}</h2> 
				<p class="lead">{$lang.delete_movie}</p> 
				<a href="#" class="button submit">{$lang.delete}</a>
				<a class="button close-modal">{$lang.cancel}</a> 
				<a class="close-reveal-modal">&#215;</a> 
			</div>
		</div>
	</form>
</div>
