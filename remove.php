<?php

include_once 'iftar.php';


/// perform some checks on the POST data
if (!$_POST || !array_key_exists('check_submit', $_POST)) {
	echo "Invalid request";
	exit;
}

processHostRemoval ();

function processHostRemoval () {
	
	global $CONFIG;

	if (isset($_POST['date'])) {
		$key = strip_tags(trim($_POST['date']));
	}

	if (isset($_POST['refid'])) {
		$refid = strip_tags(trim($_POST['refid']));
	}
	
	if (!removeHost($key, $refid)) {
		iftarcal_log(E_USER_ERROR, "processHostRemoval(): could not remove host entry for $key (refid: $refid)");
		sendResponse (false, "Error removing host (please confirm the refid)");
		exit;
	}
	else {
		sendResponse (true, "Host removed successfully");
		iftarcal_log(E_USER_NOTICE, "processHostRemoval(): removed host on $key (refid: $refid)");
	}
	
	
	if ($CONFIG['send_email_on_update']) {
		
		$dt = new DateTime($key);
		$smarty = new Smarty();
		
		$entry = getEntryByKey($key);
			
		$smarty->assign('hosting_date', date_format($dt, 'D, M j, Y'));
		$smarty->assign('contact_email', $CONFIG['contact_email']);
		$smarty->assign('iftar_hosts', $entry['hosts']);
		$smarty->assign('expected_attendees', $CONFIG['expected_attendees']);
		$smarty->assign('total_donation', $CONFIG['donation_per_iftar']);
		
		$mailbody = $smarty->fetch($CONFIG['remove_template']);
		$subject = "Updated iftar hosts for $key";
		
		if (!sendEmailTemplate($key, NULL, $entry['hosts'], $mailbody, $subject)) {
			iftarcal_log(E_USER_ERROR, "processHostRemoval(): could not send email confirmation for host removal on $key");
		}
	
	}

}

function sendResponse($status, $message) {
	
	$response = array ('status' => $status, 'message' => $message);
	echo json_encode($response);
	
}

?>