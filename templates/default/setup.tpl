{*
  The configuration template
  $Id: setup.tpl,v 2.11 2010/02/14 12:25:12 andig2 Exp $
*}



<table width="100%" >
  <tr>
    <td align="right">
      <form action="setup.php" style="display:inline">
        <input type="hidden" name="cacheempty" value="1" />
        <input type="submit" value="{$lang.cacheempty}" class="editcaption" onclick="return(confirm('{$lang.really_del|escape:javascript|escape}?'))" />
      </form>
	  <form action="users.php" style="display:inline"><input type="submit" value="{$lang.help_usermanagern}" class="button"/></form>
	</td>
  </tr>
</table>

<form method="post" action="setup.php">
<input type="hidden" name="save" value="1" />
<table width="100%" class="tableborder">
  {include file="options.tpl"}
</table>

<br />
<div align="center">
  <input type="submit" value="{$lang.save}" accesskey="s" />
</div>
<br />

</form>
