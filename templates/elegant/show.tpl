{*
  Template for the video detailview
  $Id: show.tpl,v 1.34 2013/03/14 17:16:36 andig2 Exp $
*}

<script language="JavaScript" type="text/javascript" src="./javascript/show.js"></script>
<script language="JavaScript" type="text/javascript" src="./javascript/prototype/rating.js"></script>
<script language="JavaScript" type="text/javascript" src="{$template}js/show.js"></script>

<script language="JavaScript" type="text/javascript">
Event.observe(document, 'dom:loaded', function() {
    // Seen
    bindSeen({$video.id});

    // Rating
    bindRating({$video.id}, {if $video.editable}'show.php'{else}false{/if}, 'rating_control', 'rating_value',
               {if $video.rating}{$video.rating}{else}0{/if}, {if $video.editable}true{else}false{/if});

{if $engines.trailer}
    // Youtube
    bindYoutube("{$video.title|escape:javascript|escape:html}");
{/if}

{if $engines.purchase}
    // Purchase
//    bindRenderer("{$video.title|escape:javascript|escape:html}", 'purchase',
//		new Array({foreach key=k item=v from=$engines.purchase name=dary}'{$k}'{if !$smarty.foreach.dary.last},{/if}{/foreach}));
    bindPurchase("{$video.title|escape:javascript|escape:html}",
		new Array({foreach item=eng from=$engines.purchase name=dary}'{$eng}'{if !$smarty.foreach.dary.last},{/if}{/foreach}));
{/if}

{if $engines.download}
    // Torrents
    bindTorrent("{$video.title|escape:javascript|escape:html}",
		new Array({foreach item=eng from=$engines.download name=tary}'{$eng}'{if !$smarty.foreach.tary.last},{/if}{/foreach}));
{/if}
});
</script>

{include file="fancyzoom.tpl"}
{*include file="fullsize.tpl"*}

<!-- {$smarty.template} -->
{if $video.editable || $video.copyable}
<div id="actions">
	<div id="filtersmoreless">
		<span id="indicator1" style="display:none"><img src="{$template}images/spinner.gif" alt="{$lang.working}" /></span>
	</div>

    <div id="actionsbuttons">
        {if $video.editable}
        <form action="edit.php" name="editform">
            <input type="hidden" name="id" value="{$video.id}"/>
            <input type="submit" class="button" value="{$lang.edit}" />
        </form>
        {/if}
        {if $video.copyable}
        <form action="edit.php" name="copyform">
            <input type="hidden" name="copyid" value="{$video.id}"/>{* IE whitespace fix
            *}<input type="hidden" name="copy" value="1" />{* IE whitespace fix
            *}<input type="hidden" name="save" value="1" />
            <input type="submit" class="button" value="{$lang.copy}" />
        </form>
        {/if}
        {if $video.editable}
        <form action="delete.php" name="deleteform">
            <input type="hidden" name="id" value="{$video.id}"/>
            <input type="submit" class="button" value="{$lang.delete}" onclick="return(confirm('{if $video.title}{$video.title|escape:javascript|escape}: {/if}{$lang.really_del|escape:javascript|escape}?'))" />
        </form>
        <form action="borrow.php" name="borrowform">
            <input type="hidden" name="diskid" value="{$video.diskid}"/>
            <input type="submit" class="button" value="{$lang.borrow}" />
        </form>
        {/if}
        {if $video.prev_id || $video.next_id}
        <form action="show.php" name="prevform">
            <input type="hidden" name="id" value="{$video.prev_id}"/>
            <input type="submit" class="button" value="<" {if !$video.prev_id}disabled="disabled"{/if} accesskey="," />
        </form>
        <form action="show.php" name="nextform">
            <input type="hidden" name="id" value="{$video.next_id}"/>
            <input type="submit" class="button" value=">" {if !$video.next_id}disabled="disabled"{/if} accesskey="." />
        </form>
        {/if}
    </div>
</div>
<div id="actionsspacer"></div>
<!-- /actions -->
{else}
    <div id="topspacer"></div>
{/if}
<div id="content">
<div id="showheader">
<table width="100%">
<tr>
    <td rowspan="2">
        {if $link}
        {if $config.imdbBrowser}{assign var="link" value=$link|escape:url}{assign var="link" value="trace.php?videodburl=$link"}{/if}
        {/if}
        {if $video.imgurl}
        <span class="img-shadow">{html_image file=$video.imgurl link=$link title=$lang.visit max_width="97" max_height="144" id="coverimg" class="canzoom" targetimg=$video.imgurl}</span>
        <a href="{$video.imgurl}" rel="zoom" srcimg="coverimg"><img src="{$template}images/search.gif"/></a>
        {/if}
    </td>
    <td colspan="2">
        <h1>{$video.title}</h1>
        {if $video.subtitle}
        <h2>{$video.subtitle}</h2>
        {/if}
    </td>
    <td class="center">
      {if $video.diskid}
          <h2><a href="search.php?q={$video.diskid}&amp;fields=diskid&amp;nowild=1">{$video.diskid}</a></h2>
          {if $video.who}
            <br/>
            {$lang.notavail} {$video.who}
          {/if}
      {/if}
    </td>
</tr>
<tr >
    <td>
      <table>
        {if $video.year}
            <tr><td><h3>{$lang.year}:</h3></td><td><a href="search.php?q={$video.year}&amp;fields=year&amp;nowild=1">{$video.year}</a></td></tr>
        {/if}
        {if $video.director}
            <tr><td><h3>{$lang.director}:</h3></td><td><a href="search.php?q=%22{$video.director|escape:url}%22&amp;isname=Y">{$video.director}</a></td></tr>
        {/if}
        {if count($video.country)}
            <tr><td><h3>{$lang.country}:</h3></td>
            <td>
            {foreach $video.country as $country}
                <a href="search.php?q=%22{$country|escape:url}%22&amp;fields=country">{$country}</a>
            {/foreach}
            </td></tr>
        {/if}
          <tr>
            <td><h3>{$lang.seen}:</h3></td>
            <td>
              <form action="show.php" name="show" id="show">
                <input type="hidden" name="id" value="{$video.id}" />
                <input type="hidden" name="save" value="1" />
                {html_checkbox name="seen" value=1 checked=$video.seen}
                <label for="seen" id="seen_label" {if !$video.seen}class="hidden"{/if}><img src="{$template}images/eye.gif"/></label>
              </form>
            </td>
          </tr>

          <tr>
            <td colspan="2">
				<div>
					{if $engines.trailer}<span id="youtube" class="hidden"><a href="#" onclick='showTrailer("{$video.title|escape:javascript|escape:html}"); return false;'><img src="{$template}images/youtube32.png" alt="{$lang.trailer_show}" /></a></span>&nbsp;{/if}
					{if $engines.purchase}<span id="purchase" class="hidden"><a href="#" onclick="toggler('purchases');return false;"><img src="{$template}images/rss32.png" alt="" /></a></span>&nbsp;{/if}
					{if $engines.download}<span id="torrent" class="hidden"><a href="#" onclick="toggler('torrents');return false"><img src="{$template}images/torrent32.png" alt="" /></a></span>&nbsp;{/if}
				</div>
            </td>
          </tr>
      </table>
    </td>
    <td>
      <table>
        {if $video.runtime > 0}
            <tr><td><h3>{$lang.length}:</h3></td><td><b>{math equation="floor(x/60)" x=$video.runtime}</b> hr(s) <b>{math equation="x - floor(x/60) * 60" x=$video.runtime}</b> min ({$video.runtime} min)</td></tr>
        {/if}
        {if $video.language}
            <tr>
                <td><h3>{$lang.language}:</h3></td>
                <td>
                    {foreach $video.language as $language}
                        <a href="search.php?q=%22{$language|escape:url}%22&amp;fields=language">{$language}</a>
                    {/foreach}
                </td>
            </tr>
        {/if}
        {if $video.mediatype}
            <tr><td><h3>{$lang.mediatype}:</h3></td><td><a href="search.php?q=%22{$video.mediatype|escape:url}%22&amp;fields=mediatype&amp;nowild=1">{$video.mediatype}</a></td></tr>
        {/if}
        {if $video.owner}
            <tr>
                <td><h3>{$lang.owner}:</h3></td>
                <td><a href="search.php?q={$video.owner|escape:url}&amp;fields=owner&amp;nowild=1">{$video.owner}</a>
                      {if $loggedin and $video.email and $video.owner != $loggedin and $video.who == '' and $video.diskid}
                      [ <a href="javascript:void(open('borrowask.php?id={$video.id|escape:url}&diskid={$video.diskid|escape:url}','borrowask','width=210,height=210,menubar=no,resizable=yes,scrollbars=yes,status=yes,toolbar=no'))">{$lang.borrowask}</a> ]
                      {/if}
                </td>
            </tr>
        {/if}

            <tr>
                <td><h3>{$lang.rating}:</h3></td>
                <td>
                    <div id="rating_control" class="rating_container"></div>&nbsp;(<span id="rating_value">{if $video.rating}{$video.rating}{else}0{/if}</span>)
                </td>
            </tr>

        {if $video.custom1name && $video.custom1out}
            <tr>
                <td><h3>{$video.custom1name}:</h3></td>
                <td>{$video.custom1out}</td>
            </tr>
        {/if}
        {if $video.custom2name && $video.custom2out}
            <tr>
                <td><h3>{$video.custom2name}:</h3></td>
                <td>{$video.custom2out}</td>
            </tr>
        {/if}
        {if $video.custom3name && $video.custom3out}
            <tr>
                <td><h3>{$video.custom3name}:</h3></td>
                <td>{$video.custom3out}</td>
            </tr>
        {/if}
        {if $video.custom4name && $video.custom4out}
            <tr>
                <td><h3>{$video.custom4name}:</h3></td>
                <td>{$video.custom4out}</td>
            </tr>
        {/if}
      </table>
    </td>
    <td>
        {if $genres}
        <h3>{$lang.genres}:</h3>
        {foreach $genres as $genre}
            <a href="search.php?q=&amp;genres[]={$genre.id}">{if $lang.{$genre.name}} {$lang.{$genre.name}} {else} {$genre.name} {/if}</a><br/>
        {/foreach}
        {/if}
    </td>
  </tr>
</table>
</div>
<!-- /showheader -->


<div id="purchases" class="hidden">
    <h3>{$lang.purchase}:</h3>
    <div id="purchasecontent"></div>
    <div class="clear"></div>
</div>
<!-- /purchases -->

<div id="torrents" class="hidden">
    <h3>{$lang.torrents}:</h3>
    <div id="torrentcontent"></div>
    <div class="clear"></div>
</div>
<!-- /torrents -->

{if $video.plot}
<div id="showplot">
    <h3>{$lang.plot}:</h3>
    {$video.plot|regex_replace:"/^(.)/u":"<span class=\"dropcap\">\\1</span>"}
</div>
<!-- /showplot -->
{/if}
{if $video.episodes}{include file="episodes.tpl"}{/if}
{if $video.filename}
<div id="showfile">
    <table>
    {if $video.filename}
        <tr><td><h3>{$lang.filename}:</h3></td><td>{$video.filename}</td></tr>
    {/if}
    {if $video.filesize > 0}
        <tr><td><h3>{$lang.filesize}:</h3></td><td>{$video.filesize} mb</td></tr>
    {/if}
    {if $video.filedate != "0000-00-00 00:00:00"}
        <tr><td><h3>{$lang.filedate}:</h3></td><td>{$video.filedate}</td></tr>
    {/if}
    {if $video.audio_codec}
        <tr><td><h3>{$lang.audiocodec}:</h3></td><td>{$video.audio_codec}</td></tr>
    {/if}
    {if $video.video_codec}
        <tr><td><h3>{$lang.videocodec}:</h3></td><td>{$video.video_codec}</td></tr>
    {/if}
    {if $video.video_height > 0 || $video.video_width > 0}
        <tr><td><h3>{$lang.dimension}:</h3></td><td>{$video.video_width}x{$video.video_height}</td></tr>
    {/if}
    </table>
</div>
<!-- /showfile -->
{/if}
{if $video.comment}
<div id="showcomment">
    <h3>{$lang.comment}:</h3>
    {$video.comment}
</div>
{/if}
{if $video.actors}
<div id="showcast">
    <h3>{$lang.cast}:</h3>
    <table width="100%">
    {counter start=0 print=false name=castcount}
    {foreach $video.cast as $actor}
        {if $count == 0}
        <tr>
        {/if}
            <td width="{floor(100/$config.castcolumns)}%">
                {if $actor.imgurl}
                    {assign var="link" value=$actor.imdburl}
                    <a href="{if $config.imdbBrowser}{assign var="link" value=$link|escape:url}trace.php?videodburl={/if}{$link}">{html_image file=$actor.imgurl max_width=45 max_height=60 class="thumb canzoom" targetimg=$actor.imgurl}{*<img src="{$actor.imgurl}" width="38" height="52" align="left">*}</a>
                {/if}
                <a href="search.php?q=%22{$actor.name|escape:url}%22&amp;isname=Y">{$actor.name}</a>
                {foreach $actor.roles as $role}
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
<!-- /showcast -->
{/if}

</div>
<!-- /content -->
