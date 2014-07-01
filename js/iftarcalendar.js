/**
 * 
 */

$(document).ready(function(){
	$('.iftarcal-datebutton, .iftarcal-assigned').popover({
		trigger: 'hover',
		placement: 'top',
		delay: {
			show: 500,
			hide: 100
		},
		html: true,
		content: function() {
			if ($(this).hasClass('iftarcal-datebutton')) {
				// button -- the date key is in the "value" attribute
				var key = $(this).attr('value');
			}
			else {
				// assigned -- the date key has to be parsed from the url
				var url = $(this).attr('href');
				var key = url.split('=')[1];
			}
			request = $.ajax({
				url: "getHostsAsHTML.php",
			    type: "get",
			    data: {"date": key},
			    async: "false"
			});
			request.success(function (response, status, jqXHR) {
			   	console.log('received response: ' + response);
			   	var content = response;
			    return content;
			});
			request.error(function (response, status, jqXHR) {
			   	console.error ('Error in request: ' + status, errorThrown);
			});

		}
	});
});

