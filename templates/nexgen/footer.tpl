{*
  Footer template
  $Id: footer.tpl,v 1.2 2013/03/12 19:13:18 andig2 Exp $
*}

<!-- {$smarty.template} -->

{if isset($pageno) && isset($maxpageno)}
<footer>
	<div class="pagination-centered">
		<ul class="pagination">
			<li class="arrow {if $pageno <= 1}unavailable{/if}"><a href="{if $pageno <= 1}#{else}index.php?pageno={$pageno-1}{/if}">&laquo;</a></li>

			{assign var=delta value=2}

			{if $maxpageno > 0}
			<li {if $pageno == 1}class="current"{/if}><a href="index.php?pageno=1">1</a></li>
			{/if}

			{if $pageno-$delta > 2}<li class="unavailable"><a href="">&hellip;</a></li>{/if}

			{for $page=($pageno-$delta)|max:2 to ($pageno+$delta)|min:($maxpageno-1)}
			<li {if $page==$pageno}class="current"{/if}><a href="index.php?pageno={$page}">{$page}</a></li>
			{/for}

			{if $pageno+$delta <= $maxpageno-2}<li class="unavailable"><a href="">&hellip;</a></li>{/if}

			{if $maxpageno > 1}
			<li {if $pageno == $maxpageno}class="current"{/if}><a href="index.php?pageno={$maxpageno}">{$maxpageno}</a></li>
			{/if}
			<li class="arrow {if $pageno >= $maxpageno}unavailable{/if}"><a href="{if $pageno >= $maxpageno}#{else}index.php?pageno={$pageno+1}{/if}">&raquo;</a></li>
		</ul>
	</div>
</footer>
{/if}

{if $DEBUG}
<div class="row">{$DEBUG}</div>
{/if}

</body>
</html>
