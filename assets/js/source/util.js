var Util = {
	tabMenu : function (where, defaultTab) {
		$(where+' [data-tab]').on('click', function () {
			var tab = $(this).data('tab')
			
			$(where+' [data-tab]').removeClass('selected-tab');
			$(this).addClass('selected-tab');
						
			$('.tab').hide();
			$('#tab-'+tab).show();
		});
		$(defaultTab).show();
	},
	alert : function (message) {
		Apprise(message);
	},
	confirm : function (text, okCall, koCall) {
		
		if (text === undefined || okCall === undefined) {
			throw "missing data";
			return false;
		}
		
		if (koCall === undefined || koCall == null) {
			koCall = function() { Apprise('close'); };
		}
		
		Apprise(text,{
			buttons: {
				cancel: {
					action: koCall,
					text: 'Cancelar',
				},confirm: {
					action: okCall,
					text: 'Confirmar',
				}
			}
		});
	},
	formatAmount : function (amount) {
		return amount.toFixed(0).toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1.");
	},
	startClock : function() {
		var date = {},
			/*currentDate = new Date();
		date.h = currentDate.getHours(); 
		date.m = currentDate.getMinutes();
		date.s = currentDate.getSeconds();*/
		
		
		currentDate = moment().tz('GMT+0');
		date.h = currentDate.hours(); 
		date.m = currentDate.minutes();
		date.s = currentDate.seconds();
		i = 0;

		setInterval(function() 
		{
		    date.s++;
		    if(date.s > 60)
		    {
		       date.s = 0;
		       date.m++;
		       if (date.m == 60) {
		           date.m = 0;
		           date.h++;
		       }
		     }
		   //make it beautiful
		    var h = date.h,
		        m = date.m,
		        s = date.s;
		    if(h < 10){h = '0'+h;}
		    if(m < 10){m = '0'+m;}
		    if(s < 10){s = '0'+s;}
		    $('#game-clock').text(h+ ':' +m+ ':' + s);
		},1000);
	},
	dateDiff : function(start, end) {
		var diff = start.getTime() - end.getTime();

		return diff / 1000;
	},
	showPopup : function (html, callbacks) {
		if (html == '') {
			return;
		}
		if (callbacks === undefined) {
			callbacks = {};
		}
		
		var obj = {
			items: {
			  src: html,
			  type: 'inline'
			},
			callbacks : callbacks
		};
		
		if (callbacks === true) {
			obj.closeOnBgClick = false;
		}
		
		$.magnificPopup.open(obj);
	},
    closePopup: function() {
        $.magnificPopup.close();
    }
}
function T (text) {
	return text;
}