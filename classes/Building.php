<?php

class Building {
	private $cartodb;
	public static $typeInfo;
	public $table = 'map_multipolygons';

	public $attributes = array (
		array('cartodb_id','integer'),
		array('type','integer'),
		array('status','integer'),
		array('purchase_price','integer'),
		array('rent_price','integer'),
		array('purchase_type','integer'),
		array('owner','integer'),
		array('level','integer'),
		array('storage','integer'),
		array('used_storage','integer'),
		array('delay_time','date'),
	);

	const PURCHASE_TYPE_FINAL = 1;
	const PURCHASE_TYPE_RENT = 2;

	const LEVEL_MULTIPLIER = 1.45;
	const MAX_LEVEL = 10;
	const MAX_GOLD_LEVEL = 20;
	
	const STATUS_OK = 1;
	const STATUS_BUSY = 2;
	const STATUS_DESTROYED = 3;

	public function __construct($array = null)
	{
		if ($array != null) {
			if (is_array($array)) {
				foreach ($array as $row => $v) {
                    if ($row == 'cartodb_id') {
                        $this->id = $v;
                    }
					$this->$row = $v;
	            }
			} else {
				$this->cartodb_id = $array;
			}
		}
	}
	
	/**
	* Gets building type info
	* 
	* @return mixed (array/false)
	*/
	public function getTypeInfo () {
		
		// prevent multiple cache calls
		if (empty(self::$typeInfo)) {
			$helper = new BuildingHelper();
			self::$typeInfo = $helper->get($this->type, false, true);
		}
		return self::$typeInfo;
	}
	
	/**
	 * Checks if a building is purchasable
	 *
	 * @return boolean
	 */
	public function isPurchasable () {
		
		$info = $this->getTypeInfo();

		if ($info->purchase) {
			return true;
		}
		return false;
	}
	
	/**
	 * Checks if a building is public (cant have owner)
	 *
	 * @return boolean
	 */
	public function isPublic () {
		
		$info = $this->getTypeInfo();
		
		if ($info->public) {
			return true;
		}
		return false;
	}

    /**
     * Checks if building is owned by somebody
     *
     * @return boolean
     */
	public function hasOwner () {
		if (!empty($this->owner)) {
			return true;
		}
		return false;
	}

    /*
     * starts a production and updates used storage space
     *
     * @param integer $quantity
     * @param integer $item
     *
     * @return mixed
     * */
    public function addProduction ($quantity, $item = null) {

        if ($this->used_storage == $this->storage) {
            return false;
        }

        $info = $this->getTypeInfo();

        if (empty($item) && $info->type != BuildingHelper::TYPE_MINE) {
                throw new Exception('item to produce not specified');
        }

        $this->used_storage += floor($quantity);

        if ($this->used_storage > $this->storage) {
            $quantity -= $this->used_storage - $this->storage;
            $this->used_storage = $this->storage;
        } else if ($this->used_storage < 1) {
            return false;
        }

        if ($info->type != BuildingHelper::TYPE_MINE) {
            $production = new BuildingProduction(array(
                'building' => $this->id,
                'item' => $item,
                'quantity' => $quantity,
            ));

            return $production->saveNew();
        } else {
            return true;
        }
    }

    /**
     * Upgrades building level and sets delay time
     *
     * @return mixed
     */
	public function upgrade () {
		$this->level++;
		
		$info = $this->getTypeInfo();
		
		$this->setDelayTime($info->upgrade_time * $this->level * 100);
		
		return $this->save();
	}
	
	/**
	* Sets a delay time for current building
	* @param integer $seconds
	* 
	* @return void
	*/
	public function setDelayTime ($seconds) {
		$date = new DateTime();
		$date->modify("+".$seconds." seconds");
		$this->delay_time = date('Y-m-d H:i:s', $date->getTimestamp());
		$this->status = Building::STATUS_BUSY;
	}
	
	/**
	* Building info cache key
	* @param integer $id
	* 
	* @return string
	*/
	private function getKey ($id) {
		return "buildingInfo-".$id;
	}
	
	/**
	* Checks if current building is under construction or leveling something
	* 
	* @return boolean
	*/
	public function isBusy () {
		if ($this->status == self::STATUS_OK) {
			return false;
		} elseif ($this->status == self::STATUS_BUSY) {
			$interval = Util::dateDiff($this->delay_time, date('Y-m-d H:i:s'));

			if ($interval->invert == 0) {
				$this->status = self::STATUS_OK;
				$this->save();
				return false;
			} else {
				return true;
			}
		}
	}

	public function find ($fcv_array = array(), $sortBy = '', $ascending = true, $limit = '')
	{
		$this->connect();
        $thisObjectName = get_class($this);
        $sqlLimit = ($limit != '' ? "LIMIT $limit" : '');
        // i should include ST_AsGeoJSON(the_geom_webmercator)
        $q = "select * from ".$this->table." ";

        $resultList = array();
        if (sizeof($fcv_array) > 0)
        {
                $q .= " where ";
                for ($i = 0, $c = sizeof($fcv_array); $i<$c; $i++)
                {
                        if (sizeof($fcv_array[$i]) == 1) {
                                $q .= " ".$fcv_array[$i][0]." ";
                                continue;
                        }
                        else
                        {
                            if ($i > 0 && sizeof($fcv_array[$i-1]) != 1) {
                                $q .= " AND ";
                            }

                            if (strpos($fcv_array[$i][0],' ') !== false) {
                                $delimiter = '';
                            } else {
								$delimiter = '';
							}

							if (strpos($fcv_array[$i][2],'(') !== false) {
								$vDelimiter = '';
							} else {
								$vDelimiter = "'";
							}

							$q .= $delimiter.$fcv_array[$i][0].$delimiter." ".$fcv_array[$i][1]." ".$vDelimiter.$fcv_array[$i][2].$vDelimiter;
                        }
                }
        }
		if ($sortBy != '')
		{
		        $sortBy = "$sortBy ";
		        $q .= " order by ".$sortBy." ".($ascending ? "asc" : "desc")." ";
		}
        $q .= $sqlLimit;
		$list = $this->cartodb->runSql($q);
		
		if (empty($list['return']) || empty($list['return']['rows'])) {
			return false;
		}

        foreach ($list['return']['rows'] as $row)
        {
                $user = new $thisObjectName();

                foreach($this->attributes as $attr)
                {
                        $k = $attr[0];
                        if ($k == 'cartodb_id') {
                            $user->id = $row->{$k};
                        }
                        $user->$k = $row->{$k};
                }
                $resultList[] = $user;
        }
        return $resultList;
	}

	public function get($id) {
		
		$key = $this->getKey($id);
		$data = Cache::get($key);
		
		if (empty($data)) {
			
			$this->connect();
			$data = $this->cartodb->getRow($this->table, $id);

			if (empty($data)) {
				return false;
			}
			
			$data = $data['return'];
			
			Cache::set($key, $data, 60*60*24*7);
		}

		return new Building($data);
	}

	public function save() {
		
		$key = $this->getKey($this->cartodb_id);
		$this->connect();
		
		$dataToSave = array();

		foreach ($this->attributes as $attr) {
			$k = $attr[0];
			$dataToSave[$k] = $this->$k;
		}
		$result = $this->cartodb->updateRow($this->table, $this->cartodb_id, $dataToSave);
		
		// update necessary caches
		Cache::del($key);
		Cache::del(cBuilding::getByUidKey($this->owner));

		if (isset($result['return'])) {
			return true;
		} else {
			return false;
		}
	}
	public function getRaw () {
		$data = array();

        foreach ($this->attributes as $attr)
        {
        	$k = $attr[0];

			$data[$k] = $this->$k;
        }
        return $data;
	}
	public function getArrayView ($array) {
		$list = array();
		$cBuilding = new cBuilding();
			
		foreach ($array as $obj) {
			$list[] = $cBuilding->parse($obj);
		}
		return $list;
	}
	public function connect () {
		$this->cartodb =  new CartoDBClient(Config::get('cartodb'));

		if (!$this->cartodb->authorized) {
		        throw new Exception(ApiException::DB_DOWN);
		}
	}
}
