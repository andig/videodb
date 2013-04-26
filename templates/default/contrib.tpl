{*
  Index of the contrib content
  $Id: contrib.tpl,v 2.2 2005/05/26 07:35:45 andig2 Exp $
*}

<table width="100%" class="tablelist" cellspacing="0" cellpadding="0">
<tr><td colspab="3" height="20px">&nbsp;</td></tr>
{counter start=0 print=false name=contentcount}
{foreach item=file from=$files}
    {cycle values="even,odd" assign=CLASS print=false}
    <tr class="{$CLASS}">

    <td width="10%">
    <td width="80%" align="left">
        <table>
        <tr>
            <td>
                    <a href="{$file[0]}">{$file[1]}</a>
            </td>
        </tr>
        </table>
    </td>
    <td width="10%">
    {counter assign=count name=contentcount}
    </tr>
{/foreach}
<tr><td colspab="3" height="20px">&nbsp;</td></tr>
</table>
