$(document).ready(function() {
	// focus input when modal opens
	$('.modal').modal({
		onOpenEnd: function() {
			$('#query').focus();
		}
	});

	// submit search on enter
	$('#query').keypress(function (e) {
		if (e.which == 13) {
			sendSearch();
			return false;
		}
	});
});
function sendSearch() {
	// get the values
	var query = $('#query').val();

	// check if search is not empty
	if(query.length > 1) {
		apretaste.send({
			'command': "LETRA BUSCAR", 
			'data': {query: query},
			});
	}else{
		M.toast({html: 'Por favor introduzca el nombre de una canción o un artista'});
		return false;
	}


	
}
