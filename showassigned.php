<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link href="iftarcal.css" rel="stylesheet" type="text/css">
<title>Iftar host information</title>
</head>
<body bgcolor="#FFFFCC">
  <div class="calframe">
 <?php 
	include "iftar.php";
        printAllAssigned($_GET['date']);
 ?>
 <p><a href="index.php">Back to Iftar Calendar</a></p>
 </div>
</body>
</html>
 