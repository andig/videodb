{literal}
<style>
.fullsize-icon {
	position: absolute;
	margin: 0;
	padding: 0;
	width: 30px;
	height: 30px;
	background: transparent url(/videodb/javascript/fullsize/fullsize-icon.png) no-repeat left top;
	z-index: 950;
	cursor: hand, auto;
}
</style>

<script language="JavaScript" type="text/javascript">
Event.observe(document, 'dom:loaded', function() {
	$$('img.canzoom').each(function(el) {
		postop	= el.positionedOffset().top + 5; //+ parseInt(el.getStyle('margin-top').slice(0,-2));
		posleft	= el.positionedOffset().left + 5;

		icon = new Element('div').addClassName('fullsize-icon').setStyle({display: 'none', top: postop+'px', left: posleft+'px'}); // , border: '1px solid red'
		icon.srcimage = el;
		document.body.appendChild(icon);

		icon.observe('click', function(e) {
			zoomClick(this.srcimage, e);
		});

		el.writeAttribute({href: el.readAttribute('targetimg')});
		el.icon = icon;

		el.observe('mouseenter', function(e) {
			this.icon.appear({duration: 0.3});
		});

		el.observe('mouseleave', function(e) {
			mouseX = Event.pointerX(e);
			mouseY = Event.pointerY(e);
			el = document.elementFromPoint(mouseX, mouseY);

			if (el != this.icon) {
				this.icon.fade({duration: 0.3});
			}
		});
	});
});
</script>
{/literal}