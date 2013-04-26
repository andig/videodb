{*
  Template for the user management screen
  $Id: users.tpl,v 1.13 2013/03/15 16:42:46 andig2 Exp $
*}

<div align="center">

<p>{$message}</p>

<h3>{$lang.existingusers}</h3>

<table width="100%" class="tableborder">
  <tr class="{cycle values="even,odd"}">
    <th>{$lang.username}</th>
    <th>{$lang.permissions}</th>
    <th>{$lang.email}</th>
    <th>{$lang.password}</th>
    <th colspan="3">{$lang.action}</th>
  </tr>

  {foreach item=user from=$userlist}
    <tr class="{cycle values="even,odd"}">
      <form action="users.php" method="post">
      <input type="hidden" name="id" value="{$user.id}" />
      <input type="hidden" name="name" value="{$user.name|escape}" />
    <td align="center" style="text-align:center">
      <b>{$user.name}</b>
    </td>
    <td align="left" style="text-align:left">
      	{html_checkbox style="vertical-align:middle;" name="readflag" value="1" id="readflag"|cat:$user.name checked=$user.read label=$lang.perm_readall}<br/>
      	{html_checkbox style="vertical-align:middle;" name="writeflag" value="1" id="writeflag"|cat:$user.name checked=$user.write label=$lang.perm_writeall}<br/>
      	{html_checkbox style="vertical-align:middle;" name="adultflag" value="1" id="adultflag"|cat:$user.name checked=$user.adult label=$lang.perm_adult}<br/>
      	{html_checkbox style="vertical-align:middle;" name="adminflag" value="1" id="adminflag"|cat:$user.name checked=$user.admin label=$lang.perm_admin}
      </td>
      <td align="center" style="text-align:center">
        {if $user.guest}
        <input type="hidden" name="email" value="{$user.email|escape}" />
        {else}
        <input type="text" name="email" value="{$user.email|escape}" />
        {/if}
      </td>
      <td align="center" style="text-align:center">
        {if $user.guest}
        <input type="hidden" name="password" />
        {else}
        <input type="text" name="password" />
        {/if}
      </td>
      <td align="center" style="text-align:center">
        <input type="submit" value="{$lang.update}" />
      </td>
      <td align="center" style="text-align:center">
        {if !$user.guest}
        <a href="users.php?del={$user.id|escape:url}" onClick="return confirm('{$lang.really_del|escape:javascript|escape}?')">{$lang.delete}</a>
        {else}
        &nbsp;
        {/if}
      </td>
      <td align="center" style="text-align:center">
        <a href="permissions.php?from_uid={$user.id|escape:url}">{$lang.perm}</a>
      </td>
      </form>
    </tr>
  {/foreach}
</table>

<br />
<h3>{$lang.createuser}</h3>

<form action="users.php" method="post">
<input type="hidden" name="newuser" value="1" />

  <table width="100%" class="tableborder">
    <tr class="{cycle values="even,odd"}">
		<th>{$lang.username}</th>
		<th>{$lang.permissions}</th>
		<th>{$lang.email}</th>
		<th>{$lang.password}</th>
		<th></th>
    </tr>

    <tr class="{cycle values="even,odd"}">
      <td align="center" style="text-align:center">
        <input type="text" name="name" />
      </td>
      <td align="left" style="text-align:left">
		{html_checkbox style="vertical-align:middle;" name="readflag"  value="1" id="readflagNEWUSER"  label=$lang.perm_readall}<br/>
		{html_checkbox style="vertical-align:middle;" name="writeflag" value="1" id="writeflagNEWUSER" label=$lang.perm_writeall}<br/>
		{html_checkbox style="vertical-align:middle;" name="adultflag" value="1" id="adultflagNEWUSER" label=$lang.perm_adult}<br/>
		{html_checkbox style="vertical-align:middle;" name="adminflag" value="1" id="adminflagNEWUSER" label=$lang.perm_admin}
      </td>
      <td align="center" style="text-align:center">
        <input type="text" name="email" />
      </td>
      <td align="center" style="text-align:center">
        <input type="text" name="password" />
      </td>
      <td align="center" style="text-align:center">
        <input type="submit" value="{$lang.create}" />
      </td>
      </form>
    </tr>
  </table>
</form>

</div>
