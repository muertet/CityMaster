<?php

class cUser extends Controller
{
	public function getReferredList () {
		
		$where = array(
			array('referrer', '=', $this->user->id)
		);
		
		$user = new User();
		$list = $user->find($where);
		
		if (!$list) {
			return array();
		}
		
		return $user->getArrayView($list);
	}
	public function sellItem () {
		$id = (int)$this->param('id');
		$quantity = (int)$this->param('quantity');
		$price = (int)$this->param('price');
		
		if ($price < 1 || $id < 1 || $quantity < 1) {
			throw new Exception(ApiException::MISSING_DATA);
		}
		
		$inventory = new UserInventory($this->user->id);
		$itemList = $inventory->getByUid($this->user->id);
		
		foreach ($itemList as $item)
		{
			if ($item->item == $id) {
				if ($item->quantity < $quantity) {
					throw new Exception(ApiException::MISSING_ITEMS);
				}
				
				if ($item->health < 100) {
					throw new Exception(ApiException::INVALID_ACTION);
				}

				// remove item from inventory and publish a market offer
				$removed = $inventory->removeItem($item->item, $quantity, $item->health);
				
				if ($removed) {
					$market = new Market(array(
						'item' => $id,
						'uid' => $this->user->id,
						'quantity' => $quantity,
						'price' => $price,
					));
					return $market->saveNew();
				} else {
					return false;
				}
				break;
			}
		}
		return false;
	}
	public function craft() {
		$item = (int)$this->param('item');
		$amount = (int)$this->param('amount');
		
		if (empty($item) || $item < 1 || $amount < 1) {
			throw new Exception(ApiException::MISSING_DATA);
		}
		
		$crafting = new Crafting();
		$recipe = $crafting->getByResult($item);
		
		if (!$recipe) {
			throw new Exception(ApiException::MISSING_DATA);
		}
		
		$inventory = new UserInventory($this->user->id);
		$itemList = $inventory->getByUid($this->user->id);
		$list = array();
		$aList = array();
		
		foreach ($itemList as $itm) {
			$list[$itm->item] = $itm;
		}
		
		foreach ($recipe->ingredients as $id => $quantity) {
			if (empty($list[$id]) || $list[$id]->quantity < ($quantity * $amount) ) {
				throw new Exception(ApiException::MISSING_ITEMS);
			}
			$aList[$id] = $list[$id];
			$aList[$id]->quantity -= $quantity * $amount;
		}
		
		$saved = $inventory->addItem($item, $amount);
		
		if (!$saved) {
			throw new Exception(ApiException::INTERNAL_ERROR);
		}
		
		// once we know that user has all required items, save quantity changes
		foreach ($recipe->ingredients as $id => $quantity) {
			$aList[$id]->save();
		}
	}
	public function getInventory() {
		$building = (int)$this->param('building');
		$inventory = new UserInventory();
		
		$where = array(
			array('uid', '=', $this->user->id)
		);
		
		if ($building > 0)
		{
			$item = new cItem(array('id' => $building));
			$list = $item->getByBuilding();
			$idList = array();
			
			if (!$list) {
				return array();
			}

			foreach ($list as $item) {
				$idList[] = $item['id'];
			}
			$where [] = array('item', 'IN', '('.implode(',', $idList).')');
		}
		
		$itemList = $inventory->find($where);
		
		return $inventory->getArrayView($itemList);
	}
	public function set() {
		$allowedFields = array(
			'name',
			'surnames',
			'free_vacations',
			'total_vacations',
			'email',
			'image',
			'phone',
		);
		
		if (empty($this->param('uid'))) {
			throw new Exception('missing data');
		} else {
			$id = (int)$this->param('uid');
		}
		
		$user = new User();
		$user->get($id);
		
		foreach ($allowedFields as $field) {
			if (!empty($this->param($field))) {
				$user->{$field} = $this->param($field);
				
				if (in_array($field,array("free_vacations","total_vacations"))) {
					$user->{$field} = str_replace(',','.',$user->{$field});
				}
			}
		}
		return $user->save();
	}
	public function getGrouptList($uid = false) {
		
		if (empty($uid)) {
			$uid = $_REQUEST['uid'];
		}
		
		if (empty($uid)) {
			throw new Exception('missing info');
		}
		
		$class = new UserGroup();
		$r = $class->getListByUid($uid);
		
		return $r;
	}
	public function checkEmail($email)
	{
		if (empty($email)) {
			$email = $this->param('email');
		}
		
		if (strlen($email) < 4) {
			return false;
		}
		
		$where = array(
			array('email','=',$email)
		);
		
		$user = new User();
		$r = $user->find($where);
		
		if (sizeof($r) == 0) {
			return true;
		} else {
			return false;
		}
	}
	public function checkNick($nick)
	{
		if (empty($nick)) {
			$nick = $this->param('nick');
		}
		
		if(strlen($nick)<4){
			return false;
		}
		
		$where=array(
			array('nick','=',$nick)
		);
		
		$user = new User();
		$r = $user->find($where);
		
		if (sizeof($r) == 0) {
			return true;
		} else {
			return false;
		}
	}
	public function getList()
	{
		$available = $this->param('available');
		
		if (!empty($available)) {
			$list = $this->user->getAvailableList();
		} else {
			$list = $this->user->getList();
		}
		
		foreach ($list as $k => $user) {
			$list[$k] = $this->parse($user);
		}
		
		return $list;
	}
	public function get($uid = false)
	{
		if (empty($uid)) {
			$uid = (int)$_REQUEST['uid'];
		
			if (empty($uid)) {
				$uid = $this->user->id;
			}
		}
		
		$userClass = new User();
		
		$user = $userClass->get($uid);
		
		return $this->parse($user);
	}
	public function getByEmail($email)
	{
		if (empty($email)) {
			$email = $_REQUEST['email'];
		}
		
		$where = array(
			array('email', '=', $email)
		);
		
		$userClass = new User();
		
		$results = $userClass->find($where);
		
		if (!$results || sizeof($results) < 1) {
			return false;
		}
		
		return $this->parse($results[0]);
	}
	
	public function create()
	{
		$data = array (
			'nick' => strip_tags($_POST['nick']),
			'email' => strip_tags($_POST['email']),
			'lat' => $_POST['lat'],
			'lng' => $_POST['lng'],
			'country' => $this->param('country', 'string', true),
			'referrer' => $_POST['referrer'],
			'password' => $_POST['password'],
		);
		
		if(empty($data['nick']) || empty($data['email']) || strlen($data['password']) < 6 || empty($data['password']) || empty($data['lat']) || empty($data['lng'])) {
			throw new Exception(ApiException::MISSING_DATA);
		}
		
		if (!$this->checkNick($data['nick'])) {
			throw new Exception(ApiException::NICK_IN_USE);
		}
		
		if (!$this->checkEmail($data['email'])) {
			throw new Exception(ApiException::EMAIL_IN_USE);
		}
		
		$user = new User($data);
		$result = $user->saveNew();

		if ($result > 0) {
			if (!empty($data['referrer'])) {
				$referrer = new User();
				$referrer = $referrer->get($data['referrer']);

				if ($referrer) {
					$referrer->setMoney(1000, TransactionRecord::REFER_FRIEND, $result);
				}
			}
		}
		return $result;
	}

	public function parse($obj)
	{
		if (empty($obj)) {
			throw new Exception(ApiException::INVALID_ACTION);
		}
		$raw = $obj->getRaw();

		// is not himself
		if ($this->user == null || $this->user->id != $obj->id && !$this->user->isBoss()) {
			unset($raw['gold']);
			unset($raw['money']);
			unset($raw['lat']);
			unset($raw['lng']);
			unset($raw['referrer']);
		}

		unset($raw['email']);
		unset($raw['password']);
		unset($raw['date']);

		$raw['url'] = 'http://'.Config::get('domain').'/user/'.$obj->id.'/'.Util::friendly_url($obj->nick).'.htm';

		return $raw;
	}
}
