function Building (building) {
	this.info = building;
	this.dInfo = {};
	
	if (building.type != null) {
		this.dInfo = BuildingHelper.getInfoByType(building.type);
	}
	
	for (k in building) {
		this[k] = building[k];
	}
	
	function isPublic () {
		return this.dInfo.public;
	}
	
	function isPurchasable () {
		return this.dInfo.purchase;
	}
	
	function isBusy () {
		var diff = Util.dateDiff(new Date(building.delay_time), new Date());
		
		if (diff > 0) {
			return true;
		} else {
			return false;
		}
	}
	function purchase () {
		if (User.info.money < building.purchase_price) {
			Apprise('No tienes suficiente dinero');
			return false;
		}
		
		Util.confirm('¿Seguro que quieres comprar esta propiedad ('+ this.dInfo.name +') por '+ Util.formatAmount(building.purchase_price) +'€ ?', function () {
			Site.api('building/purchase', {id:building.id, pType:BuildingHelper.PURCHASE_TYPE_FINAL, type:building.type}, function(data) {
				if (data) {
					Apprise('close');
					User.setMoney(-building.purchase_price);
					Map.displayBuildingInfo(building.id);
				}
			});
		});
	}
	function rent () {
		if (User.info.money < building.rent_price) {
			Apprise('No tienes suficiente dinero');
			return false;
		}
		
		Util.confirm('¿Seguro que quieres alquilar esta propiedad ('+ this.dInfo.name +') por '+ Util.formatAmount(building.rent_price) +'€/día ?', function () {
			Site.api('building/purchase', {id:building.id, pType:BuildingHelper.PURCHASE_TYPE_RENT, type:building.type}, function(data) {
				if (data) {
					Apprise('close');
					User.setMoney(-building.rent_price);
					Map.displayBuildingInfo(building.id);
				}
			});
		});
	}
	function sell () {
		
		var price,
			fPrice;
		if (building.purchase_type == BuildingHelper.PURCHASE_TYPE_FINAL) {
			price = building.purchase_price / 2;
		} else {
			price = building.rent_price / 2;
		}
		fPrice = Util.formatAmount(parseInt(price));
		
		Util.confirm('¿Seguro que quieres vender esta propiedad ('+ this.dInfo.name +') por '+ fPrice +'€ ?', function () {
			Site.api('building/sell', {id:building.id}, function(data) {
				if (data) {
					Apprise('close');
					User.setMoney(price);
					Map.displayBuildingInfo(building.id);
				}
			});
		});
	}
	function donate (amount) {
		
		if (amount < 1) {
			return false;
		}
		
		if (User.info.money < amount) {
			Apprise('No tienes suficiente dinero');
			return false;
		}
		
		Site.api('building/donate', {id:building.id,amount:amount}, function(data) {
			if (data) {
				User.setMoney(-amount);
				
				Map.displayBuildingInfo(building.id);
			}
		});
	}
	function upgrade () {
		var nLevel = building.level+1;
		Util.confirm('¿Seguro que quieres subirla al nivel '+nLevel+' por '+ Util.formatAmount(nLevel * BuildingHelper.LEVEL_MULTIPLIER * 100) +'€ ?', function () {
			Site.api('building/upgrade', {id:building.id}, function(data) {
				if (data) {
					Apprise('close');
					User.setMoney(-building.purchase_price);
					Map.displayBuildingInfo(building.id);
				}
			});
		});
	}
	
	function payDelay () {
		Site.api('building/pay_delay', {id:building.id}, function(data) {
			if (data) {
				User.setGold(-1);
				Map.displayBuildingInfo(building.id);
			}
		});
	}
	
	this.upgrade = upgrade;
	this.sell = sell;
	this.rent = rent;
	this.purchase = purchase;
	this.isBusy = isBusy;
	this.isPublic = isPublic;
	this.isPurchasable = isPurchasable;
	this.payDelay = payDelay;
}