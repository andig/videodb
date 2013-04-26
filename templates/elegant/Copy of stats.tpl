{*
  Template for the statistics page
  $Id: Copy\040of\040stats.tpl,v 1.1 2013/03/14 17:16:36 andig2 Exp $
*}

<!-- {$smarty.template} -->

<div id="topspacer"></div>

<div id="content">

<div id="statistics">

<table width="100%">
<tr>
    <td class="center">
      <table width="100%">
        {if $owners}
        <tr>
          <td><h3>{$lang.owner}:</h3></td>
          <td><form action="stats.php">{html_options name=owner_id options=$owners selected=$owner_id onchange="submit()"}</form></td>
        </tr>
        {/if}
        <tr>
          <td><h3>{$lang.totalfiles}:</h3></td>
          <td>{$stats.count_all}</td>
        </tr>
        <tr>
          <td><h3>{$lang.tv_episodes}:</h3></td>
          <td>{$stats.count_tv}</td>
        </tr>
        <tr>
          <td><h3>{$lang.numberdisks}:</h3></td>
          <td>{$stats.count_disk}</td>
        </tr>
        <tr>
          <td><h3 style="display: inline">{$lang.videobygen}:</h3><br/>{$lang.multiple}</td>
          <td>
            <table cellspacing="0">
            {foreach item=row from=$stats.count_genre}
              <tr><td><a href="search.php?q=&amp;genres[]={$row.id}">{$row.name}</a>:</td><td>{$row.count}</td></tr>
            {/foreach}
            </table>




			{assign var="data_series" value="<series>"}
			{assign var="data_graphs" value="<graphs><graph gid='0'>"}
			{foreach key=year item=count from=$stats.count_year}
				{assign var="data_series" value=$data_series|cat:"<value xid='$year'>$year</value>"}
				{assign var="data_graphs" value=$data_graphs|cat:"<value xid='$year' url='search.php?q=$year%26fields=year%26nowild=1'>$count</value>"}
            {/foreach}
			{assign var="data_graphs" value=$data_graphs|cat:"</graph></graphs>"}
			{assign var="data_series" value=$data_series|cat:"</series>"}
			{assign var="data" value="<chart>"|cat:$data_series|cat:$data_graphs|cat:"</chart>"}


			<div id="flash_years">
				<strong>You need to upgrade your Flash Player</strong>
			</div>

            <!-- ampie script-->
			<script type="text/javascript" src="{$template}amcharts/swfobject.js"></script>
			<script type="text/javascript">
			// <![CDATA[
				var so = new SWFObject("{$template}amcharts/amcolumn.swf", "amcolumn", "520", "200", "8", "#FFFFFF");
				so.addVariable("settings_file", escape("{$template}amcharts/amcolumn_settings.xml?{php}echo microtime();{/php}"));       // you can set two or more different settings files here (separated by commas)
				so.addVariable("chart_data", "{$data}");                                          // you can pass chart data as a string directly from this file
				so.addVariable("additional_chart_settings", "<settings><labels><label><x>0</x><y>20</y><align>center</align><text_size>12</text_size><text><![CDATA[<b>{$lang.year}</b>]]></text></label></labels></settings>");
				so.addVariable("preloader_color", "#999999");
				so.write("flash_years");
			// ]]>
			</script>
			<!-- end of ampie script -->




			{assign var="data" value="<pie>"}
			{foreach item=row from=$stats.count_genre}
				{assign var="rowname" value=$row.name}
				{assign var="rowcount" value=$row.count}
				{assign var="rowid" value=$row.id}
				{assign var="data" value=$data|cat:"<slice title='$rowname' url='search.php?q=%26genres[]=$rowid'>$rowcount</slice>"}
            {/foreach}
			{assign var="data" value=$data|cat:"</pie>"}

			<div id="flash_genres">
				<strong>You need to upgrade your Flash Player</strong>
			</div>

            <!-- ampie script-->
			<script type="text/javascript" src="{$template}amcharts/swfobject.js"></script>
			<script type="text/javascript">
			// <![CDATA[
				var so = new SWFObject("{$template}amcharts/ampie.swf", "ampie", "520", "400", "8", "#FFFFFF");
				so.addVariable("settings_file", escape("{$template}amcharts/ampie_settings.xml?{php}echo microtime();{/php}"));       // you can set two or more different settings files here (separated by commas)
				so.addVariable("chart_data", "{$data}");                                          // you can pass chart data as a string directly from this file
				so.addVariable("additional_chart_settings", "<settings><labels><label><x>0</x><y>20</y><align>center</align><text_size>12</text_size><text><![CDATA[<b>{$lang.videobygen}</b>]]></text></label></labels></settings>");
				so.addVariable("preloader_color", "#999999");
				so.write("flash_genres");
			// ]]>
			</script>
			<!-- end of ampie script -->

			{assign var="data" value="<pie>"}
			{foreach item=row from=$stats.count_lang}
				{assign var="rowname" value=$row.language}
				{assign var="rownameurl" value=$rowname|escape:url}
				{assign var="rowcount" value=$row.count}
				{assign var="data" value=$data|cat:"<slice title='$rowname' url='search.php?q=$rownameurl%26fields=language'>$rowcount</slice>"}
            {/foreach}
			{assign var="data" value=$data|cat:"</pie>"}

			<div id="flash_languages">
				<strong>You need to upgrade your Flash Player</strong>
			</div>

            <!-- ampie script-->
			<script type="text/javascript" src="../amcharts/ampie/swfobject.js"></script>
			<script type="text/javascript">
			// <![CDATA[
				var so = new SWFObject("{$template}amcharts/ampie.swf", "ampie", "520", "400", "8", "#FFFFFF");
				so.addVariable("settings_file", escape("{$template}amcharts/ampie_settings.xml?{php}echo microtime();{/php}"));       // you can set two or more different settings files here (separated by commas)
				so.addVariable("chart_data", "{$data}");                                          // you can pass chart data as a string directly from this file
				so.addVariable("additional_chart_settings", "<settings><labels><label><x>0</x><y>20</y><align>center</align><text_size>12</text_size><text><![CDATA[<b>{$lang.videobylang}</b>]]></text></label></labels></settings>");
				so.addVariable("preloader_color", "#999999");
				so.write("flash_languages");
			// ]]>
			</script>
			<!-- end of ampie script -->

			{assign var="data" value="<pie>"}
			{foreach item=row from=$stats.count_media}
				{assign var="rowname" value=$row.name}
				{assign var="rownameurl" value=$rowname|escape:url}
				{assign var="rowcount" value=$row.count}
				{assign var="data" value=$data|cat:"<slice title='$rowname' url='search.php?q=$rownameurl%26fields=mediatype%26nowild=1'>$rowcount</slice>"}
            {/foreach}
			{assign var="data" value=$data|cat:"</pie>"}

			<div id="flash_mediatypes">
				<strong>You need to upgrade your Flash Player</strong>
			</div>

            <!-- ampie script-->
			<script type="text/javascript" src="../amcharts/ampie/swfobject.js"></script>
			<script type="text/javascript">
			// <![CDATA[
				var so = new SWFObject("{$template}amcharts/ampie.swf", "ampie", "520", "400", "8", "#FFFFFF");
				so.addVariable("settings_file", escape("{$template}amcharts/ampie_settings.xml?{php}echo microtime();{/php}"));       // you can set two or more different settings files here (separated by commas)
				so.addVariable("chart_data", "{$data}");                                          // you can pass chart data as a string directly from this file
				so.addVariable("additional_chart_settings", "<settings><labels><label><x>0</x><y>20</y><align>center</align><text_size>12</text_size><text><![CDATA[<b>{$lang.videobymedia}</b>]]></text></label></labels></settings>");
				so.addVariable("preloader_color", "#999999");
				so.write("flash_mediatypes");
			// ]]>
			</script>
			<!-- end of ampie script -->

          </td>
        </tr>
        <tr>
          <td><h3>{$lang.year}:</h3></td>
          <td>
            <table cellpadding="2">
              <tr>
                <td valign="bottom"><small>{$stats.first_year|spacify:"<br/>"}</small></td>
                <td colspan="2" valign="bottom" class="odd">
                {foreach key=year item=count from=$stats.count_year}<a href="search.php?q={$year}&amp;fields=year&amp;nowild=1"><img src="images/bar.gif" width="7" height="{math equation="round(2*y/y*x)" x=$count y=$stats.max_count}" title="{$year}: {$count}" alt="{$year}: {$count}" /></a>{/foreach}
                </td>
                <td valign="bottom" class="right"><small>{$stats.last_year|spacify:"<br/>"}</small></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
    <td class="center">
      <table width="100%">
        <tr>
          <td><h3>{$lang.averagefilesize}:</h3></td>
          <td>{$stats.avg_size} mb</td>
        </tr>
        <tr>
          <td><h3>{$lang.totalsize}:</h3></td>
          <td>{$stats.sum_size} gb</td>
        </tr>
        <tr>
          <td><h3>{$lang.averageruntime}:</h3></td>
          <td>{$stats.avg_time} min</td>
        </tr>
        <tr>
          <td><h3>{$lang.totalruntime}:</h3></td>
          <td>{$stats.sum_time} h</td>
        </tr>
        <tr>
          <td><h3>{$lang.totalseen}:</h3></td>
          <td>{$stats.seen_time} h</td>
        </tr>
        <tr>
          <td><h3>{$lang.languages}:</h3><br/></td>
          <td>
            <table class="collapse">
            {foreach item=row from=$stats.count_lang}
              {if $row.language}<tr><td><a href="search.php?q={$row.language|escape:url}&amp;fields=language">{$row.language}</a>:</td><td>{$row.count}</td></tr>{/if}
            {/foreach}
            </table>
          </td>
        </tr>
        <tr>
          <td><h3>{$lang.videobymedia}:</h3></td>
          <td>
            <table class="collapse">
            {foreach item=row from=$stats.count_media}
              <tr><td><a href="search.php?q={$row.name|escape:url}&amp;fields=mediatype&amp;nowild=1">{$row.name}</a>:</td><td>{$row.count}</td></tr>
            {/foreach}
            </table>
          </td>
        </tr>
        <tr>
          <td><h3>{$lang.videobyvcodec}:</h3><br/></td>
          <td>
            <table class="collapse">
            {foreach item=row from=$stats.count_vcodec}
              {if $row.video_codec}<tr><td><a href="search.php?q={$row.video_codec|escape:url}&amp;fields=video_codec&amp;nowild=1">{$row.video_codec}</a>:</td><td>{$row.count}</td></tr>{/if}
            {/foreach}
            </table>
          </td>
        </tr>
      </table>
    </td>
</tr>
</table>

</div>
<!-- /statistics -->

</div>
<!-- /content -->
