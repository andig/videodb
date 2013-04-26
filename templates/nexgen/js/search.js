/**
 * Search logic
 *
 * @package JavaScript
 * @author  Andreas Goetz	<cpuidle@gmx.de>
 * @version $Id: search.js,v 1.1 2013/03/10 16:18:26 andig2 Exp $
 */

$(document).ready(function () {

	$("#q").autocomplete({
		source: function(request, response) {
			$.ajax({
				url: "search.php",
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
			//$("#imdbID").val(ui.item.id);
			//$("#lookup1").click();
		}
	});
});
