var Site =
{
	authToken:'',
	userToken:'',
	baseUrl:'api/',
	requestedTemplates : {},
	title:'',
	init: function () {
		$('#options-menu').on('click', function () {
	    	$('#main-menu .submenu').toggle();
	    });

        $('#main-menu .submenu li').on('click', function () {
            $('#main-menu .submenu').hide();
        });
	    
	    $('#topbar-gold').on('click', function () {
	    	Site.shopPopup();
	    });
	    
	    $('#menu-referred').on('click', function () {
	    	Site.api('user/referred/list', function(list) {
	    		Site.parseTemplate('user/referred.html', {list:list, website:$('base').attr('href')}, function (html) {
	    			Util.showPopup(html);
	    		});
	    	});
	    });
	    
	    $('#menu-market').on('click', function () {
	    	Site.api('market/list', function(offers) {
	    		Site.parseTemplate('market/list.html',{offers:offers}, function (html) {
	    			Util.showPopup(html);
	    			
	    			$('#market-popup tr').on('click', function () {
	    				var info = $(this).data('info'),
	    					html;
	    				
	    				if (info == null || info == '') {
							return false;
						}
						
						html = Site.parseTemplate(function (){/*
							<img src="/assets/images/items/<%=offer.item.id%>.jpg">
							<h1><%=offer.item.name%></h1>
							<p>
								<%=offer.item.description%><br>
								Vendedor: <a href="<%=offer.seller.url%>"><%=offer.seller.nick%></a>
							</p>
							<div>
								<h2>Comprar</h2>
								<label>
									Cantidad: <input name="quantity" type="number"> / <%=offer.quantity%>
								</label>
								<p>
									Total: <span id="market-buy-total">0</span> €
								</p>
								<input name="id" value="<%=offer.id%>" type="hidden">
								<button>Comprar</button>
							</div>
						*/},{offer:info});
						
						$('#market-item').html(html);
						
						$('#market-item [name=quantity]').on('change', function () {
							var id = $('#market-item [name=id]').val(),
								quantity = $(this).val(),
								info = $('#market-offer-'+id).data('info');
								
							if (quantity > info.quantity) {
								quantity = info.quantity;
								$(this).val(quantity);
							}
							
							$('#market-buy-total').html(Util.formatAmount(info.price*quantity));
						});
						
						$('#market-item button').on('click', function (){
							
							var id = $('#market-item [name=id]').val(),
								quantity = $('#market-item [name=quantity]').val(),
								info = $('#market-offer-'+id).data('info');
								
							if (quantity < 1) {
								return false;
							}
							
							Site.api('market/purchase', {id:id, quantity:quantity} , function(data) {
								if (data) {
									User.setMoney(-(info.price*quantity));
								}
							});
						});
	    			});
	    		});
	    	});
	    });	
	},
	shopPopup: function() {
		Site.parseTemplate('map/shop.html', function(html) {
			Util.showPopup(html, true);
			Util.tabMenu('#shop-popup', '#tab-gold');
		});
	},
	loginPopup:function()
	{
		//force login page
		User.info = false;
		crossroads.parse('/');
	},
	api:function(url,obj,callback)
	{
		Site.loading(true);
		var queryUrl=Site.baseUrl+url+'?authtoken='+Site.authToken+'&usertoken='+Site.userToken;
		
		if(typeof obj =='function')
		{
			callback=obj;
			
			$.get(queryUrl,function(data)
			{
				if(Site.checkErrors(data)) {
					callback(data.data);
				}
				Site.loading();							
			});
		}
		else
		{
			$.post(queryUrl, obj, function(data)
			{								
				if(Site.checkErrors(data))
				{
					// update inventory list in background
			        if (url == 'user/inventory/list') {
			        	var itemList = {},
			        		uItem;
			        	
						for (k in data.data) {
							uItem = data.data[k];
							User.items[uItem.item.id] = uItem;
						}
					}
					callback(data.data);
				}
				Site.loading();
			});	
		}
		
	},
	checkErrors:function(data)
	{
		if (data.status == 1) {
			return true;
		}
		
		switch(data.data)
		{
			case 'Must be logged':
				alert('Please login!');
			break;
			case 'Invalid authToken':
				document.location = 'logout.php';
			break;
		}
		return false;	
	},
	loading:function(status) {
		if (typeof status == 'undefined') {
			status=false;
		}
		if (status) {
			$('#loading-div').show();
		} else {
			$('#loading-div').hide();
		}
	},
	checkImages:function()
	{
		$('img').each(function()
		{
			if($(this).attr('onerror')==null){
				$(this).attr('onerror',"$(this).attr('src','images/noImage.jpg');");
			}
		});
	},
	parseTemplate:function(f,data,callback)
	{
		var html='',
			result;
			
		if (typeof data == 'function') {
			callback = data;
			delete(data);
		}
		
		if (typeof f == 'string')
		{
			var templateName=f.replace(/\//,'-').replace('.html','')+'-template';
			
			if(callback===undefined){
				throw "parseTemplate error: No callback set";
				return false;
			}
			
			if($('#'+templateName).length == 0)
			{
				// already request, cut this petition
				if (Site.requestedTemplates[f] !== undefined) {
					return false;
				}
				
				Site.requestedTemplates[f] = true;
				
				$.ajax({
				  url: 'templates/'+f,
				  dataType: "script",
				  error: function(a)
				  {
				  	if(a.status!=200){
						throw 'Template '+f+' not found';
						return false;
					}
				
				  	$('body').append('<script type="text/system-template" id="'+templateName+'">'+a.responseText+'</script>');
					Site.parseTemplate(f,data,callback);
					return true;
				}});
				return false;
			}else{
				html=$('#'+templateName).html().replace(/^[^\/]+\/\*!?/, '').replace(/\*\/[^\/]+$/, '');
			}
			
		}else{
			html=f.toString().replace(/^[^\/]+\/\*!?/, '').replace(/\*\/[^\/]+$/, '');
		}
		
		if(typeof data !='undefined')
		{	
			result=_tmpl(html,data);
			
			if(callback===undefined){
				return result;
			}else{
				callback(result);
			}
		}else{
			if(callback===undefined){
				return html;
			}else{
				callback(html);
			}
		}
	},
	html:function(obj)
	{
		var preHtml;
		
		if (typeof obj.title !='undefined') {
			$('title').html(obj.title+' - '+Site.title);
			$('#section-title').html(obj.title);
		} else {
			$('#section-title').html('');
		}
		
		if (obj.description != 'undefined') {
			$('meta[name=description]').attr('content',obj.description);
		}
		
		switch(obj.section)
		{
			case 'left':
			default:
				if($('.main-content-left').length==0){
					preHtml='<div class="main-content-left"></div>';
				}
				if(preHtml!=''){
					$('#main-wrapper-dark').html(preHtml);
				}
				
				$('.main-content-left').html(obj.html);
			break;
			case 'right':
				$('.sidebar').html(obj.html);
			break;
			case 'all':
				$('#page-content').html(obj.html);
			break;			
		}
		Site.loading();
		Site.checkImages();
	},
	T:function(text){
		return text;
	},
	friendlyUrl:function(str,max) 
	{
		if (max === undefined) max = 32;
		var a_chars = new Array(
			new Array("a",/[Ã¡Ã Ã¢Ã£ÂªÃÃ€Ã‚Ãƒ]/g),
			new Array("e",/[Ã©Ã¨ÃªÃ‰ÃˆÃŠ]/g),
			new Array("i",/[Ã­Ã¬Ã®ÃÃŒÃŽ]/g),
			new Array("o",/[Ã²Ã³Ã´ÃµÂºÃ“Ã’Ã”Ã•]/g),
			new Array("u",/[ÃºÃ¹Ã»ÃšÃ™Ã›]/g),
			new Array("c",/[Ã§Ã‡]/g),
			new Array("n",/[Ã‘Ã±]/g)
		);
		// Replace vowel with accent without them
		for(var i=0;i<a_chars.length;i++)
		str = str.replace(a_chars[i][1],a_chars[i][0]);
		// first replace whitespace by -, second remove repeated - by just one, third turn in low case the chars,
		// fourth delete all chars which are not between a-z or 0-9, fifth trim the string and
		// the last step truncate the string to 32 chars 
		return str.replace(/\s+/g,'-').toLowerCase().replace(/[^a-z0-9\-]/g, '').replace(/\-{2,}/g,'-').replace(/(^\s*)|(\s*$)/g, '').substr(0,max);
	}
};