/**
 * Lookup data submission logic
 *
 * @package JavaScript
 * @author  Andreas Goetz	<cpuidle@gmx.de>
 * @version $Id: lookup.js,v 1.2 2013/03/16 10:10:07 andig2 Exp $
 */

window.focus();

function returnData(id, title, subtitle, engine) {
	window.opener.$('#imdbID').val(id);
	window.opener.$('#engine').val(engine);
	window.opener.$('#title').val(title);
//	$('#subtitle').val(subtitle);

	window.opener.$('#lookup1').click();

	opener.focus();
	window.close();
}

function returnImage(imgurl) {
	window.opener.$('#imgurl').val(imgurl);

	opener.focus();
	window.close();
}
