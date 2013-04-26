{*
  Template for the statistics page
  $Id: stats.flotr.tpl,v 1.1 2013/03/14 17:16:36 andig2 Exp $
*}

<!-- {$smarty.template} -->

<script type="text/javascript" src="./javascript/flotr/flotr.js"></script>

    <script src="/raphael/raphael.js" type="text/javascript"></script>
    <script src="/raphael/g.raphael-min.js" type="text/javascript"></script>
    <script src="/raphael/grafico.min.js" type="text/javascript"></script>

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
          <td><form action="stats.php">{html_options name=owner options=$owners selected=$owner onchange="submit()"}</form></td>
        </tr>
        {/if}
        <tr>
          <td><h3>{$lang.totalfiles}:</h3></td>
          <td>{$stats.count_all}</td>
        </tr>
        <tr>
          <td><h3>{$lang.tv_episodes}:</h3></td>
          <td><a href="search.php?q=1&fields=istv">{$stats.count_tv}</a></td>
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

<div id='chart_genres1' style="width:300px;height:300px"></div>

<div id='chart_genres' style="width:300px;height:300px"></div>
{literal}
<script type="text/javascript">
document.observe('dom:loaded', function() {

	var r = Raphael("chart_genres1");
	r.g.txtattr.font = "12px 'Fontin Sans', Fontin-Sans, sans-serif";

	r.g.text(320, 100, "Interactive Pie Chart").attr({"font-size": 20});

	var pie = r.g.piechart(320, 240, 100, [55, 20, 13, 32, 5, 1, 2, 10], {legend: ["%%.%% – Enterprise Users", "IE Users"], legendpos: "west", href: ["http://raphaeljs.com", "http://g.raphaeljs.com"]});
	pie.hover(function () {
		this.sector.stop();
		this.sector.scale(1.1, 1.1, this.cx, this.cy);
		if (this.label) {
			this.label[0].stop();
			this.label[0].scale(1.5);
			this.label[1].attr({"font-weight": 800});
		}
	}, function () {
		this.sector.animate({scale: [1, 1, this.cx, this.cy]}, 500, "bounce");
		if (this.label) {
			this.label[0].animate({scale: 1}, 500, "bounce");
			this.label[1].attr({"font-weight": 400});
		}
	});

});
</script>

document.observe('dom:loaded1', function() {
	// Draw the graph
	var f = Flotr.draw($('chart_genres'), [
		{/literal}{foreach item=row from=$stats.count_genre}
			{ldelim}data:[[0, {$row.count}]], label: '<a href="search.php?q=&amp;genres[]={$row.id}">{$row.name}</a>'{rdelim},
		{/foreach}{literal}
		], {
		HtmlText: true,
		grid: {
			verticalLines: false,
			horizontalLines: false
		},
		xaxis: {showLabels: false},
		yaxis: {showLabels: false},
		pie: {
			show: true,
			borderColor: '#ccc',
			labelFormatter: function (slice) {
				return ((slice.fraction > 0.03) ? slice.name : '');
			},
		},
		legend: {
			position: null,
		},
		mouse: {
			track: true,           // => true to track the mouse, no tracking otherwise
			position: 'se',        // => position of the value box (default south-east)
		},
	});

	$('chart_genres').observe('flotr:hit', function(evt) {
		alert(evt.memo[0], evt.memo[0]); // .series
	});
});
</script>
{/literal}
            </table>


<div id='chart_timeline' style="width:400px;height:100px"></div>
{literal}
<script type="text/javascript">

document.observe('dom:loaded', function() {
	// Draw the graph
	var f = Flotr.draw($('chart_timeline'), [
		{data:[
		{/literal}{foreach key=year item=count from=$stats.count_year}
			[{$year}, {$count}],
		{/foreach}{literal}
		]}
		], {
		grid: {
			verticalLines: false,
			horizontalLines: false
		},
		xaxis: {
			showLabels: true,
			tickFormatter: function(x) {
				return (Math.round(x));
			},
		},
		lines: {
			show: true,			// => setting to true will show lines, false will hide
			lineWidth: 1, 		// => line width in pixels
			fill: false,		// => true to fill the area from the line to the x axis, false for (transparent) no fill
			fillColor: null		// => fill color
		},
		bars: {
			show: true,
			barWidth: 0.8,
		},
		mouse: {
			track: true,           // => true to track the mouse, no tracking otherwise
			position: 'se',        // => position of the value box (default south-east)
			relative: false,       // => next to the mouse cursor
			trackFormatter: function(obj) {
				return '('+Math.round(obj.x)+')';
			},
			margin: 5,             // => margin in pixels of the valuebox
			lineColor: '#FF3F19',  // => line color of points that are drawn when mouse comes near a value of a series
			trackDecimals: 1,      // => decimals for the track values
			sensibility: 2,        // => the lower this number, the more precise you have to aim to show a value
			radius: 3,             // => radius of the track point
			fillColor: null,       // => color to fill our select bar with only applies to bar and similar graphs (only bars for now)
			fillOpacity: 0.4       // => opacity of the fill color, set to 1 for a solid fill, 0 hides the fill
		}
	});

	// Observe the 'flotr:click' event.
	$('chart_timeline').observe('flotr:click', function(evt) {
		var element = evt.element();
		element.setStyle({mouse:'hand'});
	});

	$('chart_timeline').observe('flotr:click', function(evt) {
		// Get the click coordinates passed as event memo.
		var year = Math.floor(evt.memo[0].x);
		document.location = "search.php?q="+year+"&amp;fields=year&amp;nowild=1";
	});
});
</script>
{/literal}

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
              <tr><td><a href='search.php?q="{$row.name|escape:url}"&amp;fields=mediatype&amp;nowild=1'>{$row.name}</a>:</td><td>{$row.count}</td></tr>
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
        <tr>
          <td><h3>{$lang.videobyacodec}:</h3><br/></td>
          <td>
            <table class="collapse">
            {foreach item=row from=$stats.count_acodec}
              {if $row.audio_codec}<tr><td><a href="search.php?q={$row.audio_codec|escape:url}&amp;fields=audio_codec&amp;nowild=1">{$row.audio_codec}</a>:</td><td>{$row.count}</td></tr>{/if}
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
