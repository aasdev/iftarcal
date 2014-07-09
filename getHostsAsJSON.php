<?php


include_once "iftar.php";


if (isset($_GET['date'])) {
	$key = strip_tags(trim($_GET['date']));
	$entry = getEntryByKey($key);
	if ($entry == false) {
		echo "Request failed";
		return;
	}
	echo json_encode($entry);
}
else {
	echo "Invalid request";
}



?>