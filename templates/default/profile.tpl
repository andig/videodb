{*
  The configuration template
  $Id: profile.tpl,v 2.2 2004/06/09 16:13:05 agohr Exp $
*}

<form method="post" action="profile.php">
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
