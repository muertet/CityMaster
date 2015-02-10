var Timer =
{
	list : {},
	set : function (time, type, callback, endcall) {
		if (type === undefined || type == '') {
			throw "missing timer type";
		}
		
		var timerId = countdown(moment(time).tz('GMT+0'), function(ts) {
			var ps = ts,
				html = '';
		    if (ts.minutes < 10) {
		        ps.minutes = '0'+ts.minutes;
		    }
		    if (ts.seconds < 10) {
		        ps.seconds = '0'+ts.seconds;
		    }
		    
		    html = ps.minutes+':'+ps.seconds;
		    
		    if (ts.hours > 0) {
		        html = ts.hours+':'+html;
		    }
		    if (ts.hours == 0 && ts.minutes == 0 && ts.seconds == 0) {
		    	window.clearInterval(timerId);
		    	
				if (endcall !== undefined) {
					endcall();
				}
			} else {
				callback(ts, ps, html);
			}
		    
		    }, countdown.HOURS|countdown.MINUTES|countdown.SECONDS
		);
		Timer.addToList(type, timerId);
	},
	addToList : function (type, timerId) {
		if (Timer.list[type] === undefined) {
			Timer.list[type] = [];
		}
		Timer.list[type].push(timerId);
	},
	removeType : function (type) {
		if (Timer.list[type] === undefined) {
			return;
		}
		for (k in Timer.list[type]) {
			window.clearInterval(Timer.list[type][k]);
		}
		delete(Timer.list[type]);
	}
}