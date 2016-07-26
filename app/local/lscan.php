<?php
include("functions.php");
include("display.php");

$lscan = getLScan($_GET['key']);
$lscaninfo = getLScanInfo($_GET['key']);
$assocs = json_encode($lscan['assocs']);
$system = getSystemInfo($lscaninfo['system']);

//Process system text
if($system != null) {
	if($system['security'] <= 0) {
		$secClass = "sec-0-0";
	} else {
		$sec = str_replace(".", "-", $system['security']);
		$secClass = "sec-" . $sec;
	}
	$systemtext = "<span class='".$secClass."' style='margin-right: 4px;'>" . $system['security'] . "</span> " . $system['solarSystemName'] . " <span class='system-details'>&lt; " . $system['constellationName'] . " &lt; " . $system['regionName'] . "</span><br />";
}

//saveHit();
?>
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

	<!-- Latest compiled and minified CSS -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
	<script src="functions.js"></script>
	<link href='http://fonts.googleapis.com/css?family=Inconsolata:400,700' rel='stylesheet' type='text/css'>
	<?php include("../switcher.php"); ?>
	<link rel="stylesheet" href="/local/css/custom.css">
	<script>
var assocs = <?php echo $assocs; ?>;
	</script>
	<!-- Optional theme -->
	<!-- Latest compiled and minified JavaScript -->
</head>
<body>
	<?php include("../header.php"); ?>


	<div class="container">

			<div class="col-lg-12">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<?php if(isset($systemtext)) { echo $systemtext; } ?>
					Total Characters: <strong><?php echo $lscaninfo['total']; ?></strong><span class="pull-right"><strong>Created:</strong> <?php echo date("d/m/y H:i", $lscaninfo['created']); ?> EVE Time</span>
				</div>
				
				<div class="panel-body">
					<div class="progress">
						<?php echo allianceBar($lscan, $lscaninfo['total']); ?>
					</div>
					
					<div class="progress progress-striped">
						<?php echo corpBar($lscan, $lscaninfo['total']); ?>
					</div>
				
					<div class="col-lg-6">
						<div class="panel panel-default">
							<div class="panel-heading"><strong>Alliances (<?php echo count($lscan['alliances']); ?>)</strong></div>
							
							<div class="panel-body">
								<div class="bs-component">
									<ul class="list-group">
										<?php echo showAlliances($lscan); ?>
									</ul>
								</div>
							</div>
						</div>
						
						<a href="<?php echo $_GET['key']; ?>/raw" class="btn btn-primary btn-lg" style="width: 100%">Raw Scan</a>
					</div>
					
					
					<div class="col-lg-6">
						<div class="panel panel-default">
							<div class="panel-heading"><strong>Corporations (<?php echo count($lscan['corps']); ?>)</strong></div>
							
							<div class="panel-body">
								<div class="bs-component">
									<ul class="list-group">
										<?php echo showCorps($lscan); ?>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
	</div>
</body>
<script>
$('[data-toggle="tooltip"]').tooltip({animation: false});
</script>