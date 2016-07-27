<?
//Generates an JSON object containing a full list of systems in EVE
header("Cache-Control: max-age=3600");

include("config.php");


$db = new PDO('mysql:host='.$mysql_host.';dbname=sde;charset=utf8', $mysql_user, $mysql_pass);
$st = $db->prepare("SELECT solarSystemName as n, constellationName as c, regionName as r, security as s
	FROM `mapSolarSystems`
	INNER JOIN `mapRegions` ON mapSolarSystems.regionID = mapRegions.regionID
	INNER JOIN `mapConstellations` ON mapSolarSystems.constellationID = mapConstellations.constellationID
	ORDER BY solarSystemName
");
$st->execute();
$systems = $st->fetchAll(PDO::FETCH_ASSOC);

switch($_GET['format']) {
	case "json":
		//Fix stupid 0.1 sec status round mistakes
		for($i = 0; $i < count($systems); $i++) {
			if($systems[$i]['s'] > 0 && $systems[$i]['s'] < 0.1) {
				$systems[$i]['s'] = "0.1";
			} else {
				$systems[$i]['s'] = number_format(round($systems[$i]['s'], 1), 1);
			}
		}
	
		header('Content-Type: application/json');
		$systems = json_encode($systems);
		echo $systems;
		break;

	case "csv":
		header('Content-Type: text/csv');
		echo "systemName,regionName,constellationName,security";
		foreach($systems as $system) {
			//Fix stupid 0.1 sec status round mistakes
			if($system['s'] >= 0 && $system['s'] <= 0.1) {
				$sec = 0.1;
			} else {
				$sec = number_format(round($system['s'], 1), 1);
			}
			
			echo "\n" . $system['n'] . "," . $system['r'] . "," . $system['c'] . "," . $sec;
		}
		break;
}

?>