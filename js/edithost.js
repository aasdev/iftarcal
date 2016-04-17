/**
 *
 */

jQuery.validator.addMethod ("hexdigits", function (value, element) {
	return this.optional(element) || /^[a-fA-F0-9]+$/i.test(value);
}, "Hexadecimal digits only please");

var entry;


$(document).ready(function() {

	var re = /^\?date=([0-9\-]+)/;
	var key = window.location.search.match(re)[1];
	request = $.ajax({
		url: "getHostsAsJSON.php",
	    type: "get",
	    data: {"date": key},
	    async: "false"
	});
	request.success(function (response, status, jqXHR) {
		entry = JSON.parse(response);
	   	console.log('received response: ' + entry.numhosts);
	   	for (var i = 0; i < entry.numhosts; i++) {
	   		console.log(entry.hosts[i].name);
	   	}

	});
	request.error(function (response, status, jqXHR) {
	   	console.error ('Error in request: ' + status, errorThrown);
	});

});


$('#remove-modal').on('show.bs.modal', function (e) {
	  // do something...
	var index = e.relatedTarget.getAttribute("data-host-index");
	hostname = entry.hosts[index].name;
	$('#remove-hostname').html(hostname);

	$("#remove-host-form").validate({
        rules: {
            refid: {
                minlength: 13,
                required: true,
                hexdigits: true
            },
        },
        highlight: function (element) {
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error has-feedback');
        },
        unhighlight: function (element) {
            $(element).closest('.form-group').removeClass('has-error').addClass('has-success has-feedback');
        },
        submitHandler: function (form) {
        	var response = removeHost();
        },
	});

});

$('#edit-modal').on('show.bs.modal', function (e) {

	var index = e.relatedTarget.getAttribute("data-host-index");
	hostname = entry.hosts[index].name;
	$('#edit-host-form').find('#nameInput').val(hostname);
	$('#edit-host-form').find('#emailInput').val(entry.hosts[index].email);
	$('#edit-host-form').find('#phoneInput').val(entry.hosts[index].phone);
	$('#edit-host-form').find('#indexInput').val(index);

	$("#edit-host-form").validate({
        rules: {
        	debug: false,
        	name: {
                minlength: 2,
                required: true
            },
            email: {
                minlength: 3,
                required: true,
                email: true
            },
            phone: {
            	required: true,
            	phoneUS: true
            },
            refid: {
                minlength: 13,
                required: true,
                hexdigits: true
            },
        },
        highlight: function (element) {
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error has-feedback');
        },
        unhighlight: function (element) {
            $(element).closest('.form-group').removeClass('has-error').addClass('has-success has-feedback');
        },
        submitHandler: function (form) {
        	var response = updateHost();

        },
        invalidHandler: function (event, validator) {
        	console.log("errors: ", validator.numberOfInvalids());
        }
    });

});

function removeHost () {
	var request;

	if (request) {
		request.abort();
	}
	var $form = $('#remove-host-form');
	if ($form.valid()) {
    	// select all fields
    	var $inputs = $('#remove-host-form :input');
    	// alert ($inputs);
    	var serializeddata = $form.serialize();
    	console.log ("data: ", serializeddata);

    	$inputs.prop("disabled", true);

    	 request = $.ajax({
             url: "remove.php",
             type: "post",
             data: serializeddata
         });
    	 request.success(function (response, status, jqXHR) {
    			var result = JSON.parse(response);
    			console.log ("result: ", result);
    			$('#remove-modal').modal('hide');
    			$("#alert-content").html(result.message);
    			if (result.status == true) {
    				$("#response-alert").removeClass("alert-danger");
    				$("#response-alert").addClass("alert-success");
    			}
    			else {
    				$("#response-alert").removeClass("alert-success")
    				$("#response-alert").addClass("alert-danger");
    			}
				$("#alert-modal").modal('show');
				$inputs.prop("disabled", false);

   		});

    	 request.error(function (response, status, jqXHR) {
    		   	console.error ('Error in request: ' + status, errorThrown);
    		   	$inputs.prop("disabled", false);
    	});

	}
}

function updateHost () {

	var request;

	if (request) {
		request.abort();
	}
	var $form = $('#edit-host-form');
	if ($form.valid()) {
    	// select all fields
    	var $inputs = $('#edit-host-form :input');
    	// alert ($inputs);
    	var serializeddata = $form.serialize();

    	$inputs.prop("disabled", true);

    	 request = $.ajax({
             url: "replace.php",
             type: "post",
             data: serializeddata
         });
    	 request.success(function (response, status, jqXHR) {
    			var result = JSON.parse(response);
    			console.log ("result: ", result);
    			$('#edit-modal').modal('hide');
    			$("#alert-content").html(result.message);
    			if (result.status == true) {
    				$("#response-alert").removeClass("alert-danger");
    				$("#response-alert").addClass("alert-success");
    			}
    			else {
    				$("#response-alert").removeClass("alert-success")
    				$("#response-alert").addClass("alert-danger");
    			}
				$("#alert-modal").modal('show');
				$inputs.prop("disabled", false);

   		});

    	 request.error(function (response, status, jqXHR) {
    		   	console.error ('Error in request: ' + status, errorThrown);
    		   	$inputs.prop("disabled", false);
    	});

	}
}
