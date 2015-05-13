<?php

if(isset($_GET['theme'])) {
	setcookie("theme", $_GET['theme']);
	header('Location: '.$_SERVER['HTTP_REFERER']);
} else {
	if(!isset($_COOKIE['theme'])) {
		$theme = "flatly";
	} else {
		$theme = $_COOKIE['theme'];
	}
	echo '<link rel="stylesheet" href="/css/'.$theme.'.min.css">';
}

?>