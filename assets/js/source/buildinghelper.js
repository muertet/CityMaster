var BuildingHelper =
{
	TYPE_MINE : 1,
	TYPE_FACTORY : 2,
	TYPE_WAREHOUSE : 3,
	TYPE_HOSPITAL : 7,
	TYPE_GARAGE : 8,

	buildableTypes: [1,2,3,4,8],
	
	LEVEL_MULTIPLIER : 1.45,
	
	PURCHASE_TYPE_FINAL : 1,
	PURCHASE_TYPE_RENT : 2,
	
	info : {
		1 : {
			name : "Torre petrolífera",
			description : "Extrae petróleo del suelo.",
			//css : "polygon-fill:orange;polygon-opacity:0.3;",
			css : "building-fill:blue;",
		},
		2 : {
			name : "Mina de hierro",
			description : "Extrae hierro de la tierra.",
			css : "building-fill:green;",
		},
		3 : {
			name : "Fábrica de armas",
			description : "Permite ensamblar todo tipo de armas.",
			css : "building-fill:purple;",
		},
		4 : {
			name : "Almacén",
			description : "Necesario para almacenar tus objetos.",
			css : "building-fill:#C4CFC8;",
		},
		6 : {
			name : "Subestación eléctrica",
			description : "En toda ciudad, la electricidad es necesaria. Resulta una inversión cara pero segura.",
			// http://fc06.deviantart.net/fs71/i/2013/146/3/e/underground_generators_hoover_dam_by_gigi50-d66n5ag.jpg
			css : "building-fill:yellow;",
		},
		7 : {
			name : "Hospital",
			description : "Tener un hospital cerca permitirá a tus soldados curarse más rápidamente.",
			css : "building-fill:red;",
		},
		8: {
			name: "Garaje",
			description : "Lugar donde construir y reparar vehículos.",
			css : "building-fill:brown;",
		}
	},
	getInfoByType : function (type) {
		var info = BuildingHelper.info[type];
		
		info.image = "/assets/images/buildings/"+type+".jpg";
		
		return info;
	},
	showVehicleList : function (bType) {
		
		Site.api('user/inventory/list', {building:bType}, function(items) {
			var itemList = {};
			
			for (k in items) {
				item = items[k];
				
				if (item.item.vehicle == 0) {
					continue;
				}
				
				item.item.quantity = item.quantity;
				item.item.health = item.health;
				itemList[item.item.id] = item.item;
			}

			Site.parseTemplate('item/vehicle_list.html', {bType:bType, list:itemList}, function(html) {
				Util.showPopup(html);
			});
		});
	}
}

