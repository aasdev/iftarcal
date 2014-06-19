$(document).ready(function(){
    $("#signupform").validate({
        rules: {
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
        },
        highlight: function (element) {
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error has-feedback');
        },
        unhighlight: function (element) {
            $(element).closest('.form-group').removeClass('has-error').addClass('has-success has-feedback');
        }
    });
});