<?php

include("config.php");
$db = new PDO('mysql:host='.$mysql_host.';dbname=capritools;charset=utf8', $mysql_user, $mysql_pass);

function isk($isk) {
	return number_format($isk, 2, '.', ',') . " ISK";
}

function getEveCentralData($id) {
	$mem = new Memcache;
	$mem->connect('127.0.0.1', 11211);
	
	//Try to get
	$json = $mem->get("evecentral-".$id);
	if($json === false) {
		$json = file_get_contents("http://api.eve-central.com/api/marketstat/json?typeid=".$id."&usesystem=30000142");
		$json = json_decode($json);
		$json = $json[0];
		$json = json_encode($json);
		$mem->set("evecentral-".$id, $json, false, 3600);
	}
	return json_decode($json, true);
}

function getSell($id) {
	$json = getEveCentralData($id);
	return $json['sell']['fivePercent'];
}

function getBuy($id) {
	$json = getEveCentralData($id);
	return $json['buy']['fivePercent'];
}


function saveList($data) {
	global $db;
	
	//Create list entry
	$st = $db->prepare("INSERT INTO shoppingLists(`created`, `ip`) VALUES (UNIX_TIMESTAMP(), :ip)");
	$st->bindValue(":ip", $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
	$st->execute();
	
	//Get id and create key
	$id = $db->lastInsertId();
	
	//Save data to paste
	$pasteKey = savePaste($data);
	
	$key = sha1("thenewguyfromdownstairs" . $id);
	$st = $db->prepare("UPDATE shoppingLists SET `key`=:key,`pasteKey`=:pasteKey WHERE id=:id LIMIT 1");
	$st->bindValue(":key", $key, PDO::PARAM_STR);
	$st->bindValue(":pasteKey", $pasteKey, PDO::PARAM_STR);
	$st->bindValue(":id", $id, PDO::PARAM_INT);
	$st->execute();
	
	return $key;
}


function getList($key) {
	global $db;
	
	//Get paste key
	$st = $db->prepare("SELECT * FROM shoppingLists WHERE `key`=:key");
	$st->bindValue(":key", $key, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	$pasteKey = $rows[0]['pasteKey'];
	
	return getPaste($pasteKey);
}


/////////////////////////////////////////////////////////
// PASTE TOOL FUNCS
/////////////////////////////////////////////////////////
function savePaste($data) {
	global $db;
	
	//Create scan entry
	$st = $db->prepare("INSERT INTO pastes(`created`, `ip`) VALUES (UNIX_TIMESTAMP(), :ip)");
	$st->bindValue(":ip", $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
	$st->execute();
	
	//Get id and create key
	$id = $db->lastInsertId();
	$key = sha1("grrrrfalcons" . $id);
	$st = $db->prepare("UPDATE pastes SET `key`=:key WHERE id=:id LIMIT 1");
	$st->bindValue(":key", $key, PDO::PARAM_STR);
	$st->bindValue(":id", $id, PDO::PARAM_INT);
	$st->execute();
	
	//Save data segments
	$data = str_split($data, 1024);
	for($i = 0; $i < count($data); $i++) {
		$st = $db->prepare("INSERT INTO pasteData(`id`, `sequence`, `data`) VALUES (:id, :sequence, :data)");
		$st->bindValue(":id", $id);
		$st->bindValue(":sequence", $i);
		$st->bindValue(":data", $data[$i]);
		$st->execute();
	}
	
	return $key;
}


function getPasteInfo($key) {
	global $db;
	
	//Get ID and check existence
	$st = $db->prepare("SELECT * FROM pastes WHERE `key`=:key");
	$st->bindValue(":key", $key);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	
	return $rows[0];
}


function getPaste($key) {
	global $db;
	
	//Get ID and check existence
	$st = $db->prepare("SELECT * FROM pastes WHERE `key`=:key");
	$st->bindValue(":key", $key);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	
	if(count($rows) < 1) {
		return false;
	}
	
	$id = $rows[0]['id'];
	
	$st = $db->prepare("SELECT data FROM pasteData WHERE id=:id ORDER BY sequence ASC");
	$st->bindValue(":id", $id);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	
	$paste = "";
	foreach($rows as $row) {
		$paste .= $row['data'];
	}
	
	return $paste;
}
?>