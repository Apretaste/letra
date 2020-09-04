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

$(document).ready(function() {
	$('.modal').modal();
});

var share;
function init(link, song, author) {
	share = {
		text: author + ' -' + removeTags(song).substr(0, 50) + '...',
		icon: 'music',
		send: function () {
			apretaste.send({
				command: 'PIZARRA PUBLICAR',
				redirect: false,
				callback: {
					name: 'toast',
					data: 'La letra de fue compartida en Pizarra'
				},
				data: {
					text: $('#message').val(),
					image: '',
					link: {
						command: btoa(JSON.stringify({
							command: 'LETRAS LYRIC',
							data: {
								link: link
							}
						})),
						icon: share.icon,
						text: share.text
					}
				}
			})
		}
	};
}

function toast(message){
	M.toast({html: message});
}


function removeTags(str) {
	if ((str===null) || (str===''))
		return '';
	else
		str = str.toString();

	// Regular expression to identify HTML tags in
	// the input string. Replacing the identified
	// HTML tag with a null string.
	return str.replace( /(<([^>]+)>)/ig, '');
}