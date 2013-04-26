{*
  User permissions template
  $Id: permissions.tpl,v 1.2 2013/03/12 19:13:18 andig2 Exp $
*}

<!-- {$smarty.template} -->

<div class="row">
	<div class="small-10 columns small-centered">

		{if $message}
		<div class="alert-box">
			{$message}
		</div>
		{/if}

		<h3 class="subheader">{$lang.existingusers}</h3>

		<form action="permissions.php" method="post">

			<h5 class="subheader">{$lang.selecteduser}</h5>
			{html_options name=from_uid options=$owners selected=$from_uid class="autosubmit"}

			<h5 class="subheader">{$lang.permtouser}</h5>
			<table style="width:100%">
			<thead>
				<tr>
					<th><h5 class="subheader">{$lang.username}</h5></th>
					<th><h5 class="subheader">{$lang.read}</h5></th>
					<th><h5 class="subheader">{$lang.write}</h5></th>
				</tr>
			</thead>

			<tbody>
				{foreach item=perm from=$permlist}
				<tr>
					<td class="text-centered">
						<input type="hidden" name="newflag_{$perm.to_uid}" id="newflag_{$perm.to_uid}" value="{$perm.newentry}" />
						{$perm.to_name}
					</td>
					<td class="text-centered">
						<input type="checkbox" name="readflag_{$perm.to_uid}" id="readflag_{$perm.to_uid}" value="1" {if $perm.read}checked="checked"{/if}/>
					</td>
					<td class="text-centered">
						<input type="checkbox" name="writeflag_{$perm.to_uid}" id="writeflag_{$perm.to_uid}" value="1" {if $perm.write}checked="checked"{/if}/>
					</td>
				</tr>
				{/foreach}
			</tbody>
			</table>

			<div class="row">
				<div class="small-2 columns small-centered">
<!--
					<input type="button" name="back" class="button" value="{$lang.back}" onclick="window.location.href='users.php';" />
-->
					<input type="submit" name="save" class="button" value="{$lang.save}" />
				</div><!-- col -->
			</div><!-- row -->

		</form>

	</div><!-- col -->
</div><!-- row -->
