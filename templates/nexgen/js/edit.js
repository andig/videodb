/**
 * Editing logic
 *
 * @package JavaScript
 * @author  Andreas Goetz	<cpuidle@gmx.de>
 * @version $Id: edit.js,v 1.3 2013/03/13 08:01:01 andig2 Exp $
 */

/*
$(document).ready(function () {

	$("#title").autocomplete({
		source: function(request, response) {
			$.ajax({
				url: "edit.php",
				data: {
					ajax_type: "json",
					ajax_autocomplete_title: request.term
				},
				success: function(data) {
					response($.map(data, function(item) {
						return {
							id: item.id,
							value: item.title
						}
					}))
				}
			})
		},
		select: function(event, ui) {
			$("#imdbID").val(ui.item.id);
			$("#lookup1").click();
		}
	});
});
*/
function lookupData(title) {
	var win	= open('lookup.php?find=' + encodeURIComponent(title), 'lookup',
		           'width=700,height=600,menubar=no,resizable=yes,scrollbars=yes,status=yes,toolbar=no');
	win.focus();
}

function lookupImage(title) {
	var win	= open('lookup.php?find=' + encodeURIComponent(title) + '&searchtype=image&engine=google', 'lookup',
		       'width=450,height=500,menubar=no,resizable=yes,scrollbars=yes,status=yes,toolbar=no');
	win.focus();
}

function changedId() {
	if ($("#imdbID").val()) {
		$('#lookup1').click();
	}
}
