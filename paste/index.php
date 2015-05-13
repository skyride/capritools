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
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
	<?php include("../switcher.php"); ?>
	<link rel="stylesheet" href="/paste/css/custom.css">
	<!-- Optional theme -->
	<!-- Latest compiled and minified JavaScript -->
	
	<!-- Custom Page CSS -->
</head>
<body>
	
	<?php include("../header.php"); ?>


	<div class="container">
		<div class="starter-template">
			<h1>Pastebin Tool</h1>
			<p class="lead">
				<form method="POST">
					<fieldset>
					  <i><legend>Paste your text into the box below</legend></i>
					  <div class="form-group">
							<textarea id="paste" name="paste" class="form-control mono" rows="10"></textarea><br />
						
							<button type="submit" class="btn btn-primary">Submit</button>
					</fieldset>
				</form>
			</p>
	</div>
</body>
