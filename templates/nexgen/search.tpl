{*
  Search template
  $Id: search.tpl,v 1.6 2013/03/16 14:29:47 andig2 Exp $
*}

<!-- {$smarty.template} -->

<script type="text/javascript" src="{$template}js/search.js"></script>

{assign var=max_width value=220}
{assign var=max_height value=400}

<form action="search.php" id="search" name="search" method="get">

	<div class="row">
		{if $imgurl}
		<div class="large-2 columns hide-for-small search-cover">
			{assign var=link value=$q|regex_replace:"/&quot;|\"|%22/":""|escape:url}
			{assign var=link value="http://www.imdb.com/find?q=$link&s=all"}
	        {if $config.imdbBrowser}
				{assign var="link" value="trace.php?iframe=1&videodburl=$link"}
	        {/if}
			<a href='{$link}' class="th radius">{html_image file=$imgurl max_width=$max_width max_height=$max_height}</a>
		</div>
		{/if}

		<div class="small-12 large-10 columns {if !$imgurl}large-centered{/if} search-parameters">

			<h4 class="subheader">{$lang.search}</h4>

			<div class="row">
				<div class="small-12 columns">
					<label>{$lang.keywords}</label>
				</div><!-- col -->
			</div><!-- row -->

			<div class="row collapse">
				<div class="small-10 columns">
					<input type="text" name="q" id="q" value='{$q_q}' />
					<div id="item_choices" class="autocomplete" style="display: none"></div>
				</div><!-- col -->

				<div class="small-2 columns">
					<a class="button postfix submit" href="#">{$lang.l_search}</a>
				</div><!-- col -->
			</div><!-- row -->

			<!-- fields -->
			<div class="row">
				<div class="small-2 large-1 columns">
					<dl class="sub-nav">
						<dt>Note:</dt>
					</dl>
				</div><!-- col -->
				<div class="small-10 large-11 columns">
					<dl class="sub-nav">
						<dd>{$lang.keywords_desc}</dd>
					</dl>
				</div><!-- col -->
			</div><!-- row -->
			
			<div class="row">
				<div class="small-2 large-1 columns">
					<dl class="sub-nav" input-checkbox>
						<dt>{$lang.fieldselect}:</dt>
					</dl>
				</div><!-- col -->
				<div class="small-10 {if $owners}large-7{else}large-11{/if} columns">
					<dl class="sub-nav" input-checkbox>
						{foreach from=$search_fields key=k item=v}
						<dd {if $k|in_array:$selected_fields}class="active"{/if}><a href="fields[]" value="{$k}">{$v|escape}</a></dd>
						{/foreach}
					</dl>
				</div><!-- col -->

				{if $owners}
				<div class="small-2 large-1 columns">
					<dl class="sub-nav" input-radio>
						<dt>{$lang.owner}:</dt>
						</dl>
					</div><!-- col -->
					<div class="small-10 large-3 columns">
						<dl class="sub-nav" input-radio>
						{foreach from=$owners key=k item=v}
						<dd {if $owner==$v}class="active"{/if}><a href="owner" value="{$k}">{$v|escape}</a></dd>
						{/foreach}
					</dl><!-- sub-nav -->
				</div><!-- col -->
				{/if}
			</div><!-- row -->

			<div class="row">
				<div class="small-2 large-1 columns">
					<dl class="sub-nav" input-checkbox>
						<dt>{$lang.genre}:</dt>
					</dl>
				</div><!-- col -->
				<div class="small-10 large-11 columns">
					<dl class="sub-nav" input-checkbox>
						{foreach $genres as $genre}
						<dd {if $genre.checked}class="active"{/if}><a href="genres[]" value="{$genre.id}">{if $lang.{$genre.name}} {$lang.{$genre.name}} {else} {$genre.name} {/if}</a></dd>
						{/foreach}
					</dl>
				</div><!-- col -->
			</div><!-- row -->

		</div><!-- col -->
	</div><!-- row -->

</form>
