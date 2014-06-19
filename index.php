<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iftar Hosting Signup Calendar</title>

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
    
		      <h2>Ramadan Daily Iftar Signup</h2>
			  
		      <p><em>Earn the reward and blessing of providing iftar to your fasting brothers and sisters</em> ... </p>
		
		     <?php
			  include "iftar.php";
			  readSpecialEvents();
			  printCalendarTable();
			  ?>
			</div>
			<div class="col-sm-3" style="padding-top: 200px;">
		      <p>The Prophet (saaw) said,</p>
		      <p><font size="+1"><em>&quot;He who provides a fasting person
		        something with which to break his fast,
		        will earn the same reward as the one who was observing the fast,
		        without diminishing in any way the reward of the latter.&quot </em></font>[Al-Tirmidhi]</p>
		  	</div>
		</div>
	</div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="bs/js/bootstrap.min.js"></script>
  </body>
</html>