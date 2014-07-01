<?php

include_once ('iftar.php');

if (isset($_POST['key'])) {
	$key = strip_tags(trim($_POST['key']));
}
else {
	echo "Invalid request";
	return;
}

if (isset($_POST['refid'])) {
	$refid = strip_tags(trim($_POST['refid']));
}
else {
	echo "Invalid request";
	return;
}

if (!removeHost($key, $refid)) {
	echo  "removeHost() failed $key $refid";
}
else {
	echo "removeHost() successful";
}



?>