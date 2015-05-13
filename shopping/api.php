<?php

//Intelligent eve-central proxy script (DEPCRECATED DUE TO PERFORMANCE VS EVE-CENTRAL API)
header('Content-Type: application/json');

$typeids = explode(",", $_GET['typeid']);
$results = array();
$query = array();

//Fill requests
$mem = new Memcache;
$mem->connect('127.0.0.1', 11211);
foreach($typeids as $typeid) {
	//Try memcache
	$json = $mem->get("evecentral-".$typeid);
	if($json === false) {
		$query[] = $typeid;
	} else {
		$results[$typeid] = json_decode($json, true);
	}
}

$query = array_chunk($query, 20);
foreach($query as $q) {
	$url = "http://api.eve-central.com/api/marketstat/json?typeid=".implode(",", $q)."&regionlimit=10000002";
	$json = file_get_contents($url);
	$json = json_decode($json, true);
	
	foreach($json as $j) {
		$id = $j['sell']['forQuery']['types'][0];
		$mem->set("evecentral-".$id, json_encode($j), false, 10800);
		$results[$id] = $j;
	}
}


//Rebuild the return obj
$obj = array();
foreach($typeids as $id) {
	$obj[] = $results[$id];
}

if(isset($_GET['debug'])) {
	print_r($obj);
} else {
	echo json_encode($obj);
}
?>