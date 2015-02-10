<?php

class Donation extends BasicClass
{
	public $attributes=array
	(
		array('id','string'),
		array('building','string'),
		array('level','string'),
		array('amount','string'),
		array('status','string'),
		array('date','integer'),
	);
	const STATUS_PENDING = 0;
	const STATUS_FINISHED = 1;

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
		$result = $this->find($where);

		if (!$result) {
			return false;
		} else {
			return $result[0];
		}
	}
	
	public function getView () {
		$raw = $this->getRaw();
		
		unset($raw['date']);
		
		return $raw;
	}

	public function saveNew()
	{
		$this->level = 0;
		$this->amount = 0;
		$this->status = self::STATUS_PENDING;

		$q = "select `id` from `".$this->table."` where `building`='".$this->building."' LIMIT 1";
		$rows = Service::getDB()->query($q);

		if(sizeof($rows)>0){
			return false;
		}
		$r = parent::saveNew();

		return $r;
	}
}
