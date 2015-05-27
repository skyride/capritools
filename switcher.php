<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
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