<?php
/******************************************************************
Copyright (C) 2012  Anees Shaikh

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

include_once("class.phpmailer5.2.php");
error_reporting(E_ALL);
/******************************************************************/
// MAKE CONFIGURATION CHANGES BELOW THIS LINE
/******************************************************************/

// Ramadan start date
$startyear = 2013;
$startmon = 7;
$startdate = 9;
$startts = mktime (12, 0, 0, $startmon, $startdate, $startyear);

// Anticipated number days in Ramadan (e.g., 29 or 30)
$ramadandays = 30;

// full path to home calendar page
// $calendarpath = "/iftar2011/test/index.php";

// Database information
// You must create the database (mySql is assumed) and table.
// Use the supplied iftarcal.sql file to create the table (using import function)

$dbname = "";				// name of database
$dbuser = "";
$dbpw = "";
$tablename = "";		// name of table -- same as in iftarcal.sql file if used to create the db

// Maximum number of families signing up per day -- calendar is designed for 2.
// Other options may require tweaking, e.g., the size of the calendar cells, singup form, etc.

// number of families per day -- this cannot be changed currently due to db schema
$max_slots = 2;	

$disable = false;		// use this switch to disable the signup calendar

$events_file = "events.dat";

// email addr for reservation notifications
$notifyoff = 0;  // use this switch to turn off email notifications to those signing up and admin
$notifications = "";
$fromaddr = "";
$generalmail = "";

// confirmation email for families signing up -- needs work to be generalized
$conf_email_fileA = "emailconf-top.html";
$conf_email_fileC = "emailconf-bottom.html";

// email configuration for sending error notifications from the program
$err_notification_email = "";
$err_fromaddr = "";

/******************************************************************/
// YOU NEED NOT CHANGE CODE BELOW THIS LINE
/******************************************************************/

$startdatestr = date ('Y-m-d', $startts);

$events = array();		// in-memory array of reservations

$cellwidth = 110;
$cellheight = 85;

/******************************************************************/

function readSpecialEvents () {

	global $events_file, $events;

	if (!file_exists($events_file)) {
		return;
	}

	$fp = @fopen ($events_file, "r+");

  	if (!$fp) {
	    notifyError ("readSpecialEvents: Cannot open data file for reading\n");
	    die ("Cannot open data file\n");
  	}

  	while (!feof($fp)) {
	    $line = trim(fgets($fp, 1024));
	    if ($line != "")	// skip blank lines
	    $file_contents[] = $line;
    }

    foreach ($file_contents as $line) {
    	$elements = explode ("\t", $line);
    	//print "$elements[0]\n$elements[1]\n$elements[2]\n";
    	$events["$elements[0]"] = array ("date"=>$elements[0], "label"=>$elements[1], "class"=>$elements[2]);
    }
}


/******************************************************************/

function checkTable () {
  global $startmon, $startdate, $startyear, $ramadandays;
  global $dbname, $dbuser, $dbpw, $tablename;

    // connect to DB
  if (!($connection = @mysql_pconnect("localhost","$dbuser","$dbpw"))) {
    notifyError ("DB connect failed\n");
    die ("Cannot connect to mySQL DB.  Please try again later.\n");
  }
  if (!(mysql_select_db($dbname))) {
    printMysqlError ();
  }
  print "db connection successful\n";

  // get a lock on the table

  $query = "LOCK TABLES $tablename WRITE";
  if (!($result = @mysql_query ($query, $connection))) {
    printMysqlError ();
  }


  if (!($result = @mysql_query ("SELECT COUNT(*) FROM $tablename"))) {
    notifyError ("DB row count query failed\n");
    printMysqlError ();
  }
  $row = mysql_fetch_row ($result);

  // how many rows in the DB?
  if ($row[0] == 0) {
    // populate table
    for ($i = 0; $i < 30; $i++) {
      $ts = mktime (13, 0, 0, $startmon, ($startdate + $i), $startyear);
      $key = date ('Y-m-d', $ts);
      $insertquery = "INSERT INTO $tablename VALUES ('$key',
      	    NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0)";
      print "$insertquery<br>\n";
      if ((@mysql_query ($insertquery, $connection)) && (@mysql_affected_rows() == 1)) {
	//  success
	echo "successful insert\n";
      }
      else {
	printMysqlError ();
      }
    }
  }

  mysql_free_result ($result);


  // release table lock
  $query = "UNLOCK TABLES";
  if (!($result = @mysql_query ($query, $connection))) {
    printMysqlError ();
  }
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
function createNewDateRow ($key,$connection) {
	
	global $tablename;
	
	$insertquery = "INSERT INTO $tablename VALUES ('$key',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0)";
	if (!((@mysql_query ($insertquery, $connection)) && (@mysql_affected_rows() == 1))) {
	      printMysqlError ();
	    }
}


/******************************************************************/

function printCalendarTable() {

  global $startts, $startmon, $startdate, $startyear, $ramadandays;
  global $cellwidth, $cellheight;
  global $tablename, $dbname, $dbuser, $dbpw;
  global $max_slots;
  global $disable;
  global $events;

  if ($disable == true) {
    print "<h2>Iftar signup calendar is temporarily unavailable</h2>\n";
    print "<p>Please check back later to see the calendar insha Allah</p>\n";
    return;
  }

  // print Calendar headings
  print "<form action=\"reservedate.php\" method=\"post\"><table width=\"";
  print $cellwidth*7;
  print "\" border=\"1\">\n";
  print "<tr>\n";
  print "\t<td width=\"$cellwidth\" class=\"dateheader\">Sun</td>\n";
  print "\t<td width=\"$cellwidth\" class=\"dateheader\">Mon</td>\n";
  print "\t<td width=\"$cellwidth\" class=\"dateheader\">Tue</td>\n";
  print "\t<td width=\"$cellwidth\" class=\"dateheader\">Wed</td>\n";
  print "\t<td width=\"$cellwidth\" class=\"dateheader\">Thu</td>\n";
  print "\t<td width=\"$cellwidth\" class=\"dateheader\">Fri</td>\n";
  print "\t<td width=\"$cellwidth\" class=\"dateheader\">Sat</td>\n";
  print "</tr>\n";

  print "<tr valign=\"top\">\n";

  // print leading cols, if any
  $firstdate = date ('w', $startts);
  for ($j = 0; $j < ($firstdate % 7); $j++) {
    print "\t<td width=\"$cellwidth\" height=\"$cellheight\">&nbsp;</td>\n";
  }


  # connect to DB
  if (!($connection = @mysql_pconnect("localhost","$dbuser","$dbpw"))) {
    notifyError ("DB connect failed\n");
    die ("Cannot connect to mySQL DB.  Please try again later.\n");
  }
  if (!(mysql_select_db("$dbname"))) {
    printMysqlError ();
  }

  $query = "LOCK TABLES $tablename WRITE";
  if (!($result = @mysql_query ($query, $connection))) {
    printMysqlError ();
  }



  // start printing calendar cells
  // readDataFile();

  $currentmon = "";
  $currenthmon = "";
  for ($i = 0; $i < $ramadandays; $i++) {
    $ts = mktime (13, 0, 0, $startmon, ($startdate + $i), $startyear);
    $day = date ('w', $ts);
    $mon = date ('M',$ts);
    $date = date ('j',$ts);
    $hmon = "Ramadan";

    // echo "index: $i (",$ts,") mon: $mon  date: $date day: $day\n";

    $col = $day % 7;
    if ($col == 0) {
      // start a new table row
      print "</tr>\n";
      print "<tr valign=\"top\">\n";
    }

    // now print the cell contents
    print "\t<td width=\"$cellwidth\" height=\"$cellheight\">\n";
    print "\t<p class=\"date\">";
    if ($mon == $currentmon)
      print "$date";
    else {
      print "$mon $date";
      $currentmon = $mon;
    }
    print "<br><span class=\"hijridate\">";
    if ($hmon == $currenthmon)
      print ($i+1);
    else {
      echo "$hmon ", ($i+1);
      $currenthmon = $hmon;
    }
    print "</span></p>\n";

    // create DB key
    $key = date ('Y-m-d', $ts);

	// check for special events

	if (array_key_exists($key,$events)) {

		print "\t<p class=";
		print $events[$key]["class"];
		print ">";
		print $events[$key]["label"];
		print "</p>\n";
	}
	else {
	  // check the DB
	  if (!($result = mysql_query ("SELECT * FROM $tablename WHERE date='$key'", $connection)))
	    printMysqlError();
	  if (!($row = mysql_fetch_array($result))) {
	    // no matching row
	 	// echo "No such date: $key\n";
	    // insert empty date
	    createNewDateRow($key, $connection);
	    $avail = $max_slots;	 // row was just created
	 	print "\t<p align=\"center\"> <input name=\"wantdate[$key]\" type=\"submit\" class=\"datebutton\" value=\"Available($avail)\"></p>\n";
	  }
	  else {
	    if ($row["numassigned"] < 2) {
	      // day is available
	      $avail = $max_slots - $row["numassigned"];
	      print "\t<p align=\"center\"> <input name=\"wantdate[$key]\" type=\"submit\" class=\"datebutton\" value=\"Available($avail)\"></p>\n";
	    }
	    else {
	      // day is fully assigned
	      print "\t<p align=\"center\"> <a class=\"assigned\" href=\"showassigned.php?date=$key\">Assigned</a></p>\n";
	    }
	  }
	}
    print "\t</td>\n";
  }



  // print eid cell and any padding to fill out the calendar  (basically a special case of above code)

  $ts = mktime (13, 0, 0, $startmon, $startdate + ($i), $startyear);
  $day = date ('w', $ts);
  $mon = date ('M',$ts);
  $date = date ('j',$ts);
  $hmon = "Shawwal";

  $col = $day % 7;
  if ($col == 0) {
    // start a new table row
    print "</tr>\n";
    print "<tr valign=\"top\">\n";
  }

  // now print the cell contents
  print "\t<td width=\"$cellwidth\" height=\"$cellheight\">\n";
  print "\t<p class=\"date\">";
  if ($mon == $currentmon)
    print "$date";
  else {
    print "$mon $date";
    $currentmon = $mon;
  }
  print "<br><span class=\"hijridate\">";
  print "$hmon 1";
  print "</span>\n";
  print "\t<p class=\"eid\">";
  print "\tEid-ul-Fitr";
  print "</p>\n";
  print "\t</td>\n";

  // print trailing cols, if any
  $col = $day % 7;
  for ($j = 0; $j < (7- $col - 1); $j++) {
    print "\t<td width=\"$cellwidth\" height=\"$cellheight\">&nbsp;</td>\n";
  }

  print "</tr>\n</table></form>\n";

  $query = "UNLOCK TABLES";
  if (!($result = @mysql_query ($query, $connection))) {
    printMysqlError ();
  }


}

/******************************************************************/

function printDate () {
  $submit_array = array_keys ($_POST['wantdate']);
  $date = $submit_array[0];
  echo "$date";
}


function printRequestedDate () {
  $submit_array = array_keys ($_POST['wantdate']);
  $datestr = $submit_array[0];
  $date = (float) strtotime ($datestr);
  // echo strftime ('%A, %m/%d/%Y', $date);
  echo date ('l, n/j/Y', $date);
}

/******************************************************************/

function printAssigned ($date,$familynum) {
  global $tablename, $dbname, $dbuser, $dbpw;

  // connect to DB
  if (!($connection = @mysql_pconnect("localhost","$dbuser","$dbpw"))) {
    notifyError ("DB connect failed\n");
    die ("Cannot connect to mySQL DB.  Please try again later.\n");
  }
  if (!(mysql_select_db("$dbname"))) {
    printMysqlError ();
  }
  $query = "LOCK TABLES $tablename WRITE";
  if (!($result = @mysql_query ($query, $connection))) {
    printMysqlError ();
  }

  if (!($result = @mysql_query ("SELECT * FROM $tablename WHERE date='$date'", $connection)))
    printMysqlError();
  if (!($row = mysql_fetch_array($result))) {
    echo "Invalid date.  Please go back and try again\n";
    return;
  }

  print "<p class=\"reservetablefield\" align=\"center\"><strong>Host $familynum: " . $row["name$familynum"] . "</strong></p>";


  $query = "UNLOCK TABLES";
  if (!($result = @mysql_query ($query, $connection))) {
    printMysqlError ();
  }
}

/******************************************************************/

function printAllAssigned ($key) {
  global $tablename, $dbname, $dbuser, $dbpw;

	$date = (float) strtotime ($key);
  // connect to DB
  if (!($connection = @mysql_pconnect("localhost","$dbuser","$dbpw"))) {
    notifyError ("DB connect failed\n");
    die ("Cannot connect to mySQL DB.  Please try again later.\n");
  }
  if (!(mysql_select_db("$dbname"))) {
    printMysqlError ();
  }
  $query = "LOCK TABLES $tablename WRITE";
  if (!($result = @mysql_query ($query, $connection))) {
    printMysqlError ();
  }

  if (!($result = @mysql_query ("SELECT * FROM $tablename WHERE date='$key' AND name1 IS NOT NULL AND name2 IS NOT NULL", $connection)))
    printMysqlError();
  if (!($row = mysql_fetch_array($result))) {
    echo "Invalid date.  Please go back and try again\n";
    return;
  }

	print "<p class=\"iftarformsubheading\">Iftar on " . date ('l, F j, Y', $date) . " will insha Allah be hosted by</p>\n";
  for ($i = 1; $i <= 2; $i++) {
    print "<p class=\"reservetablefield\" align=\"center\"><strong>Host $i: " . $row["name$i"] . "</strong></p>";
  }


  $query = "UNLOCK TABLES";
  if (!($result = @mysql_query ($query, $connection))) {
    printMysqlError ();
  }
}


/******************************************************************/
function printReservationForm () {

  global $tablename, $dbname, $dbuser, $dbpw;
  global $max_slots;

  $submit_array = array_keys ($_POST['wantdate']);
  $key = $submit_array[0];
  $date = (float) strtotime ($key);

  // connect to DB
  if (!($connection = @mysql_pconnect("localhost","$dbuser","$dbpw"))) {
    notifyError ("DB connect failed\n");
    die ("Cannot connect to mySQL DB.  Please try again later.\n");
  }
  if (!(mysql_select_db("$dbname"))) {
    printMysqlError ();
  }
  $query = "LOCK TABLES $tablename WRITE";
  if (!($result =@mysql_query ($query, $connection))) {
    printMysqlError ();
  }

  if (!($result = @mysql_query ("SELECT * FROM $tablename WHERE date='$key'", $connection)))
    printMysqlError();
  if (!($row = mysql_fetch_array($result))) {
    echo "Invalid date.  Please go back and try again\n";
    return;
  }

  //  any families already signed up -- if yes, show
  $numassigned = $row["numassigned"];
  if ($numassigned == $max_slots) {	// date is taken
  	printDateTaken($key);
	return;
  }
  print "<p class=\"iftarformheading\">Iftar signup for " . date ('l, F j, Y', $date) . "</p>\n";
  if ( $numassigned > 0) {
    if ($row['name1'] != "" ) {
    	printAssigned ($key,1);
    }
    else {
    	printAssigned ($key,2);
    }
  }
  
  if ($numassigned == 0) {
	  $openslot = 1;
  }
  else {
  	$openslot = 2;
  }
  // print the reservation form
  print "<p class=\"iftarformsubheading\">You are signing up as host $openslot of $max_slots</p>\n";
  // <form name="reservation" method="post" action="reserve.php" >
  print "<form name=\"reservation\" method=\"post\" action=\"reserve.php\" onsubmit=\"var result = checkReservationForm(this); return result;\">\n";
  print "<input name=\"date\" type=\"hidden\" id=\"date\" value=\"$key\">\n";
  print "<p class=\"reservetablefield\">Full name: <input name=\"name\" type=\"text\" id=\"name\" onBlur=\"validateName (this, document.getElementById('name_help'))\" size=\"50\"> <span id=\"name_help\" class=\"helptext\"></span></p>\n";
  print "<p class=\"reservetablefield\">Email: <input name=\"email\" type=\"text\" id=\"email\" onBlur=\"validateEmail (this, getElementById('email_help'))\" size=\"25\"> <span id=\"email_help\" class=\"helptext\"></span></p>\n";
  print "<p class=\"reservetablefield\">Phone: <input name=\"phone\" type=\"text\" id=\"phone\" onBlur=\"validatePhone (this, getElementById('phone_help'))\" size=\"12\"> <span id=\"phone_help\" class=\"helptext\"></span></p>\n";
  print "<input type=\"submit\" name=\"Submit\" value=\"Submit\">";
  print "</form>\n";
  // print "<p>Information submitted here will be used only for coordinating the iftar program</p>\n";

  // release table lock
  $query = "UNLOCK TABLES";
  if (!($result = @mysql_query ($query, $connection))) {
    printMysqlError ();
  }

}
/******************************************************************/
function removeWhitespace ($string) {
	$string = preg_replace('/\s+/', ' ', $string);
	$string = trim ($string);
	return ($string);
}

/******************************************************************/
function validateForm ($familynum) {

  $name = trim ($_POST["name$familynum"]);
  $rawphone = ($_POST["phone$familynum"]);
  $email = trim ($_POST["email$familynum"]);

  if ($name == "") {
    printError ("Please enter your first and last name");
    exit;
  }

  if (VALIDATE_USPHONE($rawphone) == false) {
    printError ("Please enter your area code and phone number as XXX-XXX-XXXX");
    exit;
  }

  if (!eregi ("^([a-z0-9_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,4}$", $email)) {
    printError ("Please enter a valid email address");
    exit;
  }

  return array ($name, $rawphone, $email);
}

/******************************************************************/
function reserve () {

	global $tablename, $dbname, $dbuser, $dbpw;
	global $notifyoff, $notifications, $fromaddr, $generalmail;
	global $conf_email_fileA, $conf_email_fileC;
	global $max_slots, $calendarpath;


  // process the reservation

  $key = $_POST['date'];
  $date = (float) strtotime ($key);
  $name = removeWhitespace($_POST['name']);
  $email = removeWhitespace($_POST['email']);
  $phone = removeWhitespace($_POST['phone']);
  $refid = uniqid();
  

    // connect to DB
  if (!($connection = @mysql_pconnect("localhost","$dbuser","$dbpw"))) {
    notifyError ("DB connect failed\n");
    die ("Cannot connect to mySQL DB.  Please try again later.\n");
  }
  if (!(mysql_select_db("$dbname"))) {
    printMysqlError ();
  }
  $query = "LOCK TABLES $tablename WRITE";
  if (!($result = @mysql_query ($query, $connection))) {
    printMysqlError ();
  }

	// which slot is being signed up for
	if (!($result = @mysql_query ("SELECT * FROM $tablename WHERE date='$key'", $connection)))
		printMysqlError();

	if (!($row = mysql_fetch_array($result)))
		printMysqlError ();
	
	
	if ($row['numassigned'] == $max_slots) {
		// date/slot was taken
		printDateTaken ($key);
		$success = 0;
	}
	elseif ($row["name1"] == "") {
		// slot 1 empty
		$query = "UPDATE $tablename SET " .
			"name1 =\"" . $name . "\"," .
			"phone1 =\"" . $phone . "\"," .
			"email1 =\"" . $email . "\"," .
			"refid1 =\"" . $refid . "\"," .		
			"numassigned = numassigned + 1 " .
			"WHERE date='$key'";
      	if (!($result = @mysql_query ($query, $connection)))
			printMysqlError();
		else
			$success = 1;
	}
	else {
		// slot 2 empty
		$query = "UPDATE $tablename SET " .
			"name2 =\"" . $name . "\"," .
			"phone2 =\"" . $phone . "\"," .
			"email2 =\"" . $email . "\"," .
			"refid2 =\"" . $refid . "\"," .		
			"numassigned = numassigned + 1 " .
			"WHERE date='$key'";
      	if (!($result = @mysql_query ($query, $connection)))
			printMysqlError();
		else
			$success = 1;
	}
	
	// release table lock
	$query = "UNLOCK TABLES";
	if (!($result = @mysql_query ($query, $connection))) {
		printMysqlError ();
	}
	
	if ($success == 1) {
		 // print confirmation
    	echo "<p class=\"iftarformsubheading\">You have successfully signed up to provide Iftar on<br>",date ('l, F j, Y', $date), "</p>\n";
      	echo "<p class=\"iftarcontactinfo\">Contact information:<br>", "\n";
      	echo "$name<br>$phone<br>$email<br>";
      	echo "You will receive an email confirmation and further information shortly.</p>";
      	echo "<p class=\"iftarformheading\">May Allah accept from you and reward you</p>\n";

     
		if (!$notifyoff) {
	    	sendEmailConfirmation ($key);
	
		
			// send email notification to site owner
	    	$mailmsg = sprintf ("Successful signup for %s:\n%s\n%s\n%s\nRef: %s", date ('l, F j, Y', $date), $name, $phone, $email, $refid);
		    $mailto = $notifications;
		    $mailsubj = "Daily Iftar reservation " . date ('D, M j, Y', $date);
		    $mailfrom = "From: $fromaddr";
		    @mail ($mailto,$mailsubj,$mailmsg,$mailfrom);
		}
	}

}


/******************************************************************/
function printDateTaken ($datestr) {
	global $calendarpath;

  $date = (float) strtotime ($datestr);

  echo "<p class=\"iftarformsubheading\">Sorry, this iftar on " . date ('l, F j, Y', $date) . " is already assigned.<br> Please try another date insha Allah.</p>";
   
}

/******************************************************************/
function printAssignmentTable () {

  global $dbname, $tablename, $dbuser, $dbpw;

  $namewidth = 400;
  $datewidth = 200;
  $phonewidth = 200;
  $emailwidth = 200;
  $cellheight = 50;


  // print table headers

  print "<table width=\"100%\" border=\"1\">\n";

  print "<tr valign=\"top\">\n";
  print "\t<td width=\"$datewidth\" height=\"$cellheight\" class=\"assigntable\">Date</td>\n";
  print "\t<td width=\"$namewidth\" height=\"$cellheight\" class=\"assigntable\">Name</td>\n";
  print "\t<td width=\"$phonewidth\" height=\"$cellheight\" class=\"assigntable\">Phone</td>\n";
  print "\t<td width=\"$emailwidth\" height=\"$cellheight\" class=\"assigntable\">Email</td>\n";
  print "</tr>\n";

  // connect to DB
  if (!($connection = @mysql_pconnect("localhost","$dbuser","$dbpw"))) {
    notifyError ("DB connect failed\n");
    die ("Cannot connect to mySQL DB.  Please try again later.\n");
  }
  if (!(mysql_select_db("$dbname"))) {
    printMysqlError ();
  }
  $query = "LOCK TABLES $tablename READ";
  if (!($result = @mysql_query ($query, $connection))) {
    printMysqlError ();
  }

  // get rows for all dates

  if (!($result = @mysql_query ("SELECT * FROM $tablename ORDER BY date ASC", $connection)))
      printMysqlError();

  while ($row = mysql_fetch_array($result)) {
      print "<tr valign=\"top\">\n";
      print "\t<td rowspan=\"2\" width=\"$datewidth\" height=\"$cellheight\" class=\"assigntable\">" . $row["date"] . "</td>\n";

      if ($row["name1"]=="") {
	// assume this means that this slot is blank
	print "\t<td width=\"$namewidth\" height=\"$cellheight\" class=\"assigntable\">" . "&nbsp</td>\n";
	print "\t<td width=\"$phonewidth\" height=\"$cellheight\" class=\"assigntable\">" . "&nbsp</td>\n";
	print "\t<td width=\"$emailwidth\" height=\"$cellheight\" class=\"assigntable\">" . "&nbsp</td>\n";
      }
      else {
	print "\t<td width=\"$namewidth\" height=\"$cellheight\" class=\"assigntable\">" . $row["name1"] . "</td>\n";
	print "\t<td width=\"$phonewidth\" height=\"$cellheight\" class=\"assigntable\">" . $row["phone1"] . "</td>\n";
	print "\t<td width=\"$emailwidth\" height=\"$cellheight\" class=\"assigntable\">" . $row["email1"] . "</td>\n";
      }
      print "</tr>\n";
      if ($row["name2"]=="") {
	print "\t<td width=\"$namewidth\" height=\"$cellheight\" class=\"assigntable\">" . "&nbsp</td>\n";
	print "\t<td width=\"$phonewidth\" height=\"$cellheight\" class=\"assigntable\">" . "&nbsp</td>\n";
	print "\t<td width=\"$emailwidth\" height=\"$cellheight\" class=\"assigntable\">" . "&nbsp</td>\n";

      }
      else {
	print "\t<td width=\"$namewidth\" height=\"$cellheight\" class=\"assigntable\">" . $row["name2"] . "</td>\n";
	print "\t<td width=\"$phonewidth\" height=\"$cellheight\" class=\"assigntable\">" . $row["phone2"] . "</td>\n";
	print "\t<td width=\"$emailwidth\" height=\"$cellheight\" class=\"assigntable\">" . $row["email2"] . "</td>\n";
      }
      print "</tr>\n";
  }

  print "</table>";

  // release table lock
  $query = "UNLOCK TABLES";
  if (!($result = @mysql_query ($query, $connection))) {
    printMysqlError ();
  }

}

/******************************************************************/
function sendEmailConfirmation ($key) {

	global $tablename, $dbname, $dbuser, $dbpw;
	global $notifications, $fromaddr, $generalmail;
	global $conf_email_fileA, $conf_email_fileC;
	global $max_slots;
	
	$date = (float) strtotime ($key);
	
	if (!($connection = @mysql_pconnect("localhost","$dbuser","$dbpw"))) {
    	notifyError ("DB connect failed\n");
  	}
  	if (!(mysql_select_db("$dbname"))) {
    	printMysqlError ();
  	}
	
	$query = "LOCK TABLES $tablename WRITE";
	if (!($result = @mysql_query ($query, $connection))) {
    	printMysqlError ();
	}
	
	if (!($result = @mysql_query ("SELECT * FROM $tablename WHERE date='$key'", $connection)))
		printMysqlError();

	if (!($row = mysql_fetch_array($result)))
		printMysqlError ();

	$query = "UNLOCK TABLES";
	if (!($result = @mysql_query ($query, $connection))) {
		printMysqlError ();
	}
	
	$numassigned = $row['numassigned'];
	$firstassigned = $secondassigned = false;
	if (!is_null($row['name1'])) {
		$firstassigned = true;
		$name1 = $row['name1'];
		$email1 = $row['email1'];
		$phone1 = $row['phone1'];
	}
	if (!is_null ($row['name2'])) {
		$secondassigned = true;
		$name2 = $row['name2'];
		$email2 = $row['email2'];
		$phone2 = $row['phone2'];
	}
		
	// send the confirmation mail
	$mail = new PHPMailer;
	$mail->ClearAddresses();
	
	if ($firstassigned) {
		$mail->AddAddress("$email1", "$name1");
		$refid = $row['refid1'];		
	}
	if ($secondassigned) {
		$mail->AddAddress("$email2", "$name2");
		$refid = $row['refid1'];		
	}
	$mail->From = "$fromaddr";
	$mail->FromName = 'Masjid';
	$mail->Subject = "Iftar reservation for " . date ('D, n/j', $date);
	
	// construct mail body
	$htmltop = $mail->getFile ($conf_email_fileA);
	$htmlbottom = $mail->getFile ($conf_email_fileC);
	$html =$htmltop . "Hosts for <font color=\"#660000\"><b>" . date ('l, F j, Y', $date) . "</b></font><br><br>";
	if ($firstassigned) {
		$html .= "<b>" . $name1 . "</b>  (contact info:  tel: " . $phone1 . ", email: " . $email1 . ")<br>";
	}
	if ($secondassigned) {
		$html .= "<b>" . $name2 . "</b>  (contact info:  tel: " . $phone2 . ", email: " . $email2 . ")<br>";
	}
	$html .= "<br>";
	if ($numassigned > 1) {
		$html .= "If you have not already done so, please contact your co-host to coordinate as soon as possible.<br>";
	}
	$html .= "<br>";
	$html .= "Reference id for your reservation is:  $refid<br>";
	$html .= $htmlbottom;
	
	$mail->IsHTML (true);
	$mail->Body = $html;
	if (!$mail->Send()) {
		$errmsg = "Error sending mail for date: " . $key . "\n" . $mail->ErrorInfo;
		notifyError($errmsg);
	}
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


////////////////////////////////////////
//
// PHP function to validate US phone number:
// (c) 2003 Peter Kionga-Kamau,
// http://www.pmkmedia.com
// No restrictions have been placed on
// the use of this code
//
// Updated Friday Jan 9 2004 to optionally ignore
// the area code:
//
// Input: a single string parameter and an
//  optional boolean variable (default=true)
// Output: 10 digit telephone number
// or boolean false(0)
//
// The function will return the numerical part
// of the alphanumeric string parameter with
// the following sequence of characters:
// any number of spaces [optional], a single
// open parentheses [optional], any number of
// spaces [optional], 3 digits (area
// code), any number of spaces [optional], a
// single close parentheses [optional], a single
// dash [optional], any number of spaces
// [optional], 3 digits, any number of spaces
// [optional], a single dash [optional], any
// number of spaces [optional], 4 digits, any
// number of spaces [optional]:
//
////////////////////////////////////////
function VALIDATE_USPHONE($phonenumber,$useareacode=true)
{
if ( preg_match("/^[ ]*[(]{0,1}[ ]*[0-9]{3,3}[ ]*[)]{0,1}[-]{0,1}[ ]*[0-9]{3,3}[ ]*[-]{0,1}[ ]*[0-9]{4,4}[ ]*$/",$phonenumber) || (preg_match("/^[ ]*[0-9]{3,3}[ ]*[-]{0,1}[ ]*[0-9]{4,4}[ ]*$/",$phonenumber) && !$useareacode)) return eregi_replace("[^0-9]", "", $phonenumber);
return false;
}


?>

