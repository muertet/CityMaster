<?php
/**
* This cron tries to recover users that dont login since some months ago
*/
include_once(dirname(dirname(__FILE__)).'bootstrap.php');

$date = date('Y-m-d', strtotime('-2  months'));
$donationAmount = 4000;

$where = array(
	array('lastAccess', '<=', $date)
);

$users = new Users();
$list = $users->find($where);

foreach ($list as $user) {
	$user->setMoney($donationAmount, TransactionRecord::ANONYMOUS_DONATION);

	  Util::mail($this->email, 'CityMaster - Has recibido un donativo', 'donation', array (
		'amount' => $donationAmount
          ));

	// record recovery try or 
}
