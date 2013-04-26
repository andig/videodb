/**
 * Search page helper functions
 * Multi-engine radio button logic
 *
 * @package JavaScript
 * @author	Andreas Goetz	<cpuidle@gmx.de>
 * @version $Id: search.js,v 1.8 2006/11/28 23:34:04 acidity Exp $
 */

/**
 * Select all search fields
 */
function selectAllFields()
{
	for (var i = 0; i < document.search['fields[]'].length; i++)
	{
		document.search['fields[]'].options[i].selected = true;
	}
}

/**
 * Remove enclosing quotes when doing external searches
 */
function unQuote(s)
{
	var result = s;

	if ((result.length > 2) && (result.substr(0,1) == "\"") && (result.substr(result.length-1,1) == "\""))
	{
		result = result.substr(1,result.length-2);
	}

	return result;
}

/**
 * Submit search form depending on selected engine
 */
function submitSearch()
{
	if (!document.search.q.value) return false;

	with (document.search)
	{
		var radio;
		for (var i=0; i<length; i++)
		{
			if ((elements[i].type == "radio") && (elements[i].checked)) {
				radio = elements[i].value;
			}
			if (radio) break;
		}

		switch (radio)
		{
			case "videodb":
				document.search.submit();
				break;

			case "imdb":
				document.searchIMDB.forIMDB.value = unQuote(document.search.q.value);
				document.searchIMDB.submit();
				break;

			case "filmweb":
				document.searchFilmweb.forFilmweb.value = unQuote(document.search.q.value);
				document.searchFilmweb.submit();
				break;

			case "tvcom":
				document.searchTvcom.forTvcom.value = unQuote(document.search.q.value);
				document.searchTvcom.submit();
				break;

			case "amazon":
			case "amazoncom":
			case "amazonxml":
				document.searchAmazon.forAmazon.value = unQuote(document.search.q.value);
				document.searchAmazon.submit();
				break;
		}
	}
}

/**
 * Focus field and select contents
 */
function selectField(formfield)
{
	formfield.focus();
	formfield.select();
}
