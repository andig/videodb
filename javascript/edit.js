/**
 * Lookup logic
 *
 * @package JavaScript
 * @author  Andreas Goetz	<cpuidle@gmx.de>
 * @version $Id: edit.js,v 1.6 2009/04/06 06:50:54 andig2 Exp $
 */

function lookupData(title)
{
	var win	= open('lookup.php?find=' + encodeURIComponent(title), 'lookup',
		       'width=550,height=500,menubar=no,resizable=yes,scrollbars=yes,status=yes,toolbar=no');
	win.focus();
}

function lookupImage(title)
{
	var win	= open('lookup.php?find=' + encodeURIComponent(title) + '&searchtype=image&engine=google', 'lookup',
		       'width=450,height=500,menubar=no,resizable=yes,scrollbars=yes,status=yes,toolbar=no');
	win.focus();
}

function changedId()
{
	if (document.edi.imdbID.value)
	{
		if (document.edi.lookup0.checked) document.edi.lookup1.checked = true;
	}
	else
	{
		document.edi.lookup0.checked = true;
	}
}
