{*
  Template for the borrowing a single disk
  $Id: borrow.tpl,v 1.11 2008/03/09 14:57:23 andig2 Exp $
*}

<!-- {$smarty.template} -->

{if $diskid && $editable}
<div id="actions">

    <form action="borrow.php" id="formborrow" name="formborrow" method="post">
        <input type="hidden" name="diskid" id="diskid" value="{$diskid}" />
        {if $who}
            {$lang.diskid} {$diskid}
            {$lang.lentto} {$who} ({$dt})
            <br />
            <input type="hidden" name="return" value="1" />
            <input type="submit" class="button" value="{$lang.returned}" />
        {else}
            {$lang.diskid} {$diskid} {$lang.available}
            <br />
            {$lang.borrowto}:
            <input type="text" size="40" maxlength="255" id="who" name="who" />
            <input type="submit" class="button" value="{$lang.okay}" />
        {/if}
    </form>

    <script language="JavaScript" type="text/javascript">
    // <!--
        if (document.formborrow.who) document.formborrow.who.focus();
    // -->
    </script>
</div>
<!-- /actions -->
{else}
    <div id="topspacer"></div>
{/if}



<div id="content">

<div id="borrow">

{if $config.multiuser}
<table>
    <tr>
        <td><h3>{$lang.curlentfrom}</h3></td>
        <td><h3><form action="borrow.php">{html_options name=owner options=$owners selected=$owner onchange="submit()"}</form></h3></td>
        <td><h3>:</h3></td>
    </tr>
</table><br/>
{else}
<h3>{$lang.curlent}</h3>
{/if}

{if $borrowlist}
<table width="100%">
    <tr class="{cycle values="even,odd"}">
        <th>{$lang.diskid}</th>
        {if $config.multiuser}<th>{$lang.owner}</th>{/if}
        <th>{$lang.title}</th>
        <th>{$lang.lentto}</th>
        <th>{$lang.date}</th>
        <th></th>
    </tr>

    {foreach item=disk from=$borrowlist}
    <tr class="{cycle values="even,odd"}">
        <td class="center"><a href="search.php?q={$disk.diskid}&amp;fields=diskid&amp;nowild=1">{$disk.diskid}</a></td>
        {if $config.multiuser}
        <td class="center">{$disk.owner}</td>
        {/if}
        <td class="center">
            <a href="show.php?id={$disk.id}">{$disk.title}</a>
            {if $disk.count > 1} ... {/if}
        </td>
        <td class="center">{$disk.who}</td>
        <td class="center">{$disk.dt}</td>
        <td class="center">
        {if $disk.editable}
            <form action="borrow.php" method="get">
                <input type="hidden" name="diskid" value="{$disk.diskid}" />
                <input type="hidden" name="return" value="1" />
                <input type="submit" class="button" value="{$lang.returned}" />
            </form>
        {/if}
        </td>
    </tr>
    {/foreach}
</table>
{else}
    {$lang.l_nothing}
    <br/><br/>
{/if}

</div>
<!-- /borrow -->

</div>
<!-- /content -->
