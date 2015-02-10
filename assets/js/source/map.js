var Map = {
	initialCoords: [41.3886188,2.1520332], //barcelona
	init:function() {
		var cssLine = '',
			bInfo,
			tableName = App.cartodb.table;
		
		map = new L.Map('map', {
			  zoomControl: false,
			  center: Map.initialCoords,
			  zoom: 17
			});

        L.tileLayer('https://maps.nlp.nokia.com/maptile/2.1/maptile/newest/normal.day/{z}/{x}/{y}/256/png8?lg=eng&token=61YWYROufLu_f8ylE0vn0Q&app_id=qIWDkliFCtLntLma2e6O', {
          attribution: 'Nokia'
        }).addTo(map);
        
        //generate the required inline css
        for (k in BuildingHelper.info) {
        	bInfo = BuildingHelper.getInfoByType(k);
			cssLine += "#" + tableName + "[type="+ k +"]{"+ bInfo.css +"} ";
		}

        cartodb.createLayer(map, {
			  user_name: App.cartodb.username,
			  cdn_url:{"http":App.cdn_url},
			  type: 'cartodb',
			  sublayers: [{
			    sql: "SELECT * FROM "+ tableName,
			    interactivity: 'cartodb_id,type,status,owner,rent_price,purchase_price,purchase_type,delay_time,the_geom',
				cartocss: "#" + tableName + "{polygon-fill: #FF6600;building-fill:#F2EEEA;polygon-opacity:0.7;building-height: 12;} #" + tableName + "[owner>0]{polygon-opacity:0.3;} #" + tableName + "[status=4]{building-height:0;building-fill:#38393B;}" + cssLine
			  }]
			})
			.addTo(map)
			.done(function(layer) {
				layer.getSubLayer(0).setInteraction(true);
          		Map.setEvents(layer);
			});
	},
	setEvents: function (layer) {
		var hovers = {};
		
		hovers[layer] = 0;
 
        layer.bind('featureOver', function(e, latlon, pxPos, data, layer) {
        	//console.log(data);return false;
          //hovers[layer] = L.circle(latlon, 400).addTo(map);
          //hovers[data.cartodb_id] = ;
          
			// prevent duplicated events
			if (data === undefined || isNaN(hovers[layer])) {
				return false;
			}
		  
			switch (data.type) {
				/*case BuildingHelper.TYPE_ELECTRIC:
					hovers[layer] = L.circle(latlon, 800).addTo(map);
				break;*/
				default:
					hovers[layer] = 1;
				break;
			}
		  
          
			if(_.any(hovers)) {
				$('#map').css('cursor', 'pointer');
				
			}
        });
 
        layer.bind('featureOut', function(e, layer) {
        	
        	var circle = hovers[layer];
			hovers[layer] = 0;
			
			if(!_.any(hovers)) {
				$('#map').css('cursor', 'auto');
				//Map.hideBuildingInfo();

				if (circle !== undefined && isNaN(circle)) {
					window.map.removeLayer(circle);
				}

			}
        });
        
        layer.on('featureClick', function(e, latlon, pxPos, data) {
        	
        	Map.displayBuildingInfo(data.cartodb_id);
        });
	},
    refreshInfo : function () {
        Map.displayBuildingInfo($('#building-info').data('info').id);
    },
    getCurrentBuilding : function () {
        return $('#building-info').data('info').id;
    },
	displayBuildingInfo : function(id) {
		if (id == null) {
			return false;
		}
		$('#building-info').show().html('<img src="assets/images/loading.gif">');
		
		Site.api('building/get', {id:id}, function(data) {
			
			Timer.removeType('building'); //unset running timers
			
			/*if (data.type === undefined || data.status == null) {
				return false;
			}*/
			
			var template = 'building/info.html',
				building = new Building(data);
			
			if (data.status == null) {
				template = 'building/empty.html';
			}
			console.log('building-info', building);
		
			Site.parseTemplate(template, {building:building}, function(html) {
				
				$('#building-info').show().html(html).data('info', data);
				
				$('#building-purchase-button').on('click', function() {
					if ($('#building-empty').length > 0) {
						data.type = $('input[name=building-type]:checked').val();

						if (data.type === undefined) {
							Util.alert("¡Debes seleccionar el tipo de edificio a construir!");
							return;
						}
						building.dInfo = BuildingHelper.getInfoByType(data.type);
					}
					building.purchase();
				});
				$('#building-rent-button').on('click', function() {
					if ($('#building-empty').length > 0) {
						data.type = $('input[name=building-type]:checked').val();
						
						if (data.type === undefined) {
							Util.alert("¡Debes seleccionar el tipo de edificio a construir!");
							return;
						}
						building.dInfo = BuildingHelper.getInfoByType(data.type);
					}
					building.rent();
				});
				
				$('#building-sell-button').on('click', function() {
					building.sell();
				});
				
				$('#building-upgrade-button').on('click', function() {
					building.upgrade();
				});
				
				$('#building-delay-button').on('click', function() {
					building.payDelay();
				});
				
				$('.market-sell-button').on('click', function () {
					var id = $(this).data('id'),
						item = User.getItem(id),
						html;

					if (!item) {
						return;
					}	
						
					html = Site.parseTemplate(function (){/*
				    	<form id="market-sell-form" method="post" data-id="<%=item.id%>">
					    	<div>
					    		<img src="<%=item.image%>">
					    		<h2><%=item.name%></h2>
					    		<label>Cantidad: <input name="quantity" type="number"> / <%=max%></label>
					    		<label>Precio <input name="price" type="number"> €/u</label>
					    		<button><%=T("Vender")%></button>
					    	</div>
				    	</form>
				    	*/},{item:item.item, max: item.quantity});
				    Util.showPopup(html);
				    
				    $('#market-sell-form').on('submit', function () {
				    	var id = $(this).data('id'),
				    		fId = '#market-sell-form',
				    		price = $(fId+' input[name=price]').val(),
				    		quantity = $(fId+' input[name=quantity]').val();
				    	
				    	if (id < 1 || price < 1 || price > 999999 || quantity > item.quantity) {
				    		$(fId+' input[name=quantity]').val(item.quantity);
				    		$(fId+' input[name=price]').val(2);
							return false;
						}
						
						if (item.health != 100) {
							Util.alert('No puedes vender objetos desgastados');
							return false;
						}
						
						Site.api('user/inventory/sell', {id:id, price:price, quantity:quantity} , function (offerId) {
							if (!offerId) {
								//failed
							} else {
								// redirect to market offer?
							}
						});
				    	
				    	return false;
				    });
			    });

				if (building.isPublic()) {
					$('#building-donation-button').on('click', function() {
						building.donate($('#donation-amount').val());
					});
					return true;
				}
				
				if (building.isBusy()) {
					console.log('BuildingInfo:', data, data.delay_time);
					Timer.set(data.delay_time, 'building', function (ts, ps, html) {
						$('#building-busy-time').html(html);
					}, function () {
						Map.displayBuildingInfo(data.id);
					});
				}

				switch (building.dInfo.type) {
					case BuildingHelper.TYPE_FACTORY:
						$('#crafting-popup-button').on('click',function() {
							Crafting.showPopup(building.type);
						});
						
						$('#garage-vehicle-button').on('click',function() {
							BuildingHelper.showVehicleList(building.type);
						});
					break;
					case null:
						$('#building-empty label').on('click',function() {
							$('#building-empty label').removeClass('selected');
							$(this).addClass('selected');
							
							var type = $(this).data('type'),
								info = BuildingHelper.getInfoByType(type);
							$('#building-description').html(info.description);
						});
					break;
				}
			});
		});
		
	},
	hideBuildingInfo : function() {
		Map.displayBuildingInfo();
	}
}
