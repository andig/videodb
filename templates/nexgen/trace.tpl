{*
  Trace template
  $Id: trace.tpl,v 1.3 2013/03/12 19:13:18 andig2 Exp $
*}

<!-- {$smarty.template} -->

<script>$("html").css("height", "100%");$("body").css("height", "100%");</script>

<div class="row header">
	<div class="small-12 columns">
		<ul class="button-group right">
			<li><a href="{$url}" class="button small" target="_blank">Open in Browser</a></li>
			<li><a href="trace.php?iframe=1&videodburl={$url}&videodbreload=1" class="button small submit" />Reload</a></li>
		</ul>
	</div>
</div>

<div style="height:100%">
<iframe seamless="seamless" src="trace.php?iframe=2&videodburl={$url}"></iframe>
</div>