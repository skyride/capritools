var priceQueue = [];
var addItemCur;

function isk(price) {
	return price.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + " ISK";
}


function vol(price) {
	return price.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + " m3";
}


function importFit() {
	var fitString = $('#fit-data').val();
	var quantity = parseInt($('#fit-quantity').val());
	var fit = fitString.split("\n");
	
	//Regular expressions
	var cargoRe = /(.+) x([0-9]+)/;
	var emptyRe = /\[empty .+ slot\]/;
	
	//Process input
	var item;
	for(var i = 0; i < fit.length; i++) {	
		//Check for exact matches
		item = getItemFromName(fit[i]);
		if(item != null) {
			addItem(item.typeID, quantity);
			
		//Check for title line
		} else if(fit[i][0] == "[" && fit[i][fit[i].length-1] == "]") {
			//Check it isn't an empty slot
			if(emptyRe.exec(fit[i]) == null) {
		
				var line = fit[i].replace("[", "");
				line = line.replace("]", "");
				line = line.split(", ");
				
				//Add hull
				item = getItemFromName(line[0]);
				if(item != null) {
					addItem(item.typeID, quantity);
				}
				
				//Set title
				$('#title').val(item.typeName + " - " + line[1]);
				
				//Set comments
				$('#comments').val($('#comments').val() + fitString);
			}
			
		//Check for module with a charge loaded
		} else if(fit[i].split(", ").length == 2) {
			var line = fit[i].split(", ");
			
			//Module
			item = getItemFromName(line[0]);
			if(item != null) {
				addItem(item.typeID, quantity);
			}
		
		//Check if its a contract line
		} else if (fit[i].split("\t").length == 5) {
			var line = fit[i].split("\t");
			
			addItem(getItemFromName(line[0].trim()).typeID, quantity * parseInt(line[1]));
			
		//Check if we're dealing with a drone or cargo item
		} else {
			line = cargoRe.exec(fit[i]);
			if(line != null) {
				item = getItemFromName(line[1]);
				if(item != null) {
					addItem(item.typeID, quantity * parseInt(line[2]));
				}
			}
		}
	}
	
	//Flush queue to get prices
	fillQueue();
	$('#fit-data').val("");
	$('#fitarea').hide(150);
}


function addItemUi(id) {
	item = getItemFromId(id);
	addItemCur = item;
	$('#add-modal-typename').empty();
	$('#add-modal-quantity').val(1);
	$('#add-modal-typename').append(item.typeName);
	$('#add-modal-img').attr("src", 'https://image.eveonline.com/Type/'+item.typeID+'_64.png');
	
	addItemRecalc();

	$('#add-modal').modal({
		keyboard: true
	});
}


function addItemRecalc() {
	total = getPrice(addItemCur.typeID);
	quantity = parseFloat($('#add-modal-quantity').val());
	if(total === false) {
		total = '<img style="opacity: 0.2;" src="/shopping/loading.gif" />';
	} else {
		if(total > 0) {
			total = isk(getPrice(addItemCur.typeID) * quantity);
		} else {
			total = "<i>N/A</i>";
		}
	}
	$('#add-modal-cost').html(getPriceString(addItemCur.typeID));
	$('#add-modal-total').html(total);
}


function addItem(id, quantity) {
	//Check if the item is already there
	found = false;
	if(quantity == null) {
		quantity = 1;
	}
	for(i = 0; i < sList.length; i++) {
		if(sList[i].typeID == id) {
			sList[i].quantity+= quantity;
			found = true;
		}
	}
	
	if(found == false) {
		var item = {};
		item.typeID = id;
		item.quantity = 1;
		item.quantity = quantity;
		sList.push(item);
	}
	
	updateTable();
}


function removeItem(id) {
	for(i = 0; i < sList.length; i++) {
		if (sList[i].typeID == id) {
			sList.splice(i, 1);
		}
	}
	updateTable();
}


function savelist() {
	var obj = {
		"sList": sList,
		"title": $('#title').val(),
		"comments": $('#comments').val()
	}
	$('#save-data').val(JSON.stringify(obj));
	$('#save').submit();
}


function setQuantity(id, quantity) {
	for(i = 0; i < sList.length; i++) {
		if(sList[i].typeID == id) {
			sList[i].quantity = parseFloat(quantity);
			return;
		}
	}
}


function updateTable() {
	$('#list-contents').empty();
	html = "";
	var totaltotal = 0;
	var volumetotal = 0;
	for(i = 0; i < sList.length; i++) {
		item = getItemFromId(sList[i].typeID);
		cost = getPrice(sList[i].typeID);
		priceString = getPriceString(sList[i].typeID);
		quantity = sList[i].quantity;
		total = cost * quantity;
		if(cost === 0) {
			totalString = "<i>N/A</i>";
		} else {
			totalString = isk(total);
		}
		totaltotal += total;
		volumetotal += (item.volume * quantity);
		html += '<tr><td style="background: url(\'https://image.eveonline.com/Type/'+item.typeID+'_32.png\') no-repeat 4px 4px; width: 42px;">&nbsp</td> <td>'+item.typeName+'</td> <td class="text-right">'+priceString+'</td> '
		+ '<td class="text-right"><input type="number" value='+quantity+' style="width: 100px;" onchange="setQuantity('+item.typeID+', this.value); updateTable();" class="text-right"/></td>'
		+ '<td class="text-right">'+vol(item.volume * quantity)+'</td> <td class="text-right">'+totalString+'</td>'
		+ '<td class="text-right"><button type="button" class="btn btn-default btn-sm" onclick="removeItem('+item.typeID+');"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></td></tr>';
	}
	$('#list-contents').append(html);
	$('#total-volume').empty();
	$('#total-volume').append(vol(volumetotal));
	$('#total-cost').empty();
	$('#total-cost').append(isk(totaltotal));
}


function getItemFromId(id) {
	for(ii = 0; ii < items.length; ii++) {
		if(items[ii].typeID == id) {
			return items[ii];
		}
	}

	return null;
}


function getItemFromName(name) {
	for(ii = 0; ii < items.length; ii++) {
		if(items[ii].typeName == name) {
			return items[ii];
		}
	}

	return null;
}


function fillQueue() {
	muhitems = [];
	while(priceQueue.length > 0) {
		muhitems.push(priceQueue.pop());
		if(muhitems.length == 20) {
			url = "http://api.eve-central.com/api/marketstat/json?typeid="+muhitems.join()+"&regionlimit=10000002";
			$.ajax({url: url, dataType: 'json', success: function(result){
				for(i = 0; i < result.length; i++) {
					sell = result[i].sell.min;
					id = result[i].sell.forQuery.types[0];
					lscache.set("shopping-price-"+id, sell, 720);
					
					$('#price-'+id).empty();
					$('#price-'+id).append(isk(sell));
				}
				updateTable();
			}});
			muhitems = [];
		}
	}
	
	if(muhitems.length > 0) {
		url = "http://api.eve-central.com/api/marketstat/json?typeid="+muhitems.join()+"&regionlimit=10000002";
		$.ajax({url: url, dataType: 'json', success: function(result){
			for(i = 0; i < result.length; i++) {
				sell = result[i].sell.min;
				id = result[i].sell.forQuery.types[0];
				lscache.set("shopping-price-"+id, sell, 720);
					
				$('#price-'+id).empty();
				$('#price-'+id).append(isk(sell));
			}
			updateTable();
		}});
	}
}


function getPrice(id) {
	val = lscache.get("shopping-price-"+id);
	if(val !== null) {
		return val;
	} else {
		priceQueue.push(id);
		return false;
	}
}


function getPriceString(id) {
	price = getPrice(id);
	if(price === false) {
		price = '<img style="opacity: 0.2;" src="/shopping/loading.gif" />';
	} else {
		if(price > 0) {
			price = isk(price);
		} else {
			price = "<i>N/A</i>";
		}
	}
	
	return price;
}


function searchchange(name) {
	//Show/hide properly
	if(name.length > 1) {
		$('#autocomplete').show(50);
	} else {
		$('#autocomplete').hide(150);
		return;
	}
	
	//Search for items
	var results = [];
	for(i = 0; i < items.length; i++) {
		if(items[i].typeName.toLowerCase().indexOf(name.toLowerCase()) != -1) {
			results.push(items[i]);
		}
	}
	
	//Remove all existing items
	$('.searchresult').remove();
	
	var limit = 50;
	if(results.length == 0) {
		$('#results').append('<tr class="searchresult"><td></td><td><i>No Results</i></td><td></td></tr>');
	} else {
		var html = "";
		for(i = 0; i < results.length && i <= limit; i++) {
			price = getPriceString(results[i].typeID);
			html += '<tr class="searchresult" onclick="addItemUi('+results[i].typeID+');"><td style="background: url(\'https://image.eveonline.com/Type/'+results[i].typeID+'_32.png\') no-repeat 4px 4px; width: 38px;"></td><td>'+results[i].typeName
			+ '</td><td id="price-'+results[i].typeID+'" class="text-right text-muted">'+price+'</td> </tr>';
		}
		if(results.length > limit) {
			html += '<tr class="searchresult"><td></td><td><i>'+(results.length - limit)+' Hidden Results</i></td><td></td></tr>';
		}
		$('#results').append(html);
		
		fillQueue();
	}
}