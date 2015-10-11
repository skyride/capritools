<?php

function showAlliances($lscan) {
	$html = "";
	foreach($lscan['alliances'] as $alliance) {
		$url = "http://evemaps.dotlan.net/alliance/".str_replace(" ", "_", $alliance['name']);
		$html .= '<li onmouseover="highlightOn('.$alliance['id'].');" onmouseout="highlightOff('.$alliance['id'].');" class="list-group-item"><div id="ticker-'.$alliance['id'].'" class="bgticker">['.$alliance['ticker'].']</div><strong>'.$alliance['name'].'</strong> <span class="badge">'.$alliance['quantity'].'</span> <a href="'.$url.'"><img class="evelogo" src="https://image.eveonline.com/Alliance/'.$alliance['id'].'_64.png"/></a></li>'."\n";
	}
	
	return $html;
}


function showCorps($lscan) {
	$html = "";
	foreach($lscan['corps'] as $corp) {
		$url = "http://evemaps.dotlan.net/corp/".str_replace(" ", "_", $corp['name']);
		$html .= '<li onmouseover="highlightOn('.$corp['id'].');" onmouseout="highlightOff('.$corp['id'].');" class="list-group-item"><div id="ticker-'.$corp['id'].'" class="bgticker">['.$corp['ticker'].']</div><strong>'.$corp['name'].'</strong> <span class="badge">'.$corp['quantity'].'</span> <a href="'.$url.'"><img class="evelogo" src="https://image.eveonline.com/Corporation/'.$corp['id'].'_64.png"/></a></li>'."\n";
	}
	
	return $html;
}


function allianceBar($lscan, $total) {
	if($total < 500) {
		$divisor = 2;
	} else {
		$divisor = 1.5;
	}

	//Build element list
	$elements = array();
	$other = 0;
	foreach($lscan['alliances'] as $alliance) {
		if((100 / $total) * $alliance['quantity'] > $divisor) {
			$elements[] = $alliance;
		} else {
			$other += $alliance['quantity'];
		}
	}
	
	$styles = array("asd", "info", "success", "warning", "danger");
	
	$html = "";
	$totalsize = 0;
	foreach($elements as $e) {
		$style = next($styles);
		if($style === false) {
			reset($styles);
			$style = next($styles);
		}
		
		$size = round((100 / $total) * $e['quantity'], 2);
		$totalsize += $size;
		$html .= '<div id="bar-'.$e['id'].'" data-toggle="tooltip" data-placement="top" title="'.$e['name'].' ['.$e['ticker'].'] ('.$e['quantity'].')" class="progress-bar progress-bar-'.$style.'" style="width: '.$size.'%" onmouseover="highlightOn('.$e['id'].');" onmouseout="highlightOff('.$e['id'].');">'.$e['name'].' ('.$e['quantity'].')</div>'."\n";
	}
	
	//Other alliance
	if($other > 0) {
		$size = round((100 / $total) * $other, 2);
		$html .= '<div class="progress-bar" data-toggle="tooltip" data-placement="top" title="Other Alliances ('.$other.')" style="width: '.$size.'%;">Other Alliances ('.$other.')</div>'."\n";
	}
	
	return $html;
}


function corpBar($lscan, $total) {
	if($total < 500) {
		$divisor = 2;
	} else {
		$divisor = 1.5;
	}

	//Build element list
	$elements = array();
	$other = 0;
	foreach($lscan['corps'] as $corp) {
		if((100 / $total) * $corp['quantity'] > $divisor) {
			$elements[] = $corp;
		} else {
			$other += $corp['quantity'];
		}
	}
	
	$styles = array("asd", "info", "success", "warning", "danger");
	
	$totalsize = 0;
	$html = "";
	foreach($elements as $e) {
		$style = next($styles);
		if($style === false) {
			reset($styles);
			$style = next($styles);
		}
		$size = round((100 / $total) * $e['quantity'], 2);
		$totalsize += $size;
		$html .= '<div id="bar-'.$e['id'].'" data-toggle="tooltip" data-placement="top" title="'.$e['name'].' ['.$e['ticker'].'] ('.$e['quantity'].')" class="progress-bar progress-bar-'.$style.'" style="width: '.$size.'%" onmouseover="highlightOn('.$e['id'].');" onmouseout="highlightOff('.$e['id'].');">'.$e['name'].' ('.$e['quantity'].')</div>'."\n";
	}
	
	//Other Corps
	$size = round((100 / $total) * $other);
	$html .= '<div class="progress-bar" data-toggle="tooltip" data-placement="top" title="Other Corps ('.$other.')" style="width: '.(100 - $totalsize).'%;">Other Corps ('.$other.')</div>'."\n";
	
	return $html;
}

?>