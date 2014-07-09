<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iftar Hosts</title>

    <!-- Bootstrap -->
    <link href="bs/css/bootstrap.min.css" rel="stylesheet">
    <!-- link other styles -->
    <link href="iftarcal.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  <div class="container">
  		<div class="header">
  			<a href="http://www.uwms.org"><img src="img/uwms_letterhead_announce.gif" alt="Upper Westchester Muslim Society" /></a>
  		</div>
  		<div class="row">
   			<div class="col-sm-8 col-sm-offset-1">
   				<h4>Iftar on <?php include_once "iftar.php"; printRequestedDate()?> will insha Allah be hosted by</h4>
   			</div>
   		</div>
   		<div class="row">
   			<div class="col-sm-7 col-sm-offset-1">
    	    	<?php 
					include_once "iftar.php";
        			echo printAllAssigned($_GET['date']);
 				?>
 			</div>
 			<div class="col-sm-2">
 				<?php
   					if (getEntryByKey(getRequestedDateKey())['numhosts'] > 0) {
						echo "<p>";
   						echo "<a href=\"edithosts.php?date=" . getRequestedDateKey() . "\">";
   						echo "<span class=\"iftarcal-edit-button\"><span class=\"glyphicon glyphicon-edit\"></span></span>";
   						echo "</a>";
   						echo "</p>";
   					}
   				?>
 			</div>
 		</div>
 		<div class="row">
 			<div class="col-sm-5">
 				<p><a href="index.php">Back to Iftar Calendar</a></p>
			</div>
		</div>
</div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="bs/js/bootstrap.min.js"></script>
  </body>
</html>
			  
