{*
  Template for the user management screen
  $Id: users.tpl,v 1.20 2013/03/15 16:42:46 andig2 Exp $
*}

{if $message}
<table width="100%" cellpadding="0" cellspacing="0">
<tr><td>
    <table width="100%" class="tablefilter">
    <tr>
      <td class="center">
        <br/>{$message}<br/><br/>
      </td>
    </tr>
    </table>
</td></tr>
</table>
{else}

    <div id="topspacer"></div>

{/if}

<table width="90%" class="tableborder">
    <tr>
        <td class="center" colspan="5">
            <h3>{$lang.existingusers}</h3>
        </td>
    </tr>

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
      <td class="center">
        <input type="text" name="name" value="{$user.name}" />
      </td>
      <td class="center">
        {html_checkbox style="vertical-align:middle;" name="readflag"  value="1" id="readflag"|cat:$user.name  checked=$user.read label=$lang.perm_readall}&nbsp;&nbsp;
        {html_checkbox style="vertical-align:middle;" name="writeflag" value="1" id="writeflag"|cat:$user.name checked=$user.write label=$lang.perm_writeall}&nbsp;&nbsp;
        {html_checkbox style="vertical-align:middle;" name="adultflag" value="1" id="adultflag"|cat:$user.name checked=$user.adult label=$lang.perm_adult}&nbsp;&nbsp;
        {html_checkbox style="vertical-align:middle;" name="adminflag" value="1" id="adminflag"|cat:$user.name checked=$user.admin label=$lang.perm_admin}
      </td>
      <td class="center">
        {if $user.guest}
        <input type="hidden" name="email" value="{$user.email|escape}" />
        {else}
        <input type="text" name="email" value="{$user.email|escape}" />
        {/if}
      </td>
      <td class="center">
        {if $user.guest}
        <input type="hidden" name="password" />
        {else}
        <input type="text" name="password" />
        {/if}
      </td>
      <td class="center">
        <input type="submit" value="{$lang.update}" class="button" />
      </td>
      </form>
      <td class="center">
        <form action="users.php" method="post">
            <input type="hidden" name="del" value="{$user.id}" />
            {if !$user.guest}
            <input type="submit" value="{$lang.delete}" onClick="return confirm('{$lang.really_del|escape:javascript|escape}?')" class="button"/>
            {/if}
        </form>
      </td>
      <td class="center">
        <form action="permissions.php" method="post">
            <input type="hidden" name="from_uid" value="{$user.id}" />
            <input type="submit" value="{$lang.perm}" class="button"/>
        </form>
      </td>
      </form>
    </tr>
  {/foreach}


    <tr>
        <td class="center" colspan="5">
            <br/>
            <h3>{$lang.createuser}</h3>
        </td>
    </tr>
    <tr class="odd">
        <form action="users.php" method="post">
        <input type="hidden" name="newuser" value="1" />
            <td class="center">
                <input type="text" name="name" />
            </td>
            <td class="center">
                {html_checkbox style="vertical-align:middle;" name="readflag"  value="1" id="readflagNEWUSER"  label=$lang.perm_readall}&nbsp;&nbsp;
                {html_checkbox style="vertical-align:middle;" name="writeflag" value="1" id="writeflagNEWUSER" label=$lang.perm_writeall}&nbsp;&nbsp;
                {html_checkbox style="vertical-align:middle;" name="adultflag" value="1" id="adultflagNEWUSER" label=$lang.perm_adult}&nbsp;&nbsp;
                {html_checkbox style="vertical-align:middle;" name="adminflag" value="1" id="adminflagNEWUSER" label=$lang.perm_admin}
            </td>
            <td class="center">
                <input type="text" name="email" />
            </td>
            <td class="center">
                <input type="text" name="password"/>
            </td>
            <td class="center" colspan="3">
                <input type="submit" value="{$lang.create}" class="button" accesskey="s"/>
            </td>
        </form>
    </tr>
</table>
