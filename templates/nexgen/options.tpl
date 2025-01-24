{*
  Setup/profile options template
  $Id: options.tpl,v 1.5 2013/03/21 16:27:57 andig2 Exp $
*}

<!-- {$smarty.template} -->

<div class="row">
	<div class="small-12 large-10 columns large-centered">

{foreach from=$setup item=option}

	{if !empty($option.group)}
	<h3 class="subheader">{$lang[$option.group]}</h3>
	{else}

	<div class="row">
		<div class="small-12 large-4 columns">
			<label {if $option.type == 'boolean'}for="{$option.name}"{/if}>{$option.hl}</label>
			{if $option.type == 'text'}
				<input type="text" name="{$option.name}" value="{$option.set|escape}" />
			{/if}

			{if $option.type == 'boolean'}
				{html_checkbox name=$option.name id=$option.name value=1 checked=$option.set}
			{/if}

			{if $option.type == 'dropdown'}
				{html_options name=$option.name options=$option.data selected=$option.set}
			{/if}

			{if $option.type == 'multi'}
				<select name="{$option.name}[]"  multiple="multiple">
					<option value=""></option>
					{html_options options=$option.data selected=$option.set}
				</select>
			{/if}

			{if $option.type == 'special'}
				{if $option.name == 'languageflags'}<div class="small-cols-4">{/if}
				{$option.data}
				{if $option.name == 'languageflags'}</div>{/if}
			{/if}

			{if $option.type == 'link'}
				<a href="{$option.data}">{$option.hl}</a>
			{/if}
		</div><!-- col -->

		<div class="small-12 large-8 columns">
			<p>{$option.help}</p>

			{if !empty($option.thumbs)}
			<ul class="small-block-grid-4">
				{foreach item=thumb from=$option.thumbs}
				<li>
					<a href="{$smarty.server.PHP_SELF}?template={$thumb.name}&quicksave=1">
						<img src="{$thumb.img}" />
						<span>{$thumb.name}</span>
					</a>
				</li>
				{/foreach}
			</ul>
			{/if}
		</div><!-- col -->
	</div><!-- row -->
	{/if}

{/foreach}

	</div>
</div>
