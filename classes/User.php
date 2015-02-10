<?php

class User extends BasicClass
{
	private $hash = '4g4#r.gt';
	private $cookieHash = 'K4OTGdf';
	public $attributes = array
	(
		array('id','string'),
		array('nick','string'),
		array('email','string'),
		array('date','string'),
		array('status','string'),
		array('password','string'),
		array('image','integer'),
		array('money','integer'),
		array('gold','integer'),
		array('referrer','integer'),
		array('lat','string'),
		array('lng','string'),
		array('country','string'),
		array('lastAccess','date'),
	);
	const STATUS_PENDING = 0;
	const STATUS_OK = 1;
	const STATUS_BANNED = 2;
	
	public function __construct($array=null)
	{
		parent::__construct();
		
		if ($array != null) {
			if (is_array($array)) {
				foreach ($array as $row => $v) {
					$this->$row = $v;
	            }
			} else {
				$this->id = $array;
			}
		}
	}
	
	
	/**
	* Checks if user has a warehouse to store items
	* 
	* @return boolean
	*/
	public function hasWarehouse () {
		
		$class = new cBuilding(array('uid' => $this->id));
		$list = $class->getByUid();
		
		if (!$list) {
			return false;
		}
		
		foreach ($list as $building) {
			if ($building['type'] == BuildingHelper::WAREHOUSE) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	* Adds or deducts money
	* @param integer $amount
	* 
	* @return boolean
	*/
	public function setMoney ($amount, $reason = '', $extra = '') {
		$this->money += floor($amount);

        if ($this->money < 0) {
            return false;
        }

		$charged = $this->save();
		
		/*
		if ($charged) {
			// @ToDo record transaction
		}
		*/
		return $charged;
	}
	
	/**
	* Adds or deducts gold
	* @param integer $amount
	* 
	* @return boolean
	*/
	public function setGold ($amount, $reason = '', $extra = '') {
		$this->gold += floor($amount);

        if ($this->gold < 0) {
            return false;
        }

		$charged = $this->save();
		
		/*
		if ($charged) {
			// @ToDo record transaction
		}
		*/
		return $charged;
	}
	
	public function isBoss() {
		return $this->boss;
	}
	
	public function getCookieHash(){
		return $this->cookieHash;
	}
	
	public function login($email,$password,$isEncoded=false)
	{
		if(empty($email) || empty($password)){
			throw new Exception('missing login info');
		}

		if(!$isEncoded){
			$password=md5($this->hash.$password);
		}

		$where=array(
			array('email','=',$email),
			array('password','=',$password),
			array('status','=',User::STATUS_OK)
			);
		$results=$this->find($where);

		if(sizeof($results)>0) {
			return $results[0];
		}else{
			return false;
		}
	}
	public function verify($code)
	{
		if($code=='' || $code=='1' || $code=='0' || strlen($code)<31 ){
			return false;
		}

		$uid=substr($code,30);

		$r=Service::getDB()->query("SELECT id FROM user WHERE id='".$uid."'");
		if(sizeof($r)>0)
		{
			Service::getDB()->where('id',$uid);
			Service::getDB()->update("user",array('status'=>User::STATUS_OK));
			return true;
		}else{
			return false;
		}
	}
	private function encryptPassword($password) {
		return md5($this->hash.$password);
	}
	private function generatePassword($length = 8)
	{
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
	    $pass = array(); 
	    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	    for ($i = 0; $i < $length; $i++) {
	        $n = rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }
	    return implode($pass); 
	}
	public function saveNew()
	{
		$this->status = self::STATUS_PENDING;
		$this->gold = 0;
		$this->money = 3500;
		$password = $this->password;
		$this->password = $this->encryptPassword($this->password);
		$verificationCode = $this->generatePassword(30);
		$this->lastAccess = date('Y-m-d H:i:s');

		if (empty($this->image)) {
			$this->image = 1;
		}

		if (empty($this->referrer)) {
			$this->referrer = null;
		}
		
		$q = "select `id` from `".$this->table."` where `email`='".$this->email."' LIMIT 1";
		$rows = Service::getDB()->query($q);
		
		if(sizeof($rows)>0){
			return false;
		}
		$r = parent::saveNew();
	
		if($r)
		{
			$verificationCode = $verificationCode.$r;
			$verificationLink = "http://".Config::get('domain').Config::get('basedir')."verify.php?code=".$verificationCode;
	
			Util::mail($this->email, 'CityMaster - Información importante', 'register', array (
				'activation_link' => $verificationLink,
				'email' => $this->email,
				'password' => $password,
			));
		}
		return $r;
	}
}
