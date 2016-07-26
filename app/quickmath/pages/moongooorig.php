<?php
$towers = array();
function addtower($id, $name, $blockid, $usage) {
	global $towers;
	$t['id'] = $id;
	$t['name'] = $name;
	$t['blockid'] = $blockid;
	$t['usage'] = $usage;
	$towers[] = $t;
}

$minerals = array();
function addmineral($id, $name, $class) {
	global $minerals;
	$m['id'] = $id;
	$m['name'] = $name;
	$m['class'] = $class;
	$minerals[] = $m;
}

function isk($isk) {
	return number_format($isk, 2, '.', ',') . " ISK";
}

function getEveCentralData($id) {
	$mem = new Memcache;
	$mem->connect('127.0.0.1', 11211);
	
	//Try to get
	$json = $mem->get("evecentral-".$id);
	if($json === false) {
		$json = file_get_contents("http://api.eve-central.com/api/marketstat/json?typeid=".$id."&usesystem=30000142");
		$json = json_decode($json);
		$json = $json[0];
		$json = json_encode($json);
		$mem->set("evecentral-".$id, $json, false, 3600);
	}
	return json_decode($json, true);
}

function getSell($id) {
	$json = getEveCentralData($id);
	return $json['sell']['fivePercent'];
}

function getBuy($id) {
	$json = getEveCentralData($id);
	return $json['buy']['fivePercent'];
}

//Control Tower data
addtower(12235, "Amarr Large", 4247, 40);
addtower(20059, "Amarr Medium", 4247, 20);
addtower(20060, "Amarr Small", 4247, 10);
addtower(12236, "Gallente Large", 4312, 40);
addtower(20063, "Gallente Medium", 4312, 20);
addtower(20064, "Gallente Small", 4312, 10);
addtower(16214, "Minmatar Large", 4246, 40);
addtower(20065, "Minmatar Medium", 4246, 20);
addtower(20066, "Minmatar Small", 4246, 10);
addtower(16213, "Caldari Large", 4051, 40);
addtower(20061, "Caldari Medium", 4051, 20);
addtower(20062, "Caldari Small", 4051, 10);


//Mineral Data
addmineral(16650, "Dysprosium", 64);
addmineral(16651, "Neodymium", 64);
addmineral(16652, "Promethium", 64);
addmineral(16653, "Thulium", 64);
addmineral(16649, "Technetium", 32);
addmineral(16648, "Hafnium", 32);
addmineral(16647, "Caesium", 32);
addmineral(16646, "Mercury", 32);
addmineral(16643, "Cadmium", 16);
addmineral(16644, "Platinum", 16);
addmineral(16642, "Vanadium", 16);
addmineral(16641, "Chromium", 16);
addmineral(16640, "Cobalt", 8);
addmineral(16639, "Scandium", 8);
addmineral(16638, "Titanium", 8);
addmineral(16637, "Tungsten", 8);


//Check active tower
if(!isset($_GET['data'])) {
	$tower = 16214;
} else {
	$tower = $_GET['data'];
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
	
	<script type="text/javascript">
		var minerals = <? echo json_encode($minerals); ?>;
		var towers = <? echo json_encode($towers); ?>;
	</script>
</head>
<body>
	
	<?php include("../../header.php"); ?>


	<div class="container">
		<div class="starter-template">
			<h1>Moon Goo Profitability</h1>
			
			<select name="towertype" style="color: rgb(44, 62, 80);" onchange="window.location.href='/quickmath/moongoo/'+this.value">
				<?php foreach($towers as $t) { ?>
					<option value="<?php echo $t['id']; ?>"<?php if($tower == $t['id']) { echo ' selected="selected"'; $tower = $t;} ?>><?php echo $t['name']; ?></option>
				<?php } ?>
			</select>
			
			<table class="table table-striped table-hover" id="minerals">
				<thead>
					<tr>
						<th></th>
						<th></th>
						<th>Mineral</th>
						<th class="text-right">Sell (per unit)</th>
						<th class="text-right">Income</th>
						<th class="text-right">Fuel Cost</th>
						<th class="text-right">Profit</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($minerals as $m) { 
						$sell = getSell($m['id']);
						$income = $sell * 100 * 24 * 30;
						$fuelcost = getSell($tower['blockid']) * $tower['usage'] * 24 * 30;
						$profit = $income - $fuelcost;
						if($profit > 0) {
							$profitclass = 'class="text-success"';
						} else {
							$profitclass = 'class="text-danger"';
						}
						?>
					<tr>
						<td class="r<?php echo $m['class']; ?>" align="center" width="30"><strong><span class="rtype">R<?php echo $m['class']; ?></span></strong></td>
						<td style="background: url('https://image.eveonline.com/Type/<?php echo $m['id']; ?>_32.png') no-repeat 4px 4px; width: 32px;">&nbsp</td>
						<td><a class="mineral-link" style="text-decoration: none;" href="https://eve-central.com/home/quicklook.html?typeid=<?php echo $m['id']; ?>"><?php echo $m['name']; ?></a></td>
						<td align="right"><?php echo isk($sell); ?></td>
						<td align="right"><?php echo isk($income); ?></td>
						<td align="right"><?php echo isk($fuelcost); ?></td>
						<td align="right" <? echo $profitclass; ?>><?php echo isk($profit); ?></td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
			
			<div>
				<table class="table table-striped table-hover" id="fuel">
					<thead>
						<tr>
							<th>&nbsp</th>
							<th>Fuel Blocks</th>
							<th class="text-right">Sell (per unit)</th>
							<th class="text-right">Buy (per unit)</th>
							<th class="text-right">10</th>
							<th class="text-right">20</th>
							<th class="text-right">40</th>
						</tr>
					</thead>
					
					<?php
						$blocks = array(
							array("name" => "Amarr Fuel Block", "id" => 4247),
							array("name" => "Gallente Fuel Block", "id" => 4312),
							array("name" => "Minmatar Fuel Block", "id" => 4246),
							array("name" => "Caldari Fuel Block", "id" => 4051)
						);
					?>
					<tbody>
						<?php foreach($blocks as $block) { 
							$sell = getSell($block['id']);
							$buy = getBuy($block['id']);
						?>
						<tr>
							<td style="background: url('https://image.eveonline.com/Type/<?php echo $block['id']; ?>_32.png') no-repeat 4px 4px; width: 42px;">&nbsp</td>
							<td><?php echo $block['name']; ?></td>
							<td class="text-right"><?php echo isk($sell); ?></td>
							<td class="text-right"><?php echo isk($buy); ?></td>
							<td class="text-right"><?php echo isk($sell * 10); ?></td>
							<td class="text-right"><?php echo isk($sell * 20); ?></td>
							<td class="text-right"><?php echo isk($sell * 40); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<h6><i>* All figures based on 30 day month unless stated otherwise</i></h6>
			<h6><i>** All figures calculated using 5th percentile sell price from <a href="https://eve-central.com/">eve-central</a></i></h6>
		</div>
	</div>
</body>
