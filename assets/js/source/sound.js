var Sound = {
	folder : '/assets/sounds/',
	effects : {
		COIN : {
			file: 'coin.mp3',
			type: 'mpeg'
		},
		GOLD : {
			file: 'gold.mp3',
			type: 'mpeg'
		},
		CRAFT : {
			file: 'mechanic.mp3',
			type: 'mpeg'
		},
		REPAIR : {
			file: 'repair.mp3',
			type: 'mpeg'
		},
	},
	TYPE_EFFECT : 1,
	TYPE_MUSIC : 2,
	play : function (sound, type) {
		
		var player;
		
		if ($('#effects-player').length < 1) {
			var html = Site.parseTemplate(function(){/*
			<audio id="effects-player" autplay>
		  	</audio>
		  	*/});
		  	$('body').append(html);
		}
		
		if (type == Sound.TYPE_MUSIC) {
			player = 'music-player';
		} else {
			player = 'effects-player';
		}
		
		$('#'+ player +' source').remove();
		$('#'+ player).append('<source src="'+ Sound.folder+''+sound.file+'" type="audio/'+ sound.type +'">');
		
		player = document.getElementById(player);
		player.play();
	},
	stop : function (type) {
		
	}
}