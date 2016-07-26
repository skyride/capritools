var app = angular.module('myApp', ['ngSanitize']);
app.controller('myCtrl', function($scope, $http, $location) {
	//Item attributes
	$scope.itemID = 0;
	$scope.itemName = "Brutix";
	$scope.searchItems = [];

	//Search method
	$scope.updateSearch = function() {
		//Check if we have at least 3 characters
		if(search.value.length > 2) {
			//Add matched items to the new search array
			var si = [];
			for(i = 0; i < items.length; i++) {
				if(items[i].typeName.toLowerCase().indexOf(search.value.toLowerCase()) > -1) {
					//Check if we've exceeded the item limit before adding it
					if(si.length < 26) {
						si.push(items[i]);
					}
				}
			}
			
			//Update array
			this.searchItems = si;
		} else {
			this.searchItems = [];
		}
	}
	
	//Select new item method
	$scope.selectItem = function(typeID) {
		//Find item from items list
		for(i = 0; i < items.length; i++) {
			if(items[i].typeID == typeID) {
				$http.get('/itemdb/'+typeID+'.json')
					.success(function(data, status, headers, config) {
						//Update the item view
						$scope.itemID = data.typeID;
						$scope.itemName = data.typeName;
						$scope.itemGroupID = data.groupID;
						$scope.itemGroupName = data.groupName;
						$scope.itemCategoryID = data.categoryID;
						$scope.itemCategoryName = data.categoryName;
						$scope.itemAttributes = data.attributes;
						
						if(data.description == null) {
							$scope.description = "<i>No Item Description</i>";
						} else {
							//Strip trailing new lines and replace new lines with HTML linebreaks
							$scope.description = data.description.replace(/\s*\n\s*$/,"").replace(/\n/g, "<br />");
						}
						
						//Attribute table
						$scope.attributes = $scope.attributeTable();
						
						//Location Service
						$location.path($scope.itemID);
					})
					.error(function(data, status, headers, config) {
						//Error
					});
			}
		}
	}
	
	
	//Generate attribute table
	$scope.attributeTable = function() {
		var attributes = $scope.itemAttributes;
		function getAttrByDisplayName(dp) {
			for(i = 0; i < attributes.length; i++) {
				if(attributes[i].displayName == dp) {
					return i;
				}
			}
			return -1;
		}
		
		function removeAttrByDisplayName(dp) {
			id = getAttrByDisplayName(dp);
			if(id > -1) {
				attributes.splice(id, 1);
			}
		}
		
		//Remove dumb attributes
		removeAttrByDisplayName("Item Damage");
		removeAttrByDisplayName("Power Load");		
		
		//Units
		for(i = 0; i < attributes.length; i++) {
			switch(attributes[i].unitID) {
				//Inverse Absolute Percent
				case "108":
					attributes[i].displayValue = (100 - (100 * attributes[i].value)) + "%";
					break;
				
				//Milliseconds
				case "101":
					attributes[i].displayValue = (attributes[i].value / 1000) + " s";
					break;
					
				case "104":
					if(attributes[i].displayName == "Warp Speed Multiplier") {
						attributes[i].displayValue = attributes[i].value + " AU/s";
					}
					break;
					
				case "140":
					attributes[i].displayValue = "Level " + attributes[i].value;
					break;
					
				case "137":
					if(attributes[i].value == "1") {
						attributes[i].displayValue = "True";
					} else {
						attributes[i].displayValue = "False";
					}
					break;
					
				case "117":
					switch(attributes[i].value) {
						case "1":
							attributes[i].displayValue = "Small";
							break;
							
						case "2":
							attributes[i].displayValue = "Medium";
							break;
							
						case "3":
							attributes[i].displayValue = "Large";
							break;
							
						case "4":
							attributes[i].displayValue = "X-Large";
							break;
					}
					break;
					
				case "109":
					if(attributes[i].value >= 1) {
						attributes[i].displayValue = "+" + (attributes[i].value * 100 - 100) + "%";
					} else {
						attributes[i].displayValue = "-" + ((1 - attributes[i].value) * 100) + "%";
					}
					break;
					
				default:
					attributes[i].displayValue = attributes[i].value + " " + attributes[i].unit;
			}
		}
	
		//Generic Case
		return attributes;
	}
	
	
	//Init
	$scope.init = function() {
		//Load page if one is set in the URL
		if($location.path().length > 0) {
			$scope.selectItem($location.path().replace("/", ""));
			$scope.focused = false;
		} else {
			$scope.focused = true;
		}
	}
});