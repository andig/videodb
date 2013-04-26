/**
 * Lookup data submission logic
 *
 * @package JavaScript
 * @author  Andreas Goetz	<cpuidle@gmx.de>
 * @version $Id: lookup.js,v 1.6 2007/12/22 13:36:43 andig2 Exp $
 */

window.focus();

function returnData(id, title, subtitle, engine)
{
	opener.document.edi.imdbID.value = id;
	opener.document.edi.engine.value = engine;
//	if (!opener.document.edi.title.value)    opener.document.edi.title.value = title;
	opener.document.edi.title.value = title;
	if (!opener.document.edi.subtitle.value) opener.document.edi.subtitle.value = subtitle;
	if (opener.document.edi.lookup0.checked) opener.document.edi.lookup1.checked = true;
	opener.focus();
	window.close();
}

function returnImage(imgurl)
{
	opener.document.edi.imgurl.value = imgurl;
	opener.focus();
	window.close();
}
