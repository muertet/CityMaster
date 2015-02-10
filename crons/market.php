<?php
/**
* This cron simulates market offers by using fake users
*/
include_once(dirname(dirname(__FILE__)).'/bootstrap.php');


$fakeSellers = array(
 2 => BuildingHelper::GARAGE,
 3 => BuildingHelper::GARAGE
);

$market = new Market();

foreach ($fakeSellers as $seller => $bType) {

	$pItems = array();
	$nItems = array();
	$itemList = array();
	$itemClass = new cItem(array('id'=>$bType));
	$iList = $itemClass->getByBuilding();

	 foreach ($iList as $item) {
                $itemList[] = $item['id'];
        }

	$where = array(
		array('uid', '=', $seller),
		array('status', '=', Market::STATUS_SELLING),
	);

	$offers = $market->find($where);

	foreach ($offers as $offer) {
		$pItems[] = $offer->item;
	}

	$nItems = array_diff($itemList, $pItems);

	foreach ($nItems as $item) {
		$data = array(
			'uid' => $seller,
			'item' => $item,
			'quantity' => rand(40, 300),
			'price' => rand(40, 300),
		);
		$class = new Market($data);
		$r = $class->saveNew();
	}
}
