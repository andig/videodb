{*
  This is the header which is displayed on top of every page
  $Id: header.tpl,v 2.13 2013/03/14 17:17:27 andig2 Exp $
*}
{include file="xml.tpl"}

<body>

<a name="top"></a>
<div align="center">

<table width="100%" class="menutable">
  <tr>
	<td width="120" valign="bottom">
	  <span class="logo">VideoDB</span>
	</td>
	<td width="100%" valign="bottom" align="center" style="text-align:center">

	  {if isset($header.browse)}[&nbsp;<a href="{$header.browse}" accesskey="i">{$lang.browse}</a>&nbsp;] {/if}
	  {if isset($header.trace)}[&nbsp;<a href="{$header.trace}">{$lang.imdbbrowser}</a>&nbsp;] {/if}
	  {if isset($header.random)}[&nbsp;<a href="{$header.random}">{$lang.random}</a>&nbsp;] {/if}
	  {if isset($header.search)}[&nbsp;<a href="{$header.search}" accesskey="f">{$lang.search}</a>&nbsp;] {/if}
	  {if isset($header.new)}[&nbsp;<a href="{$header.new}" accesskey="n">{$lang.n_e_w}</a>&nbsp;] {/if}
	  {if isset($header.edit)}[&nbsp;<a href="{$header.edit}" accesskey="e">{$lang.edit}</a>&nbsp;] {/if}
	  {if isset($header.view)}[&nbsp;<a href="{$header.view}">{$lang.view}</a>&nbsp;] {/if}
	  {if isset($header.del)}[&nbsp;<a href="{$header.del}" onclick="return(confirm('{$lang.really_del|escape:javascript|escape}?'))">{$lang.delete}</a>&nbsp;] {/if}
	  {if isset($header.contrib)}[&nbsp;<a href="{$header.contrib}" accesskey="c">{$lang.contrib}</a>&nbsp;] {/if}
	  {if isset($header.borrow)}[&nbsp;<a href="{$header.borrow}" accesskey="b">{$lang.borrow}</a>&nbsp;] {/if}
	  {if isset($header.stats)}[&nbsp;<a href="{$header.stats}">{$lang.statistics}</a>&nbsp;] {/if}
	  {if isset($header.setup)}[&nbsp;<a href="{$header.setup}">{$lang.setup}</a>&nbsp;] {/if}
	  {if isset($header.profile)}[&nbsp;<a href="{$header.profile}">{$lang.profile}</a>&nbsp;] {/if}
	  {if isset($header.help)}[&nbsp;<a href="{$header.help}" accesskey="h">{$lang.help}</a>&nbsp;] {/if}
	  {if isset($header.login)}[&nbsp;<a href="{$header.login}" accesskey="l">{if $loggedin}{$lang.logout}{else}{$lang.login}{/if}</a>&nbsp;]{/if}
	</td>
  </tr>
</table>
<br />
