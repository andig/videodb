{*
  The filters on top of the browse page
  $Id: filters.tpl,v 2.4 2005/05/24 17:49:34 chinamann Exp $
*}
<form action="index.php" id="browse" name="browse">
  <table width="100%" class="tableborder">
    <tr>
      <td class="filter">
        {html_radios name=filter options=$filters checked=$filter label_class="filterlink" onclick="submit()"}
      </td>
      {if $order_options AND $filter<>'new'}
      <td align="right" class="filter" nowrap="nowrap">
        <label class="filterlink" for="order">{$lang.order}: </label>{html_options name="order" id="order" options=$order_options selected=$order onchange="submit()"}
      </td>
      {/if}
      <td align="right" class="filter">
        <input type="checkbox" name="showtv" id="showtv" value="1" {if $showtv}checked="checked"{/if} onclick="submit()" /><label for="showtv">{$lang.radio_showtv}</label>
      </td>
      {if $owners}
      <td class="filter">
        <div align="right">
          {html_options name=owner options=$owners selected=$owner onchange="submit()"}
        </div>
      </td>
      {/if}
      <td align="right">
        <input type="submit" value="{$lang.okay}" name="OK" id="OK" />
      </td>
    </tr>
  </table>
</form>
