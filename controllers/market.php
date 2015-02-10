<?php

class cMarket extends Controller
{
	public function create() {
		
		$data = array(
			"item" => (int)$this->param('item'),
			"price" => (int)$this->param('price'),
			"quantity" => (int)$this->param('quantity'),
			"uid" => $this->user->id,
		);
		
		if (empty($data['item']) || empty($data['price']) || empty($data['quantity'])) {
			throw new Exception(ApiException::MISSING_DATA);
		}
		
		if ($data['price'] > 999999 || $data['price'] < 1 || $data['quantity'] < 1) {
			throw new Exception(ApiException::INVALID_ACTION);
		}
		
		//check if user does really have that item quantity
		$where = array(
			array('uid', '=', $this->user->id),
			array('item', '=', $data['item']),
			array('health', '=', 100),
		);
		
		$inventory = new UserInventory();
		$results = $inventory->find($where);
		
		if (sizeof($results) < 1 || $results[0]->quantity < $data['quantity']) {
			throw new Exception(ApiException::INVALID_ACTION);
		}
		
		// remove the items from seller
		$results[0]->quantity -= $data['quantity'];
		$charged = $results[0]->save();
		
		if ($charged) {
			$market = new Market($data);
			return $market->saveNew();
		} else {
			return false;
		}
	}
	public function purchase () {
		$id = (int)$this->param('id');
		$quantity = (int)$this->param('quantity');

		if ($id < 1 || $quantity < 1) {
			throw new Exception(ApiException::MISSING_DATA);
		}
		
		$class = new Market();
		$offer = $class->get($id);
		
		if ($offer->status != Market::STATUS_SELLING || $offer->quantity < $quantity) {
			throw new Exception(ApiException::INVALID_ACTION);
		}
		
		if ($this->user->money < ($offer->price * $quantity) ) {
			throw new Exception(ApiException::NO_MONEY_ENOUGH);
		}
		
		$charged = $this->user->setMoney(-$offer->price,TransactionRecord::MARKET_PURCHASE, $offer->id);
		
		if ($charged) {
			
			$offer->quantity -= $quantity;
			
			// set offer as sold
			if ($offer->quantity < 1) {
				$offer->status = Market::STATUS_SOLD;
			}
			
			$offer->save();
			
			// give buyers item
			$inventory = new UserInventory($this->user->id);
			$inventory->addItem($offer->id, $quantity);
			
			// pay seller
			$seller = new User();
			$seller = $seller->get($offer->uid);
			$seller->setMoney($offer->price,TransactionRecord::MARKET_SELL, $offer->id);
			return true;
		} else {
			return false;
		}
	}
	
	public function getList () {
		$item = (int)$this->param('item');
		$vehicle = (int)$this->param('vehicle');
		
		$where = array(
			array('status', '=', Market::STATUS_SELLING)
		);

		if ($item > 1) {
			$where[] = array('item', '=', $item);
		}
		
		if ($vehicle > 1) {
			// va a ser que no, hay que pillar las ids de los items que sean vehículos y luego hacer un IN
			//$where[] = array('vehicle', '=', $vehicle);
		}

		$class = new Market();
		$data = $class->find($where);
		return $this->parse($data);
	}

	public function get () {
		$id = (int)$this->param('id');

		if ($id < 1) {
			throw new Exception(ApiException::MISSING_DATA);
		}

		$class = new Market();
		$data = $class->get($id);
		return $this->parse($data);
	}

	public function parse ($obj) {
		
		if (is_array($obj)) {
			$list = array();
			
			foreach ($obj as $ob) {
				$list[] = $this->parse($ob);
			}
			return $list;
		} else {
			$raw = $obj->getRaw();
			
			$user = new User();
			$user = $user->get($obj->uid);
			 
			$raw['seller'] = $user->getView();
			
			$item = new Item();
			$item = $item->get($obj->item);
			
			$raw['item'] = $item->getView();
			
			unset($raw['uid']);
		
			return $raw;
		}
	}
}
