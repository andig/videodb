{*
  Template for the video detailview
  $Id: show.tpl,v 2.17 2008/12/07 12:43:28 andig2 Exp $
*}

<table width="100%" class="tableborder"><tr><td>
<!-- content begin -->
<table width="100%" class='odd'>
  <tr>
    <td align="center" rowspan="2" width="200" style="text-align:center">
      {if $link}
      {if $config.imdbBrowser}{assign var="link" value=$link|escape:url}{assign var="link" value="trace.php?videodburl=$link"}{/if}
      {/if}
      {html_image file=$video.imgurl link=$link title=$lang.visit max_width="97" max_height="144"}
    </td>

    <td colspan="2">
      <span class="show_title">{$video.title}</span>
       {if $video.subtitle != ''}
       <br/>
       <span class="show_subtitle">{$video.subtitle}</span>
       {/if}
    </td>

    <td align="center" style="text-align:center">
      {if $video.diskid != ''}
        <span class="show_id"><a href="search.php?q={$video.diskid}&fields=diskid&nowild=1">{$video.diskid}</a></span>
        {if $video.who != ''}
          <br />
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

        {if $video.director != ''}
          <tr><td><b>{$lang.director}:</b></td><td><a href="search.php?q=%22{$video.director|escape:url}%22&isname=Y">{$video.director}</a></td></tr>
        {/if}

        {if count($video.country)}
          <tr><td><b>{$lang.country}:</b></td>
          <td>
{*
          <a href="search.php?q=%22{$video.country|escape:url}%22&fields=country&nowild=1">{$video.country}</a>
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
                <input type="hidden" name="id" id="id" value="{$video.id}" />
                <input type="hidden" name="save" id="save" value="1" />
                {html_checkbox name="seen" value=1 checked=$video.seen onclick="submit()"}
                {if $video.seen}<label for="seen">{$lang.yes}</label>{/if}
              </form>
            </td>
          </tr>

          <tr>
            <td><b>{$lang.rating}:</b></td>
            <td>{html_rating value=$video.rating}</td>
          </tr>

      </table>
    </td>

    <td>
      <table>
        {if $video.runtime > 0}
          <tr><td><b>{$lang.length}:</b></td><td><b>{math equation="floor(x/60)" x=$video.runtime}</b> h <b>{math equation="x - floor(x/60) * 60" x=$video.runtime}</b> min ({$video.runtime} min)</td></tr>
        {/if}

        {if $video.language}
            <tr>
                <td><b>{$lang.language}:</b></td>
                <td>
                    {foreach item=language from=$video.language}
                        <a href="search.php?q=%22{$language|escape:url}%22&fields=language">{$language}</a>
                    {/foreach}
                </td>
            </tr>
        {/if}

        {if $video.mediatype != ''}
          <tr><td><b>{$lang.mediatype}:</b></td><td><a href="search.php?q=%22{$video.mediatype|escape:url}%22&fields=mediatype&nowild=1">{$video.mediatype}</a></td></tr>
        {/if}

        {if $video.owner != ''}
          <tr>
            <td><b>{$lang.owner}:</b></td>
            <td>
              <a href="search.php?q={$video.owner|escape:url}&fields=owner&nowild=1">{$video.owner}</a>
              {if $loggedin != '' and $video.email != '' and $video.owner != $loggedin and $video.who == '' and $video.diskid != ''}
              [ <a href="javascript:void(open('borrowask.php?id={$video.id|escape:url}&diskid={$video.diskid|escape:url}','borrowask','width=210,height=210,menubar=no,resizable=yes,scrollbars=yes,status=yes,toolbar=no'))">{$lang.borrowask}</a> ]
              {/if}
            </td>
          </tr>
        {/if}
      </table>
    </td>

    <td width="200">
      {if $genres}
        <b>{$lang.genres}:</b><br />
          {foreach item=genre from=$genres}
              <a href="search.php?q=&genres[]={$genre.id}">{if $lang.{$genre.name}} {$lang.{$genre.name}} {else} {$genre.name} {/if}</a><br />
          {/foreach}
      {/if}
    </td>
  </tr>
</table>


<table width="100%">
  {if $video.plot != ''}
  <tr>
    <td colspan="2" style="text-align:justify">
      <b>{$lang.plot}:</b><br />{$video.plot}<br />
      <br />
    </td>
  </tr>
  {/if}

  <tr>

    <td>
      <table>
        {if $video.filename != ''}
          <tr><td><b>{$lang.filename}:</b></td><td>{$video.filename}</td></tr>
        {/if}
        {if $video.filesize > 0}
          <tr><td><b>{$lang.filesize}:</b></td><td>{$video.filesize} mb</td></tr>
        {/if}
        {if $video.filedate != "0000-00-00 00:00:00"}
          <tr><td><b>{$lang.filedate}:</b></td><td>{$video.filedate}</td></tr>
        {/if}
        {if $video.audio_codec != ''}
          <tr><td><b>{$lang.audiocodec}:</b></td><td>{$video.audio_codec}</td></tr>
        {/if}
        {if $video.video_codec != ''}
          <tr><td><b>{$lang.videocodec}:</b></td><td>{$video.video_codec}</td></tr>
        {/if}
        {if $video.video_height > 0 || $video.video_width > 0}
          <tr><td><b>{$lang.dimension}:</b></td><td>{$video.video_width}x{$video.video_height}<br /><br /></td></tr>
        {/if}
      </table>
    </td>

    <td>
      <table>
        {if $video.comment != ''}
          <tr>
            <td><b>{$lang.comment}:</b></td>
            <td>{$video.comment}</td>
          </tr>
        {/if}

        {if $video.custom1name != '' && $video.custom1out != ''}
          <tr>
            <td><b>{$video.custom1name}:</b></td>
            <td>{$video.custom1out}</td>
          </tr>
        {/if}

        {if $video.custom2name != '' && $video.custom2out != ''}
          <tr>
            <td><b>{$video.custom2name}:</b></td>
            <td>{$video.custom2out}</td>
          </tr>
        {/if}

        {if $video.custom3name != '' && $video.custom3out != ''}
          <tr>
            <td><b>{$video.custom3name}:</b></td>
            <td>{$video.custom3out}</td>
          </tr>
        {/if}

        {if $video.custom4name != '' && $video.custom4out != ''}
          <tr>
            <td><b>{$video.custom4name}:</b></td>
            <td>{$video.custom4out}</td>
          </tr>
        {/if}

      </table>
    </td>

  </tr>

  <tr>
    <td width="100%" colspan="2">
      {if $video.actors != ''}
        <b>{$lang.cast}:</b>
        <table width="100%">
    {counter start=0 print=false name=castcount}
    {foreach item=actor from=$video.cast}

    {if $count == 0}
    <tr class="{cycle values="odd,even"}">
    {/if}

            <td width="{math equation="floor(100 / x)" x=$castcolumns}%">
            <dl>
              {if $actor.imgurl != ''}
                 <a href="{$actor.imdburl}"><img src="{$actor.imgurl}" width="38" height="52" border="0" align="left"></a>
              {/if}

              <dt>
                <a href="search.php?q=%22{$actor.name|escape:url}%22&isname=Y">{$actor.name}</a>
              </dt>
              <dd>
                {foreach item=role from=$actor.roles}
                  {$role}<br/>
                {/foreach}
              </dd>
            </dl>
            </td>

      {counter assign=count name=castcount}
      {if $count == $castcolumns}
      {counter start=0 print=false name=castcount}
      </tr>
      {/if}
         {/foreach}

        </table>
        <br />
      {/if}
    </td>


  </tr>
</table>
<!-- content end -->
</td></tr></table>
