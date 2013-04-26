{*
  Search engine popup
  $Id: lookup.tpl,v 1.14 2009/04/04 16:21:33 andig2 Exp $
*}
{include file="xml.tpl"}

<body>

<!-- {$smarty.template} -->

<script language="JavaScript" type="text/javascript" src="javascript/lookup.js"></script>

<div id="header">
    <ul id="headernav">
    {foreach key=e item=eng from=$engines}
        <li class="{if $engine == $e}tabActive{else}tabInactive{/if}"><a href="{$eng.url}">{$eng.name}</a></li>
    {/foreach}
    </ul>
</div>
<!-- /header -->

<div id="actions">
    <form action="lookup.php" id="formlookup" name="formlookup" method="post">
        <input type="text" name="find" id="find" value="{$q_find}" size="31" style="width:200px" />
        {include file="lookup_engines.tpl"}
        <input type="submit" class="button" value="{$lang.l_search}" />

        <script language="JavaScript" type="text/javascript">
            $('find').focus();
        </script>
    </form>
</div>
<!-- /actions -->

<div id="content">

<div id="lookup">

<table width="100%" class="collapse">
<tr>
    <td>
    {if $http_error}
        <pre>{$http_error}</pre>
    {/if}

    {if $imdbresults}
        <b>{$lang.l_select}</b>
        {if $searchtype == 'movie' || $searchtype == ''}
            <ul>
            {foreach item=match from=$imdbresults}
                <li>
                    <a href="javascript:void(returnData('{$match.id}','{$match.title|escape:"javascript"|escape}','{$match.subtitle|escape:"javascript"|escape}', '{$engine}'));" title="add ID and close Window">{$match.title}{if $match.subtitle} - {$match.subtitle}{/if}{if $match.year} ({$match.year}){/if}</a>
                    {if $match.details or $match.imgsmall}
                    <br/>
                    <font size="-2">
                        {if $match.imgsmall}<img src="{$match.imgsmall}" align="left" width="25" height="35" />{/if}
                        {$match.details}
                    </font>
                    {/if}
                    <br class="clear" />
                </li>
            {/foreach}
            </ul>
        {else}
            {include file="lookup_ajax.tpl"}
        {/if}
    {else}
        <div class="center">
            <br />
            <b>{$lang.l_nothing}</b>
            <br /><br />
        </div>
    {/if}
    </td>
</tr>
</table>

</div>
<!-- /lookup -->

<div id="footer" class="right">
    <a href="{$searchurl}" target="_blank">{$lang.l_selfsearch}</a>
</div>

</div>
<!-- /content -->

</body>
</html>
