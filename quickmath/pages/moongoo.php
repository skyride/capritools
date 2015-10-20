<?php
$towers = array();
function addtower($id, $name, $fullName, $blockid, $usage) {
	global $towers;
	$t['id'] = $id;
	$t['name'] = $name;
	$t['fullName'] = $fullName;
	$t['blockid'] = $blockid;
	$t['usage'] = $usage;
	$t['sell'] = getSell($id);
	$towers[] = $t;
}

$minerals = array();
function addmineral($id, $name, $class) {
	global $minerals;
	$m['id'] = $id;
	$m['name'] = $name;
	$m['class'] = $class;
	$m['sell'] = getSell($id);
	$m['buy'] = getBuy($id);
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

$blocks = array(
	array("name" => "Amarr Fuel Block", "id" => 4247, "sell" => getSell(4247), "buy" => getBuy(4247)),
	array("name" => "Gallente Fuel Block", "id" => 4312, "sell" => getSell(4312), "buy" => getBuy(4312)),
	array("name" => "Minmatar Fuel Block", "id" => 4246, "sell" => getSell(4246), "buy" => getBuy(4246)),
	array("name" => "Caldari Fuel Block", "id" => 4051, "sell" => getSell(4051), "buy" => getBuy(4051))
);

//Control Tower data
addtower(12235, "Amarr Large", "Amarr Control Tower", 4247, 40);
addtower(20059, "Amarr Medium", "Amarr Control Tower Medium", 4247, 20);
addtower(20060, "Amarr Small", "Amarr Control Tower Small", 4247, 10);
addtower(12236, "Gallente Large", "Gallente Control Tower", 4312, 40);
addtower(20063, "Gallente Medium", "Gallente Control Tower Medium", 4312, 20);
addtower(20064, "Gallente Small", "Gallente Control Tower Small", 4312, 10);
addtower(16214, "Minmatar Large", "Minmatar Control Tower", 4246, 40);
addtower(20065, "Minmatar Medium", "Minmatar Control Tower Medium", 4246, 20);
addtower(20066, "Minmatar Small", "Minmatar Control Tower Small", 4246, 10);
addtower(16213, "Caldari Large", "Caldari Control Tower", 4051, 40);
addtower(20061, "Caldari Medium", "Caldari Control Tower Medium", 4051, 20);
addtower(20062, "Caldari Small", "Caldari Control Tower Small", 4051, 10);


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
addmineral(16634, "Atmospheric Gases", 4);
addmineral(16635, "Evaporate Deposits", 4);
addmineral(16633, "Hydrocarbons", 4);
addmineral(16636, "Silicates", 4);


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
	<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.3.14/angular.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/numeral.js/1.4.5/numeral.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
	<?php include("../../switcher.php"); ?>
	<link rel="stylesheet" href="/quickmath/css/custom.css">
	<!-- Optional theme -->
	<!-- Latest compiled and minified JavaScript -->


	<!-- Custom Page CSS -->
	
	<script type="text/javascript">
		var app = angular.module('myApp', []);
		app.controller('myCtrl', function($scope, $http, $location) {
			$scope.minerals = <? echo json_encode($minerals); ?>;
			$scope.towers = <? echo json_encode($towers); ?>;
			$scope.blocks = <? echo json_encode($blocks); ?>;
			
			$scope.active = $scope.towers[0].id;
			$scope.sovbonus = false;
			
			$scope.isk = function(isk) {
				return numeral(isk).format('0,0.00') + " ISK";
			};
			
			
			$scope.getFuelCost = function() {
				var tower = $scope.getTower($scope.active);
				var block = $scope.getBlock(tower.blockid);
				var cost = block.sell * $scope.getUsage(tower.usage) * 24 * 30;
				return cost;
			};
			
			$scope.income = function(sell) {
				return sell * 100 * 30 * 24;
			}
			
			$scope.profitClass = function(isk) {
				if(isk >= 0) {
					return "text-success";
				} else {
					return "text-danger";
				}
			}
			
			$scope.getUsage = function(usage) {
				if($scope.sovbonus == true) {
					return Math.round(usage * 0.75);
				} else {
					return usage;
				}
			}
			
			
			$scope.activeTower = function() {
				return $scope.getTower($scope.active);
			}
			
			$scope.getMineral = function(id) {
				for(i = 0; i < $scope.minerals.length; i++) {
					if($scope.minerals[i].id == id) {
						return $scope.minerals[i];
					}
				}
			};
			
			$scope.getTower = function(id) {
				for(i = 0; i < $scope.towers.length; i++) {
					if($scope.towers[i].id == id) {
						return $scope.towers[i];
					}
				}
			};
			
			$scope.getBlock = function(id) {
				for(i = 0; i < $scope.blocks.length; i++) {
					if($scope.blocks[i].id == id) {
						return $scope.blocks[i];
					}
				}
			};
		});
	</script>
</head>
<body>
	
	<?php include("../../header.php"); ?>

	<div id="app" ng-app="myApp" ng-controller="myCtrl">
		<div class="container">
			<div class="starter-template">
				<h1>Moon Goo Profitability</h1>
				
				<select name="towertype" class="pull-left" ng-model="active">
					<option ng-repeat="tower in towers" value="{{tower.id}}">{{tower.name}}</option>
				</select>
				
				<span class="pull-left" style="margin-left: 10px;"><input type="checkbox" ng-model="sovbonus"> Sov Fuel Bonus</span>
				
				&nbsp
				
				<hr />
				
				<div class="well well-sm" style="height: 136px;">
					<div class="col-md-1">
						<img class="pull-right" ng-src="https://image.eveonline.com/Type/{{activeTower().id}}_64.png">
					</div>
					
					<div class="col-md-11">
						<div>
							<h3 style="margin-top: 0px; margin-bottom: 4px;">{{activeTower().fullName}}</h3>
							<ul style="list-style-type: none; padding: 0px; margin: 0px;">
								<li><b>Cost (Sell):</b> {{isk(activeTower().sell)}}</li>
								<li><b>Hourly Usage:</b> {{getUsage(activeTower().usage)}} blocks ({{getUsage(activeTower().usage) * 5}} m3)</li>
								<li><b>Weekly Usage:</b> {{getUsage(activeTower().usage) * 24 * 7}} blocks ({{getUsage(activeTower().usage) * 5 * 24 * 7}} m3)</li>
								<li><b>Monthly Usage:</b> {{getUsage(activeTower().usage) * 24 * 30}} blocks ({{getUsage(activeTower().usage) * 5 * 24 * 30}} m3)</li>
							</ul>
						</div>
					</div>
				</div>
				
				<table class="table table-striped table-hover" id="minerals">
					<thead>
						<tr>
							<th width="30"></th>
							<th></th>
							<th>Mineral</th>
							<th class="text-right">Sell (per unit)</th>
							<th class="text-right">Income</th>
							<th class="text-right">Fuel Cost</th>
							<th class="text-right">Profit</th>
						</tr>
					</thead>
					<tbody>
						<tr ng-repeat="mineral in minerals">
							<td class="r{{mineral.class}}" align="center" width="30"><strong><span class="rtype">R{{mineral.class}}</span></strong></td>
							<td style="background: url('https://image.eveonline.com/Type/{{mineral.id}}_32.png') no-repeat 4px 4px; width: 32px;">&nbsp</td>
							<td><a class="mineral-link" style="text-decoration: none;" href="https://eve-central.com/home/quicklook.html?typeid={{mineral.id}}">{{mineral.name}}</a></td>
							<td align="right">{{isk(mineral.sell)}}</td>
							<td align="right">{{isk(income(mineral.sell))}}</td>
							<td align="right">{{isk(getFuelCost())}}</td>
							<td align="right" class="{{profitClass(income(mineral.sell) - getFuelCost())}}">{{isk(income(mineral.sell) - getFuelCost())}}</td>
						</tr>
						<?php /*}*/ ?>
					</tbody>
				</table>
				
				<hr />
				
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
						<tbody>
							<tr ng-repeat="block in blocks">
								<td style="background: url('https://image.eveonline.com/Type/{{block.id}}_32.png') no-repeat 4px 4px; width: 42px;">&nbsp</td>
								<td>{{block.name}}</td>
								<td class="text-right">{{isk(block.sell)}}</td>
								<td class="text-right">{{isk(block.buy)}}</td>
								<td class="text-right">{{isk(block.sell * 10)}}</td>
								<td class="text-right">{{isk(block.sell * 20)}}</td>
								<td class="text-right">{{isk(block.sell * 40)}}</td>
							</tr>
						</tbody>
					</table>
				</div>
				<h6><i>* All figures based on 30 day month unless stated otherwise</i></h6>
				<h6><i>** All figures calculated using 5th percentile sell price from <a href="https://eve-central.com/">eve-central</a></i></h6>
			</div>
		</div>
	</div>
</body>
