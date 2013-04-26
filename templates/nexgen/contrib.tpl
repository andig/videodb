{*
  Tools template
  $Id: contrib.tpl,v 1.3 2013/03/12 19:13:18 andig2 Exp $
*}
<!-- {$smarty.template} -->
<div class="row">
	<div class="small-12 large-10 columns small-centered">
		<h3 class="subheader">{$lang.contrib}</h3>
		<div class="small-cols-1 large-cols-2">
			<ul class="six side-nav">
				{foreach item=file from=$files}
				<li><a href="{$file[0]}">{$file[1]}</a></li>
				{/foreach}
			</ul>
		</div><!-- two-col -->
	</div><!-- col -->
</div><!-- row -->
