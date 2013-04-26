{*
  The configuration template
  $Id: setup.tpl,v 1.8 2010/02/14 12:15:00 andig2 Exp $
*}

<!-- {$smarty.template} -->

<div id="actions">
	<form action="users.php">
		<input type="submit" class="button" value="{$lang.help_usermanagern}" />
	</form>

	<div id="cache">
		<form action="setup.php">
			<input type="hidden" name="cacheempty" value="1"/>
			<input type="submit" class="button" value="{$lang.cacheempty}" onclick="return(confirm('{$lang.really_del|escape:javascript|escape}?'))" />
		</form>
	</div>
</div>
<!-- /actions -->


<div id="content">
	<form method="post" action="setup.php">
		<input type="hidden" name="save" value="1"/>

		<table>
			{include file="options.tpl"}
		</table>

		<div id="editbuttons">
			<input type="submit" class="button" value="{$lang.save}" id="savebutton" accesskey="s" />
		</div>
	</form>
</div>
<!-- /content -->
