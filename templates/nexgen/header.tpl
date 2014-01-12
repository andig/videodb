{*
  Header template
  $Id: header.tpl,v 1.9 2013/03/21 16:27:57 andig2 Exp $
*}
{include file="xml.tpl"}

<body>

<!-- {$smarty.template} -->

<!-- top-bar -->
<nav class="top-bar">
	<ul class="title-area">
		<li class="name">
			<h1><a href="http://videodb.net">video|db</a></h1>
		</li>
		<li class="toggle-topbar menu-icon">
			<a href="#"><span>{$lang.menu}</span></a>
		</li>
	</ul>

	<!-- top-bar contents -->
	<section class="top-bar-section">
		<ul class="left">
			{if $header.trace}<li{if $header.active == 'trace'} class="active"{/if}><a href="{$header.trace}?iframe=1">IMDB</a></li>{/if}

			{if $header.new}<li{if $header.active == 'new' || $header.active == 'edit'} class="active"{/if}><a href="{$header.new}">{$lang.create}</a></li>{/if}

			{if $header.browse}
			<li class="has-dropdown hide-for-small {if $header.active == 'browse'}active{/if}">
				<a href="{$header.browse}">{$lang.browse}</a>

				{if $header.active == 'browse'}
				<ul class="dropdown large hide-for-small">
					<li>
						<dl class="sub-nav">
						<dt>{$lang.title}:</dt>
							{foreach $filters key=k item=v}
							<dd {if $filter==$k}class="active"{/if}><a href="index.php?filter={$k}">{$v}</a></dd>
							{/foreach}
						</dl>
					</li>

					<li>
						<dl class="sub-nav">
						<dt>{$lang.mediatype}:</dt>
							{foreach from=$mediafilter key=k item=v}
							<dd {if $mediatype==$k}class="active"{/if}><a href="index.php?mediafilter={$k}">{$v|escape}</a></dd>
							{/foreach}
						</dl>
					</li>

					{if $owners}
					<li>
						<dl class="sub-nav">
						<dt>{$lang.owner}:</dt>
							{foreach from=$owners key=k item=v}
							<dd {if $owner==$v}class="active"{/if}><a href="index.php?owner={$v|escape:url}">{$v|escape}</a></dd>
							{/foreach}
						</dl>
					</li>
					{/if}
					
					{if $order_options}
					<li>
						<dl class="sub-nav">
						<dt>{$lang.order}:</dt>
							{foreach from=$order_options key=k item=v}
							<dd {if $order==$k}class="active"{/if}><a href="index.php?order={$k|escape:url}">{$v|escape}</a></dd>
							{/foreach}
						</dl>
					</li>
					{/if}
				</ul>
				{/if}
			</li>
			{/if}

			{if $header.browse}
			<li class="hide-for-medium-up {if $header.active == 'browse'}active{/if}">
				<a href="{$header.browse}">{$lang.browse}</a>
			</li>
			{/if}

			{if $header.active == 'browse'}
			<li class="has-dropdown hide-for-medium-up">
				<a href="#">{$lang.filter}</a>

				<ul class="dropdown">
					<li class="has-dropdown">
						<a href="#">{$lang.title}</a>
						<ul class="dropdown">
							{foreach $filters key=k item=v}
							<li {if $filter==$k}class="active"{/if}><a href="index.php?filter={$k}">{$v}</a></li>
							{/foreach}
						</ul>
					</li>
					<li class="has-dropdown">
						<a href="#">{$lang.mediatype}</a>
						<ul class="dropdown">
							{foreach from=$mediafilter key=k item=v}
							<li {if $mediatype==$k}class="active"{/if}><a href="index.php?mediafilter={$k}">{$v|escape}</a></li>
							{/foreach}
						</ul>
					</li>

					{if $owners}
					<li class="has-dropdown">
						<a href="#">{$lang.owner}</a>
						<ul class="dropdown">
							{foreach from=$owners key=k item=v}
							<li {if $owner==$v}class="active"{/if}><a href="index.php?owner={$v|escape:url}">{$v|escape}</a></li>
							{/foreach}
						</ul>
					</li>
					{/if}
				</ul>
			</li>
			{/if}

			{if $pdf || $xls || $xml}
			<li class="has-dropdown">
				<a href="#">Export</a>

				<ul class="dropdown">
					{if $pdf}<li><a href="{$pdf}export=pdf&ext=.pdf">Adobe PDF</a></li>{/if}
					{if $xls}<li><a href="{$xls}export=xls&ext=.xls">Microsoft Excel</a></li>{/if}
					{if $xml}<li><a href="{$xml}export=xml">XML</a></li>{/if}
				</ul>
			</li>
			{/if}

			<li class="has-dropdown {if $header.active == 'borrow' || $header.active == 'stats'}active{/if}">
				<a href="#">Manage</a>

				<ul class="dropdown">
					{if $header.borrow}<li{if $header.active == 'borrow'} class="active"{/if}><a href="{$header.borrow}">{$lang.borrow}</a></li>{/if}
					{if $header.stats}<li{if $header.active == 'stats'} class="active"{/if}><a href="{$header.stats}">{$lang.statistics}</a></li>{/if}
				</ul>
			</li>

			{if $header.contrib || $header.profile || $header.setup}
			<li class="has-dropdown {if $header.active == 'setup' || $header.active == 'profile' || $header.active == 'contrib'}active{/if}">
				<a href="{if $header.setup}{$header.setup}{else}{if $header.profile}{$header.profile}{else}#{/if}{/if}">Options</a>

				<ul class="dropdown">
					{if $header.setup}<li{if $header.active == 'setup'} class="active"{/if}><a href="{$header.setup}">{$lang.setup}</a></li>{/if}
					{if $header.profile}<li{if $header.active == 'profile'} class="active"{/if}><a href="{$header.profile}">{$lang.profile}</a></li>{/if}
					{if $header.users}<li{if $header.active == 'users'} class="active"{/if}><a href="{$header.users}">{$lang.users}</a></li>{/if}
					{if $header.contrib}<li{if $header.active == 'contrib'} class="active"{/if}><a href="{$header.contrib}">{$lang.contrib}</a></li>{/if}
				</ul>
			</li>
			{/if}
		</ul>

		<ul class="right">
			<!-- other elements for top bar -->
			<li class="has-form">
				<form action="search.php">
				<input type="hidden" name="default" />
					<div class="row collapse">
						<div class="small-8 columns">
							<input type="text" name="q" />
						</div>
						<div class="small-4 columns">
							<button type="submit" class="button">{$lang.search}</button>
						</div>
					</div>
				</form>
			</li>

			{if $header.login}
			<li>
				<a href="{$header.login}">{if $loggedin}{$lang.logout}{else}{$lang.login}{/if}</a>
			</li>
			{/if}
		</ul>
	</section>
</nav><!-- top-bar -->

{*
{if $header.active == 'browse' && $breadcrumbs}
<div class="row">
	<div class="small-12 columns">
		<ul class="breadcrumbs">
			{foreach $breadcrumbs.crumbs as $b}
			<li {if $b.id !== $breadcrumbs.current}class="current"{/if}><a href="show.php?id={$b.id}">{$b.title}</a></li>
			{/foreach}
		</ul>
	</div><!-- col -->
</div><!-- row -->
{/if}
*}

<!-- /header -->