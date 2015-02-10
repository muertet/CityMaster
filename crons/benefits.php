<?php
/**
* Calculate daily benefits for each profitable building
* Daily cron
* 
*/
include_once(dirname(dirname(__FILE__)).'/bootstrap.php');
set_time_limit(0);

$where = array(
    array('type', '=', BuildingHelper::TYPE_MINE)
);

$helper = new BuildingHelper();
$r = $helper->find($where);
$tList = array();

foreach ($r as $building) {
    $tList[] = $building->id;
}

$where = array(
//	array('used_storage', '!=', 'storage'),
	array('status', '=', Building::STATUS_OK),
	array('owner', '>', 0),
	array('type', 'IN', '('.implode(',', $tList).')'),
);

$b = new Building();
$bList = $b->find($where);

foreach ($bList as $building) {

	$quantity = 100 * ($building->level * 0.15);

    $building->addProduction($quantity);
    $building->save();
}