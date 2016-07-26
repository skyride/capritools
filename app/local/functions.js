function highlightOn(id) {
	$("#ticker-"+id).fadeTo(0, 1);
	$("#bar-"+id).addClass("selected");
	for(i = 0; i < assocs[id].length; i++) {
		$("#ticker-"+assocs[id][i]).fadeTo(0, 1);
		$("#bar-"+assocs[id][i]).addClass("selected");
	}
}

function highlightOff(id) {
	$("#ticker-"+id).fadeTo(0, 0.06);
	$("#bar-"+id).removeClass("selected");
	for(i = 0; i < assocs[id].length; i++) {
		$("#ticker-"+assocs[id][i]).fadeTo(0, 0.06);
		$("#bar-"+assocs[id][i]).removeClass("selected");
	}
}