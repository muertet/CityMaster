<?php

class cBuilding extends Controller
{
    public function redeem () {
        $id = $this->param('building', 'int', true);

        $building = new Building();
        $bInfo = $building->get($id);

        if (!$bInfo || $bInfo->owner != $this->user->id || $bInfo->isBusy()) {
            throw new Exception(ApiException::INVALID_ACTION);
        }

        $inventory = new UserInventory($this->user->id);
        $production = new BuildingProduction();
        $list = $production->get($id);

        foreach ($list as $item) {
            $inventory->addItem($item->item, $item->quantity);
            $item->delete();
        }
        return true;
    }

    /**
     * Produces and stores items on building warehouse
     *
     * @throws Exception if missing data
     * @throws Exception if building is busy
     * @throws Exception if user isnt building owner
     * @throws Exception if building does not exist
     * @throws Exception if user hasnt enough resources
     *
     * @return mixed (boolean/integer)
     */
    public function produce () {
		$id = $this->param('building', 'int', true);
        $item = $this->param('item', 'int', true);
        $quantity = $this->param('quantity', 'int', true);

        $building = new Building();
        $bInfo = $building->get($id);

        $freeSpace = $bInfo->storage - ($bInfo->used_storage + $quantity);

        if (!$bInfo || $bInfo->owner != $this->user->id || $bInfo->isBusy()) {
            throw new Exception(ApiException::INVALID_ACTION);
        }

        if ($freeSpace <= 0) {
            throw new Exception(ApiException::NO_STORAGE_ENOUGH);
        }

        $dInfo = $bInfo->getTypeInfo();
        $delayTime = $dInfo->build_delay;

        // if its a factory, get required items to make the recipe
        if ($dInfo->type == BuildingHelper::TYPE_FACTORY) {
            $inventory = new UserInventory();
            $uinv = $inventory->getByUid($this->user->id);
			$itemList = array();
			$aList = array();

			// reorder uinv to set item as key
			foreach ($uinv as $itm) {
				if ($itm->health != 100) {
					continue;
				}
				$itemList[$itm->item] = $itm;
			}

            $crafting = new Crafting();
            $recipe = $crafting->getByResult($item);

			// first for to check if has all necessary items
			foreach ($recipe->ingredients as $itm => $qtity) {
				if (empty($itemList[$itm]) || $itemList[$itm]->quantity < $qtity ) {
					throw new Exception(ApiException::MISSING_ITEMS);
				}
                $aList[$itm] = $itemList[$itm];
                $aList[$itm]->quantity -= $qtity * $quantity;
            }

			// remove them from inventory to start production
			foreach ($aList as $itm) {
				$itm->save();
			}
        }

        if (empty($delayTime)) {
            $delayTime = 14;
        }
        $bInfo->setDelayTime($delayTime);

		// start production
        $started = $bInfo->addProduction($quantity, $item);

		if ($started) {
			return $bInfo->save();
		} else {
            return false;
        }
    }

	/**
	 * Pays current building delay with gold
     *
     * @throws Exception user hasnt enough gold to pay
     *
     * @return boolean
	*/
	public function payDelay () {
		$id = (int)$this->param('id');
		
		if ($id < 1) {
            throw new Exception(ApiException::MISSING_DATA);
        }
        $building = new Building();
        $bInfo = $building->get($id);
        
        // check if is not the owned or is a public building
        if (!$bInfo || $bInfo->owner != $this->user->id || !$bInfo->isBusy()) {
            throw new Exception(ApiException::INVALID_ACTION);
        }

        if ($this->user->gold < 1) {
            throw new Exception(ApiException::NO_GOLD_ENOUGH);
        }
        
        $bInfo->status = Building::STATUS_OK;
        $bInfo->delay_time = null;
        
        $charged = $this->user->setGold(-1, TransactionRecord::PAY_DELAY, $bInfo->id);
			
		if ($charged) {
			return $bInfo->save();
		}
        
        return false;
	}

	/**
	 * Upgrades building level (warehouse and other features)
	 *
     * @throws Exception if user hasnt enough money/gold to pay the upgrade
	 */
	public function upgrade () {
		$id = (int)$this->param('id');

        if ($id < 1) {
                throw new Exception(ApiException::MISSING_DATA);
        }
        $building = new Building();
        $bInfo = $building->get($id);
        
        // check if is already owned or is a public building
        if (!$bInfo || $bInfo->isPublic() || $bInfo->owner != $this->user->id || $bInfo->isBusy()) {
                throw new Exception(ApiException::INVALID_ACTION);
        }
        
        // check payment type
        if ($bInfo->level > Building::MAX_LEVEL) {
			if ($this->user->gold < 1) {
				throw new Exception(ApiException::NO_GOLD_ENOUGH);
			}
			$charged = $this->user->setGold(-1, TransactionRecord::HOUSE_UPGRADE, $bInfo->id);
			
			if ($charged) {
				return $bInfo->upgrade();
			}
		} else {
			$toPay = ($bInfo->level + 1) * Building::LEVEL_MULTIPLIER * 100;
			if ($this->user->money < $toPay) {
				throw new Exception(ApiException::NO_MONEY_ENOUGH);
			}
			
			$charged = $this->user->setMoney($toPay, TransactionRecord::HOUSE_UPGRADE, $bInfo->id);
			
			if ($charged) {
				return $bInfo->upgrade();
			}
		}
	}
	public static function getByUidKey ($uid) {
		return "buildingList-".$uid;
	}
	public function getByUid () {
		$uid = (int)$this->param('uid');

        if ($uid < 1) {
                throw new Exception(ApiException::MISSING_DATA);
        }
        
        $key = self::getByUidKey($uid);
        $buildingList = Cache::get($key);
        if (!$buildingList) {
        	$where = array(
        		array('owner', '=', $uid),
        	);
        	$building = new Building();
        	$buildingList = $building->find($where);
        	
        	if (!$buildingList) {
				return array();
			}
        	
        	$buildingList = $building->getArrayView($buildingList);
        	
			Cache::set($key, $buildingList, Cache::HOUR);
		}
		
		return $buildingList;
	}

    /**
     * Donates money to a public building
     *
     * @throws Exception if user hasnt enough money
     *
     * @return boolean
     */
	public function donate () {
		$id = (int)$this->param('id');
		$amount = (int)$this->param('amount');

        if ($amount < 1 || $id < 1) {
                throw new Exception(ApiException::MISSING_DATA);
        }

		if ($this->user->money < $amount) {
                throw new Exception(ApiException::NO_MONEY_ENOUGH);
        }

		$donation = new Donation();
		$donation = $donation->getByBuilding($id);

		// no one has donated yet
		if (!$donation) {
			$b = new Building();
			$b = $b->get($id);

			if (!$b || !$b->isPublic()) {
				throw new Exception(ApiException::INVALID_ACTION);
			}
			$donation = new Donation(array(
				'building' => $id,
			));
			$saved = $donation->saveNew();

			if (!$saved) {
				throw new Exception(ApiException::INTERNAL_ERROR);
			}

			$donation = $donation->getByBuilding($id);

			if (!$donation) {
				throw new Exception(ApiException::INTERNAL_ERROR);
			}
		}

		if ($donation->status == Donation::STATUS_FINISHED) {
			throw new Exception(ApiException::INVALID_ACTION);
		}


		// charge user donation
		$charged = $this->user->setMoney($amount, TransactionRecord::HOUSE_DONATION, $id);

		if ($charged) {

			$donation->amount += $amount;
			$oldLevel = $donation->level;

			// check if donation has reached its goal
			if ($donation->amount >= floor(($donation->level + 1) * Building::LEVEL_MULTIPLIER * 10000)) {
				$donation->amount = 0;
				$donation->level++;

				if ($donation->level == Building::MAX_LEVEL) {
					$donation->status = Donation::STATUS_FINISHED;
				}
			}
			$donation->save();

			// save the donation log
			$where = array(
				array('uid', '=', $this->user->id),
				array('level', '=', $oldLevel),
				array('building', '=', $id),
			);
			
			$log = new DonationLog();
			$results = $log->find($where);
			
			if (sizeof($results) < 1) {
				$log = new DonationLog(array(
					'uid' => $this->user->id,
					'level' => $oldLevel,
					'amount' => $amount,
					'building' => $id,
				));
				$log->saveNew();
			} else {
				$results[0]->amount += $amount;
				$results[0]->save();
			}
			
		}
		return $charged;
	}
	public function sell () {
		$id = (int)$this->param('id');

		$building = new Building();
        $bInfo = $building->get($id);

		if ($id < 1 || !$bInfo->isPurchasable()) {
                throw new Exception(ApiException::MISSING_DATA);
        }

		// check if is already owned or is a public building
        if (!$bInfo || !$bInfo->hasOwner() || $bInfo->isPublic() || $bInfo->owner != $this->user->id || $bInfo->isBusy()) {
                throw new Exception(ApiException::INVALID_ACTION);
        }

		if ($bInfo->purchase_type == Building::PURCHASE_TYPE_RENT) {
                $price = $bInfo->rent_price;
        } else {
                $price = $bInfo->purchase_price;
        }

		// reset building
		$bInfo->owner = null;
		$bInfo->purchase_type = null;
		$bInfo->level = null;
		$bInfo->status = null;
		$bInfo->type = null;

		$charged = $this->user->setMoney(($price/2), TransactionRecord::HOUSE_SELL, $id);

		if ($charged) {
                return $bInfo->save();
        } else {
                return false;
        }
	}
	public function purchase () {
		$id = (int)$this->param('id');
		$pType = (int)$this->param('pType');
		$type = (int)$this->param('type');

		if ($id < 1) {
			throw new Exception(ApiException::MISSING_DATA);
		}

		$building = new Building();
		$bInfo = $building->get($id);
		
		if (!empty($type)) {
			$bInfo->type = $type;
		}


		if ($pType == Building::PURCHASE_TYPE_RENT) {
			$price = $bInfo->rent_price;
			$reason = TransactionRecord::HOUSE_RENT;
		} else {
			$price = $bInfo->purchase_price;
			$reason = TransactionRecord::HOUSE_PURCHASE;
		}

		// check if is already owned or is a public building
		if (!$bInfo || $bInfo->hasOwner() || !$bInfo->isPurchasable() || $bInfo->isPublic() || $price < 1) {
			throw new Exception(ApiException::INVALID_ACTION);
		}

		if ($this->user->money < $price) {
			throw new Exception(ApiException::NO_MONEY_ENOUGH);
		}

		$bInfo->owner = $this->user->id;
		$bInfo->status = Building::STATUS_BUSY;
		$bInfo->purchase_type = $pType;
		$bInfo->level = 1;
        $bInfo->used_storage = 0;
		
		$dInfo = $bInfo->getTypeInfo();
        $delayTime = $dInfo->build_delay;
        $bInfo->storage = $dInfo->storage;
		
		if (empty($delayTime)) {
			$delayTime = 14;
		}
		
		$bInfo->setDelayTime($delayTime);
		
		$charged = $this->user->setMoney(-$price, $reason, $id);

		if ($charged) {
			return $bInfo->save();
		} else {
			return false;
		}
	}


	public function get() {
		$id = (int)$this->param('id');

		if ($id < 1) {
			throw new Exception(ApiException::MISSING_DATA);
		}
		$class = new Building();
		$data = $class->get($id);
		return $this->parse($data);
	}


	public function parse ($obj) {

		$obj->isBusy();
		$raw = $obj->getRaw();

		$raw['id'] = $obj->cartodb_id;

		if (!empty($obj->owner)) {
			$cUser = new cUser();
			$raw['owner'] = $cUser->get($obj->owner);
		} else {
			unset($raw['owner']);
		}
		
		if ($obj->isPublic()) {
			$donation = new Donation();
			$donation= $donation->getByBuilding($obj->cartodb_id);
			
			if ($donation->building == null) {
				$donation = new Donation(array(
					'building' => $obj->cartodb_id,
				));
				$donation->saveNew();
			}
			
			$raw['donation'] = $donation->getView();
		}

		unset($raw['cartodb_id']);

		return $raw;
	}
}