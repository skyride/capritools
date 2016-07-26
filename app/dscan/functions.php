<?php

function distanceToMeters($dist, $unit) {
	$dist = str_replace(",", "", $dist);

	switch($unit) {
		case "m":
			return $dist;
			break;
			
		case "km":
			return 1000 * $dist;
			break;
			
		case "AU":
			return 149597870700 * floatval($dist);
			break;
			
		default:
			return null;
			break;
	}
}



//Parses a dscan to an array
function parseDscan($dscan) {
	$objects = array();
	
	//Split into rows
	$rows = explode("\n", $dscan);
	
	//Iterate through our rows
	foreach($rows as $row) {
		//Check if it matches a dscan row format
		if(preg_match("/^([^\t]+)\t([^\t]+)\t(([0-9,.]+) (km|m|AU)|-)/", $row, $matches) == 1) {
			$ob['type'] = $matches[2];
			$ob['name'] = $matches[1];
			
			//Parse distance
			if(count($matches) == 4) {
				//Unknown distance
				$ob['distance'] = -1;
			} else {
				//Known distance
				$ob['distance'] = distanceToMeters($matches[4], $matches[5]);
			}
			
			//Add to list
			$objects[] = $ob;
		}
	}
	
	return $objects;
}



function saveHit() {
	//Connect MySQL
	include("config.php");
	$db = new PDO('mysql:host='.$mysql_host.';dbname='.$mysql_db.';charset=utf8', $mysql_user, $mysql_pass);
	
	//Create scan entry
	$st = $db->prepare("INSERT INTO pageHits (`date`, `ip`, `referrer`, `page`) VALUES (UNIX_TIMESTAMP(), :ip, :referrer, :page)");
	$st->bindValue(":ip", $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
	$st->bindValue(":referrer", $_SERVER['HTTP_REFERER'], PDO::PARAM_STR);
	$st->bindValue(":page", $_SERVER["REQUEST_URI"], PDO::PARAM_STR);
	$st->execute();
}



function saveDscan($dscan) {
	//Connect MySQL
	include("config.php");
	$db = new PDO('mysql:host='.$mysql_host.';dbname='.$mysql_db.';charset=utf8', $mysql_user, $mysql_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	//Check there are actually objects to save
	if(count($dscan) < 1) {
		return;
	}
	
	//Create scan entry
	$st = $db->prepare("INSERT INTO dscanScans(`created`, `ip`) VALUES (UNIX_TIMESTAMP(), :ip)");
	$st->bindValue(":ip", $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
	$st->execute();
	
	//Get id and create key
	$id = $db->lastInsertId();
	$key = sha1("cloakypilgrim" . $id);
	$st = $db->prepare("UPDATE dscanScans SET `key`=:key WHERE id=:id LIMIT 1");
	$st->bindValue(":key", $key, PDO::PARAM_STR);
	$st->bindValue(":id", $id, PDO::PARAM_INT);
	$st->execute();
	
	//Build SQL for object dump
	$sql = "INSERT INTO dscanObjects(`scan`, `name`, `type`, `distance`) VALUES ";
	for($i = 0; $i < count($dscan); $i++) {
		$sql .= "(:" . $i . "scan, :" . $i . "name, :" . $i . "type, :" . $i . "distance),";
	}
	$sql = rtrim($sql, ",");
	$st = $db->prepare($sql);
	
	//Bind values
	for($i = 0; $i < count($dscan); $i++) {
		$st->bindValue(":".$i."scan", $id, PDO::PARAM_INT);
		$st->bindValue(":".$i."name", $dscan[$i]['name'], PDO::PARAM_STR);
		$st->bindValue(":".$i."type", $dscan[$i]['type'], PDO::PARAM_STR);
		$st->bindValue(":".$i."distance", $dscan[$i]['distance'], PDO::PARAM_INT);
	}
	
	//Execute the dump
	$st->execute($values);
	
	return $key;
}



//Parses a dscan down for an ongrid control tower
function parseDscanTower($dscan) {
	//Parse mods file
	$csv = explode("\n", file_get_contents("posmods.csv"));
	foreach($csv as $mod) {
		$mod = str_getcsv($mod, ",", "\"", "\\");
		$m['id'] = $mod[0];
		$m['igbName'] = $mod[1];
		$m['typeName'] = $mod[2];
		$mods[$m['typeName']] = $m;
	}
	
	//Parse control towers file
	$csv = explode("\n", file_get_contents("controltowers.csv"));
	foreach($csv as $ct) {
		$ct = str_getcsv($ct, ",", "\"", "\\");
		$m['id'] = $ct[0];
		$m['igbName'] = $ct[1];
		$m['typeName'] = $ct[2];
		$cts[$m['typeName']] = $m;
	}
	
	//Parse the dscan
	$pos = array();
	$ct = null;
	$rows = explode("\n", $dscan);
	foreach($rows as $row) {
		$cols = explode("\t", $row);
		
		//Check if it's on grid
		if(preg_match("/([0-9,]+) (km|m|AU)/", $cols[2], $matches) == 1) {
			//Check if this module exists in our pos mod db
			if(array_search($cols[1], array_keys($mods)) != false) {
				//Add it to our pos
				$pos[] = $mods[$cols[1]];
			} else {
				//Check if it's a control tower
				if(array_search($cols[1], array_keys($cts)) != false) {
					//Check we don't already have one
					if($ct != null) {
						die("Error, more than 1 control tower on scan");
					}
					
					$ct = $cts[$cols[1]];
				}
			}
		}
	}
	
	//Check if empty
	if($ct === null) {
		return null;
	}
	
	//Generate URL
	$url = "http://eve.1019.net/pos/index.php?ct=%%CT%%&mod=%%MODS%%&off=";
	$modlist = "";
	foreach($pos as $mod) {
		$modlist .= $mod['id'];
	}
	$url = str_replace("%%CT%%", $ct['id'], $url);
	$url = str_replace("%%MODS%%", $modlist, $url);
	return $url;
}



function getDscan($key) {
	//Connect MySQL
	include("config.php");
	$db = new PDO('mysql:host='.$mysql_host.';dbname='.$mysql_db.';charset=utf8', $mysql_user, $mysql_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	//Check if scan exists
	$st = $db->prepare("SELECT * FROM dscanScans WHERE `key`=:key LIMIT 1");
	$st->bindValue(":key", $key, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	if(count($rows) < 1) {
		//No scan found
		return null;
	} else {
		return $rows[0];
	}
}



function getDscanList() {
	//Connect MySQL
	include("config.php");
	$db = new PDO('mysql:host='.$mysql_host.';dbname='.$mysql_db.';charset=utf8', $mysql_user, $mysql_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	//Check if scan exists
	$st = $db->prepare("SELECT * FROM dscanScans");
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	if(count($rows) < 1) {
		//No scan found
		return null;
	} else {
		return $rows;
	}
}



function getDscanLocation($key) {
	//Connect MySQL
	include("config.php");
	$db = new PDO('mysql:host='.$mysql_host.';dbname='.$mysql_db.';charset=utf8', $mysql_user, $mysql_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	//Check if scan exists
	$st = $db->prepare("SELECT * FROM dscanScans WHERE `key`=:key LIMIT 1");
	$st->bindValue(":key", $key, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	if(count($rows) < 1) {
		//No scan found
		return null;
	}
	$id = $rows[0]['id'];
	
	//Try for celestials
	//$st = $db->prepare("SELECT * FROM dscanObjects WHERE scan=:scan AND type='Moon' ORDER BY distance ASC");
	$st = $db->prepare("SELECT * FROM dscanObjects WHERE scan=:scan ORDER BY distance ASC");
	$st->bindValue(":scan", $id, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	
	if(count($rows > 0)) {
		//Iterate through objects
		for($i = 0; $i < count($rows); $i++) {
			$st = $db->prepare("SELECT * FROM celestials WHERE itemName=:name LIMIT 1");
			$st->bindValue(":name", $rows[$i]['name'], PDO::PARAM_STR);
			$st->execute();
			$r = $st->fetchAll(PDO::FETCH_ASSOC);
			if(count($r) > 0) {
				return $r[0];
			}
		}
	}
	
	//Try to work it out using single gates
	$st = $db->prepare("SELECT * FROM dscanObjects WHERE scan=:scan AND type LIKE 'Stargate %' ORDER BY distance ASC");
	$st->bindValue(":scan", $id, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	
	foreach($rows as $row) {
		$st = $db->prepare("SELECT * FROM gates WHERE itemName=:name AND typeName=:type");
		$st->bindValue(":name", $row['name'], PDO::PARAM_STR);
		$st->bindValue(":type", $row['type'], PDO::PARAM_STR);
		$st->execute();
		$r = $st->fetchAll(PDO::FETCH_ASSOC);
		if(count($r) == 1) {
			return $r[0];
		}
	}
}



function getDscanShips($key) {
	//Connect MySQL
	include("config.php");
	$db = new PDO('mysql:host='.$mysql_host.';dbname='.$mysql_db.';charset=utf8', $mysql_user, $mysql_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode=""'));
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	//Check if scan exists
	$st = $db->prepare("SELECT * FROM dscanScans WHERE `key`=:key LIMIT 1");
	$st->bindValue(":key", $key, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	if(count($rows) < 1) {
		//No scan found
		return null;
	}
	
	//Get objects for scan
	$id = $rows[0]['id'];
	$st = $db->prepare("SELECT type, count(*) as quantity, groupName FROM dscanObjects INNER JOIN sde.invTypes as ships ON ships.typeName=dscanObjects.type INNER JOIN sde.invGroups AS groups ON groups.groupID = ships.groupID WHERE scan=:scan AND groups.categoryID = 6 GROUP BY type ORDER BY quantity DESC");
	$st->bindValue(":scan", $id, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	
	return $rows;
}



function getDscanShipsMass($key) {
	//Connect MySQL
	include("config.php");
	$db = new PDO('mysql:host='.$mysql_host.';dbname='.$mysql_db.';charset=utf8', $mysql_user, $mysql_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	//Check if scan exists
	$st = $db->prepare("SELECT * FROM dscanScans WHERE `key`=:key LIMIT 1");
	$st->bindValue(":key", $key, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	if(count($rows) < 1) {
		//No scan found
		return null;
	}
	
	//Get objects for scan
	$id = $rows[0]['id'];
	$st = $db->prepare("SELECT sum(mass) as totalMass FROM dscanObjects INNER JOIN sde.invTypes as ships ON ships.typeName=dscanObjects.type INNER JOIN sde.invGroups AS groups ON groups.groupID = ships.groupID WHERE scan=:scan AND ships.groupID NOT IN(30, 659) AND groups.categoryID = 6;");
	$st->bindValue(":scan", $id, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	
	return $rows[0]['totalMass'];
}



function getDscanShipsVolume($key) {
	//Connect MySQL
	include("config.php");
	$db = new PDO('mysql:host='.$mysql_host.';dbname='.$mysql_db.';charset=utf8', $mysql_user, $mysql_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	//Check if scan exists
	$st = $db->prepare("SELECT * FROM dscanScans WHERE `key`=:key LIMIT 1");
	$st->bindValue(":key", $key, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	if(count($rows) < 1) {
		//No scan found
		return null;
	}
	
	//Get objects for scan
	$id = $rows[0]['id'];
	$st = $db->prepare("SELECT sum(volume) as totalVolume FROM dscanObjects INNER JOIN sde.invTypes as ships ON ships.typeName=dscanObjects.type INNER JOIN sde.invGroups AS groups ON groups.groupID = ships.groupID WHERE scan=:scan AND ships.groupID NOT IN(30, 659) AND groups.categoryID = 6;");
	$st->bindValue(":scan", $id, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	
	return $rows[0]['totalVolume'];
}



function getDscanShipTypesSubs($key) {
	//Connect MySQL
	include("config.php");
	$db = new PDO('mysql:host='.$mysql_host.';dbname='.$mysql_db.';charset=utf8', $mysql_user, $mysql_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	//Check if scan exists
	$st = $db->prepare("SELECT * FROM dscanScans WHERE `key`=:key LIMIT 1");
	$st->bindValue(":key", $key, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	if(count($rows) < 1) {
		//No scan found
		return null;
	}
	
	//Get objects for scan
	$id = $rows[0]['id'];
	$st = $db->prepare("SELECT groups.groupName as type, count(*) as quantity, groupName FROM dscanObjects INNER JOIN sde.invTypes as ships ON ships.typeName=dscanObjects.type INNER JOIN sde.invGroups as groups ON groups.groupID = ships.groupID
	WHERE scan=:scan AND ships.groupID NOT IN(30, 659, 485, 547, 883, 902, 1538) AND groups.categoryID = 6 GROUP BY groups.groupName ORDER BY quantity DESC");
	$st->bindValue(":scan", $id, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	
	return $rows;
}



function getDscanShipTypesCaps($key) {
	//Connect MySQL
	include("config.php");
	$db = new PDO('mysql:host='.$mysql_host.';dbname='.$mysql_db.';charset=utf8', $mysql_user, $mysql_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	//Check if scan exists
	$st = $db->prepare("SELECT * FROM dscanScans WHERE `key`=:key LIMIT 1");
	$st->bindValue(":key", $key, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	if(count($rows) < 1) {
		//No scan found
		return null;
	}
	
	//Get objects for scan
	$id = $rows[0]['id'];
	$st = $db->prepare("SELECT groups.groupName as type, count(*) as quantity, groupName FROM dscanObjects INNER JOIN sde.invTypes as ships ON ships.typeName=dscanObjects.type INNER JOIN sde.invGroups as groups ON groups.groupID = ships.groupID
	WHERE scan=:scan AND ships.groupID IN(30, 659, 485, 547, 883, 902, 1538) AND groups.categoryID = 6 GROUP BY groups.groupName ORDER BY quantity DESC");
	$st->bindValue(":scan", $id, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	
	return $rows;
}



function getDscanObjects($key) {
	//Connect MySQL
	include("config.php");
	$db = new PDO('mysql:host='.$mysql_host.';dbname='.$mysql_db.';charset=utf8', $mysql_user, $mysql_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	//Check if scan exists
	$st = $db->prepare("SELECT * FROM dscanScans WHERE `key`=:key LIMIT 1");
	$st->bindValue(":key", $key, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	if(count($rows) < 1) {
		//No scan found
		return null;
	}
	
	//Get objects for scan
	$id = $rows[0]['id'];
	$st = $db->prepare("SELECT type, count(*) as quantity FROM dscanObjects WHERE scan=:scan GROUP BY type ORDER BY quantity DESC");
	$st->bindValue(":scan", $id, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	
	return $rows;
}



function getDscanTower($key) {
	//Parse control towers file
	/*$csv = explode("\n", file_get_contents("controltowers.csv"));
	foreach($csv as $ct) {
		$ct = str_getcsv($ct, ",", "\"", "\\");
		$m['id'] = $ct[0];
		$m['igbName'] = $ct[1];
		$m['typeName'] = $ct[2];
		$cts[$m['typeName']] = $m;
	}*/

	//Connect MySQL
	/*include("config.php");
	$db = new PDO('mysql:host='.$mysql_host.';dbname='.$mysql_db.';charset=utf8', $mysql_user, $mysql_pass);
	
	//Check if scan exists
	$st = $db->prepare("SELECT * FROM dscanScans WHERE `key`=:key LIMIT 1");
	$st->bindValue(":key", $key, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	if(count($rows) < 1) {
		//No scan found
		return null;
	}
	
	//Get objects for scan
	$id = $rows[0]['id'];
	$st = $db->prepare("SELECT type, distance FROM dscanObjects WHERE scan=:scan AND distance >= 0");
	$st->bindValue(":scan", $id, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	
	//Search objects for tower
	$found = false;
	foreach($rows as $row) {
		if(array_search($row['type'], array_keys($cts)) !== false) {
			$type = $row['type'];
			$found = true;
		}
	}*/
	
	if(true) {
		$dscan = file_get_contents("scans/".$key);
		//echo $dscan;
		return parseDscanTower($dscan);
	} else {
		return null;
	}
}



function dscanListObjectsCount($objects) {
	$count = 0;
	foreach($objects as $ob) {
		$count += $ob['quantity'];
	}
	return $count;
}



function dscanListObjects($objects) {
	include("config.php");
	$html = "<table class=\"table table-striped table-hover \">
	<tbody>";
	
	foreach($objects as $ob) {
		//Link
		/*$url = "https://wiki.eveonline.com/en/wiki/".str_replace(" ", "_", $ob['type']);
		$html .= "\n<li class=\"list-group-item\"><span class=\"badge\">".$ob['quantity']."
		</span><a href=\"".$url."\">".$ob['type']."</a></li>";*/
		
		//No link
		/*$html .= "\n<li class=\"list-group-item\"><span class=\"badge\">".$ob['quantity']."
		</span><strong>".$ob['type']."</strong></li>";*/
		
		//Table
		$html .= "\n<tr style=\"padding: 3px;\"><td class=\"".$classcolour[$ob['groupName']]."\" style=\"width: 10%; padding: 6px;\" align=\"center\"><strong>".$ob['quantity']."</strong></td><td style=\"padding: 6px;\">
		</span><strong>".$ob['type']."</strong></td></tr>";
	}
	
	$html .= "</tbody></table>";
	
	return $html;
}


function towerChecker($key) {
	//Connect MySQL
	include("config.php");
	$db = new PDO('mysql:host='.$mysql_host.';dbname='.$mysql_db.';charset=utf8', $mysql_user, $mysql_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	//Check if scan exists
	$st = $db->prepare("SELECT * FROM dscanScans WHERE `key`=:key LIMIT 1");
	$st->bindValue(":key", $key, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	if(count($rows) < 1) {
		//No scan found
		return null;
	}
	
	//Get towers
	$id = $rows[0]['id'];
	$st = $db->prepare("SELECT count(*) as towers FROM dscanObjects INNER JOIN sde.invTypes on sde.invTypes.typeName=dscanObjects.type WHERE `scan`=:scan AND groupID = 365");
	$st->bindValue(":scan", $id, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	$towers = $rows[0]['towers'];
	
	//Get forcefields
	$st = $db->prepare("SELECT count(*) as forcefields FROM dscanObjects WHERE `scan`=:scan AND type = 'Force Field'");
	$st->bindValue(":scan", $id, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	$forcefields = $rows[0]['forcefields'];
	
	$res['towers'] = $towers;
	$res['forcefields'] = $forcefields;
	
	return $res;
}


?>
