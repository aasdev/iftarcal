function validateNonEmpty (inputfield, helptext, emptymsg, len) {
// check that the name is not empty and does not exceed the length limit
	if (inputfield.value.length == 0) {
		//set the help message
		if (helptext != null)
			helptext.innerHTML = emptymsg + " (up to " + len + " char)";
		return false;
	}
	else {
		// data is ok
		if (helptext != null)
			helptext.innerHMTL = "no error";
		return true;
	}
}

function validateRegEx (regex, inputstr, helptext, helpmsg) {
	if (!regex.test (inputstr)) {
		if (helptext != null)
			helptext.innerHTML = helpmsg;
		return false;
	}
	else {	// regex is ok
		if (helptext != null)
			helptext.innerHTML = "";
		return true;
	}
}


function validateName (inputfield, helptext) {
// check that the name is not empty and does not exceed the length limit
	
	if (!validateNonEmpty (inputfield, helptext, "Please enter your full name", 128))
		return false;
	else
		return true;
}

function validateEmail (inputfield, helptext) {
// check that the email is not empty, does not exceed the length limit, and looks valid
	if (!validateNonEmpty (inputfield, helptext, "Please enter your email address", 96))
		return false;
		
	return (validateRegEx (/^[\w\.-_\+]+@[\w-]+(\.\w{2,4})+$/, 
		inputfield.value, helptext,
		"Please enter a valid email address (ex. sister@muslimah.com)"
		));
}

function validatePhone (inputfield, helptext) {
// check that the phone is not empty, does not exceed the length limit, and looks valid
	if (!validateNonEmpty (inputfield, helptext, "Please enter your phone number (ex. 123-456-7890)", 12))
		return false;
		
	return (validateRegEx (/^\d{3}-\d{3}-\d{4}$/, 
		inputfield.value, helptext,
		"Please enter your phone number (ex. 123-456-7890)"));
}

function checkReservationForm (form) {
	if (validateName (form["name"], form["name_help"]) &&
		validateEmail (form["email"], form["email_help"]) &&
		validatePhone (form["phone"], form["phone_help"]) ) {
		return true;
	}
	else {
		alert ("Please check errors in the form before submitting");
		return false;
	}
}
		