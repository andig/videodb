<div id="dlog" sstyle="position: absolute; top: 600px; left: 0px; width:200px; height: 80px;"></div>

{literal}
function dlog(d)
{
	el = $('dlog');
/*
	if (!el) {
		el = new Element('div', {'id': 'dlog'}).setStyle({position:'absolute', left:'0px', top:'200px', width:'200px', border:'1px solid #000', height:'20px'});
		document.body.appendChild(icon);
	}
*/
	el.innerHTML = d + "<br/>" + el.innerHTML;
}
{/literal}
