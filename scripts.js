// submit search on enter
$(document).ready(function() {
	$('#query').keypress(function (e) {
		if (e.which == 13) {
			getList();
			return false;
		}
	});
});

function getList() {
	var query = $('#query').val();
	get(query);
}

function get(query) {
	// check if search is not empty
	if(query.length < 3) {
		M.toast({html: 'Introduzca una canción o un artista'});
		return false;
	}

	// search for the list of occurrencies
	apretaste.send({
		'command': "LETRAS SEARCH", 
		'data': {query: query},
	});
}

function getLyric(query) {
	apretaste.send({
		'command': "LETRAS LYRIC", 
		'data': {'link': query},
	});
}
