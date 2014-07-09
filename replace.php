<?php

include_once 'iftar.php';


/// perform some checks on the POST data
if (!$_POST || !array_key_exists('check_submit', $_POST)) {
	echo "Invalid request";
	exit;
}

processHostUpdate ();

function processHostUpdate () {
	
	global $CONFIG;

	if (isset($_POST['date'])) {
		$key = strip_tags(trim($_POST['date']));
	}
	
	if (isset($_POST['index'])) {
		$index = strip_tags(trim($_POST['index']));
	}
	
	if (isset($_POST['refid'])) {
		$refid = strip_tags(trim($_POST['refid']));
	}
	
	if (isset($_POST['name'])) {
		$name = strip_tags(trim($_POST['name']));
	}
	
	if (isset($_POST['email'])) {
		$email = strip_tags(trim($_POST['email']));
	}
	
	if (isset($_POST['phone'])) {
		$phone = strip_tags(trim($_POST['phone']));
	}
	
	$entry = getEntryByKey($key); 
	if (!$entry) {
		iftarcal_log(E_USER_ERROR, "processHostUpdate(): could not retrieve host entry for $key");
		sendResponse (false, "Error updating host entry -- no updates made");
		exit;
	}
	
	// check that the refid matches
	if ($entry['hosts'][$index]['refid'] != $refid) {
		iftarcal_log(E_USER_NOTICE, "processHostUpdate(): submitted refid does not match for host " . $entry['hosts'][$index]['name'] . "on $key");
		sendResponse(false, "Invalid refid -- please check it and try again. No updates made");
		exit;
	}
	
	// refid matches -- update
	$entry['hosts'][$index]['name'] = $name;
	$entry['hosts'][$index]['phone'] = $phone;
	$entry['hosts'][$index]['email'] = $email;
	// create a new refid and timestamp
	$refid = uniqid();
	$dtts = new DateTime();
	$timestamp = date_format ($dtts, 'Y-m-d H:i:s');
	$entry['hosts'][$index]['refid'] = $refid;
	$entry['hosts'][$index]['timestamp'] = $timestamp;
	
	if (!replaceEntryByKey($key, $entry)) {
		iftarcal_log(E_USER_ERROR, "processHostUpdate(): could not update host entry for $key");
		sendResponse (false, "Error updating host entry -- no updates made");
		exit;
	}
	else {
		sendResponse (true, "Update successful");
		iftarcal_log(E_USER_NOTICE, "processHostUpdate(): updated host contact info for host " . $entry['hosts'][$index]['name'] . "on $key");
	}
	
	if ($CONFIG['send_email_on_update']) {
		
		$dt = new DateTime($key);
		$smarty = new Smarty();
			
		$smarty->assign('host_name', $entry['hosts'][$index]['name']);
		$smarty->assign('hosting_date', date_format($dt, 'D, M j, Y'));
		$smarty->assign('contact_email', $CONFIG['contact_email']);
		$smarty->assign('iftar_hosts', $entry['hosts']);
		$smarty->assign('expected_attendees', $CONFIG['expected_attendees']);
		$smarty->assign('total_donation', $CONFIG['donation_per_iftar']);
		
		$mailbody = $smarty->fetch($CONFIG['update_template']);
		$subject = "Updated iftar host information for $key";

		if (!sendEmailTemplate($key, $entry['hosts'][$index], $entry['hosts'], $mailbody, $subject)) {
			iftarcal_log(E_USER_ERROR, "processHostUpdate(): could not send email confirmation for host entry update on $key");
		}
	
	}

}

function sendResponse($status, $message) {
	
	$response = array ('status' => $status, 'message' => $message);
	echo json_encode($response);
	
}

?>