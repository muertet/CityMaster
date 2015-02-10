<?php

class Market extends BasicClass {
	
	public $attributes = array
	(
        array('id','integer'),
        array('item','integer'),
        array('uid','integer'),
        array('quantity','integer'),
        array('price','integer'),
        array('status','integer'),
        array('date','date'),
	);
	
	const STATUS_DISABLED = 0;
	const STATUS_SELLING = 1;
	const STATUS_SOLD = 2;

	public function __construct($array = null)
	{
        parent::__construct();

        if ($array != null) {
            foreach ($array as $row => $v) {
                    $this->$row = $v;
            }
        }
	}


	public function saveNew () {
		$this->status = self::STATUS_SELLING;
		
		return parent::saveNew();
	}
}
