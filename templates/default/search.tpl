{*
  Template for the search interface
  $Id: search.tpl,v 2.14 2005/06/04 16:21:09 andig2 Exp $
*}

<script language="JavaScript" type="text/javascript" src="javascript/search.js"></script>

{include file="searchengines.tpl"}

<form action="search.php" id="search" name="search">
  <table width="100%" class="tableborder" cellpadding="4">
    <tr>
      <td>
        <table width="100%" border="0" cellpadding="5" cellspacing="0">
          <tr>
            <td width="20%">
                <span class="filterlink">{$lang.keywords}:</span>
                <br/>
                <input type="text" name="q" id="q" value="{$q_q}" size="45" maxlength="300"/>
                <br/>
                {include file="searchradios.tpl"}
                <input type="button" value="{$lang.l_search}" onClick="submitSearch()" class="button" />
            </td>
          </tr>
          <tr>
            <td>
              {$lang.keywords_desc}
            </td>
          </tr>
        </table>
      </td>
      <td nowrap="nowrap" align="center" style="text-align:center">
        {$lang.fieldselect}:<br />
        <select name="fields[]" size="6" multiple="multiple">
          {html_options options=$search_fields selected=$selected_fields}
        </select><br />
        <a href="javascript:selectAllFields()">{$lang.selectall}</a>
      </td>
      <td nowrap="nowrap">
        {if $owners}
          <span>{$lang.owner}:</span>
          {html_options name=owner options=$owners selected=$video.owner_id}<br/>
        {/if}
        {$lang.genre_desc}:
        {$genreselect}
      </td>
    {if $imgurl}
      <td>
{*
          <a href='http://uk.imdb.com/Name?{$q_q|replace:"&quot;":""|escape:url}'>
*}
          <a href='http://uk.imdb.com/Name?{$q|replace:"&quot;|\"":""|escape:url}'>
          <img align=left src="{$imgurl}" border="0" width="97" height="144">
          </a>
      </td>
    {/if}
    </tr>

  </table>
</form>

<script language="JavaScript" type="text/javascript">
    selectField(document.search.q);
</script>
