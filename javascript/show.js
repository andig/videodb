/**
 * Show logic
 *
 * @package JavaScript
 * @author  Andreas Goetz	<cpuidle@gmx.de>
 * @version $Id: show.js,v 1.1 2007/08/08 19:01:29 andig2 Exp $
 */

function showTrailer(title)
{
	open('trailer.php?title=' + escape(title), 'trailer',
		 'width=500,height=500,menubar=no,resizable=yes,scrollbars=yes,status=yes,toolbar=no');
}
