/**
 * Prototype javascript effects for showing
 * 
 * @package Templates
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * $Id: show.js,v 1.5 2009/04/04 16:26:30 andig2 Exp $
 */

/**
 * Toggle element's visibility
 */
function toggler(el) 
{
	if ($(el).style.display == 'block')
		$(el).setStyle({display: 'none'});
	else
		$(el).setStyle({display: 'block'});
}

/**
 * Using global responder to manage indicator handling for multiple requests
 */
Ajax.Responders.register({
	onCreate: function() {
		if (Ajax.activeRequestCount > 0 && $('indicator1')) {
			$('indicator1').setStyle({display: 'inline'});
		}
	},

	onComplete: function() {
		if(Ajax.activeRequestCount == 0 && $('indicator1')) {
			$('indicator1').setStyle({display: 'none'});
		}
	}
});

/**
 * Seen
 */
function bindSeen(id)
{
    $('seen').observe('click', function(event) {

        new Ajax.Request('show.php', {
            parameters: {ajax_update: id, seen: ($('seen').checked) ? 1 : 0},

            onSuccess: function(transport, json) {
                $('seen').checked = json.result;
                $('seen_label').setStyle({display: (json.result) ? 'inline' : 'none'});
            }
        });
    });
}

/**
 * Rating
 */
function bindRating(id, url, control, control_val, val, editable)
{
    var rating = new Control.Rating(control, {updateUrl: url, updateParameterName: 'rating', value: val,
        rated: false, multiple: true, max:10, capture: true,
        parameters: {ajax_update: id},
        
        afterChange: function(value) {
            $(control_val).update(value);
            this.options.rated = false;
        }.bind(this)
    });
    
    if (!editable) rating.disable();
}

/**
 * Youtube
 */
function bindYoutube(title)
{
    new Ajax.Request('lookup.php', {
		parameters: {ajax_render: 1, searchtype: 'trailer', engine: 'youtube', find: title},

        onSuccess: function(transport, json) {
			if (json.count > 0) {
	            $('youtube').setStyle({display: 'inline'});
	        }    
        }
    });
}

/**
 * Purchase
 */
function bindPurchase(title, engines)
{
	engines.each(function(e)
	{
		new Ajax.Request('lookup.php', {
			parameters: {ajax_render: 1, searchtype: 'purchase', engine: e, find: title},

			onSuccess: function(transport, json) {
				if (json.count > 0) {
					$('purchase').setStyle({display: 'inline'});
					$('purchasecontent').insert(transport.responseText);
	//				$('images').setStyle({display: 'block'});
	//            	Effect.BlindDown('images');
				}
			}
		});
	});
}


/**
 * Torrent
 */
function bindTorrent(title, engines)
{
	engines.each(function(e)
	{
		new Ajax.Request('lookup.php', {
			parameters: {ajax_render: 1, searchtype: 'download', engine: e, find: title},

			onSuccess: function(transport, json) {
				if (json.count > 0) {
					$('torrent').setStyle({display: 'inline'});
					$('torrentcontent').insert(transport.responseText);
	//				$('images').setStyle({display: 'block'});
	//            	Effect.BlindDown('images');
				}
			}
		});
	});
}

function bindRenderer(title, type, engines)
{
	engines.each(function(e)
	{
		new Ajax.Request('lookup.php', {
			parameters: {ajax_render: 1, searchtype: type, engine: e, find: title},

			onSuccess: function(transport, json) {
//alert(this.options);
				if (json.count > 0) {
					$('purchase').setStyle({display: 'inline'});
					$('purchasecontent').insert(transport.responseText);
	//				$('images').setStyle({display: 'block'});
	//            	Effect.BlindDown('images');
				}
			}.bind(this)
		});
	});
}
