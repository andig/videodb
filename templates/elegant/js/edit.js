/**
 * Prototype javascript effects for editing
 * 
 * @package Templates
 *
 * @todo	Check if Ajax.Responder could be used
 *
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * $Id: edit.js,v 1.10 2009/03/26 22:23:49 andig2 Exp $
 */

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
 * Title search
 */
function bindTitle()
{
    $('title').focus();

    // autocompleter is only enabled for new items
    if (!$F('imdbID'))
    {
        new Ajax.Autocompleter("title", "title_choices", "edit.php", {paramName: "ajax_autocomplete_title", 
            minChars: 3, frequency: 0.8,
            
            afterUpdateElement: function(text, li) {
                document.edi.imdbID.value   = li.id;
                document.edi.lookup1.checked= true;

                // start image search
                bindImageLookup();

                new Ajax.Request('edit.php', {
                    parameters: {ajax_prefetch_id: li.id},
    /*                         
                    // inline update
                    onSuccess: function(transport) {
                        $('indicator1').setStyle({display: 'none'});

                        var el = $('u1');
                        el.update(transport.responseText);
    //                    alert(el.down('div#content',0).innerHTML);
                        $('content').update(el.down('#content',0).innerHTML);
                        el.update('');
                    }
    */
                });
            }
        });
    }
}

/**
 * Rating
 */
function bindRating(control, control_val, val)
{
    var rating = new Control.Rating(control, {updateUrl: false, value: val,
        rated: false, multiple: true, max:10, capture: true,
        
        afterChange: function(value) {
            $('rating').value = value;
            $(control_val).update(value);
            rating.options.rated = false;
        }
    });
}

/**
 * Image lookup
 */
function bindImageLookup()
{
    if (!$F('title')) return;
    
    new Ajax.Request('lookup.php', {
        parameters: {ajax_render: 1, searchtype: 'image', engine: 'google', find: $F('title')},

        onSuccess: function(transport, json) {
            if (json.count > 0) {
				$('imagecontent').update(transport.responseText);

				// either img or a
				$$('#imagecontent div.thumbnail').invoke('observe', 'click', function(event) {
					$('imgurl').value = event.element().readAttribute('url');
					Event.stop(event);
				});

				$('images').setStyle({display: 'block'});
//            	Effect.BlindDown('images');
			}
        }
    });
}
