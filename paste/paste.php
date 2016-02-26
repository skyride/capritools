<?php
include("functions.php");

//Get paste
$paste = getPaste($_GET['key']);
$info = getPasteInfo($_GET['key']);
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
	<?php include("../switcher.php"); ?>
	<link rel="stylesheet" href="/paste/css/custom.css">
	<!-- Optional theme -->
	<!-- Latest compiled and minified JavaScript -->

	<script type="text/javascript">
		function fnSelect(objId) {
		fnDeSelect();
		if (document.selection) {
		var range = document.body.createTextRange();
 	        range.moveToElementText(document.getElementById(objId));
		range.select();
		}
		else if (window.getSelection) {
		var range = document.createRange();
		range.selectNode(document.getElementById(objId));
		window.getSelection().addRange(range);
		}
	}

	function fnDeSelect() {
		if (document.selection) document.selection.empty(); 
		else if (window.getSelection)
                window.getSelection().removeAllRanges();
	}
	</script>

	<!-- Custom Page CSS -->
</head>
<body>
	
	<?php include("../header.php"); ?>


	<div class="container">
		<div class="starter-template">
			<h1>Pastebin Tool</h1>
				<div id="data" class="well content-area" ondblclick="fnSelect('data');"><? echo htmlspecialchars($paste); ?></div>
				<div>
					<span class="text-muted"><?php echo strlen($paste); ?> characters | <?php echo mb_strlen($paste); ?> bytes</span><br />
					<span class="text-muted">Created: <?php echo date("d/m/y H:i", $info['created']); ?></span>
				</div>
	</div>
</body>
