<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="iftarcal.css" rel="stylesheet" type="text/css">
<title>Iftar Signup Calendar</title>
</head>
<body bgcolor="#FAF0BF">

  <div class="calframe">
      <h2>Ramadan Daily Iftar Signup</h2>
	  
      <p><em>Earn the reward and blessing of providing iftar to your fasting brothers and sisters</em> ... </p>

      	  <?php
	  include "iftar.php";
	  readSpecialEvents();
	  printCalendarTable();
	  ?>
      <p>The Prophet (saaw) said,</p>
      <p><font size="+1"><em>&quot;He who provides a fasting person
        something with which to break his fast,<br>
        will earn the same reward as the one who was observing the fast,<br>
        without diminishing in any way the reward of the latter.'' </em></font>[Al-Tirmidhi]</p>
  </div>
</body>
</html>
