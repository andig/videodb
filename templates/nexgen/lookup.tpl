{*
  Lookup template
  $Id: lookup.tpl,v 1.4 2013/03/16 14:29:47 andig2 Exp $
*}
{include file="xml.tpl"}

<body>

<script type="text/javascript" src="{$template}js/lookup.js"></script>

<!-- {$smarty.template} -->

<dl class="tabs">
    {foreach key=e item=eng from=$engines}
	<dd class="{if $engine == $e}active{/if}"><a href="{if $engine == $e}#{else}{$eng.url}{/if}">{$eng.name}</a></dd>
    {/foreach}
</dl>

<div class="row">
	<div class="small-12 columns">

		<div class="row header">
			<form action="lookup.php" name="formlookup" method="post">
				<input type="hidden" name="engine" value="{$engine}" />
				<input type="hidden" name="searchtype" value="{$searchtype}" />

				<div class="small-6 columns">
					<input type="text" name="find" id="find" value="{$q_find}" class="autofocus" />
				</div><!-- col -->
				<div class="small-6 columns left">
					<a href="#" class="button small submit" />{$lang.l_search}</a>
				</div><!-- col -->
			</form>
		</div><!-- row -->

		{if $http_error}
		<div class="alert-box alert">
			{$http_error}
			<a href="#" class="close">&times;</a>
		</div>
		{/if}

		{if !$imdbresults}
		<div class="alert-box">
			{$lang.l_nothing}
		</div>
		{/if}

		{if $imdbresults}
        <h4 class="subheader">{$lang.l_select}</h4>

        {if $searchtype == 'image'}
		<ul class="small-block-grid-4">
			{foreach item=match from=$imdbresults name="col"}
			<li>
				<a href="javascript:void(returnImage('{$match.coverurl}'))" title="Select">
					<img src="{$match.imgsmall}" max-width="90" max-height="60" />
				</a>

				<div class="small">
					{$match.title}
				</div>
			</li>
			{/foreach}
		</ul>
		{else}
		<ul class="no-bullet">
			<li>
				<ol>
					{foreach item=match from=$imdbresults}
					<li>
						<a href="javascript:void(returnData('{$match.id}','{$match.title|escape:"javascript"|escape}','{$match.subtitle|escape:"javascript"|escape}', '{$engine}'));" title="add ID and close Window">{$match.title}{if $match.subtitle} - {$match.subtitle}{/if}{if $match.year} ({$match.year}){/if}</a>

						{if $match.details || $match.imgsmall}
						<font size="-2">
							{if $match.imgsmall}<img src="{$match.imgsmall}" align="left" width="25" height="35" />{/if}
							{$match.details}
						</font>
						{/if}
					</li>
					{/foreach}
				</ol>
				{/if}
			</li>
		</ul>
		{/if}

		<div class="right">
			<a href="{$searchurl}" class="button small" target="_blank">{$lang.l_selfsearch}</a>
		</div>

	</div><!-- col -->
</div><!-- row -->

</body>
</html>
