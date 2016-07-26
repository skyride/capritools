<?php

include("functions.php");

$list = getDscanList();

//Connect MySQL
include("config.php");
$db = new PDO('mysql:host='.$mysql_host.';dbname='.$mysql_db.';charset=utf8', $mysql_user, $mysql_pass);

foreach($list as $scan) {
	$raw = file_get_contents("scans/".$scan['key']);
	$dscan = parseDscan($raw);
	echo $scan['key'] . "... ";
	
	//Build SQL for object dump
	$sql = "INSERT INTO dscanObjects(`scan`, `name`, `type`, `distance`) VALUES ";
	for($i = 0; $i < count($dscan); $i++) {
		$sql .= "(:" . $i . "scan, :" . $i . "name, :" . $i . "type, :" . $i . "distance),";
	}
	$sql = rtrim($sql, ",");
	$st = $db->prepare($sql);
	
	//Bind values
	for($i = 0; $i < count($dscan); $i++) {
		$st->bindValue(":".$i."scan", $scan['id'], PDO::PARAM_INT);
		$st->bindValue(":".$i."name", $dscan[$i]['name'], PDO::PARAM_STR);
		$st->bindValue(":".$i."type", $dscan[$i]['type'], PDO::PARAM_STR);
		$st->bindValue(":".$i."distance", $dscan[$i]['distance'], PDO::PARAM_INT);
	}
	
	//Execute the dump
	$st->execute();
	
	echo " Done!\n";
}

?>