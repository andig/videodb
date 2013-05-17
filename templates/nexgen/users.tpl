{*
  Users template
  $Id: users.tpl,v 1.6 2013/03/21 16:27:57 andig2 Exp $
*}

<!-- {$smarty.template} -->

<div class="row">
	<div class="small-12 large-10 columns small-centered">

		{if $message}
		<div class="alert-box {if $alert}alert{/if}">
			{$message}
			<a href="#" class="close">&times;</a>
		</div>
		{/if}


		<h3 class="subheader">{$lang.createuser}</h3>

		<form action="users.php" method="post">
		<input type="hidden" name="newuser" value="1" />

		<div class="panel">
			<div class="row">
				<div class="small-12 large-6 columns">
					<label>{$lang.username}
					<input type="text" name="name" class="autofocus" /></label>
				</div><!-- col -->

				<div class="small-12 large-6 columns">
					<label>{$lang.password}
					<input type="text" name="password" /></label>
				</div><!-- col -->

				<div class="small-12 large-6 columns">
					<label>{$lang.email}
					<input type="text" name="email" /></label>
				</div><!-- col -->

				<div class="small-12 large-6 columns">
					<label>{$lang.permissions}</label>
					<dl class="sub-nav" input-checkbox>
						<dd><a href="readflag"  value="1">{$lang.perm_readall}</a></dd>
						<dd><a href="writeflag" value="1">{$lang.perm_writeall}</a></dd>
						<dd><a href="adultflag" value="1">{$lang.perm_adult}</a></dd>
						<dd><a href="adminflag" value="1">{$lang.perm_admin}</a></dd>
					</dl>
				</div><!-- col -->
			</div><!-- row -->

			<div class="row">
				<div class="small-2 columns small-centered">
					<a href="#" class="button submit">{$lang.create}</a>
				</div><!-- col -->
			</div><!-- row -->
		</div>
		</form>


		<h3 class="subheader">{$lang.existingusers}</h3>

		<table class="small-12">
		<thead>
			<tr>
				<th><h5 class="subheader">{$lang.username}</h5></th>
				<th><h5 class="subheader">{$lang.permissions}</h5></th>
				<th><h5 class="subheader">{$lang.email}</h5></th>
				<th><h5 class="subheader">{$lang.password}</h5></th>
				<th colspan="3"><h5 class="subheader">{$lang.action}</h5></th>
			</tr>
		</thead>

		<tbody>
			{foreach item=user from=$userlist}
			<tr>
				<form action="users.php" method="post">
				<input type="hidden" name="id" value="{$user.id}" />
				<td>
					<input type="text" name="name" value="{$user.name}" />
				</td>
				<td>
					{html_checkbox name="readflag"  value="1" id="readflag"|cat:$user.name  checked=$user.read  label=$lang.perm_readall}
					{html_checkbox name="writeflag" value="1" id="writeflag"|cat:$user.name checked=$user.write label=$lang.perm_writeall}
					{html_checkbox name="adultflag" value="1" id="adultflag"|cat:$user.name checked=$user.adult label=$lang.perm_adult}
					{html_checkbox name="adminflag" value="1" id="adminflag"|cat:$user.name checked=$user.admin label=$lang.perm_admin}
				</td>
				<td>
					{if !$user.guest}
					<input type="text" name="email" value="{$user.email|escape}" />
					{/if}
				</td>
				<td>
					{if !$user.guest}
					<input type="text" name="password" />
					{/if}
				</td>
				<td>
					<a href="#" class="button small submit">{$lang.update}</a>
				</td>
				</form>
				<td>
					{if !$user.guest}
					<a href="#" class="button small" data-reveal-id="delete-modal-{$user.id}"/>{$lang.delete}</a>
					{/if}
				</td>
				<td>
					<form action="permissions.php" method="post">
						<input type="hidden" name="from_uid" value="{$user.id}" />
						<a href="#" class="button small submit">{$lang.perm}</a>
					</form>
				</td>
			</tr>
			{/foreach}
		</tbody>
		</table>

{*
		{foreach item=user from=$userlist}
		<h5 class="subheader">{$user.name}</h5>

		<form action="users.php" method="post">
		<input type="hidden" name="id" value="{$user.id}" />

		<div class="row">
			<div class="small-4 columns">
				<label>{$lang.username}
				<input type="text" name="name" value="{$user.name}" /></label>
			</div>
			<div class="small-5 columns">
				{if !$user.guest}
				<label>{$lang.password}
				<input type="text" name="password" /></label>
				{/if}
			</div>
			<div class="small-3 columns">
				<label>{$lang.action}</label>
				<a href="#" class="button small submit">{$lang.update}</a>
				{if !$user.guest}
				<a href="#" class="button small" data-reveal-id="delete-modal-{$user.id}"/>{$lang.delete}</a>
				{/if}
			</div>
		</div>

		<div class="row">
			<div class="small-4 columns">
				{if !$user.guest}
				<label>{$lang.email}
				<input type="text" name="email" value="{$user.email|escape}" /></label>
				{/if}
			</div>
			<div class="small-5 columns">
				<label>{$lang.permissions}</label>
				<dl class="sub-nav" input-checkbox>
					<dd {if $user.read}class="active"{/if}><a href="readflag" value="1">{$lang.perm_readall}</a></dd>
					<dd {if $user.write}class="active"{/if}><a href="writeflag" value="1">{$lang.perm_writeall}</a></dd>
					<dd {if $user.adult}class="active"{/if}><a href="adultflag" value="1">{$lang.perm_adult}</a></dd>
					<dd {if $user.admin}class="active"{/if}><a href="adminflag" value="1">{$lang.perm_admin}</a></dd>
				</dl>
			</div>
			<div class="small-3 columns">
				<form action="permissions.php" method="post">
					<input type="hidden" name="from_uid" value="{$user.id}" />
					<a href="#" class="button small submit">{$lang.perm}</a>
				</form>
			</div>
		</div>
		</form>
		{/foreach}
*}
	</div><!-- col -->
</div><!-- row -->

<!-- modal dialogs -->
{foreach item=user from=$userlist}
<div id="delete-modal-{$user.id}" class="reveal-modal medium">
	<form action="users.php" method="post">
		<input type="hidden" name="del" value="{$user.id}" />

		<h2>{$user.name}</h2>
		<p class="lead">{$lang.delete_user}</p>
		<a href="#" class="button submit">{$lang.delete}</a>
		<a class="button close-modal">{$lang.cancel}</a>

		<a class="close-reveal-modal">&#215;</a>
	</form>
</div>
{/foreach}
