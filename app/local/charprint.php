<?php

error_reporting(E_ERROR | E_PARSE);

include("functions.php");

if(isset($argv[1])) {
	$scan = $argv[1];
} else {
	header('Content-Type:text/plain');
	$scan = "scans/".$_GET['key'];
}

$list = file_get_contents($scan);
$list = explode("\n", $list);

foreach($list as $name) {
	$u = getCharFromGate($name);
	echo sprintf("%-30s%-50s%-20s", $u['name'], $u['corpName'], $u['allianceName']) . "\n";
}

?>