<?php
include("../config.php");
$db = new PDO('mysql:host='.$mysql_host.';dbname=sde;charset=utf8', $mysql_user, $mysql_pass);

function isk($isk) {
	return number_format($isk, 2, '.', ',') . " ISK";
}

//Get implant set list
$st = $db->prepare("SELECT typeId, typeName FROM invTypes WHERE groupID = 300 AND typeName LIKE '% Omega' ORDER BY typeName ASC");
$st->execute();
$rows = $st->fetchAll(PDO::FETCH_ASSOC);
$sets = array();
$implants = array();
foreach($rows as $r) {
	$name = str_replace(" Omega", "", $r['typeName']);
	if($name != "Aurora") {
		$sets[] = $name;
		$implants[] = $name . " Alpha";
		$implants[] = $name . " Beta";
		$implants[] = $name . " Gamma";
		$implants[] = $name . " Delta";
		$implants[] = $name . " Epsilon";
		$implants[] = $name . " Omega";
	}
}

//Build full set -> id translation matrix
$sql = "SELECT typeId, typeName FROM invTypes WHERE typeName IN(::REPLACEME::) ORDER BY typeName ASC";
$str = "";
foreach($implants as $i) {
	$str .= "?, ";
}
$str = substr($str, 0, -2);
$sql = str_replace("::REPLACEME::", $str, $sql);

$st = $db->prepare($sql);
$st->execute($implants);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $r) {
	$items[] = $r['typeId'];
}

//Get price data
$mem = new Memcache;
$mem->connect('127.0.0.1', 11211);
$json = $mem->get("implants-");
if($json === false) {
	$json = file_get_contents("http://api.eve-central.com/api/marketstat/json?typeid=".implode(",", $items)."&usesystem=30000142");
	$mem->set("implants-", $json, false, 3600);
}
$pricing = json_decode($json, true);

$implants = array();
for($i = 0; $i < count($rows); $i++) {
	$implants[$rows[$i]['typeName']]['typeId'] = $rows[$i]['typeId'];
	foreach($pricing as $p) {
		if($p['all']['forQuery']['types'][0] == $rows[$i]['typeId']) {
			$implants[$rows[$i]['typeName']]['sell'] = $p['sell']['fivePercent'];
		}
	}
}

?>
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

	<!-- Latest compiled and minified CSS -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
	<?php include("../../switcher.php"); ?>
	<link rel="stylesheet" href="/quickmath/css/custom.css">
	<!-- Optional theme -->
	<!-- Latest compiled and minified JavaScript -->


	<!-- Custom Page CSS -->
</head>
<body>
	
	<?php include("../../header.php"); ?>


	<div class="container">
		<div class="starter-template">
			<h1>Implant Sets</h1>
			
			<?php foreach($sets as $set) { ?>
			<div class="col-lg-4 col-md-6 .col-xs-12">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<?php echo $set; ?> Set
					</div>
					
					<div class="panel-body">
						<table class="table table-striped table-hover">
							<tbody>
								<?php $total = 0; ?>
								<tr>
									<td style="background: url('https://image.eveonline.com/Type/20499_32.png') no-repeat 4px 4px; width: 42px;">&nbsp</td>
									<td><a href="/itemdb/#/<?php echo $implants[$set." Alpha"]['typeId']; ?>">Alpha</a></td>
									<td><?php echo isk($implants[$set." Alpha"]['sell']); $total += $implants[$set." Alpha"]['sell']; ?></td>
								</tr>
								<tr>
									<td style="background: url('https://image.eveonline.com/Type/20501_32.png') no-repeat 4px 4px; width: 42px;">&nbsp</td>
									<td><a href="/itemdb/#/<?php echo $implants[$set." Beta"]['typeId']; ?>">Beta</a></td>
									<td><?php echo isk($implants[$set." Beta"]['sell']); $total += $implants[$set." Beta"]['sell']; ?></td>
								</tr>
								<tr>
									<td style="background: url('https://image.eveonline.com/Type/20507_32.png') no-repeat 4px 4px; width: 42px;">&nbsp</td>
									<td><a href="/itemdb/#/<?php echo $implants[$set." Gamma"]['typeId']; ?>">Gamma</a></td>
									<td><?php echo isk($implants[$set." Gamma"]['sell']); $total += $implants[$set." Gamma"]['sell']; ?></td>
								</tr>
								<tr>
									<td style="background: url('https://image.eveonline.com/Type/20503_32.png') no-repeat 4px 4px; width: 42px;">&nbsp</td>
									<td><a href="/itemdb/#/<?php echo $implants[$set." Delta"]['typeId']; ?>">Delta</a></td>
									<td><?php echo isk($implants[$set." Delta"]['sell']); $total += $implants[$set." Delta"]['sell']; ?></td>
								</tr>
								<tr>
									<td style="background: url('https://image.eveonline.com/Type/20505_32.png') no-repeat 4px 4px; width: 42px;">&nbsp</td>
									<td><a href="/itemdb/#/<?php echo $implants[$set." Epsilon"]['typeId']; ?>">Epsilon</a></td>
									<td><?php echo isk($implants[$set." Epsilon"]['sell']); $total += $implants[$set." Epsilon"]['sell']; ?></td>
								</tr>
								<tr>
									<td style="background: url('https://image.eveonline.com/Type/20509_32.png') no-repeat 4px 4px; width: 42px;">&nbsp</td>
									<td><a href="/itemdb/#/<?php echo $implants[$set." Omega"]['typeId']; ?>">Omega</a></td>
									<td><?php echo isk($implants[$set." Omega"]['sell']); $total += $implants[$set." Omega"]['sell']; ?></td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<th></th>
									<th>Total:</th>
									<th><?php echo isk($total); ?></th>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
</body>
