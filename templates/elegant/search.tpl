{*
  Template for the search interface
  $Id: search.tpl,v 1.15 2009/03/26 11:17:17 andig2 Exp $
*}

<!-- {$smarty.template} -->

<script language="JavaScript" type="text/javascript" src="./javascript/search.js"></script>
<script language="JavaScript" type="text/javascript" src="{$template}js/search.js"></script>

<div id="actions">
    {include file="searchengines.tpl"}

    <form action="search.php" id="search" name="search" method="get">

    {if $imgurl}
    <div id="searchimage">
        <span class="img-shadow">
        <a href='http://uk.imdb.com/Name?{$q|replace:"&quot;|\"":""|escape:url}'>{html_image file=$imgurl max_width=97 max_height=144 id="actorimg"}</a>
        </span>
    </div>
    {/if}

<table>
<tr>
    <td>
        <h3 style="display: block">{$lang.keywords}:</h3>

        <input type="text" name="q" id="q" value='{$q_q}' size="45" autocomplete="off" maxlength="300" />
		<div id="item_choices" class="autocomplete" style="display: none"></div>

        <input type="button" class="button" value="{$lang.l_search}" onClick="submitSearch()" id="docpart_search" />
		<span id="indicator1" style="display: none"><img src="{$template}images/spinner.gif" alt="{$lang.working}" /></span>

        <br/>
        {include file="searchradios.tpl"}

        {if $owners}
            <p>
            <h3>{$lang.owner}:</h3>
            {html_options name=owner id=owner options=$owners selected=$owner}<br/>
            </p>
        {/if}

        <p>{$lang.keywords_desc}</p>
    </td>
    <td>
        <h3 style="display: block">{$lang.fieldselect}:</h3>

        <select name="fields[]" id="fields" size="8" multiple="multiple">
        {html_options options=$search_fields selected=$selected_fields}
        </select><br />

        <span style="font-size:10px; font-weight: bold;"><a href="#" id="select_all">{$lang.selectall}</a></span>
    </td>
    <td>
        <h3>{$lang.genre_desc}:</h3>

        <div id="genres">{$genreselect}</div>
    </td>
</tr>
</table>

</form>
</div>

<!-- /actions -->
