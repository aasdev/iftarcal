<?php

include_once ('iftarcal_runconfig.php');

if (LOCAL == true) {
	include ("iftarcal_settings.php");
}

else {
	include ("/home/uwms/conf/iftarcal_settings.php");
}

include_once "iftar.php";


buildHostTable ();


function buildHostTable ()
{
	
	global $CONFIG;
	
	$returndata = array();
	$dtcurrent = new DateTime ($CONFIG['ramadan_start_date']);
	$dtincr = new DateInterval('P1D');
	$numdays = calcNumRamadanDays();
	for ($i = 0; $i <= $numdays; $i++, $dtcurrent = $dtcurrent->add($dtincr)) { 
		
		$key = $dtcurrent->format ('Y-m-d');
		if ($entry = getEntryByKey($key)) {
			if ($entry['numhosts'] > 0) {
				$hoststring = "<p>";
				foreach ($entry['hosts'] as $h) {
					$hoststring .= sprintf ("%s (email: %s phone: %s) refid: %s<br>", $h['name'], $h['email'], $h['phone'], $h['refid']);
				}
				$hoststring .= "</p>";
			}
			else {
				$hoststring = "";
			}
				
				
			$rowarray = array (
				'date' => $key,
				'hosts' => $hoststring,
				'coordinators' => $entry['coordinators'],
				'volunteers' => $entry['volunteers']
			);
		}
		// iftarcal_log(E_USER_NOTICE, "buildHostTable(): entry $i: " . print_r($rowarray, true));
		$returndata[] = $rowarray;
	}

	
	echo json_encode ($returndata);

}