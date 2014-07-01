<?php
/******************************************************************
Copyright (C) 2012-2014  Anees Shaikh

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
******************************************************************/

include_once ('iftarcal_runconfig.php');

if (LOCAL == true) {
	include ("iftarcal_settings.php");
	require ("lib/PHPMailerAutoload.php");
	require ("lib/Smarty/Smarty.class.php");
}

else {
	include ("/home/uwms/conf/iftarcal_settings.php");
	require ("/home/uwms/lib/PHPMailerAutoload.php");
	require ("/home/uwms/lib/Smarty/Smarty.class.php");
}


error_reporting(E_ALL);

/******************************************************************/
// YOU NEED NOT CHANGE CODE BELOW THIS LINE
/******************************************************************/

// $startdatestr = date ('Y-m-d', $startts);

// in-memory array of special event reservations
	$events = array();

// $cellwidth = 110;
// $cellheight = 85;

/******************************************************************/

function readSpecialEvents () {

	global $CONFIG;
	global $events;

	if (!file_exists($CONFIG['events_file'])) {
		iftarcal_log(E_USER_ERROR, "events file does not exist");
		return;
	}

	$file_contents = file($CONFIG['events_file']);

  	foreach ($file_contents as $line) {
  		trim ($line);
    	$elements = explode ("\t", $line);
    	//print "$elements[0]\n$elements[1]\n$elements[2]\n";
    	$events["$elements[0]"] = array ("date"=>$elements[0], "label"=>$elements[1], "class"=>$elements[2]);
    }
}

/******************************************************************/
function calcNumRamadanDays () {
	global $CONFIG;
	
	// first compute how many days in Ramadan
	$dtstart = new DateTime ( $CONFIG ['ramadan_start_date'] );
	$dtend = new DateTime ( $CONFIG ['eid_date'] );
	// subtract one day from Eid to get the last day of Ramadan
	$dtend->sub ( new DateInterval ( 'P1D' ) );
	// compute the difference betw start and end dates
	$interval = $dtend->diff ( $dtstart );
	$numdays = $interval->d;
	
	return $numdays;
	
} 

/******************************************************************/

function checkTable () {

	global $CONFIG;

    // connect to DB
    $dbconn = mysqli_connect ($CONFIG['dbserver'], $CONFIG['dbuser'], $CONFIG['dbpw'], $CONFIG['dbname']) or die ("Error connecting to database: " . mysqli_error($dbconn));

    if (!$dbconn) {
    	iftarcal_log (E_USER_ERROR, "Can't connect to db " . $CONFIG['dbname'] . mysqli_connect_error());
    	die;
    }
		
	// get a lock on the table
	$query = "LOCK TABLES " . $CONFIG ['tablename'] . " WRITE";
	if (mysqli_query ( $dbconn, $query ) === false) {
		iftarcal_log ( E_USER_ERROR, "Can't lock table " . $CONFIG ['tablename'] . mysqli_connect_error () );
	}
	
	$query = "SELECT COUNT(*) FROM " . $CONFIG ['tablename'];
	if (! ($result = mysqli_query ( $dbconn, $query ))) {
		iftarcal_log ( E_USER_ERROR, "Can't query count for table " . $CONFIG ['tablename'] . mysqli_connect_error () );
	} else {
		$row = mysql_fetch_row ( $result );
	}
	
	// how many rows in the DB?
	if ($row [0] == 0) {
		iftarcal_log ( E_USER_NOTICE, "Table " . $CONFIG ['tablename'] . " is empty. Populating ..." );
		// populate table
		
		// first compute how many days in Ramadan
		$numdays = calcNumRamadanDays();
		
		$dtindex = new DateTime ( $CONFIG ['ramadan_start_date'] );
		for($i = 0; $i < $numdays; $i ++) {
			// $ts = mktime (13, 0, 0, $startmon, ($startdate + $i), $startyear);
			$key = $dtindex->format ( 'Y-m-d' );
			$query = "INSERT INTO " . $CONFIG ['tablename'] . " VALUES ('$key', 0, NULL)";
			iftarcal_log ( E_USER_NOTICE, "$query" );
			if (mysqli_query ( $dbconn, $query ) === true && mysqli_affected_rows ( $dbconn ) == 1) {
				// success
				echo "successful insert\n";
			} else {
				iftarcal_log ( E_USER_ERROR, "Insert failed: $query -- " . mysqli_connect_error () );
			}
			// increment the date
			$dtindex->add ( new DateInterval ( 'P1D' ) );
		}
	}
	
	mysqli_free_result ( $result );
	
	// release table lock
	$query = "UNLOCK TABLES";
	if (! ($result = mysqli_query ($dbconn,$query))) {
		iftarcal_log ( E_USER_ERROR, "UNLOCK failed: " . mysqli_connect_error () );
	}
	
	
	mysqli_close($dbconn);
}
/******************************************************************/
function getEntryByKey ($key) {
	
	// returns an array containing db contents for the given date key.  returns false
	// on failure. 
	
	global $CONFIG;
	
	// connect to DB
	$dbconn = mysqli_connect ( $CONFIG ['dbserver'], $CONFIG ['dbuser'], $CONFIG ['dbpw'], $CONFIG ['dbname'] ) or die ( "Error connecting to database: " . mysqli_error ( $dbconn ) );
	
	if (! $dbconn) {
		iftarcal_log ( E_USER_ERROR, "Can't connect to db " . $CONFIG ['dbname'] . mysqli_connect_error () );
		die ();
	}
	
	// get a lock on the table
	$query = "LOCK TABLES " . $CONFIG ['tablename'] . " READ";
	if (mysqli_query ( $dbconn, $query ) === false) {
		iftarcal_log ( E_USER_ERROR, "Can't lock table " . $CONFIG ['tablename'] . mysqli_connect_error () );
	}
	
	$query = "SELECT * FROM " . $CONFIG ['tablename'] . " WHERE date='$key'";
	$data = mysqli_query ( $dbconn, $query );
	if (mysqli_num_rows($data) == 0) {
		// no row for this date
		iftarcal_log ( E_USER_ERROR, "getEntryByKey(): Failed to lookup entry for date: $key ..." );
		return false;
	} else {
		// found existing entry for this date
		$row = mysqli_fetch_assoc ( $data );
	}
	
	$entry = array();
	
	$entry['key'] = $key;
	$entry['numhosts'] = $row['numhosts'];
	if ($row['numhosts'] > 0) {
		$hostarray = unserialize($row['hosts']);
		foreach ($hostarray as $host) {
			$entry['hosts'][] = $host;
		}
	}
	else {
		$entry['hosts'] = NULL;  // initialize to an empty array
	}
	
	$entry['numcoord'] = $row['numcoord']; 
	if ($row['numcoord'] > 0) {
		$coordarray = unserialize($row['coordinators']);
		foreach ($coordarray as $coord) {
			$entry['coordinators'][] = $coord; 
		}
	}
	else {
		$entry['coordinators'] = NULL;
	}
	
	$entry['numvolun'] = $row['numvolun'];
	if ($row['numvolun'] > 0) {
		$volunarray = unserialize($row['volunteers']);
		foreach ($volunarray as $volun) {
			$entry['volunteers'][] = $volun;
		}
	}
	else {
		$entry['volunteers'] = NULL;
	}
	
	// release table lock
	$query = "UNLOCK TABLES";
	if (! ($result = mysqli_query ($dbconn,$query))) {
		iftarcal_log ( E_USER_ERROR, "UNLOCK failed: " . mysqli_connect_error () );
	}
	
	mysqli_free_result($data);
	mysqli_close($dbconn);
	
	return $entry;
}
/******************************************************************/
function replaceEntryByKey ($key, $entry)
{
	// takes an assoc array with host entry elements and writes them into 
	// the specified date, given by the key paramater.
	// USE WITH CARE -- this will overwrite existing entry
	
	global $CONFIG;
	
	// connect to DB
	$dbconn = mysqli_connect ( $CONFIG ['dbserver'], $CONFIG ['dbuser'], $CONFIG ['dbpw'], $CONFIG ['dbname'] ) or die ( "Error connecting to database: " . mysqli_error ( $dbconn ) );
	
	if (! $dbconn) {
		iftarcal_log ( E_USER_ERROR, "Can't connect to db " . $CONFIG ['dbname'] . mysqli_connect_error () );
		die ();
	}
	
	// get a lock on the table
	$query = "LOCK TABLES " . $CONFIG ['tablename'] . " WRITE";
	if (mysqli_query ( $dbconn, $query ) === false) {
		iftarcal_log ( E_USER_ERROR, "Can't lock table " . $CONFIG ['tablename'] . mysqli_connect_error () );
	}
	
	$numhosts = $entry['numhosts'];
	$hosts = serialize($entry['hosts']);
	$numcoord = $entry['numcoord'];
	$coordinators = serialize($entry['coordinators']);
	$numvolun = $entry['numvolun'];
	$volunteers = serialize($entry['volunteers']);
	
	$query = "UPDATE " . $CONFIG['tablename'] . " SET numhosts = '$numhosts', hosts = '$hosts', ";
	$query .= "numcoord = '$numcoord', coordinators = '$coordinators', ";
	$query .= "numvolun = '$numvolun', volunteers = '$volunteers' ";
	$query .= "WHERE date = '$key'";
	
	if (!mysqli_query($dbconn, $query)) {
		iftarcal_log(E_USER_ERROR, "replaceEntryByKey() failed to update database: $query" . " error: " . mysqli_error($dbconn));
		return false;
	}
	
	// release table lock
	$query = "UNLOCK TABLES";
	if (! ($result = mysqli_query ($dbconn,$query))) {
		iftarcal_log ( E_USER_ERROR, "UNLOCK failed: " . mysqli_connect_error () );
	}
	
	mysqli_close($dbconn);
	
	return true;
	
	
}


/******************************************************************/

function printDays() {
  global $startdatestr;
  $startdate = (float) strtotime ($startdatestr);

  for ($i = 0; $i < 30; $i++) {

    $date = $startdate + (float) ($i * 24 * 3600);
    printf ("%s\n", date ('w m-d-Y', $date));
  }
}


/******************************************************************/
function createNewDateRow ($key, $dbconn) {
	
	global $CONFIG;
	
	$query = "INSERT INTO " . $CONFIG ['tablename'] . " VALUES ('$key', 0, NULL)";
	iftarcal_log ( E_USER_NOTICE, "createNewDateRow: $query" );
	if (mysqli_query ( $dbconn, $query ) === true && mysqli_affected_rows ( $dbconn ) == 1) {
		// success
		return true;
	} else {
		iftarcal_log ( E_USER_ERROR, "Insert failed: $query -- " . mysqli_connect_error () );
		return false;
	}
}


/******************************************************************/

function printCalendarTable() {

	global $CONFIG, $startts, $startmon, $startdate, $startyear, $ramadandays;
	global $cellwidth, $cellheight;
	global $tablename, $dbname, $dbuser, $dbpw;
	global $max_slots;
	global $disable;
	global $events;
	
	if ($CONFIG ['disable_signup'] == true) {
		print "<h2>Iftar signup calendar is temporarily unavailable</h2>\n";
		print "<p>Please check back later to see the calendar insha Allah</p>\n";
		return;
	}
	
	// print Calendar headings
	print "<form action=\"signupform.php\" method=\"post\"><table class=\"table table-bordered iftarcal-table\">\n";
	print "<tr>\n";
	print "\t<th class=\"iftarcal-table-cell dateheader\">Sun</th>\n";
	print "\t<th class=\"iftarcal-table-cell dateheader\">Mon</th>\n";
	print "\t<th class=\"iftarcal-table-cell dateheader\">Tue</th>\n";
	print "\t<th class=\"iftarcal-table-cell dateheader\">Wed</th>\n";
	print "\t<th class=\"iftarcal-table-cell dateheader\">Thu</th>\n";
	print "\t<th class=\"iftarcal-table-cell dateheader\">Fri</th>\n";
	print "\t<th class=\"iftarcal-table-cell dateheader\">Sat</th>\n";
	print "</tr>\n";
	
	print "<tr class=\"iftarcal-table-row\">\n";
	
	$dtstart = new DateTime ( $CONFIG ['ramadan_start_date'] );
	
	// print leading cols, if any
	$firstdate = date_format ( $dtstart, 'w');
	// iftarcal_log(E_USER_NOTICE, "leading columns for first day ($firstdate): " . ($firstdate % 7));
	for($j = 0; $j < ($firstdate % 7); $j ++) {
		print "\t<td class=\"iftarcal-table-cell\"></td>\n";
	}
	
	// connect to DB
	$dbconn = mysqli_connect ( $CONFIG ['dbserver'], $CONFIG ['dbuser'], $CONFIG ['dbpw'], $CONFIG ['dbname'] ) or die ( "Error connecting to database: " . mysqli_error ( $dbconn ) );
	
	if (! $dbconn) {
		iftarcal_log ( E_USER_ERROR, "Can't connect to db " . $CONFIG ['dbname'] . mysqli_connect_error () );
		die ();
	}
	
	// get a lock on the table
	$query = "LOCK TABLES " . $CONFIG ['tablename'] . " WRITE";
	if (mysqli_query ( $dbconn, $query ) === false) {
		iftarcal_log ( E_USER_ERROR, "Can't lock table " . $CONFIG ['tablename'] . mysqli_connect_error () );
	}
		
	// start printing calendar cells
	$currentmon = "";
	$currenthmon = "";
	$ramadandays = calcNumRamadanDays();
	// iftarcal_log(E_USER_NOTICE, "calcNumRamadanDays returned $ramadandays");
	
	$dtcurrent = new DateTime($CONFIG['ramadan_start_date']);
	// iftarcal_log(E_USER_NOTICE, "Starting printCalendarTable from date ". date_format($dtcurrent, 'Y-m-d'));
	for($i = 0; $i <= $ramadandays; $i++) {
		
		$day = date_format ( $dtcurrent, 'w');
		$mon = date_format ( $dtcurrent, 'M');
		$date = date_format ( $dtcurrent, 'j');
		$hmon = "Ramadan";
		
		$col = $day % 7;
		// iftarcal_log(E_USER_NOTICE, "current date: " . date_format($dtcurrent, 'Y-m-d') . " (d: $day m: $mon dt: $date)" . " col: $col");
		if ($col == 0) {
			// start a new table row
			print "</tr>\n";
			print "<tr class=\"iftarcal-table-row\">\n";
		}
		
		
		// create DB key
		$key = date_format ( $dtcurrent, 'Y-m-d');
		
		
		
		// figure out what the contents of the cell will be
		
		// what gregorian date to print
		if ($mon == $currentmon)
			$gregdate = "$date";
		else {
			$gregdate = "$mon $date";
			$currentmon = $mon;
		}
		// what hijri date to print
		if ($hmon == $currenthmon)
			$hijdate =  ($i + 1) ;
		else {
			$hijdate = "$hmon " . ($i + 1);
			$currenthmon = $hmon;
		}
		
		// figure out if this date is avail for reservation
		
		// check the DB
		$query = "SELECT * FROM " . $CONFIG['tablename'] . " WHERE date='$key'";
		$data = mysqli_query ($dbconn, $query);
		if (!$data) {
			iftarcal_log(E_USER_ERROR, "printCalendarEntry(): DB lookup failed for date: $key ... exiting");
			echo "Sorry, a database error occurred.  Please try again later.\n";
			return;
		}
		if (mysqli_num_rows($data) == 0) {
			// no row for this date
			iftarcal_log(E_USER_NOTICE, "No database entry for date: $key ... creating new row");
			// insert empty date
			if (!createNewDateRow ( $key, $dbconn )) {
				iftarcal_log(E_USER_ERROR, "Could not create new db entry for date $key");
			}
			$avail = $CONFIG['default_num_hosts']; // row was just created
		} else {
			// found existing entry for this date
				$row = mysqli_fetch_assoc($data);
				$avail = $CONFIG['default_num_hosts'] - $row ['numhosts'];
		}
		

		// check for special events -- no reservations on these dates
		if (array_key_exists ( $key, $events )) {
			print "\t<td iftarcal-table-cell\">\n";
			print "\t\t<p class=\"iftarcal-date\">";
			print $gregdate;
			print "<br><span class=\"iftarcal-hijridate\">";
			print $hijdate;
			print "</span></p>\n";
			
			print "\t\t<p class=";
			print $events [$key] ["class"];
			print ">";
			print $events [$key] ["label"];
			print "</p>\n";
			print "\t</td>\n";
			$dtcurrent->add(new DateInterval('P1D'));
			continue; // go to the next day
		}
		
		// now print the table cell contents
		// <td>
		//	<p> [gregdate] <br> <span> [hijridate] </span> </p>
		//	<input />
		//	<button> [avail] </button>
		// </td>
		
		// use a class to color cell based on availability
		if ($avail >= $CONFIG['default_num_hosts']) {
			print "\t<td class=\"success iftarcal-table-cell\">\n";
		}
		elseif ($avail < $CONFIG['default_num_hosts'] && $avail > 0) {
			print "\t<td class=\"warning iftarcal-table-cell\">\n";
		}
		else {
			print "\t<td class=\"danger iftarcal-table-cell\">\n";
		}
		print "\t\t<p class=\"iftarcal-date\">";
		print $gregdate;
		print "<br><span class=\"iftarcal-hijridate\">";
		print $hijdate;
		print "</span></p>\n";
		
		if ($avail > 0) {
			// print "\t<input type=\"hidden\" name=\"date\" value=\"$key\">\n";
			print "\t<button  type=\"submit\" name=\"date\" value=\"$key\" class=\"btn btn-primary btn-xs iftarcal-datebutton\">Available ($avail)</button>\n";
		}
		else {
			// day is fully assigned
			print "\t<p><a href=\"showassigned.php?date=$key\"  class=\"iftarcal-assigned\">Assigned</a></p>\n";
		}
		print "\t</td>\n";

		// increment date
		$dtcurrent->add(new DateInterval('P1D'));
	}
		
	// print eid cell and any padding to fill out the calendar (basically a special case of above code)
	// $dtcurrent->add(new DateInterval('P1D'));
	$day = date_format ( $dtcurrent, 'w');
	$mon = date_format ( $dtcurrent, 'M');
	$date = date_format ( $dtcurrent, 'j');
	$hmon = "Shawwal";
	
	$col = $day % 7;
	if ($col == 0) {
		// start a new table row
		print "</tr>\n";
		print "<tr class=\"iftarcal-table-row\">\n";
	}
	
	// now print the cell contents
	print "\t<td class=\"iftarcal-table-cell\">\n";
	print "\t<p class=\"iftarcal-date\">";
	if ($mon == $currentmon)
		print "$date";
	else {
		print "$mon $date";
		$currentmon = $mon;
	}
	print "<br><span class=\"iftarcal-hijridate\">";
	print "$hmon 1";
	print "</span>\n";
	print "\t<p class=\"iftarcal-eid\">";
	print "\tEid-ul-Fitr";
	print "</p>\n";
	print "\t</td>\n";
	
	// print trailing cols, if any
	$col = $day % 7;
	for($j = 0; $j < (7 - $col - 1); $j ++) {
		print "\t<td class=\"iftarcal-table-cell\"></td>\n";
	}
	
	print "</tr>\n</table></form>\n";
	
	// release table lock
	$query = "UNLOCK TABLES";
	if (! ($result = mysqli_query ($dbconn,$query))) {
		iftarcal_log ( E_USER_ERROR, "UNLOCK failed: " . mysqli_connect_error () );
	}
	
	
	mysqli_close($dbconn);

}

/******************************************************************/

function printDate () {
  $submit_array = array_keys ($_POST['date']);
  $date = $submit_array[0];
  echo "$date";
}

/******************************************************************/
function getRequestedDateKey () {
	$key = trim($_POST['date']);
	return $key;
}

/******************************************************************/

function printRequestedDate () {

	if (!$_POST) {
		$dt = new DateTime($_GET['date']);
	}
	else {
		$dt = new DateTime($_POST['date']);
	}
	
	echo date_format ($dt, 'l, F j, Y');
}	

/******************************************************************/
function printHosts ($key) {
	// returns a formatted HTML string with the hosts for the given date key.
	// includes CSS styles so caller may need to strip them if they are not wanted
	
	global $CONFIG;
	
	$entry = getEntryByKey($key);
	if (!$entry) {
		iftarcal_log ( E_USER_ERROR, "printHosts(): Failed to lookup entry for date: $key ..." );
		return false;
	}
	
	// found existing entry for this date
	$numhosts = $entry['numhosts'];
		
	if ($numhosts == 0) {
		$cohosts = "<p class=\"iftarhost-display-sm\">None</p>";
	}
	else {
		$cohosts = "";
		foreach ($entry['hosts'] as $host) {
			$cohosts .= "<p class=\"iftarhost-display-sm\">Co-host: " . $host['name'] . "</p>\n";
		}
		
	}
	
	iftarcal_log(E_USER_NOTICE, "printHosts(): date: $key, num: $numhosts, cohosts: $cohosts");
	
	
	return $cohosts;
	
}

/******************************************************************/

function printAssigned ($host, $key) {


  	print "<p class=\"reservetablefield\" align=\"center\"><strong>Host: " . $host['name'] . "</strong></p>";

}
	

/******************************************************************/

function printAllAssigned ($key) {
  global $CONFIG;

	$dt = new DateTime($key);
	
	// connect to DB
	$dbconn = mysqli_connect ( $CONFIG ['dbserver'], $CONFIG ['dbuser'], $CONFIG ['dbpw'], $CONFIG ['dbname'] ) or die ( "Error connecting to database: " . mysqli_error ( $dbconn ) );
	
	if (! $dbconn) {
		iftarcal_log ( E_USER_ERROR, "Can't connect to db " . $CONFIG ['dbname'] . mysqli_connect_error () );
		die ();
	}
	
	// get a lock on the table
	$query = "LOCK TABLES " . $CONFIG ['tablename'] . " READ";
	if (mysqli_query ( $dbconn, $query ) === false) {
		iftarcal_log ( E_USER_ERROR, "Can't lock table " . $CONFIG ['tablename'] . mysqli_connect_error () );
	}
	
	$query = "SELECT * FROM " . $CONFIG ['tablename'] . " WHERE date='$key'";
	$data = mysqli_query ( $dbconn, $query );
	if (mysqli_num_rows($data) == 0) {
		// no row for this date
		iftarcal_log ( E_USER_ERROR, "printHosts(): Failed to lookup entry for date: $key ..." );
		return;
	} else {
		// found existing entry for this date
		$row = mysqli_fetch_assoc ( $data );
		$numhosts = $row ['numhosts'];
		if ($numhosts > 0) {
			$hostarray = unserialize($row['hosts']);
		}
	}
	
	// print "<p class=\"iftarformsubheading\">Iftar on " . date_format ($dt, 'l, F j, Y') . " will insha Allah be hosted by</p>\n";
	
	$cohosts = "";
	foreach ($hostarray as $host) {
		$cohosts .= "<p class=\"iftarhost-display\">Co-host: " . $host['name'] . "</p>\n";
	}
		
		// release table lock
	$query = "UNLOCK TABLES";
	if (! ($result = mysqli_query ( $dbconn, $query ))) {
		iftarcal_log ( E_USER_ERROR, "UNLOCK failed: " . mysqli_connect_error () );
	}
	
	mysqli_free_result ( $data );
	mysqli_close ( $dbconn );
	
	return $cohosts;

}


/******************************************************************/
function printSignupInfo() {

	// creates and display an html file from a smarty template with information for hosts
	
	global $CONFIG;
	
	if (!file_exists($CONFIG['signupinfo_template'])) {
		iftarcal_log(E_USER_ERROR, "signupinfo template file not found");
		return ;
	}
	
	$smarty = new Smarty();
	
	$smarty->assign('expected_attendees', $CONFIG['expected_attendees']);
	$smarty->assign('total_donation', $CONFIG['donation_per_iftar']);
	
	echo $smarty->fetch($CONFIG['signupinfo_template']);
	
}

function getContactEmail() {
	global $CONFIG;
	
	return ($CONFIG['contact_email']);
}


/******************************************************************/
function reserve () {

	global $CONFIG;
	global $notifyoff, $notifications, $fromaddr, $generalmail;
	global $conf_email_fileA, $conf_email_fileC;
	global $max_slots, $calendarpath;

	/// perform some checks on the POST data
	if (!$_POST || !array_key_exists('check_submit', $_POST)) {
		echo "Invalid request";
		iftarcal_log(E_USER_ERROR, "reserve(): check_submit key not found -- invalid request. Exiting ...");
		exit();
	}
	
	if (isset($_POST['date'])) {
		$key = strip_tags(trim($_POST['date']));
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
		


	// connect to DB
	$dbconn = mysqli_connect ( $CONFIG ['dbserver'], $CONFIG ['dbuser'], $CONFIG ['dbpw'], $CONFIG ['dbname'] ) or die ( "Error connecting to database: " . mysqli_error ( $dbconn ) );
	if (! $dbconn) {
		iftarcal_log ( E_USER_ERROR, "Can't connect to db " . $CONFIG ['dbname'] . mysqli_connect_error () );
		die ();
	}
	
	// get a lock on the table
	$query = "LOCK TABLES " . $CONFIG ['tablename'] . " WRITE";
	if (mysqli_query ( $dbconn, $query ) === false) {
		iftarcal_log ( E_USER_ERROR, "Can't lock table " . $CONFIG ['tablename'] . mysqli_connect_error () );
	}
	
	$query = "SELECT * FROM " . $CONFIG ['tablename'] . " WHERE date='$key'";
	$data = mysqli_query ( $dbconn, $query );
	if (mysqli_num_rows($data) == 0) {
		// no row for this date
		iftarcal_log ( E_USER_ERROR, "reserve(): Failed to lookup entry for date: $key ..." );
		echo "Invalid date.  Please go back and try again or contact the administrators\n";
		return;
	} else {
		// found existing entry for this date
		$row = mysqli_fetch_assoc ( $data );
		$numhosts = $row ['numhosts'];
	}
	
	$hostarray = unserialize($row['hosts']);
	
	// a new reservation may be added if:
	//   there are less than 'default_num_hosts' already signed up
	//   TODO or the refid of an existing host is supplied
	
	if ($numhosts >= $CONFIG['default_num_hosts']) {
		iftarcal_log(E_USER_INFO, "reserve(): new signup when there are already $numhosts hosts");
		echo "Number of allowed hosts for this date has already been exceeded -- please choose another date";
		return;
	}
	
	// add the new host info
	$refid = uniqid();
	$dtts = new DateTime();
	$timestamp = date_format ($dtts, 'Y-m-d H:i:s');
	$host = array(
		'name' => $name,
		'email' => $email,
		'phone' => $phone,
		'refid' => $refid,
		'timestamp' => $timestamp
	);
	
	
	iftarcal_log(E_USER_NOTICE, "reserve(): processing signup for $name on $key ($email / $phone / $refid / $timestamp)");
	
	$hostarray[] = $host;
	
	$updated_hosts = serialize($hostarray);
	$numhosts++;
  
	$query = "UPDATE " . $CONFIG['tablename'] . " SET numhosts = '$numhosts', hosts = '$updated_hosts' WHERE date = '$key'";
	if (!mysqli_query ($dbconn, $query)) {
		// update failed
		iftarcal_log(E_USER_ERROR, "reserve(): database update for host failed: $name -- " . mysqli_error($dbconn) );
		return;
	}
	else {
		$success = true;
	}
	
	// release table lock
	$query = "UNLOCK TABLES";
	if (! ($result = mysqli_query ($dbconn,$query))) {
		iftarcal_log ( E_USER_ERROR, "UNLOCK failed: " . mysqli_connect_error () );
	}
	
	mysqli_close($dbconn);
	
	if ($success) {
		$dt = new DateTime($key);
		 // print confirmation
    	
      	echo "<p>Contact information:</p>", "\n";
      	echo "<p class=\"iftarcal-contactinfo\">$name<br>$phone<br>$email</p>";
      	echo "<p>You will receive an email confirmation and further information shortly.</p>";
      	echo "<p class=\"iftarformheading\">May Allah accept from you and reward you</p>\n";
	}

	if ($CONFIG['send_email_notifications']) {
   	    sendEmailConfirmation ($key, $host, $hostarray);
	}
	
/*		
			// send email notification to site owner
	    	$mailmsg = sprintf ("Successful signup for %s:\n%s\n%s\n%s\nRef: %s", date ('l, F j, Y', $date), $name, $phone, $email, $refid);
		    $mailto = $notifications;
		    $mailsubj = "Daily Iftar reservation " . date ('D, M j, Y', $date);
		    $mailfrom = "From: $fromaddr";
		    @mail ($mailto,$mailsubj,$mailmsg,$mailfrom);
		}
	}
 */
}
/******************************************************************/
function removeHost ($key, $refid)
{
	$hostentry = getEntryByKey($key);
	if (!$hostentry) {
		iftarcal_log(E_USER_ERROR, "removeHost(): Could not retrieve entry for date $key");
		return false;
	}
	if ($hostentry['numhosts'] == 0) {
		// this would be unexpected
		iftarcal_log(E_USER_WARNING, "removeHost(): No hosts to remove for date $key");
		return false;
	}
	
	$found = false;
	// find the index of the host with the given refid
	for ($i = 0; $i < count($hostentry['hosts']); $i++) {
		if ($hostentry['hosts'][$i]['refid'] == $refid) {
			// found the host -- remove from the array
			$found = true;
			array_splice($hostentry['hosts'], $i, 1);
			// decrement number of hosts
			$hostentry['numhosts'] -= 1;
			break;
		}	
	}

	if ($found) {
		if (!replaceEntryByKey($key, $hostentry)) {
			iftarcal_log(E_USER_ERROR, "removeHost(): could not replace host entry (refid: $refid date $key)");
			return false;
		}
		iftarcal_log(E_USER_NOTICE, "removeHost(): removed host with refid: $refid on date $key");
		return true;
		
	}
	else {
		iftarcal_log(E_USER_NOTICE, "removeHost(): did not find host with refid: $refid on date $key");
		return false;
	}
	
	
}

/******************************************************************/
function sendEmailConfirmation($key, $host, $hostarray) {
	
	global $CONFIG;
	
	$dt = new DateTime($key);
	$smarty = new Smarty();
	
	
	iftarcal_log(E_USER_NOTICE, "sendEmailConfirmation(): date: $key " . print_r($host,true) . print_r ($hostarray, true));
	$smarty->assign('host_name', $host['name']);
	$smarty->assign('hosting_date', date_format($dt, 'D, M j, Y'));
	$smarty->assign('masjid_name', $CONFIG['masjid_name']);
	$smarty->assign('contact_email', $CONFIG['contact_email']);
	$smarty->assign('iftar_hosts', $hostarray);
	$smarty->assign('expected_attendees', $CONFIG['expected_attendees']);
	$smarty->assign('total_donation', $CONFIG['donation_per_iftar']);
	
	
	$mailbody = $smarty->fetch($CONFIG['notification_template']);
	
	$mail = new PHPMailer;
	
	$mail->isSMTP();                        // Set mailer to use SMTP
	$mail->Host = $CONFIG['mailhost'];				// Specify server
	$mail->Port = 465;						// SMTP server port
	$mail->SMTPAuth = true;                 // Enable SMTP authentication
	$mail->Username = $CONFIG['mailusername'];        // SMTP username
	$mail->Password = $CONFIG['mailpw'];              // SMTP password
	$mail->SMTPSecure = 'ssl';              // Enable encryption, 'ssl' also accepted
	
	$mail->From = $CONFIG['mailfromaddr'];
	$mail->FromName = $CONFIG['mailfromname'];

	if (DEBUG == true) {
		$mail->addAddress($CONFIG['debugmail']);  // Add a recipient
	}
	else {
		$mail->addAddress($host['email'], $host['name']);  // Add a recipient
		if ($CONFIG['send_to_all_hosts']) {
			foreach ($hostarray as $h) {
				if ($h['email'] === $host['email'])
					continue;
				$mail->addCC($h['email'], $h['name']);
			}
		}
	}
	$mail->isHTML (true);
	$mail->Subject = "Iftar signup confirmation for $key";
	
	$mail->addEmbeddedImage("img/uwms_letterhead_announce.gif", "uwmsletterhead");
	
	$mail->Body = $mailbody;
	if(!$mail->send()) {
		iftarcal_log(E_USER_ERROR, "sendEmailNotification(): Message could not be sent: " . $mail->ErrorInfo);
		echo 'Email could not be sent.  Mailer Error: ' . $mail->ErrorInfo;
		exit;
	}
	else {
		iftarcal_log(E_USER_NOTICE, "sendEmailNotification(): Sent email notification to "  . $host['name'] . "(" . $host['email'] . ") for $key");
	}
	
}

/******************************************************************/

function printDateTaken ($datestr) {

  $date = (float) strtotime ($datestr);

  echo "<p class=\"iftarformsubheading\">Sorry, this iftar on " . date ('l, F j, Y', $date) . " is already assigned.<br> Please try another date insha Allah.</p>";
   
}


/******************************************************************/
function printError ($errstring) {

  echo "<h3>", $errstring, "</h3>\n";
  echo "<p>Please press the back button to try again</p>\n";

}

function printMysqlError() {
  $errstring = "DB Error " . mysql_errno() . " : " . mysql_error();
  echo "<p>A database error occurred.  Admins have been notified -- please try again later</p>\n";
  // printError ($errstring);
  notifyError ($errstring);
  exit;
}


function notifyError ($errstring) {
	global $err_notification_email, $err_fromaddr;
// send email notification
  $mailmsg = sprintf ("[%s]\nError:  %s\n", date('r'), $errstring);
  $mailto = $err_notification_email;
  $mailsubj = "Iftar application error";
  $mailfrom = $err_fromaddr;
  @mail ($mailto,$mailsubj,$mailmsg,$mailfrom);
}

/******************************************************************/
function iftarcal_log ($loglevel, $message) {
	global $CONFIG;
	
	
	switch ($loglevel) {
		case (E_USER_ERROR):
			$errlev = "ERROR ";
			break;
		case (E_USER_WARNING):
			$errlev = "WARNING ";
			break;
		case (E_USER_NOTICE):
			$errlev = "INFO ";
			break;
		default:
			$errlev = "INFO	";
			
	}

	if ($loglevel <= E_USER_ERROR) {
		error_log(date('[Y-m-d H:i e] ') . $errlev . $message . PHP_EOL, 3, $CONFIG['IFTARCAL_LOG_FILE']);
	}
	else {
		if (DEBUG) { // only print warnings and info if debugging is turned on
			error_log(date('[Y-m-d H:i e] ') . $errlev . $message . PHP_EOL, 3, $CONFIG['IFTARCAL_LOG_FILE']);
		}
	}

	if ($CONFIG['email_notify_errors']) {
		$mail = new PHPMailer;
		
		$mail->isSMTP();                        // Set mailer to use SMTP
		$mail->Host = $CONFIG['mailhost'];				// Specify server
		$mail->Port = $CONFIG['mailport'];						// SMTP server port
		$mail->SMTPAuth = true;                 // Enable SMTP authentication
		$mail->Username = $CONFIG['mailusername'];        // SMTP username
		$mail->Password = $CONFIG['mailpw'];              // SMTP password
		$mail->SMTPSecure = 'ssl';              // Enable encryption, 'ssl' also accepted
		
		$mail->From = $CONFIG['mailfromaddr'];
		$mail->FromName = $CONFIG['mailfromname'];
		
		$mail->addAddress($CONFIG['admin_email_notifications']);  // Add a recipient

		$mail->isHTML (false);
		$mail->Subject = "iftarcal error";
		
		$mailbody = date('[Y-m-d H:i e] ') . $errlev . $message . PHP_EOL;
		
		if(!$mail->send()) {
			error_log(date('[Y-m-d H:i e] ') . "ERROR " . $mail->ErrorInfo . PHP_EOL, 3, $CONFIG['IFTARCAL_LOG_FILE']);
		}
		
	}
}

?>

