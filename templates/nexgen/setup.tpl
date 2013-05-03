{*
  Setup template
  $Id: setup.tpl,v 1.4 2013/03/16 10:10:07 andig2 Exp $
*}

<!-- {$smarty.template} -->

<form action="setup.php" method="post">
	<input type="hidden" name="save" value="1" />

	<div class="row header">
		<div class="small-12 columns">
			<ul class="button-group right">
				<a class="button small" href="setup.php?cacheempty=1">{$lang.cacheempty}</a>
				<a class="button small submit" href="#">{$lang.save}</a>
			</ul><!-- nav-bar -->
		</div><!-- button-bar -->
	</div>


	{if $cacheclear}
	<div class="alert-box sticky">
		Cache cleared.
		<a href="#" class="close">&times;</a>
	</div>
	{/if}

	{include file="options.tpl"}
</form>
