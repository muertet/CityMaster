<?php
/*
Tracks the amount donated by each player to public buildings
*/
class DonationLog extends BasicClass
{
	public $table = "donation_log";
	public $attributes = array
	(
		array('id','integer'),
		array('building','integer'),
		array('level','integer'),
		array('amount','integer'),
		array('uid','integer'),
		array('date','date'),
	);

	const STATUS_PENDING = 2;

	public function __construct($array=null)
	{
		parent::__construct();

		if($array!=null){
			foreach($array as $row=>$v){
				$this->$row=$v;
			}
		}
	}

	public function getByBuilding ($id) {
		$where = array(
			array('building','=',$id)
		);
		return $this->find($where);
	}
	public function getByUid ($uid) {
		$where = array(
                        array('uid','=',$id)
                );
                return $this->find($where);
	}

	public function saveNew()
	{
		$this->status = self::STATUS_PENDING;

		$q = "select `id` from `".$this->table."` where `building`='".$this->building."' AND uid = '".$this->uid."' LIMIT 1";
		$rows = Service::getDB()->query($q);

		if(sizeof($rows)>0){
			return false;
		}
		$r = parent::saveNew();

		return $r;
	}
}
