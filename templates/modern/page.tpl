{*
  This is the page navigation which is displayed on bottom of new filter
  $Id: page.tpl,v 1.1 2003/10/13 16:57:00 andig2 Exp $
*}
	<div class="page">
	{foreach from=$page item=curPage key=page name=pages}
		{if $smarty.foreach.pages.first and $lastPage}
		<a href="index.php?filter=new&OK=OK&page={$lastPage}">
			&lt;&lt;
		</a>
		{/if}
		
		{if $curPage == 1}
		<span class="curPage">[ {$page} ]</span>
		{else}
		<a href="index.php?filter=new&OK=OK&page={$page}">
			{$page}
		</a>
		{/if}
		
		{if $smarty.foreach.pages.last and $nextPage}
		<a href="index.php?filter={$filter}&OK=OK&page={$nextPage}">
			&gt;&gt;
		</a>
		{/if}
	{/foreach}
	</div>
