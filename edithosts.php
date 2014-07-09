<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit hosts</title>

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
  			<div class="col-sm-8 col-sm-offset-3">
  				<p class="iftarcal-host-display">Edit hosts for <?php include_once "iftar.php"; printRequestedDate(); ?></p>
  			</div>
  		</div>
  		<div class="row">
   			<div class="col-sm-8 col-sm-offset-3">
   				<?php 
   				include_once 'iftar.php';
   				echo printEditHosts(getRequestedDateKey());
   				?>
   				<p>Any changes or removals require the unique reference id</p>
   			</div>
   			
   			<div id="edit-modal" class="modal fade" role="dialog">
   				<div class="modal-dialog">
   					<div class="modal-content">
   						<div class="modal-header">
   							<button type="button" class="close" data-dismiss="modal">
   								&times;<span class="sr-only">Close</span>
   							</button>
   						</div>
   						<div class="modal-body">
   							<h4 class="form-control-static uwms-form-section">Editing host for <?php include_once "iftar.php"; printRequestedDate(); ?></h4>
	   						<form class="form-horizontal" role="form" id="edit-host-form" action="replace.php" method="POST">
								<input type="hidden" name="check_submit" value="check_submit">
								<input type="hidden" name="date" value="<?php include_once "iftar.php";echo trim(getRequestedDateKey());?>">
								<input type="hidden" name="index" value="" id="indexInput">
								<div class="form-group"></div>
								<div class="form-group">
			  						<label class="control-label col-sm-3" for="nameInput">Full name</label>
			  						<div class="col-sm-6">
		  								<input class="form-control" type="text" name="name" value="" id="nameInput">
		  							</div>
		  						</div>
			  					<div class="form-group">
			  						<label class="control-label col-sm-3" for="emailInput">Email</label>
			  						<div class="col-sm-6">
			  							<input class="form-control" type="email" name="email" value="" id="emailInput">
			  						</div>
			  					</div>
			  					<div class="form-group">
			  						<label class="control-label col-sm-3" for="phoneInput">Telephone</label>
			  						<div class="col-sm-6">
		  								<input class="form-control" type="tel" name="phone" value="" id="phoneInput">
		  							</div>
			  					</div>
			  					<div class="form-group">
			  						<label class="control-label col-sm-3" for="editRefidInput">Reference id</label>
			  						<div class="col-sm-6">
		  								<input class="form-control" type="text" name="refid" value="" id="editRefidInput">
		  							</div>
			  					</div>

   						</div> <!-- modal-body -->
   						<div class="modal-footer">
   		        			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        					<button type="submit" name="btnSubmit" class="btn btn-primary">Save changes</button>
        				</div>
        				</form>   						   						
   					</div> <!-- modal-content -->
   				</div> <!-- modal-dialog -->
   			</div> <!-- modal -->
   			
   			<div id="remove-modal" class="modal fade" role="dialog">
   				<div class="modal-dialog">
   					<div class="modal-content">
   						<div class="modal-header">
   							<button type="button" class="close" data-dismiss="modal">
   								&times;<span class="sr-only">Close</span>
   							</button>
   						</div>
   						<div class="modal-body">
   							<p class="iftarcal-host-display">Date: <?php include_once "iftar.php"; printRequestedDate(); ?></p>
   							<p class="iftarcal-host-display">Removing host: <span id="remove-hostname"></span></p>
   							<form class="form-horizontal" role="form" id="remove-host-form" action="remove.php" method="POST">
								<input type="hidden" name="check_submit" value="check_submit">
								<input type="hidden" name="date" value="<?php include_once "iftar.php";echo trim(getRequestedDateKey());?>">
								
								<div class="form-group"></div>
								<div class="form-group">
			  						<label class="control-label col-sm-3" for="submitRefidInput">Reference id:</label>
			  						<div class="col-sm-6">
		  								<input class="form-control" type="text" name="refid" value="" id="submitRefidInput">
		  							</div>
		  						</div>
		  					
   						</div> <!-- modal-body -->
   						<div class="modal-footer">
   		        			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        					<button type="submit" class="btn btn-primary">Remove host</button>
        				</div>
        				</form>
        			</div> <!-- remove modal content -->
        		</div> <!-- remove modal -->
        	</div>
        	<div id="alert-modal" class="modal fade" role="alert">
   				<div class="modal-dialog modal-sm">
   					<div class="modal-content">
   						<div class="modal-header">
   							<button type="button" class="close" data-dismiss="modal">
   								&times;<span class="sr-only">Close</span>
   							</button>
   						</div>
   						<div class="modal-body">
   							<div class="alert" role="alert" id="response-alert">
        						<p id="alert-content"></p>
        					</div>
   						</div>
   					</div> <!-- alert model-content -->
   				</div>
   			</div>
        	
   		</div> <!-- row -->
   		
   		<div class="row">
 			<div class="col-sm-5 col-sm-offset-3">
 				<p><a href="index.php">Back to Iftar Calendar</a></p>
			</div>
		</div>
   		
  </div>


	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
 						<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="bs/js/bootstrap.min.js"></script>
    <script src="js/jquery.validate.min.js"></script>
   	<script src="js/additional-methods.min.js"></script>
    <script src="js/edithost.js"></script>
    </body>
</html>