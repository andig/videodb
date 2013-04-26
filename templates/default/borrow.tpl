{*
  Template for the borrowing a single disk
  $Id: borrow.tpl,v 2.10 2008/03/09 14:57:22 andig2 Exp $
*}

<div align="center">

{if $diskid != '' && $editable}
  <table width="50%" class="infobox">
    <tr>
      <td align="center" style="text-align:center">
        <form action="borrow.php" name="borrow" id="borrow" method="post">
          <input type="hidden" name="diskid" id="diskid" value="{$diskid}" />
          {if $who != ''}
            <br />
            {$lang.diskid} {$diskid}
            {$lang.lentto} {$who} ({$dt})
            <br />
            <input type="hidden" name="return" id="return" value="1" />
            <input type="submit" value="{$lang.returned}" />
          {else}
            <br />
            {$lang.diskid} {$diskid} {$lang.available}
            <br />
            {$lang.borrowto}
            <input type="text" size="40" maxlength="255" name="who" id="who" />
            <input type="submit" value="{$lang.okay}" />
          {/if}
          <br />
          <br />
        </form>
      </td>
    </tr>
  </table>

<script language="JavaScript" type="text/javascript">
    if (document.forms['borrow'].who) document.forms['borrow'].who.focus();
</script>
{/if}

<br />
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
  <table width="100%" class="tableborder">
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
        <td align="center" style="text-align:center"><a href="search.php?q={$disk.diskid}&fields=diskid&nowild=1">{$disk.diskid}</a></td>
        {if $config.multiuser}
          <td align="center" style="text-align:center">{$disk.owner}</td>
        {/if}
        <td align="center" style="text-align:center">
          <a href="show.php?id={$disk.id}">{$disk.title}</a>
          {if $disk.count > 1} ... {/if}
        </td>
        <td align="center" style="text-align:center">{$disk.who}</td>
        <td align="center" style="text-align:center">{$disk.dt}</td>
        <td align="center" style="text-align:center">
          {if $disk.editable}
            <a href="borrow.php?diskid={$disk.diskid}&return=1">[ {$lang.returned} ]</a>
          {/if}
        </td>
      </tr>
    {/foreach}
  </table>
{else}
  {$lang.l_nothing}
{/if}
</div>
<br /><br />
