<?php
	
include_once "iftar.php";


if (isset($_GET['date'])) {
	$key = strip_tags(trim($_GET['date']));
	echo printHosts ($key);
}
else {
	echo "Invalid request";
}



?>