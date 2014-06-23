/**
 *  use jquery datatable plugin to build the table of registration information 
 */

$(document).ready(function() {
	request = $.ajax ({
		url: 'buildhosttable.php'
	});
	request.done (function (response, status, jqXHR) {
		var jsonresponse = JSON.parse(response);
		$('#hosttable').dynatable({
			dataset: {
				records: jsonresponse,
				perPageDefault: 30
			},
			table: {
				defaultColumnIdStyle: 'underscore'
			}
		});
	});
});