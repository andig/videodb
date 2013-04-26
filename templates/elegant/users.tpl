{*
  Template for the user management screen
  $Id: users.tpl,v 1.10 2013/03/15 16:42:46 andig2 Exp $
*}

<!-- {$smarty.template} -->

{if $message}
	<div id="actions">
		<p class="center">{$message}</p>
	</div>
	<!-- /actions -->
{else}
	<div id="topspacer"></div>
{/if}


<div id="content">

<div id="users">

	<table width="100%">
		<tr>
			<td colspan="5" class="center">
				<h2>{$lang.existingusers}</h2>
			</td>
		</tr>

		<tr class="{cycle values="even,odd"}">
			<th>{$lang.username}</th>
			<th>{$lang.permissions}</th>
			<th>{$lang.email}</th>
			<th>{$lang.password}</th>
			<th colspan="3">{$lang.action}</th>
		</tr>

	{foreach item=user from=$userlist}
		<tr class="{cycle values="even,odd"}">
			<form action="users.php" method="post">
			<input type="hidden" name="id" value="{$user.id}" />
			<td class="center">
				<input type="text" name="name" value="{$user.name}" />
			</td>
			<td class="center">
				{html_checkbox style="vertical-align:middle;" name="readflag"  value="1" id="readflag"|cat:$user.name  checked=$user.read label=$lang.perm_readall}&nbsp;&nbsp;
		      	{html_checkbox style="vertical-align:middle;" name="writeflag" value="1" id="writeflag"|cat:$user.name checked=$user.write label=$lang.perm_writeall}&nbsp;&nbsp;
		      	{html_checkbox style="vertical-align:middle;" name="adultflag" value="1" id="adultflag"|cat:$user.name checked=$user.adult label=$lang.perm_adult}&nbsp;&nbsp;
		      	{html_checkbox style="vertical-align:middle;" name="adminflag" value="1" id="adminflag"|cat:$user.name checked=$user.admin label=$lang.perm_admin}
			</td>
			<td class="center">
				{if $user.guest}
                <input type="hidden" name="email" value="{$user.email|escape}" />
                {else}
                <input type="text" name="email" value="{$user.email|escape}" />
                {/if}
			</td>
			<td class="center">
				{if $user.guest}
                <input type="hidden" name="password" />
                {else}
                <input type="text" name="password" />
                {/if}
			</td>
			<td class="center">
				<input type="submit" class="button" value="{$lang.update}" />
			</td>
			</form>
			<td class="center">
				<form action="users.php" method="post">
				<input type="hidden" name="del" value="{$user.id}" />
				{if !$user.guest}
				<input type="submit" class="button" value="{$lang.delete}" onClick="return confirm('{$lang.really_del|escape:javascript|escape}?')" />
				{/if}
				</form>
			</td>
			<td class="center">
				<form action="permissions.php" method="post">
				<input type="hidden" name="from_uid" value="{$user.id}" />
				<input type="submit" value="{$lang.perm}" class="button"/>
				</form>
			</td>
		</tr>
	{/foreach}


		<tr>
			<td colspan="5" class="center">
				<h2>{$lang.createuser}</h2>
			</td>
		</tr>
		<form action="users.php" method="post">
		<input type="hidden" name="newuser" value="1" />
		<tr class="odd">
			<td class="center">
				<input type="text" name="name" />
			</td>
			<td class="center">
				{html_checkbox style="vertical-align:middle;" name="readflag"  value="1" id="readflagNEWUSER"  label=$lang.perm_readall}&nbsp;&nbsp;
				{html_checkbox style="vertical-align:middle;" name="writeflag" value="1" id="writeflagNEWUSER" label=$lang.perm_writeall}&nbsp;&nbsp;
				{html_checkbox style="vertical-align:middle;" name="adultflag" value="1" id="adultflagNEWUSER" label=$lang.perm_adult}&nbsp;&nbsp;
				{html_checkbox style="vertical-align:middle;" name="adminflag" value="1" id="adminflagNEWUSER" label=$lang.perm_admin}
			</td>
			<td class="center">
				<input type="text" name="email" />
			</td>
			<td class="center">
				<input type="text" name="password"/>
			</td>
			<td class="center" colspan="3">
				<input type="submit" class="button" value="{$lang.create}" accesskey="s" />
			</td>
		</tr>
		</form>
	</table>

</div>
<!-- /users -->

</div>
<!-- /content -->
