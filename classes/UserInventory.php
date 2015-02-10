<?php

class UserInventory extends BasicClass {
	
	public $table = "user_inventory";
	public $attributes = array
	(
        array('id','integer'),
        array('uid','integer'),
        array('item','integer'),
        array('quantity','integer'),
        array('health','integer'),
        array('date','date'),
	);

	public function __construct($array = null)
	{
        parent::__construct();

        if ($array != null) {
        	if (is_array($array)) {
				foreach ($array as $row => $v) {
					$this->$row = $v;
	            }
			} else {
				$this->uid = $array;
			}
        }
	}
	
	public function getByUid ($uid) {
		$where = array(
			array('uid', '=', $uid)
		);
		
		return $this->find($where);
	}
	
	public function getView () {
		$raw = $this->getRaw();
		
		$item = new Item();
		$item->get($this->item);
		$raw['item'] = $item->getView();
		
		unset($raw['id']);
		
		return $raw;
	}
	
	/**
	* Adds an item to user inventory
	* @param integer $id
	* @param integer $quantity
	* @param integer $health
	* 
	* @return boolean
	*/
	public function addItem ($id, $quantity, $health = 100) {
		
		$where = array (
			array('uid', '=', $this->uid),
			array('item', '=', $id),
			array('health', '=', $health),
		);
		
		$results = $this->find($where);
		
		if (sizeof($results) < 1) {
			$item = new UserInventory(array(
				'uid' => $this->uid,
				'item' => $id,
				'quantity' => $quantity,
				'health' => $health,
			));
			return $item->saveNew();
		} else {
			$results[0]->quantity += $quantity;
			return $results[0]->save();
		}
	}
	
	public function removeItem ($id, $quantity, $health) {
		$where = array (
			array('uid', '=', $this->uid),
			array('item', '=', $id),
			array('health', '=', $health),
		);
		
		$results = $this->find($where);
		if (sizeof($results) == 1) {
			
			$results[0]->quantity -= $quantity;
			
			if ($results[0]->quantity < 1) {
				return $results[0]->delete();
			} else {
				return $results[0]->save();
			}
		} else {
			return false;
		}
	}
	
	public function save () {
		if ($this->quantity < 1) {
			return $this->delete();
		} else {
			return parent::save();
		}
	}

}
