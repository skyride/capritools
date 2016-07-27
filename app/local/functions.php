<?php

include("config.php");
include("simple_html_dom.php");

//Connect MySQL
$db = new PDO('mysql:host='.$mysql_host.';dbname='.$mysql_db.';charset=utf8', $mysql_user, $mysql_pass);
$odb = new PDO('mysql:host='.$mysql_host.';dbname=sde;charset=utf8', $mysql_user, $mysql_pass);


function saveHit() {
	//Connect MySQL
	global $db;
	
	//Create scan entry
	if(!isset($_SERVER['HTTP_REFERER'])) {
		$referrer = "";
	} else {
		$referrer = $_SERVER['HTTP_REFERER'];
	}
	$st = $db->prepare("INSERT INTO pageHits (`date`, `ip`, `referrer`, `page`) VALUES (UNIX_TIMESTAMP(), :ip, :referrer, :page)");
	$st->bindValue(":ip", $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
	$st->bindValue(":referrer", $_SERVER['HTTP_REFERER'], PDO::PARAM_STR);
	$st->bindValue(":page", $_SERVER["REQUEST_URI"], PDO::PARAM_STR);
	$st->execute();
}



function getSystemInfo($name) {
	global $odb;
	
	$st = $odb->prepare("SELECT solarSystemName, constellationName, regionName, security
	FROM `mapSolarSystems`
	INNER JOIN `mapConstellations` ON mapConstellations.constellationID = mapSolarSystems.constellationID
	INNER JOIN `mapRegions` ON mapRegions.regionID = mapSolarSystems.regionID
	WHERE solarSystemName = :name");
	$st->bindValue(":name", $name, PDO::PARAM_STR);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	if(count($rows) > 0) {
		//Fix stupid 0.1 sec status round mistakes
		$row = $rows[0];
		if($row['security'] > 0 && $row['security'] < 0.1) {
			$row['security'] = 0.1;
		} else {
			$row['security'] = number_format(round($row['security'], 1), 1);
		}
		
		return $row;
	} else {
		return null;
	}
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

//////////////////////////////////////////////////////////////////////
// LOCAL SCAN TOOL FUNCS
//////////////////////////////////////////////////////////////////////


//Saves the local and returns the key
function saveLScan($lscan, $system = "") {
	global $db;
	
	//Save the json object in the pastebin dumb system
	$pasteKey = savePaste(json_encode($lscan));
	
	//Tally total
	$total = 0;
	foreach($lscan['corps'] as $corp) {
		$total += $corp['quantity'];
	}
	
	//Create scan entry
	$st = $db->prepare("INSERT INTO lscanScans(`created`, `ip`, `system`, `pasteKey`, `total`) VALUES (UNIX_TIMESTAMP(), :ip, :system, :pasteKey, :total)");
	$st->bindValue(":ip", $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
	$st->bindValue(":system", $system, PDO::PARAM_STR);
	$st->bindValue(":pasteKey", $pasteKey, PDO::PARAM_STR);
	$st->bindValue(":total", $total, PDO::PARAM_INT);
	$st->execute();
	
	//Get id and create key
	$id = $db->lastInsertId();
	$key = sha1("bigatron" . $id);
	$st = $db->prepare("UPDATE lscanScans SET `key`=:key WHERE id=:id LIMIT 1");
	$st->bindValue(":key", $key, PDO::PARAM_STR);
	$st->bindValue(":id", $id, PDO::PARAM_INT);
	$st->execute();
	
	return $key;
}



function getLScanInfo($key) {
	global $db;
	
	//Get ID and check existence
	$st = $db->prepare("SELECT * FROM lscanScans WHERE `key`=:key");
	$st->bindValue(":key", $key);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	
	return $rows[0];
}


function getLScan($key) {
	global $db;
	
	//Get ID and check existence
	$st = $db->prepare("SELECT * FROM lscanScans WHERE `key`=:key");
	$st->bindValue(":key", $key);
	$st->execute();
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	
	if(count($rows) < 1) {
		return false;
	}
	
	$pasteKey = $rows[0]['pasteKey'];
	$lscan = getPaste($pasteKey);
	
	return json_decode($lscan, true);
}



//Takes an array containing a list of character names and returns an array of corps
function getCorps($chars) {
	global $db;
	
	//Build query
	for($i = 0; $i < count($chars); $i++) {
		$in[] = ":char".$i;
	}
	$sql = 'SELECT plrCorps.id AS  `id` , plrCorps.name AS  `name`, plrCorps.ticker as `ticker` , COUNT( * ) AS quantity, plrCorps.alliance as `allianceId`
				FROM plrChars
				INNER JOIN plrCorps ON plrCorps.id = plrChars.corp
				WHERE plrChars.name
				IN (' . implode(", ", $in) . ')
				GROUP BY plrCorps.id
				ORDER BY quantity DESC';

	$st = $db->prepare($sql);
	//Bind Values
	for($i = 0; $i < count($chars); $i++) {
		$values[":char".$i] = str_replace("\r", "", $chars[$i]);
	}
	$st->execute($values);
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	
	return $rows;
}



//Takes an array containing a list of character names and returns an array of alliances
function getAlliances($chars) {
	global $db;
	
	//Build query
	for($i = 0; $i < count($chars); $i++) {
		$in[] = ":char".$i;
	}
	$sql = 'SELECT plrAlliances.id AS  `id` , plrAlliances.name AS  `name`, plrAlliances.ticker as `ticker` , COUNT( * ) AS quantity
				FROM plrChars
				INNER JOIN plrCorps ON plrCorps.id = plrChars.corp
				INNER JOIN plrAlliances ON plrAlliances.id = plrCorps.alliance
				WHERE plrChars.name
				IN (' . implode(", ", $in) . ')
				GROUP BY plrAlliances.id
				ORDER BY quantity DESC';

	$st = $db->prepare($sql);
	//Bind Values
	for($i = 0; $i < count($chars); $i++) {
		$values[":char".$i] = str_replace("\r", "", $chars[$i]);
	}
	$st->execute($values);
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	
	return $rows;
}



//Takes an array containing a list of character names and returns an array of characters that aren't in our DB
function getUnknown($chars) {
	global $db;
	
	//Build query
	for($i = 0; $i < count($chars); $i++) {
		$in[] = ":char".$i;
	}
	$sql = 'SELECT name
				FROM plrChars
				WHERE plrChars.name
				IN (' . implode(", ", $in) . ')';

	$st = $db->prepare($sql);
	//Bind Values
	for($i = 0; $i < count($chars); $i++) {
		$values[":char".$i] = str_replace("\r", "", $chars[$i]);
	}
	$st->execute($values);
	$rows = $st->fetchAll(PDO::FETCH_ASSOC);
	
	//Seperate unknown characters
	$unknown = array();
	foreach($chars as $char) {
		$char = str_replace("\r", "", $char);
	
		//Try and find it in the return list
		$found = false;
		foreach($rows as $row) {
			if(strtolower($row['name']) == strtolower($char)) {
				$found = true;
			}
		}
		
		if($found == false) {
			$unknown[] = $char;
		}
	}
	
	return $unknown;
}



function getCharFromGate($charname) {
	//Connect to memcache and check if char is there
	$memcache = new Memcache;
	$memcache->connect('127.0.0.1', 11211);
	$char = $memcache->get("localscan-char-".$charname);
	if($char != false) {
		$char = json_decode($char, true);
		return $char;
	}

	$html = "https://gate.eveonline.com/Profile/".str_replace(" ", "%20", str_replace("\r", "", $charname));
	$html = file_get_html($html);
	
	//Get Details
	$char['id'] = $html->find('img[id=imgActiveCharacter]')[0]->src;
	$char['id'] = str_replace("_200.jpg", "", str_replace("https://image.eveonline.com/Character/", "", $char['id']));
	
	$char['name'] = $html->find('h2[class=profileName]')[0]->plaintext;
	
	$char['corpName'] = $html->find('a[class=BoldOrange12]')[0]->href;
	$char['corpName'] = str_replace("/Corporation/", "", str_replace("%20", " ", $char['corpName']));
	$char['corpName'] = str_replace("._", ".", $char['corpName']);
	
	$char['corpId'] = $html->find('img[alt="Corporation logo"]')[0]->src;
	$char['corpId'] = str_replace("_30.png", "", str_replace("https://image.eveonline.com/Corporation/", "", $char['corpId']));
	
	$char['allianceName'] = $html->find('a[class=BoldOrange12]')[1]->href;
	$char['allianceName'] = str_replace("/Alliance/", "", str_replace("%20", " ", $char['allianceName']));
	$char['allianceName'] = str_replace("._", ".", $char['allianceName']);
	
	$char['allianceId'] = $html->find('img[alt="Alliance logo"]')[0]->src;
	$char['allianceId'] = str_replace("_30.png", "", str_replace("https://image.eveonline.com/Alliance/", "", $char['allianceId']));
	
	$char['corpTicker'] = $html->find('a[class=BoldOrange12]')[0]->plaintext;
	$char['corpTicker'] = str_replace($char['corpName'] . " [", "", str_replace("]", "", $char['corpTicker']));
	
	$char['allianceTicker'] = $html->find('a[class=BoldOrange12]')[1]->plaintext;
	$char['allianceTicker'] = str_replace($char['allianceName'] . " [", "", str_replace("]", "", $char['allianceTicker']));
	
	//Check if we missed any corp data
	if($char['corpName'] == "") {
		$cn = $html->find('div[style="float: left; width: 50%;"]')[0]->children(1)->innertext;
		preg_match("/^[\s]+([\w\s\-\.\_]+)\s\[([\w\s\-\.\_]+)/", $cn, $matches);
		$char['corpName'] = $matches[1];
		$char['corpTicker'] = $matches[2];
	}
	
	//Remove faction ID if no alliance was detected
	if($char['allianceName'] == "") {
		$char['allianceId'] = "";
	}
	
	//Add result to memcache for next time
	$memcache->set("localscan-char-".$charname, json_encode($char), false, 259200);
	
	return $char;
}



//Builds the return object
function buildFromList($chars) {
	//Corps
	$corps = getCorps($chars);
	$alliances = getAlliances($chars);
	
	//Fill in the gaps left by unknowns
	$unknowns = getUnknown($chars);
	foreach($unknowns as $u) {
		$u = getCharFromGate($u);
		
		//Try to find corp
		$found = false;
		for($i = 0; $i < count($corps); $i++) {
			if($corps[$i]['id'] == $u['corpId']) {
				$corps[$i]['quantity']++;
				$found = true;
				$i = count($corps);
			}
		}
		
		//Add corp if it wasn't found
		if($found == false) {
			$corp['id'] = $u['corpId'];
			$corp['name'] = $u['corpName'];
			$corp['ticker'] = $u['corpTicker'];
			$corp['allianceId'] = $u['allianceId'];
			$corp['quantity'] = 1;
			$corps[] = $corp;
		}
		
		//Try to find alliance
		if($u['alliance'] != false) {
			$found = false;
			for($i = 0; $i < count($alliances); $i++) {
				if($alliances[$i]['id'] == $u['allianceId']) {
					$alliances[$i]['quantity']++;
					$found = true;
					$i = count($alliances);
				}
			}
			
			//Add alliance if it wasn't found
			if($found == false) {
				$alliance['id'] = $u['allianceId'];
				$alliance['name'] = $u['allianceName'];
				$alliance['ticker'] = $u['allianceTicker'];
				$alliance['quantity'] = 1;
				$alliances[] = $alliance;
			}
		}
	}
	
	//Re-sort corps
	for($i = 0; $i < count($corps); $i++) {
		$sort[$i] = $corps[$i]['quantity'];
	}
	arsort($sort);
	$sort = array_keys($sort);
	for($i = 0; $i < count($corps); $i++) {
		$sorted[] = $corps[$sort[$i]];
	}
	$corps = $sorted;
	$sorted = array();
	unset($sort);
	
	//Re-sort alliances
	for($i = 0; $i < count($alliances); $i++) {
		$sort[$i] = $alliances[$i]['quantity'];
	}
	arsort($sort);
	$sort = array_keys($sort);
	for($i = 0; $i < count($alliances); $i++) {
		$sorted[] = $alliances[$sort[$i]];
	}
	$alliances = $sorted;
	
	
	//Build associations
	//Corp -> Alliance
	$assocs = array();
	foreach($corps as $corp) {
		if($corp['allianceId'] != "") {
			$assocs[$corp['id']][] = $corp['allianceId'];
		}
	}
	
	//Alliance -> Corp
	foreach($alliances as $alliance) {
		foreach($corps as $corp) {
			if($corp['allianceId'] == $alliance['id']) {
				$assocs[$alliance['id']][] = $corp['id'];
			}
		}
	}
	
	
	//Build final return object
	$ret['corps'] = $corps;
	$ret['alliances'] = $alliances;
	$ret['assocs'] = $assocs;
	return $ret;
}


//Builds the return object
function buildFromListNew($charlist) {
	//Connect to Redis and get as many characters as possible
	$redis = new Redis;
	$redis->connect("127.0.0.1");
	$unknowns = array();
	$chars = array();
	foreach($charlist as $char) {
		$char = str_replace("\r", "", $char);
		$res = $redis->get("capritools-char-".$char);
		if($res == false) {
			$unknowns[] = $char;
		} else {
			$chars[] = json_decode($res, true);
		}
	}
	
	if(count($unknowns) > 0) {
		//Get unknowns to ids using eve api
		$url = "https://api.eveonline.com/eve/CharacterID.xml.aspx";
		$data = array('names' => implode(",", $unknowns));
		$options = array(
			'http' => array (
				'header'	=> "Content-type: application/x-www-form-urlencoded\r\n",
				'method'	=> 'POST',
				'content'	=> http_build_query($data)
			)
		);
		$context = stream_context_create($options);
		$xml = file_get_contents($url, false, $context);
		$xml = new SimpleXMLElement($xml);
		$unknownids = array();
		foreach($xml->result->rowset->row as $row) {
			$unknownids[] = $row['characterID'];
		}
		
		//Populate unknown chars from API
		$chunks = array_chunk($unknownids, 250);
		$unknownchars = array();
		foreach($chunks as $chunk) {
			$url = "https://api.eveonline.com/eve/CharacterAffiliation.xml.aspx";
			$data = array('ids' => implode(",", $chunk));
			$options = array(
				'http' => array (
					'header'	=> "Content-type: application/x-www-form-urlencoded\r\n",
					'method'	=> 'POST',
					'content'	=> http_build_query($data)
				)
			);
			$context = stream_context_create($options);
			$xml = file_get_contents($url, false, $context);
			$xml = new SimpleXMLElement($xml);
			foreach($xml->result->rowset->row as $row) {
				$char = array();
				$char['characterID'] = (string) $row['characterID'];
				$char['characterName'] = (string) $row['characterName'];
				$char['corporationID'] = (string) $row['corporationID'];
				$char['allianceID'] = (string) $row['allianceID'];
				$unknownchars[] = $char;
				$redis->set("capritools-char-".$char['characterName'], json_encode($char), 3600);
			}
		}
	
		//Merge unknown chars into the full list
		$chars = array_merge($chars, $unknownchars);
	}
	
	//Calculate corp/alliance quantities and build associations
	$corps = array();
	$alliances = array();
	$assocs = array();
	foreach($chars as $char) {
		if($char['corporationID'] > 0) {
			$corps[$char['corporationID']]++;
		}
		
		if($char['allianceID'] > 0) {
			$alliances[$char['allianceID']]++;
			
			//Add associations
			//Alliance -> Corp
			$assocs[$char['allianceID']][] = $char['corporationID'];
			
			//Corp -> Alliance
			$assocs[$char['corporationID']][] = $char['allianceID'];
		}
	}
	
	//Clean up association duplicates
	foreach(array_keys($assocs) as $key) {
		$assocs[$key] = array_values(array_unique($assocs[$key]));
	}
	
	//Sort by the number of characters from them
	arsort($corps);
	arsort($alliances);
	
	//Get corps
	$corplist = array();
	foreach(array_keys($corps) as $id) {
		$res = $redis->get("capritools-corp-".$id);
		if($res == false) {
			$url = "https://api.eveonline.com/corp/CorporationSheet.xml.aspx";
			$data = array('corporationID' => $id);
			$options = array(
				'http' => array (
					'header'	=> "Content-type: application/x-www-form-urlencoded\r\n",
					'method'	=> 'POST',
					'content'	=> http_build_query($data)
				)
			);
			$context = stream_context_create($options);
			$xml = file_get_contents($url, false, $context);
			$xml = new SimpleXMLElement($xml);
			
			$corp = array();
			$corp['id'] = $id;
			$corp['name'] = (string) $xml->result->corporationName;
			$corp['ticker'] = (string) $xml->result->ticker;
			
			//Save to redis
			$redis->set("capritools-corp-".$id, json_encode($corp));
			
			$corp['quantity'] = $corps[$id];
			
			//Put in corp list
			$corplist[] = $corp;
		} else {
			$corp = json_decode($res, true);
			$corp['quantity'] = $corps[$id];
			//Put in corp list
			$corplist[] = $corp;
		}
	}
	
	//Get alliances
	$alliancelist = array();
	$xml = false;
	foreach(array_keys($alliances) as $id) {
		$res = $redis->get("capritools-alliance-".$id);
		if($res == false) {
			unset($alliance);
			if($xml === false) {
				$url = "https://api.eveonline.com/eve/AllianceList.xml.aspx";
				$xml = file_get_contents($url);
				$xml = new SimpleXMLElement($xml);
			}
			
			foreach($xml->result->rowset->row as $row) {
				if($row['allianceID'] == $id) {
					$alliance['id'] = $id;
					$alliance['name'] = (string) $row['name'];
					$alliance['ticker'] = (string) $row['shortName'];
				}
			}
			
			//Save to redis
			$redis->set("capritools-alliance-".$id, json_encode($alliance));
			
			$alliance['quantity'] = $alliances[$id];
			
			//Put in alliance list
			$alliancelist[] = $alliance;
		} else {
			$alliance = json_decode($res, true);
			$alliance['quantity'] = $alliances[$id];
			//Put in corp list
			$alliancelist[] = $alliance;
		}
	}
	
	//Build return object
	$obj['corps'] = $corplist;
	$obj['alliances'] = $alliancelist;
	$obj['assocs'] = $assocs;
	
	return $obj;
}


//$chars = explode("\n", file_get_contents("test4.txt"));

//print_r(getCorps($chars));
//print_r(getAlliances($chars));
//print_r(getUnknown($chars));
//print_r(getCharFromGate("Fear Naught"));
//print_r(buildFromList($chars));

?>