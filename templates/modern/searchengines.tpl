{*
  Hidden search engine forms for multi-engine support
  $Id: searchengines.tpl,v 1.7 2011/06/19 11:05:31 andig2 Exp $
*}

{assign var="link" value="http://www.imdb.com/find"}
<form action="trace.php" id="searchIMDB" name="searchIMDB" method="get" {if !$config.imdbBrowser}target="_blank"{/if}>
    {if $config.imdbBrowser}<input type="hidden" name="videodburl" value="{$link}"/>{/if}
    <input type="hidden" name="q" id="forIMDB" value=""/>
    <input type="hidden" name="s" value="all" />
</form>

{if $engine.tvcom}
{assign var="link" value="http://www.tv.com/search.php"}
<form action="trace.php" id="searchTvcom" name="searchTvcom" method="get" {if !$config.imdbBrowser}target="_blank"{/if}>
    {if $config.imdbBrowser}<input type="hidden" name="videodburl" value="{$link}"/>{/if}
    <input type="hidden" name="qs" id="forTvcom" value=""/>
    <input type="hidden" name="stype" value="all"/>
</form>
{/if}

{if $engine.filmweb}
{assign var="link" value="http://www.filmweb.pl/Find"}
<form action="trace.php" id="searchFilmweb" name="searchFilmweb" method="get" {if !$config.imdbBrowser}target="_blank"{/if}>
       {if $config.imdbBrowser}<input type="hidden" name="videodburl" value="{$link}"/>{/if}
       <input type="hidden" name="query" id="forFilmweb" value=""/>
       <input type="hidden" name="category" value="all" />
</form>
{/if}

{if $engine.amazon || $engine.amazoncom || $engine.amazonxml}
{assign var="link" value="http://www.amazon.com/exec/obidos/search-handle-form/ref=dp_sr_00/102-0276103-8470541"}
<form action="trace.php" id="searchAmazon" name="searchAmazon" method="post" {if !$config.imdbBrowser}target="_blank"{/if}>
    {if $config.imdbBrowser}<input type="hidden" name="videodburl" value="{$link}"/>{/if}
    <input type="hidden" name="field-keywords" id="forAmazon" value=""/>
    <input type="hidden" name="url" value="index=blended" />
</form>
{/if}
