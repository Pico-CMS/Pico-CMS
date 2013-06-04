
function Blog_ShowLink(obj)
{
	var index = obj.selectedIndex;
	if (index != 0)
	{
		window.location = obj.options[index].value;
	}
}

function Blog_Search(component_id)
{
	var search_obj    = document.getElementById('navsearch_'+component_id);
	var search_phrase = urlencode(search_obj.value);
	if (search_phrase.length < 3) {
		alert('Please enter a search phrase');
	}
	else {
		var search_alias  = document.getElementById('navsearch_alias_'+component_id);
		var link          = url(search_alias.value + '/search/' + search_phrase);
		window.location   = link;
	}
}