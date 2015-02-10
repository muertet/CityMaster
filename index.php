<?php
include_once('bootstrap.php');

if (empty($_SESSION['user'])) {
	header('Location: '.Config::get('basedir').'login.php');
	exit;
}

if (!isset($_SESSION['app']->authToken)) {
	$api = new ApiCaller();
	$api->getAuthToken();
}

$crafting = new Crafting();
$craftList = $crafting->getList();
$craftList = $crafting->getArrayView($craftList);

$bHelper = new BuildingHelper();
$bList = $bHelper->getList();
$tmpList = $bHelper->getArrayView($bList);
$bList = array();

foreach($tmpList as $b) {
	$bList[$b['id']] = $b;
}
/*
$vars = array( 
    'encode' => false, 
    'timer' => true, 
    'gzip' => false, 
    'closure' => false
);
$minified = new Minifier( $vars );*/

?>
<!DOCTYPE html>
<html>
<head>
    <title>City Master</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
    <base href="http://<?=Config::get('domain');?><?=Config::get('basedir');?>">
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="stylesheet" href="assets/css/lib/apprise-v2.css" />
    <link rel="stylesheet" href="assets/css/lib/cartodb.css" />
    <link rel="stylesheet" href="assets/css/lib/magnific-popup.css" />
    <link rel="stylesheet" href="assets/css/source/map.css" />
    <link rel="stylesheet" href="assets/css/source/ui.css" />
</head>
<body>
	<div id="main-menu">
		<ul>
			<li>
				<span id="options-menu"><?=$_SESSION['user']->nick;?> ▾ </span>
				<ul class="submenu">
				    <li id="menu-market">Mercado</li>
				    <li id="menu-referred">Referidos</li>
				    <li id="menu-settings">Ajustes</li>
					<li><a href="/logout.php">Salir</a></li>
			  </ul>
			</li>
			<li><img src="assets/images/currency/euro.png"> <span id="user-money"><?=Util::formatAmount($_SESSION['user']->money);?></span>€</li>
			<li id="topbar-gold"><img src="assets/images/currency/gold.png"> <span id="user-gold"><?=$_SESSION['user']->gold;?></span></li>
			<li><img src="assets/images/menu_ui/clock.png"> <span id="game-clock"></span></li>
			<li id="loading-div" style="display: none;"><img src="assets/images/loading.gif"></li>
		</ul>
	</div>
	<div id="map"></div>
    <div id="building-info">Loading</div>

    <!-- include cartodb.js library -->
    <script src="assets/js/source/cartodb.js"></script>
    <script src="assets/js/lib/jquery.magnific-popup.min.js"></script>
    <script src="assets/js/lib/apprise-v2.js"></script>
    <script src="assets/js/lib/templates.js"></script>
    <script src="assets/js/lib/js-signals.min.js"></script>
    <script src="assets/js/lib/crossroads.min.js"></script>
    <script src="assets/js/lib/moment.min.js"></script>
    <script src="assets/js/lib/moment-timezone.min.js"></script>
    <script src="assets/js/lib/countdown.min.js"></script>
    <script src="assets/js/source/timer.js"></script>
    <script src="assets/js/source/app.js"></script>
    <script src="assets/js/source/site.js"></script>
    <script src="assets/js/source/building.js"></script>
    <script src="assets/js/source/buildinghelper.js"></script>
    <script src="assets/js/source/map.js"></script>
    <script src="assets/js/source/user.js"></script>
    <script src="assets/js/source/util.js"></script>
    <script src="assets/js/source/sound.js"></script>
    <script src="assets/js/source/crafting.js"></script>
    <script>
		App.cartodb = {
			table: "map_multipolygons",
			username: "<?=Config::get('cartodb')['subdomain']?>",
		};
		App.cdn_url = "<?=Config::get('domain');?><?=Config::get('basedir');?>api/maps";
		<? if (!empty($_SESSION['user']->lat)) { ?>
    		Map.initialCoords = [<?=$_SESSION['user']->lat;?>,<?=$_SESSION['user']->lng;?>];
		<? } ?>

		window.onload = Map.init;
		
		Site.title = 'CityMaster';
		Site.authToken = "<?=$_SESSION['app']->authToken;?>";
		Site.userToken = "<?=$_SESSION['app']->userToken;?>";
		
		Util.startClock();
		
		User.init(<?php echo json_encode($_SESSION['user']);?>);
		
		Crafting.recipes = <?=json_encode($craftList);?>;
		BuildingHelper.info = <?=json_encode($bList);?>;
		
		$(function() {
		    Site.init();
		});
	</script>
	</body>
</html>
