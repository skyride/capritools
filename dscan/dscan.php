<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

	<!-- Latest compiled and minified CSS -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
	<?php include("../switcher.php"); ?>
	<link rel="stylesheet" href="/dscan/css/custom.css">
	<!-- Optional theme -->
	<!-- Latest compiled and minified JavaScript -->
</head>
<?php
include("functions.php");
$key = $_GET['key'];

saveHit();

if(!isset($key)) {
	$result = "No scan given";
} else {
	$ds = getDscan($key);
	$obs = getDscanObjects($key);
	$ships = getDscanShips($key);
	$caps = getDscanShipTypesCaps($key);
	$subs = getDscanShipTypesSubs($key);
	$mass = getDscanShipsMass($key);
	$volume = getDscanShipsVolume($key);
}

//Location
$location = getDscanLocation($key);
if($location != null) {
	
	$loc = $location;
	//Check if it's a wormhole system
	if(preg_match("/^[jJ][0-9]{6}$/", $loc['systemName'], $matches) == 1) {
		//J-Space
		$location = "<a href=\"http://wh.pasta.gg/" . $loc['systemName'] . "\">" . $loc['systemName'] . "</a> &lt; " .
			$loc['constellationName'] . " &lt; " .
			$loc['regionName'] . "</a>";
	} else {
		//K-Space
		$regionName = str_replace(" ", "_", $loc['regionName']);
		$constellationName = str_replace(" ", "_", $loc['constellationName']);
		$systemName = str_replace(" ", "_", $loc['systemName']);
		$location = "<a href=\"http://evemaps.dotlan.net/map/" . $regionName . "/" . $loc['systemName'] . "\">" . $loc['systemName'] . "</a> &lt; " .
			"<a href=\"http://evemaps.dotlan.net/map/" . $regionName . "/" . $constellationName . "\">" . $loc['constellationName'] . "</a> &lt; " .
			"<a href=\"http://evemaps.dotlan.net/map/" . $regionName . "\">" . $loc['regionName'] . "</a>";
	}
} else {
	$location = "Unknown";
}

//Tower
$towerurl = getDscanTower($key);
$towerdata = towerChecker($key);
if($towerurl == null) {
	$tower = "No tower detected";
} else {
	$tower = "<a class=\"btn btn-success\" style=\"margin-bottom: 10px;\" href=\"".$towerurl."\">Tower Detected On-grid</a>";
}
?>
<body>
	<?php include("../header.php"); ?>


	<div class="container">

			<div class="col-lg-10 col-lg-offset-1">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<strong>Location:</strong> <?php echo $location; ?>
					<span class="pull-right"><strong>Created:</strong> <?php echo date("d/m/y H:i", $ds['created']); ?> EVE Time</span>
				</div>
				
				<div class="panel-body">
					<div class="col-lg-6">
						<div class="panel panel-default">
							<div class="panel-heading"><strong>Ships (<?php echo dscanListObjectsCount($ships); ?>)</strong></div>
							
							<div class="panel-body">
								<div class="bs-component">
									<ul class="list-group">
										<?php echo dscanListObjects($ships);?>
									</ul>
								</div>
							</div>
						</div>
					</div>
					
					
					<div class="col-lg-6">					
						<div class="panel panel-default">
							<div class="panel-heading"><strong>Subcaps (<?php echo dscanListObjectsCount($subs); ?>)</strong></div>
							
							<div class="panel-body">
								<?php echo dscanListObjects($subs); ?>
							</div>
						</div>
						
						
						<?php if(count($caps) > 0) { ?>
						<div class="panel panel-default">
							<div class="panel-heading"><strong>Capitals (<?php echo dscanListObjectsCount($caps); ?>)</strong></div>
							
							<div class="panel-body">
								<?php echo dscanListObjects($caps); ?>
							</div>
						</div>
						<?php } ?>
						
						<div class="panel panel-default">
							<div class="panel-heading"><strong>Info</strong></div>
							
							<div class="panel-body">
								<?php if(true) {
									$titfuel = $mass * 0.000000001 * ((1000) - (1000 * 0.10 * 5)) * 5;
									$blopsfuel = $mass * 0.00000018 * ((300) - (300 * 0.10 * 5)) * 8;
									?>
									<strong>Est. Fleet Mass (excl. Supers):</strong> <?php echo number_format($mass); ?> kg<br />
									<strong>Est. Fleet Volume (excl. Supers):</strong> <?php echo number_format($volume); ?> m3<br />
									<strong>Est. Isotopes to Bridge 5ly (Titan):</strong> <?php echo number_format($titfuel); ?> isotopes<br />
									<strong>Est. Isotopes to Bridge 8ly (Black Ops):</strong> <?php echo number_format($blopsfuel); ?> isotopes<br />
									<span style="font-size: 0.9em;" class="text-muted"><i>Doesn't account for active MWD/ABs or rigs</span></i>
									<?php //echo $_SERVER['HTTP_REFERER'] . "<br />" . $_SERVER["REQUEST_URI"]; ?>
								<?php } else { ?>
									
								<?php } ?>
							</div>
						</div>
					
						<?php if($towerurl != null || $towerdata['towers'] > 0) { ?>
						<div class="panel panel-default">
							<div class="panel-heading"><strong>Control Towers</strong></div>
							
							<div class="panel-body">
							<?php if($towerurl != null) { ?>
								<?php echo $tower; ?><br />
							<?php } ?>
							
							<?php if($towerdata['towers'] > 0) { ?>
								<strong>Towers Detected:</strong> <? echo $towerdata['towers']; ?><br />
								<strong>Force Fields Detected:</strong> <? echo $towerdata['forcefields']; ?><br />
								<strong>Offline Towers:</strong> <? echo ($towerdata['towers'] - $towerdata['forcefields']); ?>
							<?php } ?>
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
	</div>
</body>
