{*
  Index of the contrib content
  $Id: contrib.tpl,v 1.4 2005/10/13 19:30:55 andig2 Exp $
*}

<div id="topspacer"></div>

<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td colspab="3" height="20px">&nbsp;</td>
    </tr>

{foreach item=file from=$files}
    {cycle values="even,odd" assign=CLASS print=false}
    <tr class="{$CLASS}">
        <td width="10%"></td>
        <td width="80%">
            <table>
            <tr>
                <td>
                    <a href="{$file[0]}">{$file[1]}</a>
                </td>
            </tr>
            </table>
        </td>
        <td width="10%"></td>
    </tr>
{/foreach}
    <tr>
        <td colspab="3" height="20px">&nbsp;</td>
    </tr>
</table>
