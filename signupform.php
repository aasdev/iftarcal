<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iftar Reservation Form</title>

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
   			<div class="col-sm-8">
   			<form class="form-horizontal" role="form" id="signupform" action="reserve.php" method="POST">
				<input type="hidden" name="check_submit" value="check_submit">
				<input type="hidden" name="date" value="<?php include_once "iftar.php";echo trim(getRequestedDateKey());?>">
					<div class="col-sm-offset-3">
						<h4 class="form-control-static uwms-form-section">Host signup for <?php include_once "iftar.php"; printRequestedDate(); ?></h4>
					</div>						
					<div class="form-group">
					</div>
					<div class="form-group">
  						<label class="col-sm-3 control-label" for="nameInput">Full name:</label>
  						<div class="col-sm-5">
  							<input class="form-control input-sm" type="text" name="name" value="" id="nameInput">
  						</div>
  					</div>
  					<div class="form-group">
  						<label class="col-sm-3 control-label" for="emailInput">Email</label>
  						<div class="col-sm-5">
  							<input class="form-control input-sm" type="email" name="email" value="" id="emailInput">
  						</div>
  					</div>
  					<div class="form-group">
  						<label class="col-sm-3 control-label" for="phoneInput">Telephone</label>
  						<div class="col-sm-5">
  							<input class="form-control input-sm" type="tel" name="phone" value="" id="phoneInput">
  						</div>
  					</div>
  					<div class="form-group">
  						<div class="col-sm-offset-3 col-sm-5">
  							<button type="submit" class="btn btn-primary btn-sm">Submit</button>
  						</div>
  					</div> 					
			</form>
   			</div>
   			<div class="col-sm-4">
   			<p class="iftarhost-display-sm">Current hosts for <?php include_once "iftar.php"; printRequestedDate(); ?></p>
   			<?php
   			include_once "iftar.php"; echo (printHosts(getRequestedDateKey()));
   			$key = getRequestedDateKey();
   			$entry = getEntryByKey($key);
   			$numhosts = $entry['numhosts'];
   			if ($numhosts > 0) {
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
 			<div class="col-sm-offset-2 col-sm-4">
 				<p>Up to <?php include_once "iftar.php"; echo ($CONFIG['default_num_hosts']);?> hosts may sign up for each day.  
 				If you wish to host a full day, please sign up for all <?php include_once "iftar.php"; echo ($CONFIG['default_num_hosts']);?> slots.</p>
			</div>
		</div>
   		<div class="row">
 			<div class="col-sm-5">
 				<p><a href="index.php">Back to Iftar Calendar</a></p>
			</div>
		</div>
   		<div class="row" id="signupinfo">
   			<div class="col-sm-8">
   				<?php
   					include_once "iftar.php";
   					printSignupInfo();
   				?>
   			</div>
   		</div>
  </div>

	
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="bs/js/bootstrap.min.js"></script>
    <script src="js/jquery.validate.min.js"></script>
   	<script src="js/additional-methods.min.js"></script>
    <script src="js/signup.js"></script>
</body>
</html>
