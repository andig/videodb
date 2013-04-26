{*
  The configuration template
  $Id: setup.tpl,v 2.18 2010/02/14 12:25:12 andig2 Exp $
*}

<table width="100%" cellspacing="0" cellpadding="0">
<tr><td>

<table width="100%" class="tablefilter" cellspacing="5">
<tr>
    <td align="right">
        <form action="setup.php" style="display:inline">
            <input type="hidden" name="cacheempty" value="1"/>
            <input type="submit" value="{$lang.cacheempty}" onclick="return(confirm('{$lang.really_del|escape:javascript|escape}?'))" class="button"/>
        </form>
        <form action="users.php" style="display:inline">
            <input type="submit" value="{$lang.help_usermanagern}" class="button"/>
        </form>
    </td>
</tr>
</table>

<form method="post" action="setup.php">
<input type="hidden" name="save" value="1"/>
<table width="90%" class="tableborder">
  {include file="options.tpl"}
</table>

<br/>
<div align="center">
  <input type="submit" value="{$lang.save}" class="button" accesskey="s" />
</div>

</form>

</td></tr>
</table>
<br/>
