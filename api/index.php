<?php
header('Content-type: application/json');
include_once('../bootstrap.php');

$apiRouter = new AltoRouter();
$apiRouter->setBasePath(Config::get('basedir').'api');

// ROUTER MAPPING


// AUTH TOKEN
$apiRouter->map('POST','/app/create', array('c' => 'App', 'a' => 'create','authtoken'=>false,'usertoken'=>false,'official'=>false));
$apiRouter->map('POST|GET','/authtoken/create', array('c' => 'AuthToken', 'a' => 'create','authtoken'=>false,'usertoken'=>false,'official'=>false));
$apiRouter->map('POST','/usertoken/create', array('c' => 'UserToken', 'a' => 'create','authtoken'=>true,'usertoken'=>false,'official'=>true));

// USER
$apiRouter->map('POST|GET','/user/referred/list', array('c' => 'User', 'a' => 'getReferredList','authtoken'=>true,'usertoken'=>true,'official'=>true));
$apiRouter->map('POST','/user/building/list', array('c' => 'Building', 'a' => 'getByUid','authtoken'=>true,'usertoken'=>true,'official'=>true));
$apiRouter->map('POST','/user/inventory/sell', array('c' => 'User', 'a' => 'sellItem','authtoken'=>true,'usertoken'=>true,'official'=>true));
$apiRouter->map('POST','/user/inventory/craft', array('c' => 'User', 'a' => 'craft','authtoken'=>true,'usertoken'=>true,'official'=>true));
$apiRouter->map('POST','/user/inventory/list', array('c' => 'User', 'a' => 'getInventory','authtoken'=>true,'usertoken'=>true,'official'=>true));
$apiRouter->map('POST','/user/create', array('c' => 'User', 'a' => 'create','authtoken'=>true,'usertoken'=>false,'official'=>true));
$apiRouter->map('POST','/user/set', array('c' => 'User', 'a' => 'set','authtoken'=>true,'usertoken'=>true,'official'=>true,'boss'=>true));
$apiRouter->map('POST|GET','/user/get', array('c' => 'User', 'a' => 'get','authtoken'=>true,'usertoken'=>true,'official'=>false));
$apiRouter->map('POST|GET','/user/list', array('c' => 'User', 'a' => 'getList','authtoken'=>true,'usertoken'=>true,'official'=>false,'boss'=>true));
$apiRouter->map('GET','/user/group/list', array('c' => 'User', 'a' => 'getGrouptList','authtoken'=>true,'usertoken'=>true,'official'=>true));
$apiRouter->map('POST|GET','/user/group/set', array('c' => 'User', 'a' => 'setGroup','authtoken'=>true,'usertoken'=>true,'official'=>true,'boss'=>true));

// MAPS
// http://citymaster.com/api/maps/datest/api/v1/map/88ffc8a6db6f507a94111521d67fbf93:1406479525938.22/17/66319/48955.png
$apiRouter->map('POST|GET','/maps/[*]', array('c' => 'Map', 'a' => 'get','authtoken'=>false,'usertoken'=>false,'official'=>true));

// BUILDING
$apiRouter->map('POST|GET','/building/get', array('c' => 'Building', 'a' => 'get','authtoken'=>true,'usertoken'=>true,'official'=>true));
$apiRouter->map('POST|GET','/building/upgrade', array('c' => 'Building', 'a' => 'upgrade','authtoken'=>true,'usertoken'=>true,'official'=>true));
$apiRouter->map('POST|GET','/building/purchase', array('c' => 'Building', 'a' => 'purchase','authtoken'=>true,'usertoken'=>true,'official'=>true));
$apiRouter->map('POST|GET','/building/donate', array('c' => 'Building', 'a' => 'donate','authtoken'=>true,'usertoken'=>true,'official'=>true));
$apiRouter->map('POST|GET','/building/sell', array('c' => 'Building', 'a' => 'sell','authtoken'=>true,'usertoken'=>true,'official'=>true));
$apiRouter->map('POST|GET','/building/list', array('c' => 'Building', 'a' => 'getList','authtoken'=>true,'usertoken'=>true,'official'=>true));
$apiRouter->map('POST|GET','/building/pay_delay', array('c' => 'Building', 'a' => 'payDelay','authtoken'=>true,'usertoken'=>true,'official'=>true));
$apiRouter->map('POST|GET','/building/produce', array('c' => 'Building', 'a' => 'produce','authtoken'=>true,'usertoken'=>true,'official'=>true));

// MARKET
$apiRouter->map('POST|GET','/market/purchase', array('c' => 'Market', 'a' => 'purchase','authtoken'=>true,'usertoken'=>true,'official'=>true));
$apiRouter->map('POST|GET','/market/list', array('c' => 'Market', 'a' => 'getList','authtoken'=>true,'usertoken'=>true,'official'=>true));


$match = $apiRouter->match();
$api=new Api($_REQUEST['authtoken'],$_REQUEST['usertoken']);

if(!$match){
	echo $api->replyError('Invalid call');
}else{	
	$call=$match['target'];
	
	$isOk=$api->checkSecurity($call['authtoken'],$call['usertoken'],$call['official']);
	
	if($isOk===true){
		$api->checkPermissions($call['boss']);
		
		echo $api->exec($call['c'],$call['a']);
	}else{
		echo $isOk;
	}
}
