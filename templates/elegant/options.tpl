{*
  Template to display config options used by setup.tpl and profile.tpl
  $Id: options.tpl,v 1.6 2006/11/11 15:53:59 andig2 Exp $
*}

<!-- {$smarty.template} -->

{foreach from=$setup item=option}
<div class="{cycle values="optionseven,optionsodd"}">
    {if $option.group}
    <div class="center">
        <h2><a name="{$option.group}"></a>{$lang[$option.group]}</h2>
    </div>
    {else}
    <div>
        <div class="optionsleft">
            <b>{$option.hl}</b>
            <br/>

            {if $option.type == 'text'}
                <input type="text" size="20" name="{$option.name}" id="{$option.name}" value="{$option.set|escape}" class="center" />
            {/if}

            {if $option.type == 'boolean'}
                {html_checkbox name=$option.name id=$option.name value=1 checked=$option.set}
            {/if}

            {if $option.type == 'dropdown'}
                {html_options name=$option.name options=$option.data selected=$option.set}
            {/if}

            {if $option.type == 'multi'}
                <select name="{$option.name}[]" size="5" multiple="multiple">
                    <option value=""></option>
                    {html_options options=$option.data selected=$option.set}
                </select>
            {/if}

            {if $option.type == 'special'}
                {$option.data}
            {/if}

            {if $option.type == 'link'}
                <a href="{$option.data}">{$option.hl}</a>
            {/if}
        </div>
        <div class="optionsright">
            {$option.help}

            {if $option.thumbs}
                {include file="options_thumbs.tpl"}
            {/if}
        </div>

        <div class="clear"></div>
    </div>
    {/if}
</div>
{/foreach}
