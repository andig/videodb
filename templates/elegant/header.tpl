{*
  This is the header which is displayed on top of every page
  $Id: header.tpl,v 1.6 2008/02/17 17:44:54 andig2 Exp $
*}
{include file="xml.tpl"}

<body>

<!-- {$smarty.template} -->

<a name="top"></a>

<div id="container">

<div id="logo">
    <div id="logotitle">
        <a href="http://www.videodb.net">videoDB</a>
    </div>
    <ul id="headernav2">
        {if $header.login}<li><div id="logologin"><a href="{$header.login}">{if $loggedin}{$lang.logout}{else}{$lang.login}{/if}</a>
        {if $loggedin}({$lang.loggedinas} {$loggedin}){/if}</div></li>{/if}

        {if $header.profile}<li><div id="logoprofile"><a href="{$header.profile}">{$lang.profile}</a></div></li>{/if}
        {if $header.help}<li><div id="logohelp"><a href="{$header.help}">{$lang.help}</a></div></li>{/if}
        <li><div id="logoversion"><a href="http://www.videodb.net">v{$version|strip|replace:"_":"."|replace:" ":""}</a></div></li>
    </ul>
</div>
<!-- /logo -->

<div id="header">
    <ul id="headernav">
        {if $header.browse}<li class="{if $header.active == 'browse'}tabActive{else}tabInactive{/if}"><a href="{$header.browse}{if $browseid}#{$browseid}{/if}" accesskey="i">{$lang.browse}</a></li>{/if}
        {if $header.trace}<li class="{if $header.active == 'trace'}tabActive{else}tabInactive{/if}"><a href="{$header.trace}">{$lang.imdbbrowser}</a></li>{/if}
        {if $header.random}<li class="{if $header.active == 'random'}tabActive{else}tabInactive{/if}"><a href="{$header.random}">{$lang.random}</a></li>{/if}
        {if $header.search}<li class="{if $header.active == 'search'}tabActive{else}tabInactive{/if}"><a href="{$header.search}">{$lang.search}</a></li>{/if}
        {if $header.new}<li class="{if $header.active == 'new'}tabActive{else}tabInactive{/if}"><a href="{$header.new}" accesskey="n">{$lang.n_e_w}</a></li>{/if}
        {if $header.active == 'show'}<li class="tabActive"><a href="{php}echo $_SERVER['REQUEST_URI'];{/php}">{$lang.view}</a></li> {/if}
        {if $header.active == 'edit'}<li class="tabActive"><a href="{$header.edit}">{$lang.edit}</a></li> {/if}
        {if $header.borrow}<li class="{if $header.active == 'borrow'}tabActive{else}tabInactive{/if}"><a href="{$header.borrow}">{$lang.borrow}</a></li>{/if}
        {if $header.stats}<li class="{if $header.active == 'stats'}tabActive{else}tabInactive{/if}"><a href="{$header.stats}">{$lang.statistics}</a></li>{/if}
        {if $header.contrib}<li class="{if $header.active == 'contrib'}tabActive{else}tabInactive{/if}"><a href="{$header.contrib}">{$lang.contrib}</a></li>{/if}
        {if $header.setup}<li class="{if $header.active == 'setup'}tabActive{else}tabInactive{/if}"><a href="{$header.setup}">{$lang.setup}</a></li>{/if}

        {if $header.profile}<li class="{if $header.active == 'profile'}tabActive{else}tabInactive{/if}"><div id="headerprofile"<a href="{$header.profile}">{$lang.profile}</a></div></li>{/if}
        {if $header.help}<li class="{if $header.active == 'help'}tabActive{else}tabInactive{/if}"><div id="headerhelp"><a href="{$header.help}">{$lang.help}</a></div></li>{/if}
        {if $header.login}<li class="{if $header.active == 'login'}tabActive{else}tabInactive{/if}"><div id="headerlogin"><a href="{$header.login}">{if $loggedin}{$lang.logout}{else}{$lang.login}{/if}</a></div></li>{/if}
    </ul>
    <!-- /headernav -->
</div>
<!-- /header -->
