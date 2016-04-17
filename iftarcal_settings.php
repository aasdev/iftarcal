<?php
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

// set some defaults
date_default_timezone_set('America/New_York');


// main configuration

/******************************************************************/
// MAKE CONFIGURATION CHANGES BELOW THIS LINE
/******************************************************************/


$CONFIG = array (

		'IFTARCAL_LOG_FILE' => "./iftarcal.log",

		// DATABASE CONFIG

		'dbserver' => "localhost",
		'dbname' => "test",				// name of database
		'dbuser' => "root",
		'dbpw' => "",
		'tablename' => "iftarschedule",		// name of table

		////////////////
		// EMAIL CONFIG
		///////////////

		'mailhost' => 'smtp.mail.com',	// SMTP hostname
		'mailport' => 25,									// port for mailserver
		'mailusername' => 'user@mail.com',        // SMTP username
		'mailpw' => 'mailpw',                      // SMTP password

		'mailfromname' => 'FromName',  // name in From: field of email notifications
		'mailfromaddr' => 'fromaddr@mail.org',  // From: addr for email notifications

		// send notifications to those signing up
		'send_email_notifications' => true,

		// send email notifications when hosts are updated
		'send_email_on_update' => true,

		// notifies all hosts whenever there is a change (addition/deletion/update of a host)
		'send_to_all_hosts' => true,

		// email addr for administrator notifications, including error notifcations
		'admin_email_notifications' => 'adminuser@mail.com',

		// send email to admin for errors
		'email_notify_errors' => false,

		// contact email for questions, etc.
		'contact_email' => 'info@mail.com',

		// email addr for debugging messages
		'debugmail' => 'adminuser@mail.com',

		// confirmation email for families signing up -- implemented as a Smarty template
		'notification_template' => "templates/notification.tpl",

		// email template for update notifications - implemented as a Smarty template
		'update_template' => "templates/updateconf.tpl",

		// email template for removal notifications - implemented as a Smarty template
		'remove_template' => "templates/removeconf.tpl",

		'email_image_path' => "img/letterhead.png",

		/////////////////////
		// APPLICATION CONFIG
		/////////////////////

		// masjid name
		'masjid_name' => 'Muslim Society',

		// Ramadan start date
		'ramadan_start_date' => "2016-06-06",
		// Anticipated Eid date
		'eid_date' => "2016-07-06",

		// switch to disable the signup calendar
		'disable_signup' => false,

		// default number of host families per day
		'default_num_hosts' => 4,
		// maximum number of host families per day
		'max_num_hosts' => 4,

		// donation amount per iftar
		'donation_per_iftar' => 120,

		// expected number of attendees each night (for email)
		'expected_attendees' => 100,

		// special events file
		'events_file' => "events.dat",

		// file with information for hosts (html markup)
		'signupinfo_template' => "templates/signupinfo.tpl"


		);




