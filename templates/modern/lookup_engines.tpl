{*
  Lookup engine options
  $Id: lookup_engines.tpl,v 1.1 2008/02/03 17:59:24 andig2 Exp $
*}

<input type="hidden" name="engine" id="engine" value="{$engine}" />
{if $searchtype}<input type="hidden" name="searchtype" id="searchtype" value="{$searchtype}" />{/if}

{if $engine=="amazona2s" || $engine=="amazonxml"}
    {html_options name=catalog values=$catalog output=$catalog selected=$selectedcatalog}
{/if}
{if $engine=="amazonxml"}
    {html_options name=area values=$area output=$area selected=$selectedarea}
{/if}
{if $engine=="imdb"}
    {html_checkbox name="searchaka" value="1" checked=$searchaka label=$lang.aka}
{/if}
