<?php

//Connect to MySQL
include("config.php");
$db = new PDO('mysql:host='.$mysql_host.';dbname='.$mysql_db.';charset=utf8', $mysql_user, $mysql_pass);
	
//Get type info
$st = $db->prepare("SELECT invTypes.typeID, invTypes.groupID, invTypes.typeName, invTypes.description, invTypes.mass, invTypes.volume, invTypes.capacity, marketGroupID, invGroups.groupName, invGroups.categoryID,
invCategories.categoryName

	FROM invTypes
	INNER JOIN invGroups ON invTypes.groupID = invGroups.groupID
	INNER JOIN invCategories ON invGroups.categoryID = invCategories.categoryID
	WHERE typeID = :typeID");
$st->bindValue(":typeID", $_GET['type'], PDO::PARAM_INT);
$st->execute();
$row = $st->fetch(PDO::FETCH_ASSOC);

//Begin building object
$type = $row;

//Get attributes
$st = $db->prepare("SELECT dgmTypeAttributes.typeID, dgmTypeAttributes.attributeID, dgmTypeAttributes.valueInt, dgmTypeAttributes.valueFloat,
dgmAttributeTypes.attributeName, dgmAttributeTypes.description, dgmAttributeTypes.iconID, dgmAttributeTypes.displayName, dgmAttributeTypes.categoryID,
dgmAttributeCategories.categoryName, dgmAttributeCategories.categoryDescription,
eveIcons.iconFile,
eveUnits.displayName as unit, eveUnits.unitID

FROM dgmTypeAttributes
INNER JOIN dgmAttributeTypes ON dgmTypeAttributes.attributeID = dgmAttributeTypes.attributeID
INNER JOIN dgmAttributeCategories ON dgmAttributeTypes.categoryID = dgmAttributeCategories.categoryID
INNER JOIN eveIcons ON dgmAttributeTypes.iconID = eveIcons.iconID
INNER JOIN eveUnits ON dgmAttributeTypes.unitID = eveUnits.unitID
WHERE dgmTypeAttributes.typeID = :typeID AND dgmAttributeTypes.categoryID IN(2, 3, 4, 5, 6, 7, 10, 17, 20) AND dgmAttributeTypes.published = 1
ORDER BY dgmAttributeTypes.categoryID, dgmAttributeTypes.attributeID
");
$st->bindValue(":typeID", $_GET['type'], PDO::PARAM_INT);
$st->execute();
$rows = $st->fetchAll(PDO::FETCH_ASSOC);
//Fix values
for($i = 0; $i < count($rows); $i++) {
	if($rows[$i]['valueInt'] === null) {
		$rows[$i]['value'] = $rows[$i]['valueFloat'];
	} else {
		$rows[$i]['value'] = $rows[$i]['valueInt'];
	}
	
	unset($rows[$i]['valueInt']);
	unset($rows[$i]['valueFloat']);
	
	$rows[$i]['iconFile'] = str_replace("_", "_32_", $rows[$i]['iconFile']);
}
$type['attributes'] = $rows;

echo json_encode($type);

?>