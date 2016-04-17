/******************************************************************
Copyright 2012-2016 Anees Shaikh

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
******************************************************************/

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

