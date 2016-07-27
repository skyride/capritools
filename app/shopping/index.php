<?php
include("config.php");
include("functions.php");

if(isset($_GET['data'])) {
	$data = json_decode(getList($_GET['data']), true);
	$sList = json_encode($data['sList']);
	$title = $data['title'];
	$comments = $data['comments'];
} else {
	$sList = "[]";
	$title = "";
	$comments = "";
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
	<script type="text/javascript" src="/shopping/items.js"></script>
	<script type="text/javascript" src="/shopping/lscache.min.js"></script>
	<script src="/shopping/functions.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
	<?php include("../switcher.php"); ?>
	<link rel="stylesheet" href="/shopping/css/custom.css">
	<!-- Optional theme -->
	<!-- Latest compiled and minified JavaScript -->


	<!-- Custom Page CSS -->
</head>
<body>
	
	<?php include("../header.php"); ?>


	<div class="container">
		<div class="starter-template">
			<h1>Shopping List</h1>
			
			<div id="resultarea">
				<div class="input-group">
					<input id="search" type="text" class="form-control" placeholder="Search" autocomplete="off" onkeyup="searchchange(this.value);" onfocus="searchchange(this.value);">
					<span class="input-group-btn">
						<button class="btn btn-primary" type="button" onclick="$('#fitarea').show(150);">Import Fit/Contract</button>
					</span>
				</div>
				<div id="autocomplete" class="autocomplete">
					<table id="results" class="table table-striped table-hover noselect" style="margin-bottom: 0px;">
						<tr class="searchresult">
							<td style="background: url('https://image.eveonline.com/Type/18883_32.png') no-repeat 4px 4px; width: 38px;"></td>
							<td>Centum A-Type Energized Adaptive Nano Membrane</td>
						</tr>
					</table>
				</div>
			</div>
			
			<div id="fitarea">
				<hr />
				<h4>Import Fit</h4>
				<textarea id="fit-data" class="form-control mono" rows="7" placeholder="Paste your fit here..."></textarea><br />
				<div width="100%" align="right">
					<div class="input-group" style="width: 30%;">
						<span class="input-group-addon">Quantity:</span>
						<input type="number" id="fit-quantity" value=1 min=1 class="form-control"/>
						<span class="input-group-btn">
							<button class="btn btn-primary" type="button" onclick="importFit();">Import</button>
						</span>
					</div>
				</div>
			</div>
			
			<hr />
			
			<input id="title" type="text" class="form-control input-lg" placeholder="Title..." autocomplete="off" value="<?php echo $title; ?>"/>
			<div>
				<table class="table table-striped table-hover">
					<thead>
						<tr>
							<th>&nbsp</th>
							<th>Item</th>
							<th class="text-right">Cost (per unit)</th>
							<th class="text-right">Quantity</th>
							<th class="text-right">Volume</th>
							<th class="text-right" style="width: 180px;">Total</th>
							<th class="text-right"></th>
						</tr>
					</thead>

					<tbody id="list-contents">
					</tbody>
					
					<tfoot>
						<tr>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th id="total-volume" class="text-right">0.00 m3</th>
							<th id="total-cost" class="text-right">0.00 ISK</th>
							<th></th>
						</tr>
					</tfoot>
				</table>
			</div>
			
			<div class="row">
				<div class="col-lg-5">
					<textarea id="comments" class="form-control input-sm" rows="5" placeholder="Comments..."><?php echo $comments; ?></textarea>
				</div>
				
				<div class="col-lg-7 text-right">
					<a class="btn btn-primary btn-lg" onclick="savelist();">Submit</a>
				</div>
			</div>
			
			<div class="row">
				<br />
				<h6><i>* All figures are lowest sell price in The Forge taken from <a href="https://eve-central.com/">eve-central</a></i></h6>
			</div>
			
			<form id="save"  method="POST" action="/shopping/save.php">
				<input id="save-data" name="save-data" type="hidden" value="" />
			</form>
			
			<div id="add-modal" class="modal fade">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title">Add New Item</h4>
						</div>
						<div class="modal-body" style="padding: 6px; padding-left: 12px; height: 110px;">
							<div class="col-xs-2">
								<img id="add-modal-img" src="https://image.eveonline.com/Type/29984_64.png"/>
							</div>
							<div class="col-xs-10" style="padding-left: 20px;">
								<table style="padding: 3px; width: 100%;">
									<tr>
										<td style="width: 250px;"><strong><span id="add-modal-typename">Tengu<span></strong></td>
										<td class="text-right">Quantity: <input id="add-modal-quantity" class="pull-right text-right" style="width: 90px; margin-left: 10px;" onkeyup="addItemRecalc();" onchange="addItemRecalc();" type="number" name="quantity" value=1></td>
									</tr>
									<tr>
										<td style="padding-top: 7px;">Cost (per unit):</td>
										<td style="padding-top: 7px;" class="text-right"><span id="add-modal-cost">5,000,000.00 ISK</span></td>
									</tr>
									<tr>
										<td style="padding-top: 7px;">Total Cost:</td>
										<td style="padding-top: 7px;" class="text-right"><span id="add-modal-total">5,000,000.00 ISK</span></td>
									</tr>
								</table>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-primary" onclick="addItem(addItemCur.typeID, parseFloat($('#add-modal-quantity').val())); $('#add-modal').modal('hide');">Add to List</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						</div>
					</div><!-- /.modal-content -->
				</div><!-- /.modal-dialog -->
			</div><!-- /.modal -->
			
		</div>
	</div>
</body>
<script>
	var sList = <?php echo $sList; ?>;
	
	$('#autocomplete').hide(0);
	$('#fitarea').hide(0);
	$('#list-contents').empty();

	$('body').click(function() {
		$('#autocomplete').hide(150);
		$('#fitarea').hide(150);
		//$('#search').val("");
	});

	$('#resultarea').click(function(event){
		event.stopPropagation();
	});
	
	$('#fitarea').click(function(event){
		event.stopPropagation();
		$('#autocomplete').hide(150);
	});
	
	$('#add-modal').click(function(event){
		event.stopPropagation();
	});
	
	updateTable();
	fillQueue();
</script>