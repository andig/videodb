{*
  The filters on top of the browse page
  $Id: filters.tpl,v 2.15 2006/03/28 11:50:09 andig2 Exp $
*}

<div>
    <form action="index.php" id="browse" name="browse">
    <table width="100%" class="tablefilter" cellspacing="5">
    <tr>
        <td class="filter" nowrap="nowrap">
            {html_radios name=filter options=$filters checked=$filter label_class="filterlink" onclick="submit()"}
        </td>
        {if $order_options AND $filter<>'new'}
        <td class="filter" nowrap="nowrap" width="50%">
            <div align="right">
                <label class="filterlink" for="order">{$lang.order}: </label>{html_options name="order" id="order" options=$order_options selected=$order onchange="submit()"}
            </div>
        </td>
        {/if}
        <td class="filter" width="50%">
            <div align="right">
                <input type="checkbox" name="showtv" id="showtv" value="1" {if $showtv}checked="checked"{/if} onclick="submit()" /><label class="filterlink" for="showtv">{$lang.radio_showtv}</label>
            </div>
        </td>
        {if $owners}
        <td class="filter">
            <div align="right">
                {html_options name=owner options=$owners selected=$owner onchange="submit()"}
            </div>
        </td>
        {/if}
<!--
        <td align="right">
            <input type="submit" value="{$lang.okay}" class="button"/>
        </td>
-->
        {if $listcolumns AND $moreless}
        <td align="right" valign="middle">
            <div align="center"><span class="filterlink" style="font-size:10px; font-weight: bold;">
            <a href="index.php?listcolumns={math equation="columns+1" columns=$listcolumns}">{$lang.more}</a><br/>
            {if $listcolumns gt 1}<a href="index.php?listcolumns={math equation="columns-1" columns=$listcolumns}">{/if}{$lang.less}{if $listcolumns gt 1}</a>{/if}
            </span></div>
        </td>
        {/if}
    </tr>
    </table>
    </form>
</div>
