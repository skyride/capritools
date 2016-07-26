<?php

include("config.php");
include("functions.php");

if(isset($_POST['save-data'])) {
	//Connect MySQL
	$key = saveList($_POST['save-data']);
	header('Location: /shopping/'.$key);
}

//print_r($_POST);

?>