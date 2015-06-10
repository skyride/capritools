<?php
include("functions.php");

//Save paste if it was recieved
if(isset($_POST['paste'])) {
	$key = savePaste($_POST['paste']);
	header('Location: /paste/'.$key);
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
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.3.14/angular.min.js"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.3.14/angular-sanitize.min.js"></script>
	<script type="text/javascript" src="/shopping/items.js"></script>
	<script src="/itemdb/itemdb.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
	<?php include("../switcher.php"); ?>
	<link rel="stylesheet" href="/itemdb/css/custom.css">
	<!-- Optional theme -->
	<!-- Latest compiled and minified JavaScript -->
	
	<!-- Custom Page CSS -->
</head>
<body>
	
	<?php include("../header.php"); ?>


	<div class="container">
		<div id="app" ng-app="myApp" ng-controller="myCtrl" ng-init="init()">
			<div class="starter-template" style="display: table; width: 100%; border-top: 2px solid rgb(220, 228, 236);">
				<div id="searchcol" style="display: table-cell; vertical-align: top; height: 800px; margin-bottom: 15px; background-image: url('img/vr.png'); background-position-x: right; background-repeat: no-repeat; padding-top: 10px; padding-right: 7px;"
				ng-class="{'searchcol-in':focused==true,'searchcol-out':focused==false}" ng-click="focused = true">
					<div class="btn-group" style="width: 100%;">
						<input id="search" type="text" class="form-control" placeholder="Search" autocomplete="off" ng-model="search" ng-change="updateSearch()">
						<span id="searchclear" class="glyphicon glyphicon-remove-circle" ng-click="search = ''; searchItems = []" ng-hide="!search.length"></span>
					</div>
					
					<div id="results" style="overflow-y: auto; max-height: 800px; border-radius: 4px; border: 2px solid #dce4ec;" ng-hide="!searchItems.length">
						<table class="table table-striped table-hover noselect" style="margin-bottom: 0px;">
							<tbody>
								<tr class="searchresult" ng-repeat="item in searchItems" ng-click="selectItem(item.typeID)">
									<td style="background: url('https://image.eveonline.com/Type/{{item.typeID}}_32.png') no-repeat 4px 4px; width: 38px;"></td>
									<td>{{item.typeName}}</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				
				<div id="itemcol" style="display: table-cell; width: auto; vertical-align: top; border-left: 2px solid rgb(220, 228, 236); padding-top: 10px;" ng-click="focused = false">
					<div id="item" ng-hide="itemID == 0">
						<div id="itemheader" class="col-md-12">
							<img style="margin-right: 12px;" class="pull-left" ng-src="https://image.eveonline.com/Type/{{itemID}}_64.png" />
						
							<h2 style="margin-top: 0px;">{{itemName}}</h2>
							<h4 style="margin-top: -10px; margin-bottom: 16px;">{{itemCategoryName + " > " + itemGroupName}}</h4>
							
							<div class="well well-sm" ng-bind-html="description">
							</div>
						</div>
						
						<div id="attributes" class="col-md-6" ng-hide="false">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h3 class="panel-title">Attributes</h3>
								</div>
								
								<table class="table table-striped" style="font-size: 13px; font-family: sans-serif;">
									<tbody>
										<tr ng-repeat="attr in attributes">
											<td><img width="32" height="32" ng-src="https://wiki.eveonline.com/wikiEN/images_icons/iconID_{{attr.iconID}}_grey.jpg"></td>
											<td>{{attr.displayName}}</td>
											<td>{{attr.displayValue}}</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div id="ship-modal" class="modal fade">
				<div class="modal-dialog" style="width: 90%">
					<div class="modal-content">
						<div class="modal-body">
							<!--<iframe style="width: 100%; height: 80%;" frameborder="0" src="http://www.caldariprimeponyclub.com/demo/?hull=Brutix"></iframe> -->
						</div>
					</div><!-- /.modal-content -->
				</div><!-- /.modal-dialog -->
			</div><!-- /.modal -->
		</div>
	</div>
</body>