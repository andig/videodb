{*
  IMDB search popup
  $Id: lookup.tpl,v 2.15 2009/04/04 16:21:33 andig2 Exp $
*}
{include file="xml.tpl"}

<body>

<script language="JavaScript" type="text/javascript" src="javascript/lookup.js"></script>

<table width="100%" class="tablemenu" cellpadding="0" cellspacing="0">
<tr valign="bottom">
    <td width="100%" align="left" style="text-align:left">
        {foreach item=eng from=$engines}
        [&nbsp;<a href="{$eng.url}">{$eng.name}</a>&nbsp;]
        {/foreach}
    </td>
</tr>

<tr>
    <td>
    <form action="lookup.php" id="lookup" name="lookup">
        <table width="100%" class="tablefilter" cellspacing="5">
        <tr>
            <td nowrap="nowrap">
                <input type="text" name="find" id="find" value="{$q_find}" size="31" style="width:200px" />
                <input type="submit" class="button" value="{$lang.l_search}" />
                {include file="lookup_engines.tpl"}

                <script language="JavaScript" type="text/javascript">
                document.lookup.find.focus();
                </script>
            </td>
        </tr>
        </table>
    </form>
    </td>
</tr>
</table>


{if $http_error}
    <pre>{$http_error}</pre>
{/if}


{if $imdbresults}
    <b>{$lang.l_select}</b>

    {if $searchtype == 'image'}
        {foreach item=match from=$imdbresults}
            <div class="thumbnail">
                <a href="javascript:void(returnImage('{$match.coverurl|escape:"javascript"}'));" title="Select image and close Window">
                    <img src="{$match.imgsmall}" align="left" width="60" height="90" /><br />
                    {$match.title}
                </a>
            </div>
        {/foreach}
    {else}
        <ul>
        {foreach item=match from=$imdbresults}
            <li>
                <a href="javascript:void(returnData('{$match.id}','{$match.title|escape:"javascript"}','{$match.subtitle|escape:"javascript"}', '{$engine}'));" title="add ID and close Window">{$match.title}{if $match.subtitle} - {$match.subtitle}{/if}</a>
                {if $match.details or $match.imgsmall}
                <br/>
                <font size="-2">
                    {if $match.imgsmall}<img src="{$match.imgsmall}" align="left" width="25" height="35">{/if}
                    {$match.details}
                </font>
                {/if}
            </li>
        {/foreach}
        </ul>
    {/if}

{else}
    <div align="center"><b>{$lang.l_nothing}</b></div>
    <br />
{/if}

<br /><br />

<div align="right">
[ <a href="http://uk.imdb.com/Find?{$find|escape:url}" target="_blank">{$lang.l_selfsearch}</a> ]
</div>

</body>
</html>
