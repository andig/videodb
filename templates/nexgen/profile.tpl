{*
  User profile template
  $Id: profile.tpl,v 1.2 2013/03/12 19:13:18 andig2 Exp $
*}

<!-- {$smarty.template} -->

<form method="post" action="profile.php">
	<input type="hidden" name="save" value="1" />

	<div class="row header">
		<div class="small-12 columns">
			<ul class="button-group right">
				<a class="button small submit" href="#">{$lang.save}</a>
			</ul><!-- nav-bar -->
		</div><!-- button-bar -->
	</div>

	{include file="options.tpl"}
</form>
