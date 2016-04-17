<!DOCTYPE html>
<!--
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
-->
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
  			<a href="http://www.masjid.org"><img src="img/letterhead.png" alt="Muslim Society" /></a>
  		</div>
  		<div class="row">
   			<div class="col-sm-8">
   			<form class="form-horizontal" role="form" id="regform" action="reserve.php" method="POST">
				<input type="hidden" name="check_submit" value="check_submit">
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
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
