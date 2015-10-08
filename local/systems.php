<?
//Generates an JSON object containing a full list of systems in EVE
header("Cache-Control: max-age=3600");

include("config.php");


$db = new PDO('mysql:host='.$mysql_host.';dbname=oceanus;charset=utf8', $mysql_user, $mysql_pass);
$st = $db->prepare("SELECT solarSystemName as n, constellationName as c, regionName as r, ROUND(security, 1) as s
	FROM `mapSolarSystems`
	INNER JOIN `mapRegions` ON mapSolarSystems.regionID = mapRegions.regionID
	INNER JOIN `mapConstellations` ON mapSolarSystems.constellationID = mapConstellations.constellationID
	ORDER BY solarSystemName
");
$st->execute();
$systems = $st->fetchAll(PDO::FETCH_ASSOC);

switch($_GET['format']) {
	case "json":
		header('Content-Type: application/json');
		$systems = json_encode($systems);
		echo $systems;
		break;

	case "csv":
		header('Content-Type: text/csv');
		echo "systemName,regionName,constellationName,security";
		foreach($systems as $system) {
			echo "\n" . $system['n'] . "," . $system['r'] . "," . $system['c'] . "," . $system['s'];
		}
		break;
}

?>