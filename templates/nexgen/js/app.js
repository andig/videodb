/**
 * General logic
 *
 * @package JavaScript
 * @author  Andreas Goetz	<cpuidle@gmx.de>
 * @version $Id: app.js,v 1.7 2013/03/21 16:27:57 andig2 Exp $
 */

$(document).ready(function() {
	
	// customize forms
	$("form").addClass("custom");

	// add submit on click
	$(".autosubmit").change(function() {
		$(this).parent().submit();
	});

	// add submit on click
	$(".submit").click(function() {
		$(this).closest("form").submit();
	});

	// disable "disabled" buttons
	$("a.button.disabled").click(function(event) {
		event.stopImmediatePropagation();
		return(false);
	});

	// allow closing reveal dialogs
	$(".button.close-modal").click(function() {
		$(this).closest("div.reveal-modal").find('a.close-reveal-modal').trigger('click');
		return(false);
	});

	// focus primary element (must be only one)
	$(".autofocus").focus();

	// prevent tabbing through <i> tags
	$('i').parent().attr('tabindex', '-1');

	// lazy-load images
	if (typeof jQuery == "function" && typeof jQuery().lazyload == "function") {
		$("img.lazy").lazyload({threshold: 500});
	}

	// substitute input[file] with custom control
	$("input[type=file]").each(function() {
		var proxy = $('<input type="text" value="'+$(this).val()+'" />');

		var context = {_this: $(this), _proxy: proxy};
		var intervalFunc = $.proxy(function() {
			this._proxy.val(this._this.val());
		}, context);

		// hide file input and watch for changes
		$(this)
			.css("position", "absolute")
			.css("opacity", "0.000001")
			.attr("size", "100")
			.parent().append(proxy)
			.click(function() {
				setInterval(intervalFunc, 1000);
			});
	});

	// dl.input-radio
	$("dl[input-radio] dd").each(function() {
		var a = $(this).find("a");
		// create proxy element
		if ($(this).hasClass("active") && $("#"+a.attr("href")).length == 0) {
			$(this).closest("form").append(
				$('<input type="hidden" name="'+a.attr("href")+'" id="'+a.attr("href")+'" value="'+a.attr("value")+'" />')
			);
		}

		// add click function
		$(this).click(function() {
			$(this).siblings().removeClass("active");
			$(this).addClass("active");
			// set proxy value
			var a = $(this).find("a");
			$("#"+a.attr("href")).val(a.attr("value"));
			return(false);
		});
	});

	// dl.input-checkbox
	$("dl[input-checkbox] dd").each(function() {
		var a = $(this).find("a");
		var id = $(this).closest("form").attr("id")+a.attr("href").replace("[]","")+a.attr("value");

		// create proxy elements
		if ($("#"+id).length == 0) {
// alert($(this).closest("form").attr("id"));
			$(this).closest("form").append(
				$('<input type="hidden" name="'+a.attr("href")+'" id="'+id+'" value="' + (
					($(this).hasClass("active")) ? a.attr("value") : ''
				) + '" />')
			);
		}

		// add click function
		$(this).click(function() {
			$(this).toggleClass("active");
			// set proxy value
			var a = $(this).find("a");
			var id = $(this).closest("form").attr("id")+a.attr("href").replace("[]","")+a.attr("value");
// alert($("#"+id).closest("form").attr("action") +"/"+$("#"+id).closest("form").attr("id") +" "+ "#"+id + ":" + (($(this).hasClass("active")) ? a.attr("value") : ""));
			$("#"+id).val(($(this).hasClass("active")) ? a.attr("value") : "");
			return(false);
		});
	});
});
