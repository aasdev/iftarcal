<!DOCTYPE html>
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
  			<a href="http://www.masjid.org"><img src="img/letterhead.png" alt="Muslim Society" /></a>
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
			  
