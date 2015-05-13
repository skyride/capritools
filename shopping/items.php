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
	$st = $db->prepare("SELECT invTypes.typeID, typeName, volume, groupName, metaGroupID, marketGroupID FROM `invTypes` 
JOIN invGroups ON invGroups.groupID = invTypes.groupID
RIGHT JOIN invMetaTypes ON invMetaTypes.typeID = invTypes.typeID
WHERE marketGroupID IS NOT NULL
UNION
SELECT invTypes.typeID, typeName, volume, groupName, metaGroupID, marketGroupID FROM `invTypes` 
JOIN invGroups ON invGroups.groupID = invTypes.groupID
LEFT JOIN invMetaTypes ON invMetaTypes.typeID = invTypes.typeID
WHERE marketGroupID IS NOT NULL ORDER BY case when metaGroupID IS NULL then 15 end ASC, metaGroupID, marketGroupID, typeName");
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