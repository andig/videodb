/**
 * Prototype javascript effects for filtering
 *
 * @package Templates
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * $Id: index.js,v 1.10 2009/03/26 11:17:21 andig2 Exp $
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
 * Clean pre-populated text from input controls
 */
function clearInput(id, term_to_clear)
{
    el = $(id);
    if (el) {
        // Clear input if it matches default value
        if (el.value == term_to_clear) el.value = '';
        // If the value is blank, then put back term
        else if (el.value == '') el.value = term_to_clear;
    }
}

Event.observe(document, 'dom:loaded', function()
{
    /**
     * More/less columns
     */
    function send_request(event, val) {
        $('columns_less').setAttribute('listcolumns', val);

        new Ajax.Request('index.php', {
            parameters: {ajax_render: 1, listcolumns: val},

            onSuccess: function(transport) {
                $('content').update(transport.responseText);
            }
        });

        Event.stop(event);
    }

    $('columns_less').observe('click', function(event) {
        var val = $('columns_less').getAttribute('listcolumns');
        val = (val > 1) ? val-1 : 1;

        send_request(event, val);
    });

    $('columns_more').observe('click', function(event) {
        var val = $('columns_less').getAttribute('listcolumns');
        val++;

        send_request(event, val);
    });

    /**
     * Render list contents and update item count
     */
    function render_list(transport, json) {
        $('content').update(transport.responseText);
        $('count').update(json.totalresults);

        // paged display
        if (json.maxpageno) {
        	$('pageno').update(json.pageno);
        	$('maxpageno').update(json.maxpageno);
        }	
    }
    
    /**
     * Filters
     */
    $('showtv').observe('click', function(event) {
        new Ajax.Request('index.php', {
            parameters: {ajax_render: 1, showtv: ($('showtv').checked) ? 1 : 0},
            onSuccess: render_list
        });
    });

    // radio buttons
    $$('#filtersalphabet input[type="radio"]').invoke('observe', 'click', function(event) {
        new Ajax.Request('index.php', {
            parameters: {ajax_render: 1, filter: event.element().value},
            onSuccess: render_list
        });
    });
/*
    // seen links
    $$('div.list_seen a').invoke('observe', 'click', function(event) {
        Event.stop(event);
        
        $('filter'+event.element().readAttribute('filter')).click();
    });
*/

    /**
     * Owner selection
     */
    $$('#owner').invoke('observe', 'change', function(event) {
        new Ajax.Request('index.php', {
            parameters: {ajax_render: 1, owner: $F('owner')},
            onSuccess: render_list
        });
    });

    /**
     * Media selection
     */
    $$('#mediafilter').invoke('observe', 'change', function(event) {
        new Ajax.Request('index.php', {
            parameters: {ajax_render: 1, mediafilter: $F('mediafilter')},
            onSuccess: render_list
        });
    });

    /**
     * Quicksearch
     */
    $('quicksearch').focus();

    new Ajax.Autocompleter("quicksearch", "item_choices", "index.php", {paramName: "ajax_quicksearch", 
        minChars: 1, frequency: 0.2,
        afterUpdateElement: function(text, li) {
            document.location = 'show.php?id='+li.id;
        }
    });

    /**
     * Enable disabled controls
     */
//    $$('.autoenable').invoke('writeAttribute', {disabled: false});
//    $('quicksearch').writeAttribute({disabled: false});
});
