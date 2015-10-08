<?php 

error_reporting(E_ERROR | E_PARSE);
include("functions.php");

echo "\nRunning:\n";

$list = glob("scans/*");
for($i = 0; $i < count($list); $i++) {
	echo "\t" . $i . "/" . count($list) . " - " . $list[$i] . "... ";
	buildFromListNew(explode("\n", file_get_contents($list[$i])));
	echo "Done!\n";
}

?>