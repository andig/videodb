{*
  The filters on top of the browse page
  $Id: filters.tpl,v 1.17 2009/03/26 11:17:17 andig2 Exp $
*}

<!-- {$smarty.template} -->

<script language="JavaScript" type="text/javascript" src="{$template}js/index.js"></script>

<div id="filters">

    <form action="index.php" id="browse" name="browse">

        {if $listcolumns AND $moreless}
        <div id="filtersmoreless">
        	<span id="indicator1" style="display:none"><img src="{$template}images/spinner.gif" alt="{$lang.working}" /></span>

            <a href="index.php?listcolumns={math equation="(columns>1)?columns-1:1" columns=$listcolumns}" listcolumns="{$listcolumns}" id="columns_less"><img src="{$template}images/minus2.png" class="button" /></a>
            <a href="index.php?listcolumns={math equation="columns+1" columns=$listcolumns}" id="columns_more"><img src="{$template}images/plus2.png"  class="button" /></a>
        </div>
        {/if}

        <div id="filtersalphabet">
            {html_radios name=filter options=$filters checked=$filter class="radio"}
            <input type="checkbox" name="showtv" id="showtv" value="1" {if $showtv}checked="checked"{/if} /><label for="showtv">{$lang.radio_showtv}</label>

            &nbsp;<label for="quicksearch">{$lang.quicksearch}:</label>
            <input type="text" class="autoenable" {*disabled="disabled" *}name="quicksearch" id="quicksearch" autocomplete="off" value="{$lang.search}" onblur="clearInput('quicksearch', '{$lang.search}')" onfocus="clearInput('quicksearch', '{$lang.search}')"/>
			<div id="item_choices" class="autocomplete" style="display:none"></div>

			{if $owners}{html_options name=owner id=owner options=$owners selected=$owner}{/if}

			{html_options name="mediafilter" id="mediafilter" options=$mediafilter selected=$mediatype}
			{if $order_options}
				<span style="font-weight:bold">{$lang.order}:</span>{html_options name="order" id="order" options=$order_options selected=$order}
			{/if}
            <input type="image" name="submit" src="{$template}images/search.gif" alt="{$lang.search}" align="absmiddle" />
        </div>
    </form>

</div>

<!-- /filters -->
