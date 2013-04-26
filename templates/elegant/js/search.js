/**
 * Prototype javascript effects for searching
 * 
 * @package Templates
 * @author  Andreas Goetz   <cpuidle@gmx.de>
 * $Id: search.js,v 1.8 2009/04/04 16:26:30 andig2 Exp $
 */

Event.observe(document, 'dom:loaded', function()
{
    /**
     * Quicksearch
     */
    $('q').focus();

    // Ajax.Autocompleter2 allows passing in complex search parameters like arrays
    new Ajax.Autocompleter("q", "item_choices", "search.php", {
        minChars: 2, indicator: 'indicator1', frequency: 0.2,
        parameters: collect_parameters({ajax_quicksearch: 1}),
        
        afterUpdateElement: function(text, li) {
            document.location = 'show.php?id=' + li.id;
        }
    });

    /**
     * Collect form parameters for search fields and genres
     */
    function collect_parameters(parameters)
    {
        var res = Object.toQueryString(parameters);

        // owner
        if ($('owner')) {
            res += '&owner=' + $F('owner');
        }
        
        // genres
        $$('#genres input').each(function(el) {
            if (el.checked) {
                res += '&genres[]=' + el.value;
            }
        });

        // fields
        $$('#fields option').each(function(el) {
            if (el.selected) {
                res += '&fields[]=' + el.value;
            }
        });

        return res;
    }

    /**
     * Create submit request
     */
    function submit_form(event)
    {
//        if (!$F('q')) return;        
        $('indicator1').setStyle({display: 'inline'});

        new Ajax.Request('search.php', {
            parameters: collect_parameters({ajax_render: 1, q: $F('q')}),

            onSuccess: function(transport, json) {
                $('indicator1').setStyle({display: 'none'});
                $('content').update(transport.responseText);
                $('count').update(json.count);
            }
        });
    }

    /**
     * Search fields
     */
    $$('#fields option').invoke('observe', 'click', submit_form);

    // toggle all
    $('select_all').observe('click', function() {
        selectAllFields();
        submit_form();
    });

    /**
     * Genres
     */
    $$('#genres input').invoke('observe', 'change', submit_form);

    /**
     * Owner selection
     */
    $$('#owner').invoke('observe', 'change', submit_form);
});
