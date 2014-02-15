{*
  Borrow template
  $Id: borrow.tpl,v 1.4 2013/03/12 19:13:18 andig2 Exp $
*}

<!-- {$smarty.template} -->

<div class="row">
	<div class="small-12 large-8 columns small-centered">

		<h3 class="subheader">{$lang.borrow}</h3>

		{if $diskid && $editable}
		<form action="borrow.php" name="formborrow" method="post">
			<input type="hidden" name="diskid" value="{$diskid}" />
			<input type="hidden" name="return" value="1" />

			{if $who}
			<h5 class="subheader">
				{$lang.diskid} {$diskid}
				{$lang.lentto} {$who} ({$dt})
			</h5>
			<input type="submit" class="button" value="{$lang.returned}" />

			{else}
			<h5 class="subheader">
				{$lang.diskid} {$diskid} {$lang.available}
			</h5>
			<div class="row collapse">
				<div class="small-10 columns">
					<input type="text" name="who" />
				</div>
				<div class="small-2 columns">
					<a href="#" class="button postfix submit">{$lang.borrow}</a>
				</div>
			</div>
			{/if}
		</form>
		{/if}


		{if $config.multiuser}
		<h5 class="subheader">{$lang.curlentfrom}</h5>
		<form action="borrow.php">
			{html_options name=owner options=$owners selected=$owner class="autosubmit"}
		</form>
		{else}
		<h5 class="subheader">{$lang.curlent}</h5>
		{/if}

		{if $borrowlist}
		<table class="small-12">
			<thead>
				<tr>
					<th><h5 class="subheader">{$lang.diskid}</h5></th>
					{if $config.multiuser}<th><h5 class="subheader">{$lang.owner}</h5></th>{/if}
					<th><h5 class="subheader">{$lang.title}</h5></th>
					<th><h5 class="subheader">{$lang.lentto}</h5></th>
					<th><h5 class="subheader">{$lang.date}</h5></th>
					<th></th>
				</tr>
			</thead>

			<tbody>
				{foreach item=disk from=$borrowlist}
				<tr>
					<td><a href="search.php?q={$disk.diskid}&amp;fields=diskid&amp;nowild=1">{$disk.diskid}</a></td>
					{if $config.multiuser}
					<td>{$disk.owner}</td>
					{/if}
					<td>
						<a href="show.php?id={$disk.id}">{$disk.title}</a>
						{if $disk.count > 1} ... {/if}
					</td>
					<td>{$disk.who}</td>
					<td>{$disk.dt}</td>
					<td>
						{if $disk.editable}
						<form action="borrow.php" method="get">
							<input type="hidden" name="diskid" value="{$disk.diskid}" />
							<input type="hidden" name="return" value="1" />
							<input type="submit" class="button small" value="{$lang.returned}" />
						</form>
						{/if}
					</td>
				</tr>
				{/foreach}
			</tbody>
		</table>

		{else}
		<div class="alert-box">
			{$lang.l_nothing}
		</div>
		{/if}

	</div><!-- col -->
</div><!-- row -->
