{assign var=IMGWIDTH value="194"}
{assign var=IMGHEIGHT value="288"}

{literal}

<style>
/* Outer div for image container */
.boxgrid{
	position: relative;
	overflow: hidden;
	/* float:left; */
	background:#161613;
	border: solid 0px #8399AF;
	width: 194px;
	height: 288px;
	margin: 10px;
}
.boxgrid img{
	position: absolute;
	top: 0;
	left: 0;
	border: 0;
}

/* Inner div for sliding content */
.boxcaption{
	position: absolute;
	width: 174px; /* note: 100% doesn't work here */
	background: #000;
	opacity: .8;
	/* For IE 5-7 */
	filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=80);
	/* For IE 8 */
	-MS-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=80)";

	padding: 0 10px 10px 10px;
}

/* Inner content styling */
.boxcaption h3 {
	font-size: 12pt;
	font-weight: bold;
	color: #fff;

	margin: 0;
	padding: 10px 0 10px 0;
}

.boxcaption div, .boxcaption h4, .boxcaption a {
	font-size: 10pt;
	color: #ddd;
	padding: 10px 0 0 0;
}

/* Start position for actions */
.boxcaption .caption {
	top: 220;
	left: 0;
}
</style>

<script>
Event.observe(document, 'dom:loaded', function() {
	// setup initial slider position based on height of h3 tag (plot)
	$$('.boxcaption').each(function(el) {
		el.setStyle({top: (288 - el.down('h3').getHeight())+'px'});
	});

	// unique identifier for queue scope
	var elid = 1;

	$$('.boxgrid.caption').each(function(el) {
		elid++;

		el.observe('mouseenter', function(e) {
			var el = this.down('.boxcaption');
			var dy = el.getHeight() - el.down('h3').getHeight();
			new Effect.Move(el, {duration: 0.1, x: 0, y: -dy, mode: 'relative' //});
				,queue: { position:'end', scope:'s'+elid }
			});
		});

		el.observe('mouseleave', function(e) {
			var el = this.down('.boxcaption');
			var dy = el.getHeight() - el.down('h3').getHeight();
			new Effect.Move(el, {duration: 0.1, x: 0, y: dy, mode: 'relative' // });
				,queue: { position:'end', scope:'s'+elid }
			});
		});
	});
});
</script>

{/literal}
