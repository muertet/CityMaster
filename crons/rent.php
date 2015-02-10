<?php
/**
* Deduct at the end of the month, the rent of each building
* Monthly cron
*/
include_once(dirname(dirname(__FILE__)).'/bootstrap.php');
set_time_limit(0);

$where = array(
	array('status', 'IN', array(Building::STATUS_OK, Building::STATUS_BUSY)),
	array('purchase_type', '=', Building::PURCHASE_TYPE_RENT),
	array('owner', '>', 0),
);

$b = new Building();
$bList = $b->find($where);

foreach ($bList as $building) {
	
	$user = new User();
	$user->get($building->owner);
	$user->setMoney(-$building->rent_price, TransactionRecord::HOUSE_RENT, $building->cartodb_id);
}