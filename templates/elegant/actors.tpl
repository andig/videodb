<div id="showcast">
    <h3>{$lang.cast}:</h3>
    <table width="100%">
    {counter start=0 print=false name=castcount}
    {foreach item=actor from=$video.cast}
        {if $count == 0}
        <tr>
        {/if}
            <td width="{floor(100/$config.castcolumns)}%">
                {if $actor.imgurl}
                    {assign var="link" value=$actor.imdburl}
                    <a href="{if $config.imdbBrowser}{assign var="link" value=$link|escape:url}trace.php?videodburl={/if}{$link}">{html_image file=$actor.imgurl max_width=45 max_height=60 class=thumb}{*<img src="{$actor.imgurl}" width="38" height="52" align="left">*}</a>
                {/if}
                <a href="search.php?q=%22{$actor.name|escape:url}%22&amp;isname=Y">{$actor.name}</a>
                {foreach item=role from=$actor.roles}
                    <br/>{$role}
                {/foreach}
            </td>
        {counter assign=count name=castcount}
        {if $count == $config.castcolumns}
        </tr>{counter start=0 print=false name=castcount}
        {/if}
    {/foreach}
    {if $count != 0}
        {section name="columnLoop" start=$count loop=$config.castcolumns}
            <td>&nbsp;</td>
        {/section}
        </tr>
    {/if}
    </table>
</div>