
function loadPage(){
	var currentRoute=window.location.href.replace($('base').attr('href'),'/');
	if(currentRoute!=''){
		crossroads.parse(currentRoute);
	}
}
loadPage();

window.onpopstate = function () {
    loadPage();
};

$(document).ready(function()
{
	$(document).on('click','a',function(event)
	{
		var href=$(this).attr('href');
		
		if(href!=null && href!='' && href.indexOf('http')==-1 && href.indexOf('#')==-1 && !$(this).hasClass('no-ajaxify'))
		{
			event.preventDefault();
		
			history.pushState('test',{},href);
			console.log('parsing',href);
			
			if(href.substring(0, 1) != '/'){
				href='/'+href;
			}
			crossroads.parse(href);
		}
	});
});
function $_GET(k) { 
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars[k];

}
