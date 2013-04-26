{*
  Template for the permission management screen
  $Id: permissions.tpl,v 1.4 2005/05/30 11:52:50 andig2 Exp $
*}

<!-- {$smarty.template} -->

{if $message}
<div id="actions">
    <br/>
    <div class="center">
        {$message}
    </div>
</div>
<!-- /actions -->
{else}
    <div id="topspacer2"></div>
{/if}

<div id="content">

<div id="permissions">

<form action="permissions.php" method="post">

<table width="100%">
    <tr>
        <td colspan="3" class="left">
            <h3>{$lang.permforuser} {html_options name=from_uid options=$owners selected=$from_uid onchange="submit()"}:</h3>
        </td>
    </tr>

    <tr class="{cycle values="even,odd"}">
        <th>{$lang.username}</th>
        <th>{$lang.read}</th>
        <th>{$lang.write}</th>
    </tr>

    {foreach item=perm from=$permlist}
    <input type="hidden" name="newflag_{$perm.to_uid}" id="newflag_{$perm.to_uid}" value="{$perm.newentry}" />
    <tr class="{cycle values="even,odd"}">
        <td class="center">
            {$perm.to_name}
        </td>
        <td class="center">
            <input type="checkbox" name="readflag_{$perm.to_uid}" id="readflag_{$perm.to_uid}" value="1" {if $perm.read}checked="checked"{/if}/>
        </td>
        <td class="center">
            <input type="checkbox" name="writeflag_{$perm.to_uid}" id="writeflag_{$perm.to_uid}" value="1" {if $perm.write}checked="checked"{/if}/>
        </td>
    </tr>
    {/foreach}
</table>

<div id="permissionbuttons">
    <input type="button" value="{$lang.back}" class="button" onclick="window.location.href='users.php';" accesskey="c" />
    <input type="submit" name="save" value="{$lang.save}" class="button" accesskey="s" />
</div>

</form>

</div>
<!-- /permissions -->

</div>
<!-- /content -->
