<?php
include("functions.php");

//Parse local scan
if(isset($_POST['paste']) && isset($_POST['system'])) {
	$lscan = buildFromListNew(explode("\n", $_POST['paste']));
	$key = saveLScan($lscan, $_POST['system']);
	file_put_contents("scans/".$key, $_POST['paste']);
	header('Location: /local/'.$key);
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
	<script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.11.1/typeahead.bundle.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/4.1.2/papaparse.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.3/handlebars.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
	<link rel="stylesheet" href="/local/css/typeaheadjs.css">
	<?php include("../switcher.php"); ?>
	<link rel="stylesheet" href="/local/css/custom.css">
	<!-- Optional theme -->
	<!-- Latest compiled and minified JavaScript -->
	
	<!-- Custom Page CSS -->
</head>
<body>
	
	<?php include("../header.php"); ?>


	<div class="container">
		<div class="starter-template">
			<h1>Localscan Tool</h1>
			<p class="lead">
				<form method="POST">
					<i><legend>Paste your local/fleet scan into the box below</legend></i>
					<textarea id="paste" name="paste" class="form-control" rows="8" placeholder="Local Scan"></textarea><br />
					<input id="system" name="system" class="typeahead form-control" type="text" placeholder="System (Optional)"/>
					<input type="submit" style="margin-top: 16px;" class="btn btn-lg btn-primary"></input>
				</form>
			</p>
	</div>
</body>

<script>
	//Load the system list into the typeahead
	Papa.parse("systems.csv", {
	download: true,
	header: true,
		complete: function(results) {
			$('#system').typeahead({
				hint: true,
				highlight: true,
				minLength: 0,
			}, {
				name: 'system',
				limit: 200,
				displayKey: 'systemName',
				source: substringMatcher(results.data),
				templates: {
					suggestion: Handlebars.compile("<div class='sysbar'><div class='sec {{secClass}}'>{{security}}</div> {{systemName}} <span class='system-details'>\< {{constellationName}} \< {{regionName}}</span></div>")
				}
			});
		}
	});
	
	
	var substringMatcher = function(strs) {
		return function findMatches(q, cb) {
			var matches, substringRegex;

			// an array that will be populated with substring matches
			matches = [];

			// regex used to determine if a string contains the substring `q`
			substrRegex = new RegExp(q, 'i');

			// iterate through the pool of strings and for any string that
			// contains the substring `q`, add it to the `matches` array
			$.each(strs, function(i, sys) {
				if (substrRegex.test(sys.systemName) || substrRegex.test(sys.constellationName) || substrRegex.test(sys.regionName)) {
					if(sys.security <= 0) {
						sys.secClass = "sec-0-0";
					} else {
						sec = sys.security.toString().replace(".", "-");
						sys.secClass = "sec-" + sec;
					}
					matches.push(sys);
				}
			});

			cb(matches);
		};
	};
</script>