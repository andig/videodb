{*
  Template for the video detailview
  $Id: show.tpl,v 2.60 2013/03/14 17:17:27 andig2 Exp $
*}

<script language="JavaScript" type="text/javascript" src="javascript/show.js"></script>

<!-- {$smarty.template} -->
{if $video.editable || $video.copyable}
<table width="100%" class="tablefilter" cellspacing="5">
  <tr>
    <td width="100%">&nbsp;</td>
    {if $video.copyable}
    <td>
        <form action="edit.php" name="copyform">
            <input type="hidden" name="copyid" value="{$video.id}"/>
            <input type="hidden" name="copy" value="1" />
            <input type="hidden" name="save" value="1" />
            <input type="submit" value="{$lang.copy}" class="button"/>
        </form>
    </td>
    {/if}
    {if $video.editable}
    <td>
        <form action="edit.php" name="editform">
            <input type="hidden" name="id" value="{$video.id}"/>
            <input type="submit" value="{$lang.edit}" class="button"/>
        </form>
    </td><td>
        <form action="borrow.php" name="borrowform">
            <input type="hidden" name="diskid" value="{$video.diskid}"/>
            <input type="submit" value="{$lang.borrow}" class="button"/>
        </form>
    </td><td>
        <form action="delete.php" name="deleteform">
            <input type="hidden" name="id" value="{$video.id}"/>
            <input type="submit" value="{$lang.delete}" onclick="return(confirm('{$video.title|escape:javascript|escape}: {$lang.really_del|escape:javascript|escape}?'))" class="button"/>
        </form>
    </td>
    {/if}
  </tr>
</table>
{else}
    <div id="topspacer"></div>
{/if}
<!-- content begin -->
<table width="100%" class="show_info" cellspacing="5">
  <tr>
    <td class="center" rowspan="2" width="200">
    {if $link}
    {if $config.imdbBrowser}{assign var="link" value=$link|escape:url}{assign var="link" value="trace.php?videodburl=$link"}{/if}
    {/if}
{*  <a href="{$link}" title="{$lang.visit}">
    <img src="{$video.imgurl}" width="97" height="144" alt="" />{if $video.imdbID}</a>{/if} *}
    {html_image file=$video.imgurl link=$link title=$lang.visit max_width="97" max_height="144"}
    </td>
    <td colspan="2">
      <span class="show_title">{$video.title}</span>
       {if $video.subtitle}
       <br/>
       <span class="show_subtitle">{$video.subtitle}</span>
       {/if}
    </td>
    <td class="center">
      {if $video.diskid}
          <span class="show_id"><a href="search.php?q={$video.diskid}&fields=diskid&nowild=1">{$video.diskid}</a></span>
          {if $video.who}
            <br/>
            {$lang.notavail} {$video.who}
          {/if}
      {/if}
    </td>
  </tr>
  <tr>
    <td>
      <table>
        {if $video.year}
            <tr><td><b>{$lang.year}:</b></td><td><a href="search.php?q={$video.year}&fields=year&nowild=1">{$video.year}</a></td></tr>
        {/if}
        {if $video.director}
            <tr><td><b>{$lang.director}:</b></td><td><a href="search.php?q=%22{$video.director|escape:url}%22&isname=Y">{$video.director}</a></td></tr>
        {/if}
        {if count($video.country)}
            <tr><td><b>{$lang.country}:</b></td>
            <td>
{*
            <td><a href="search.php?q=%22{$video.country|escape:url}%22&fields=country&nowild=1">{$video.country}</a>
*}
            {foreach item=country from=$video.country}
                <a href="search.php?q=%22{$country|escape:url}%22&fields=country">{$country}</a>
            {/foreach}
            </td></tr>
        {/if}
          <tr>
            <td><b>{$lang.seen}:</b></td>
            <td>
              <form action="show.php" name="show" id="show">
                <input type="hidden" name="id" value="{$video.id}" />
                <input type="hidden" name="save" value="1" />
                {html_checkbox name="seen" value=1 checked=$video.seen onclick="submit()"}
                {if $video.seen}<label for="seen">{$lang.yes}</label>{/if}
              </form>
            </td>
          </tr>

          <tr>
            <td><b>{$lang.rating}:</b></td>
            <td>{html_rating value=$video.rating}</td>
          </tr>

	{if $engines.trailer}
          <tr>
            <td colspan="2">
    		  <a href="#" onclick='showTrailer("{$video.title|escape:javascript|escape:html}"); return false;'>{$lang.trailer_show}</a>
            </td>
          </tr>
	{/if}

      </table>
    </td>
    <td>
      <table>
        {if $video.runtime > 0}
            <tr><td><b>{$lang.length}:</b></td><td><b>{math equation="floor(x/60)" x=$video.runtime}</b> hr(s) <b>{math equation="x - floor(x/60) * 60" x=$video.runtime}</b> min ({$video.runtime} min)</td></tr>
        {/if}
        {if $video.language}
            <tr>
                <td><b>{$lang.language}:</b></td>
                <td>
{*
                    <a href="search.php?q=%22{$video.language|escape:url}%22&fields=language&nowild=1">{$video.language}</a>
*}
                    {foreach item=language from=$video.language}
                        <a href="search.php?q=%22{$language|escape:url}%22&fields=language">{$language}</a>
                    {/foreach}
                </td>
            </tr>
        {/if}
        {if $video.mediatype}
            <tr><td><b>{$lang.mediatype}:</b></td><td><a href="search.php?q=%22{$video.mediatype|escape:url}%22&fields=mediatype&nowild=1">{$video.mediatype}</a></td></tr>
        {/if}
        {if $video.owner}
            <tr>
                <td><b>{$lang.owner}:</b></td>
                <td><a href="search.php?q={$video.owner|escape:url}&fields=owner&nowild=1">{$video.owner}</a>
                      {if $loggedin and $video.email and $video.owner != $loggedin and $video.who == '' and $video.diskid}
                      [ <a href="javascript:void(open('borrowask.php?id={$video.id|escape:url}&diskid={$video.diskid|escape:url}','borrowask','width=210,height=210,menubar=no,resizable=yes,scrollbars=yes,status=yes,toolbar=no'))">{$lang.borrowask}</a> ]
                      {/if}
                </td>
            </tr>
        {/if}
        {if $video.custom1name && $video.custom1out}
            <tr>
                <td><b>{$video.custom1name}:</b></td>
                <td>{$video.custom1out}</td>
            </tr>
        {/if}
        {if $video.custom2name && $video.custom2out}
            <tr>
                <td><b>{$video.custom2name}:</b></td>
                <td>{$video.custom2out}</td>
            </tr>
        {/if}
        {if $video.custom3name && $video.custom3out}
            <tr>
                <td><b>{$video.custom3name}:</b></td>
                <td>{$video.custom3out}</td>
            </tr>
        {/if}
        {if $video.custom4name && $video.custom4out}
            <tr>
                <td><b>{$video.custom4name}:</b></td>
                <td>{$video.custom4out}</td>
            </tr>
        {/if}
      </table>
    </td>
    <td>
      {if $genres}
          <b>{$lang.genres}:</b><br/>
            {foreach item=genre from=$genres}
                  <a href="search.php?q=&genres[]={$genre.id}">{$lang.{$genre.name}}</a><br/>
            {/foreach}
      {/if}
    </td>
  </tr>
</table>
{if $video.plot}
<table width="100%" class="show_plot">
  <tr>
    <td colspan="2" style="text-align:justify">
      <b>{$lang.plot}:</b><br/>{$video.plot}<br/>
    </td>
  </tr>
</table>
{/if}
{if $video.episodes}{include file="episodes.tpl"}{/if}
<table width="100%" class="show_info">
{if $video.filename}
  <tr>
    <td>
      <table>
        {if $video.filename}
            <tr><td><b>{$lang.filename}:</b></td><td>{$video.filename}</td></tr>
        {/if}
        {if $video.filesize > 0}
            <tr><td><b>{$lang.filesize}:</b></td><td>{$video.filesize} mb</td></tr>
        {/if}
        {if $video.filedate != "0000-00-00 00:00:00"}
            <tr><td><b>{$lang.filedate}:</b></td><td>{$video.filedate}</td></tr>
        {/if}
        {if $video.audio_codec}
            <tr><td><b>{$lang.audiocodec}:</b></td><td>{$video.audio_codec}</td></tr>
        {/if}
        {if $video.video_codec}
            <tr><td><b>{$lang.videocodec}:</b></td><td>{$video.video_codec}</td></tr>
        {/if}
        {if $video.video_height > 0 || $video.video_width > 0}
            <tr><td><b>{$lang.dimension}:</b></td><td>{$video.video_width}x{$video.video_height}<br /><br /></td></tr>
        {/if}
      </table>
    </td>
  </tr>
{/if}
{if $video.comment}
  <tr>
    <td colspan="2" style="text-align:justify">
      <b>{$lang.comment}:</b><br/>{$video.comment}<br/>
    </td>
  </tr>
{/if}
</table>

{if $video.actors}
<table width="100%">
  <tr>
    <td>
        <b>{$lang.cast}:</b>
        <table width="100%">
        {counter start=0 print=false name=castcount}
        {foreach item=actor from=$video.cast}
        {if $count == 0}
        <tr>
        {/if}
            <td width="{math equation="floor(100/x)" x=$config.castcolumns}%">
              {if $actor.imgurl}
                {assign var="link" value=$actor.imdburl}
                <a href="{if $config.imdbBrowser}{assign var="link" value=$link|escape:url}trace.php?videodburl={/if}{$link}">{html_image file=$actor.imgurl max_width=45 max_height=60 class=thumb}{*<img src="{$actor.imgurl}" width="38" height="52" align="left">*}</a>
              {/if}
              <a href="search.php?q=%22{$actor.name|escape:url}%22&isname=Y">{$actor.name}</a><br/>
                {foreach item=role from=$actor.roles}
                  {$role}<br/>
                {/foreach}
            </td>
          {counter assign=count name=castcount}
          {if $count == $config.castcolumns}
            {counter start=0 print=false name=castcount}
          </tr>
          {/if}
        {/foreach}
        {if $count != 0}
            {section name="columnLoop" start=$count loop=$config.castcolumns}
                <td>&nbsp;</td>
            {/section}
            </tr>
        {/if}
        </table>
    </td>
  </tr>
</table>
{/if}
<!-- content end -->
