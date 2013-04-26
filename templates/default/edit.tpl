{*
  Template for the edit interface
  $Id: edit.tpl,v 2.19 2007/12/29 10:23:18 andig2 Exp $
*}

{if $http_error}
  <pre>{$http_error}</pre>
{/if}

<script language="JavaScript" type="text/javascript" src="javascript/edit.js"></script>

<form action="edit.php" id="edi" name="edi" method="post" enctype="multipart/form-data">
  <input type="hidden" name="id" id="id" value="{$video.id}" />
  <input type="hidden" name="engine" id="engine" value="{$engine}" />
  <input type="hidden" name="save" id="save" value="1" />
  <table class="tableborder">
    <tr>
      <td>

      <table>
        <tr>
          <td>
            {$lang.title}
          </td>
          <td>
            <input type="text" name="title" id="title" value="{$video.q_title}" size="43" maxlength="255" />
            <a href="javascript:void(lookupData(document.edi.title.value))"><img src="images/search.gif" alt="" border="0" align="middle" /></a>
          </td>
        </tr>

        <tr>
          <td>
            {$lang.subtitle}
          </td>
          <td>
            <input type="text" name="subtitle" id="subtitle" value="{$video.q_subtitle}" size="43" maxlength="255" />
            <a href="javascript:void(lookupData(document.edi.subtitle.value))"><img src="images/search.gif" alt="" border="0" align="middle" /></a>
          </td>
        </tr>

        <tr>
          <td>
            {$lang.language}
          </td>
          <td>
            {$video.f_language}
          </td>
        </tr>

        <tr>
          <td>
            {$lang.diskid}
          </td>
          <td>
            <input type="text" name="diskid" id="diskid" value="{$video.q_diskid}" size="15" maxlength="255" />
            <input type="hidden" name="autoid" id="autoid" value="{$autoid}" />
            <input type="hidden" name="oldmediatype" id="oldmediatype" value="{$video.mediatype}" />
            {$lang.mediatype} {html_options name="mediatype" options=$mediatypes selected=$video.mediatype}
          </td>
        </tr>

        <tr>
          <td>
            <label for="istv">{$lang.tvepisode}</label>
          </td>
          <td>
            {html_checkbox name="istv" value=1 checked=$video.istv}
          </td>
        </tr>

        <tr>
          <td>
            <label for="seen">{$lang.seen}</label>
          </td>
          <td>
            {html_checkbox name="seen" value=1 checked=$video.seen}
          </td>
        </tr>

        <tr>
          <td>
            {$lang.filename}
          </td>
          <td>
            <input type="text" name="filename" id="filename" value="{$video.q_filename}" size="45" maxlength="255" />
          </td>
        </tr>

        <tr>
          <td>
            {$lang.filesize}
          </td>
          <td>
            <input type="text" name="filesize" id="filesize" value="{$video.q_filesize}" size="10" maxlength="15" /> bytes
          </td>
        </tr>

        <tr>
          <td>
            {$lang.filedate}
          </td>
          <td>
            <input type="text" name="filedate" id="filedate" value="{$video.q_filedate}" size="18" maxlength="20" />
          </td>
        </tr>

        <tr>
          <td>
            {$lang.audiocodec}
          </td>
          <td>
            <input type="text" name="audio_codec" id="audio_codec" value="{$video.q_audio_codec}" />
          </td>
        </tr>

        <tr>
          <td>
            {$lang.videocodec}
          </td>
          <td>
            <input type="text" name="video_codec" id="video_codec" value="{$video.q_video_codec}" />
          </td>
        </tr>

        <tr>
          <td>
            {$lang.dimension}
          </td>
          <td>
            <input type="text" name="video_width" id="video_width" value="{$video.q_video_width}" size="5" maxlength="4" /> x
            <input type="text" name="video_height" id="video_height" value="{$video.q_video_height}" size="5" maxlength="4" />
          </td>
        </tr>

          <tr>
            <td>{$lang.rating}:</td>
            <td>{rating_input value=$video.rating}</td>
          </tr>

        <tr>
          <td colspan="2">
            {$lang.genre}
            <br />
            {$genreselect}
          </td>
        </tr>

        {if $video.custom1name}
        <tr>
          <td>{$video.custom1name}</td>
          <td>{$video.custom1in}</td>
        </tr>
        {/if}

        {if $video.custom2name}
        <tr>
          <td>{$video.custom2name}</td>
          <td>{$video.custom2in}</td>
        </tr>
        {/if}

        {if $video.custom3name}
        <tr>
          <td>{$video.custom3name}</td>
          <td>{$video.custom3in}</td>
        </tr>
        {/if}

        {if $video.custom4name}
        <tr>
          <td>{$video.custom4name}</td>
          <td>{$video.custom4in}</td>
        </tr>
        {/if}

      </table>
      </td>
      <td>
      <table>

        <tr>
          <td>
            IMDb-ID
          </td>
          <td>
            <input type="text" name="imdbID" id="imdbID" value="{$video.q_imdbID}" size="15" maxlength="30" onchange="{literal}if(document.edi.imdbID.value != '') {document.edi.lookup1.checked=true} else {document.edi.lookup0.checked=true}{/literal}" />
            {if $video.q_imdbID != ''}
            <a href="http://uk.imdb.com/Title?{$video.q_imdbID}" target="_blank">{$lang.visit}</a>
            {/if}
          </td>
        </tr>

        <tr>
          <td>
            {$lang.coverurl}
          </td>
          <td>
            <input type="text" name="imgurl" id="imgurl" value="{$video.q_imgurl}" size="45" maxlength="255" />
            <a href="javascript:void(lookupImage(document.edi.title.value))"><img src="images/search.gif" alt="" border="0" align="middle" /></a>
          </td>
        </tr>

        <tr>
          <td>
            {$lang.coverupload}
          </td>
          <td>
            <input type="file" name="coverupload" id="coverupload" size="30" />
          </td>
        </tr>

        <tr>
          <td>
            {$lang.country}
          </td>
          <td>
            <input type="text" name="country" id="country" value="{$video.q_country}" size="45" maxlength="255" />
          </td>
        </tr>

        <tr>
          <td>
            {$lang.director}
          </td>
          <td>
            <input type="text" name="director" id="director" value="{$video.q_director}" size="45" maxlength="255" />
          </td>
        </tr>

        <tr>
          <td>
            {$lang.runtime}
          </td>
          <td>
            <input type="text" name="runtime" id="runtime" value="{$video.q_runtime}" size="5" maxlength="5" />min
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$lang.year}
            <input type="text" name="year" id="year" value="{$video.q_year}" size="5" maxlength="4" />
          </td>
        </tr>

        <tr>
          <td>
            {$lang.plot}
          </td>
          <td>
            <textarea cols="36" rows="6" name="plot" id="plot" wrap="virtual" class="textbox">{$video.q_plot}</textarea>
          </td>
        </tr>

        <tr>
          <td>
            {$lang.cast}
          </td>
          <td>
            <textarea cols="36" rows="6" name="actors" id="actors" wrap="off" class="textbox">{$video.q_actors}</textarea>
          </td>
        </tr>

        <tr>
          <td>
            {$lang.comment}
          </td>
          <td>
            <textarea cols="36" rows="6" name="comment" id="comment" wrap="virtual" class="textbox">{$video.q_comment}</textarea>
          </td>
        </tr>

        {if $owners}
        <tr>
          <td>
            {$lang.owner}
          </td>
          <td>
            {html_options name=owner_id options=$owners selected=$video.owner_id}
          </td>
        </tr>
        {/if}

      </table>
      </td>
    </tr>
  </table>

  <b>{$lang.radio_look_caption}:</b> {html_radios name=lookup options=$lookup checked="$lookup_id"}
  {html_checkbox name="add_flag" value=1 checked=$add_flag label=$lang.add_another}
  <div align="center"><input type="submit" value="{$lang.save}" accesskey="s" /></div>
  <br />
</form>
