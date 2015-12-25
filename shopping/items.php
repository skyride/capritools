<?php
//Generates a JSON object containing a list of all market-available items in the EVE Online static data export

header('Content-Type: application/javascript');
?>var items = <?php
include("functions.php");
include("config.php");

//Get item list
if((time() - 86400) > filemtime("itemscache") || isset($_GET['debug'])) {
	include("ships.php");
	$db = new PDO('mysql:host='.$mysql_host.';dbname='.$mysql_db.';charset=utf8', $mysql_user, $mysql_pass);
	$st = $db->prepare("SELECT invTypes.typeID, typeName, groupName, COALESCE(invVolumes.volume, invTypes.volume) AS volume FROM `invTypes` 
JOIN invGroups ON invGroups.groupID = invTypes.groupID
LEFT JOIN invVolumes ON invVolumes.typeid = invTypes.typeID
INNER JOIN dgmTypeAttributes ON dgmTypeAttributes.typeID = invTypes.typeID
WHERE invTypes.marketGroupID IS NOT NULL AND  dgmTypeAttributes.attributeID = 633
ORDER BY invTypes.volume DESC, marketGroupID ASC, COALESCE(dgmTypeAttributes.valueInt, dgmTypeAttributes.valueFloat) ASC");
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	
	//Perform ship volume replacements
	for($i = 0; $i < count($rows); $i++) {
		if(isset($ships[$rows[$i]['groupName']])) {
			$rows[$i]['volume'] = $ships[$rows[$i]['groupName']];
		}
		unset($rows[$i]['groupName']);
	}
	
	$items = json_encode($rows);
	file_put_contents("itemscache", $items);
	
	//Debug output
	if(isset($_GET['debug'])) {
		print_r($rows);
	} else {
		echo $items;
	}
} else {
	echo file_get_contents("itemscache");
}

?>;