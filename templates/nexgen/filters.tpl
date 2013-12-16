{*
  Filters template
  $Id: filters.tpl,v 1.2 2013/03/12 19:13:18 andig2 Exp $
*}

<!-- {$smarty.template} -->

{*

<div class="row">
	<div class="small-12 columns">
		<dl class="sub-nav">
		<dt>{$lang.title}:</dt>
			{foreach $filters key=k item=v}
			<dd {if $filter==$k}class="active"{/if}><a href="index.php?filter={$k}">{$v}</a></dd>
			{/foreach}
		</dl>
	</div><!-- col -->
</div><!-- row -->

<div class="row">
	<div class="small-12 columns">
		<dl class="sub-nav">
		<dt>{$lang.mediatype}:</dt>
			{foreach from=$mediafilter key=k item=v}
			<dd {if $mediatype==$k}class="active"{/if}><a href="index.php?mediafilter={$k}">{$v|escape}</a></dd>
			{/foreach}
		</dl>
	</div><!-- col -->
</div><!-- row -->

<div class="row">
	{if $owners}
	<div class="small-12 columns">
		<dl class="sub-nav">
		<dt>{$lang.owner}:</dt>
			{foreach from=$owners key=k item=v}
			<dd {if $owner==$v}class="active"{/if}><a href="index.php?owner={$v|escape:url}">{$v|escape}</a></dd>
			{/foreach}
		</dl>
	</div><!-- col -->
	{/if}
</div><!-- row -->

<div class="row">
	{if $order_options}
	<div class="small-12 columns">
		<dl class="sub-nav">
		<dt>{$lang.order}:</dt>
			{foreach from=$order_options key=k item=v}
			<dd {if $order==$k}class="active"{/if}><a href="index.php?order={$k|escape:url}">{$v|escape}</a></dd>
			{/foreach}
		</dl>
	</div><!-- col -->
	{/if}
</div><!-- row -->

*}
