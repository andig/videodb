{*
  Template for the edit interface
  $Id: edit.tpl,v 1.18 2010/02/24 21:20:18 andig2 Exp $
*}

<!-- {$smarty.template} -->

<script language="JavaScript" type="text/javascript" src="./javascript/edit.js"></script>
<script language="JavaScript" type="text/javascript" src="./javascript/prototype/rating.js"></script>
<script language="JavaScript" type="text/javascript" src="{$template}js/edit.js"></script>

<script language="JavaScript" type="text/javascript">
Event.observe(document, 'dom:loaded', function() {
    /**
     * Title search
     */
    bindTitle();

    /**
     * Rating
     */
    bindRating('rating_control', 'rating_value', {if $video.rating}{$video.rating}{else}0{/if});

    /**
     * Image lookup
     */
    {if $engines.google}bindImageLookup();{/if}
});
</script>

{if $video.id || $xmlimport}
    <div id="actions">
    {if $video.id}
        <form action="show.php" method="get" name="show">
            <input type="hidden" name="id" value="{$video.id}" />
            <input type="submit" class="button" value="{$lang.view}" />
        </form>
    {else}
        <form action="edit.php" method="get" name="import">
            <input type="hidden" name="import" value="xml" />
            <input type="submit" class="button" value="Import" />
        </form>
    {/if}
    </div>
    <!-- /actions -->
{else}
    <div id="topspacer2"></div>
{/if}

{if $http_error}<div class="center">{$http_error}</div>{/if}

<div id="content">

<div id="edit">

<form action="edit.php" id="edi" name="edi" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" id="id" value="{$video.id}" />
    <input type="hidden" name="engine" id="engine" value="{$engine}" />
    <input type="hidden" name="save" id="save" value="1" />

<table width="100%">
<tr>
    <td>

    <table>
        <tr>
          <td><h4>{$lang.title}:</h4></td>
          <td>
            <input type="text" name="title" id="title" value="{$video.q_title}" size="50" autocomplete="off" maxlength="255" />
            <div id="title_choices" class="autocomplete" style="display:none"></div>

            <a href="javascript:void(lookupData(document.edi.title.value))"><img src="{$template}images/search.gif" alt="" align="top" /></a>
			<span id="indicator1" style="display: none"><img src="{$template}images/spinner.gif" alt="{$lang.working}" /></span>
          </td>
        </tr>

        <tr>
          <td><h4>{$lang.subtitle}:</h4></td>
          <td>
            <input type="text" name="subtitle" id="subtitle" value="{$video.q_subtitle}" size="50" maxlength="255" />
            <a href="javascript:void(lookupData(document.edi.subtitle.value))"><img src="{$template}images/search.gif" alt="" align="top" /></a>
          </td>
        </tr>

        <tr>
          <td><h4>{$lang.language}:</h4></td>
          <td>{$video.f_language}</td>
        </tr>

        <tr>
          <td><h4>{$lang.diskid}:</h4></td>
          <td>
            <input type="text" name="diskid" id="diskid" value="{$video.q_diskid}" size="15" maxlength="255" />
            <input type="hidden" name="autoid" id="autoid" value="{$autoid}" />
            <input type="hidden" name="oldmediatype" id="oldmediatype" value="{$video.mediatype}" />
            {$lang.mediatype} {html_options name="mediatype" options=$mediatypes selected=$video.mediatype}
          </td>
        </tr>

        <tr>
          <td><h4><label for="istv">{$lang.tvepisode}</label>:</h4></td>
          <td><input type="checkbox" name="istv" id="istv" value="1" {if $video.istv}checked="checked"{/if}/></td>
        </tr>

        <tr>
          <td><h4><label for="seen">{$lang.seen}</label>:</h4></td>
          <td><input type="checkbox" name="seen" id="seen" value="1" {if $video.seen}checked="checked"{/if}/></td>
        </tr>

        <tr>
          <td><h4>{$lang.filename}:</h4></td>
          <td><input type="text" name="filename" id="filename" value="{$video.q_filename}" size="50" maxlength="255" /></td>
        </tr>

        <tr>
          <td><h4>{$lang.filesize}:</h4></td>
          <td><input type="text" name="filesize" id="filesize" value="{$video.q_filesize}" size="10" maxlength="15" /> bytes</td>
        </tr>

        <tr>
          <td><h4>{$lang.filedate}:</h4></td>
          <td><input type="text" name="filedate" id="filedate" value="{$video.q_filedate}" size="18" maxlength="20" /></td>
        </tr>

        <tr>
          <td><h4>{$lang.audiocodec}:</h4></td>
          <td><input type="text" name="audio_codec" id="audio_codec" value="{$video.q_audio_codec}" /></td>
        </tr>

        <tr>
          <td><h4>{$lang.videocodec}:</h4></td>
          <td><input type="text" name="video_codec" id="video_codec" value="{$video.q_video_codec}" /></td>
        </tr>

        <tr>
          <td><h4>{$lang.dimension}:</h4></td>
          <td>
            <input type="text" name="video_width" id="video_width" value="{$video.q_video_width}" size="5" maxlength="4" /> x
            <input type="text" name="video_height" id="video_height" value="{$video.q_video_height}" size="5" maxlength="4" />
          </td>
        </tr>


        <tr>
          <td><h4>{$lang.rating}:</h4></td>
          <td>
            <div id="rating_control" class="rating_container"></div>&nbsp;(<span id="rating_value">{if $video.rating}{$video.rating}{else}0{/if}</span>)
            <input type="hidden" name="rating" id="rating" value="{$video.rating}" />
          </td>
        </tr>

        <tr>
          <td colspan="2"><h4>{$lang.genre}:</h4>
            <br/>
            {$genreselect}
          </td>
        </tr>

        {if $owners}
        <tr>
            <td>{$lang.owner}</td>
            <td>
                {html_options name=owner_id options=$owners selected=$video.owner_id}
            </td>
        </tr>
        {/if}

        {if $video.custom1name}
        <tr>
            <td><h4>{$video.custom1name}:</h4></td>
            <td>{$video.custom1in}</td>
        </tr>
        {/if}

        {if $video.custom3name}
        <tr>
            <td><h4>{$video.custom3name}:</h4></td>
            <td>{$video.custom3in}</td>
        </tr>
        {/if}

    </table>
    </td>


    <td>
    <table>
        <tr>
          <td><h4>{$lang.extid}:</h4></td>
          <td>
            <input type="text" name="imdbID" id="imdbID" value="{$video.q_imdbID}" size="15" maxlength="30" onchange="changedId()" />
            {if $link}<h4><a href="{$link}" target="_blank">{$lang.visit}</a></h4>{/if}</td>
        </tr>

        <tr>
          <td><h4>{$lang.coverurl}:</h4></td>
          <td>
            <input type="text" name="imgurl" id="imgurl" value="{$video.q_imgurl}" size="50" maxlength="255" />
            <a href="javascript:void(lookupImage(document.edi.title.value))"><img src="{$template}images/search.gif" alt="" align="top" /></a>
          </td>
        </tr>

        <tr>
          <td><h4>{$lang.coverupload}:</h4></td>
          <td><input type="file" name="coverupload" id="coverupload" size="35" /></td>
        </tr>

        <tr>
          <td><h4>{$lang.country}:</h4></td>
          <td><input type="text" name="country" id="country" value="{$video.q_country}" size="50" maxlength="255" /></td>
        </tr>

        <tr>
          <td><h4>{$lang.director}:</h4></td>
          <td><input type="text" name="director" id="director" value="{$video.q_director}" size="50" maxlength="255" /></td>
        </tr>

        <tr>
          <td><h4>{$lang.runtime}:</h4></td>
          <td>
            <input type="text" name="runtime" id="runtime" value="{$video.q_runtime}" size="5" maxlength="5" />min
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$lang.year}
            <input type="text" name="year" id="year" value="{if $video.year}{$video.year}{/if}" size="5" maxlength="4" />
          </td>
        </tr>

        <tr>
          <td><h4>{$lang.plot}:</h4></td>
          <td><textarea cols="40" rows="8" name="plot" id="plot" wrap="virtual">{$video.q_plot}</textarea></td>
        </tr>

        <tr>
          <td><h4>{$lang.cast}:</h4></td>
          <td><textarea cols="40" rows="8" name="actors" id="actors" wrap="off">{$video.q_actors}</textarea></td>
        </tr>

        <tr>
          <td><h4>{$lang.comment}:</h4></td>
          <td><textarea cols="40" rows="3" name="comment" id="comment" wrap="virtual">{$video.q_comment}</textarea></td>
        </tr>

        {if $video.custom2name}
        <tr>
            <td><h4>{$video.custom2name}:</h4></td>
            <td>{$video.custom2in}</td>
        </tr>
        {/if}

        {if $video.custom4name}
        <tr>
            <td><h4>{$video.custom4name}:</h4></td>
            <td>{$video.custom4in}</td>
        </tr>
        {/if}

    </table>
    </td>
</tr>
</table>

<div id="editbuttons">
	{$lang.radio_look_caption}: {html_radios name=lookup options=$lookup checked="$lookup_id"}
	<label for="add_flag"><input type="checkbox" name="add_flag" id="add_flag" value="1" {if $add_flag}checked="checked"{/if}/>{$lang.add_another}</label>
	<input type="submit" class="button" value="{$lang.save}" id="savebutton" accesskey="s" />
</div>

</form>

</div>
<!-- /edit -->

<div id="images" class="hidden">
    <div id="images2">Alternative images:</div>
    <div id="imagecontent"></div>
    <div class="clear"></div>
</div>
<div class="clear"></div>
<!-- /images -->

</div>
<!-- /content -->
