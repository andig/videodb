{*
  Template to display thumbnails for available templates
  $Id: options_thumbs.tpl,v 2.2 2006/11/11 16:18:13 andig2 Exp $
*}

<!-- {$smarty.template} -->

{foreach item=thumb from=$option.thumbs}
    <div class="setup-thumb">
        <a href="{$smarty.server.PHP_SELF}?template={$thumb.name}&quicksave=1"><img src="{$thumb.img}"/><br/>
        {$thumb.name}</a>
    </div>
{/foreach}
