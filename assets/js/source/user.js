var User = {
	info:{},
	items:{},
	init:function(info)
	{
		if (info !== undefined && info != null) {
			User.info = info;
		}
	},
	isLogged:function() {
		if (User.info.name === undefined) {
			return false;
		} else {
			return true;
		}
	},
	loggedOnly:function(callback){
		if(!User.isLogged()){
			Site.loginPopup();
			
			if(callback !== undefined){
				callback();
			}
			
			throw "logged only";
		}
	},
	setMoney : function (amount) {
		
		if (isNaN(amount)) {
			throw "Invalid amount";
		}
		
		var oldValue = User.info.money;
		User.info.money += amount;
		
		Sound.play(Sound.effects.COIN, Sound.TYPE_EFFECT);
		
		$('#user-money').css('font-size','18px');
		
	    jQuery({someValue: oldValue}).animate({someValue: User.info.money}, {
	        duration: 1000,
	        easing:'swing', // can be anything
	        step: function() { // called on every step
	            $('#user-money').html(Util.formatAmount(Math.ceil(this.someValue)));
	        },
	        complete:function(){
	        	$('#user-money').html(Util.formatAmount(User.info.money));
	            $('#user-money').css('font-size','');
	        }
	    });
	},
	setGold : function (amount) {
		
		if (isNaN(amount)) {
			throw "Invalid amount";
		}
		
		var oldValue = User.info.gold;
		User.info.gold += amount;
		
		Sound.play(Sound.effects.GOLD, Sound.TYPE_EFFECT);
		
		$('#user-gold').css('font-size','18px');
	    jQuery({someValue: oldValue}).animate({someValue: User.info.gold}, {
	        duration: 1000,
	        easing:'swing', // can be anything
	        step: function() { // called on every step
	            $('#user-gold').html(Util.formatAmount(Math.ceil(this.someValue)));
	        },
	        complete:function(){
	        	$('#user-gold').html(Util.formatAmount(User.info.gold));
	            $('#user-gold').css('font-size','');
	        }
	    });
	},
	getItemsByBuilding : function(building) {
		var list = [];
		for (k in User.items) {
			if (User.items[k].item.building == building || User.items[k].item.building == 0) {
				list.push(User.items[k]);
			}
		}
		return list;
	},
	getItem : function (id) {
		if (User.items[id] === undefined) {
			return false;
		} else {
			return User.items[id];
		}
	},
	bossOnly:function() {
		User.loggedOnly();
		
		if (User.info.boss != 1) {
			throw "boss only";
		}
	},
	isBoss: function (){
		return User.info.boss;
	}
}