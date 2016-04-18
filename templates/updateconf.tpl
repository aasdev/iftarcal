<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!--
Copyright 2016 Anees Shaikh

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
-->
<html>
<head>
<meta http-equiv="Content-Type" content="text/html">
<title></title>
</head>
<body>
<img src="cid:letterhead" height="84" width="800">

<p>AsSalamuAlaikum,</p>

<p>Dear {$host_name},</p>

<p>
This is a confirmation that your iftar hosting contact info has been successfully updated.
</p>

<p>Hosts for {$hosting_date}:</p>

<p>
<!-- assume an array hosts with variables name, phone, email -->
{foreach $iftar_hosts as $host}
	<b>{$host.name}</b>  (contact info:  phone: {$host.phone}, email: {$host.email})<br>
{/foreach}
</p>

<hr />
<p>
<u><b>Number of attendees</b></u><br>
We expect roughly {$expected_attendees} attendees each day insha Allah.  Please keep
in mind other factors that affect the number
attendees, such as weather conditions, and how many friends and family
you may have invited.
</p>

<p>
<u><b>Donation for cleaning</b></u><br>
We ask that hosting families contribute a total of ${$total_donation} towards the masjid fund
to help cover the costs of cleaning and supplies.  Please give your 
donation to the iftar coordinator present who will record it.
</p>

<u><b>Responsibilities of the Hosts</b></u><br>
Please plan to arrive 45 minutes early to set up the tables with the food and plates, cups, napkins, utensils, etc.  
Please provide dates and water for breaking the fast. Note that the masjid does not have a heating oven.<br>
You are expected to be present for the duration of the iftar.  At the end of the iftar, please:
<ul>
<li>Remove left over food from tables</li>
<li>Clear and clean the tables</li>
<li>Wash all utensils, serving spoons, trays etc. that you may have used from the kitchen</li>
<li>Leave the kitchen tidy and clean</li>
<li>Remove all of the left over food -- do not leave it in the kitchen or the refrigerator and please do not discard it.</li>
<li>Remove all garbage to the dumpster outside</li>
</ul>

<p>
  <u><b>Further information</b></u><br>
  Should you require any further information, please contact us at {$contact_email}<br>
</p>

</body>
</html>