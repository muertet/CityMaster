var Crafting = {
	recipes: [],
	getByBuilding : function (type) {
		var list = [],
			recipe;
		for (k in Crafting.recipes) {
			recipe = Crafting.recipes[k];

			if (recipe.result.item.building == type) {
				list.push(recipe);
			}
		}
		return list;
	},
	getByResult : function (result, buildingType) {
		var recipeList = Crafting.getByBuilding(buildingType),
			recipe;
		
		for (k in recipeList) {
			recipe = recipeList[k];
			
			if (recipe.result.item.id == result) {
				return recipe;
			}
		}
		return false;
	},
	showPopup : function (type) {
		var recipeList = Crafting.getByBuilding(type),
			item,
			itemList = {};
		
		Site.api('user/inventory/list', {building:type}, function(items) {
			
			for (k in items) {
				item = items[k];
				
				item.item.quantity = item.quantity;
				itemList[item.item.id] = item.item;
			}
			
			Site.parseTemplate('item/crafting.html', {type:type, recipeList:recipeList, itemList:itemList}, function(html) {
				Util.showPopup(html);
				
				$('#crafting-popup button').on('click', function() {
					var result = $(this).data('result'),
						building = $(this).data('building'),
						amount = $('#crafting-amount-'+result).val();
					
					if (amount < 1) {
						return false;
					}
					
					Crafting.craft(result, type, amount);
				});
			});
		});
	},
	craft: function (result, buildingType, amount) {
        var recipe = Crafting.getByResult(result, buildingType),
            uItem,
            item;

		for (k in recipe.ingredients) {
			item = recipe.ingredients[k];
            uItem = User.getItem(item.item.id);

			if (!uItem || uItem.quantity < (item.quantity * amount)) {
				Apprise('No tienes los objetos o las cantidades necesarias');
				return false;
			}
		}
		//Site.api('user/inventory/craft', {item:result, amount:amount}, function (data) {
		Site.api('building/produce', {item:result, quantity:amount, building:Map.getCurrentBuilding()}, function (data) {
			Sound.play(Sound.effects.CRAFT, Sound.TYPE_EFFECT);
			
            // update clientside inventory cache
            Map.refreshInfo();
            Util.closePopup();
        });
	}
}