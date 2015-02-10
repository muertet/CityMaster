<?php

class ApiCaller
{
	private $app;
	private $secret;
	public $authToken;
	public $userToken;
	private $apiRoute;
	const STATUS_OK = 1;
	
	public function __construct($requireUserToken=false)
	{
		$this->app=Config::get('appId');
		$this->secret=Config::get('secret');
		$this->apiRoute='http://'.Config::get('domain').Config::get('basedir').'api/';
		
		$this->authToken = $this->getAuthToken();
		
		if($requireUserToken){
			$this->userToken = $this->getUserToken();
		}
	}
	
	public static function get($url,$data=array(),$userToken=false)
	{
		$call=new self($userToken);
		
		if(sizeof($data)==0){
			$method='GET';
		}else{
			$method='POST';
		}
		
		$data['authtoken']=$call->authToken;
		
		if($userToken){
			$data['usertoken']=$call->userToken;
		}
		
		return $call->curl($url,$method,$data);
	}
	
	private function getUserToken()
	{
		if(!isset($_SESSION['app']->userToken)){
			throw new Exception('missing userToken');
		}
		
		return $_SESSION['app']->userToken;
	}
	
	public function getAuthToken()
	{
		$key="currentAuthToken";
		
		if(isset($_SESSION['app']->authToken)){
			return $_SESSION['app']->authToken;
		}
		
		if( ($token=Cache::get($key)) ==false )
		{
			$post=array(
				'app'=>$this->app,
				'secret'=>$this->secret,
			);
			$response=$this->curl('authtoken/create','POST',$post);
			
			if($response->status!=1){
				Util::p($response);
				throw new Exception('could not generate authToken');
			}
			
			$token=$response->data;
			
			Cache::set($key,$token,60*60*2);
		}
		
		if (empty($_SESSION['app'])) {
			$_SESSION['app'] = new stdClass();
		}
		$_SESSION['app']->authToken=$token;
		
		return $token;
	}
	
	public function checkErrors($obj)
	{
		if($obj->status==1){
			return false;
		}
		
		switch($obj->data)
		{
			case 'Invalid authToken':
				unset($_SESSION['app']->authToken);
				$this->getAuthToken();
				
			break;
			case 'Invalid userToken':
				unset($_SESSION['user']);
			break;
			default:
				return false;
			break;
		}
		return true;
	}
	
	private function curl($url,$method, $data = array())
	{
		$url=$this->apiRoute.$url;
		
		$ch = curl_init($url);
		$header = array();
		$header[0]  = "Accept: text/xml,application/xml,application/xhtml+xml,";
		$header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$header[]   = "Cache-Control: max-age=0";
		$header[]   = "Connection: keep-alive";
		$header[]   = "Keep-Alive: 300";
		$header[]   = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$header[]   = "Accept-Language: en-us,en;q=0.5";
		$header[]   = "Pragma: "; // browsers keep this blank.

		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US; rv:1.8.1.7) Gecko/20070914 Firefox/2.0.0.7');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		$data=http_build_query($data);
		
		if($method=='POST'){
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}else{
			$url.='?'.$data;
		}

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_URL, $url);

		$json=curl_exec($ch);
		if($_SESSION['underControl']){Util::p($url,$data,$json);}
		
		$result=json_decode($json);
		
		$errors=$this->checkErrors($result);
		
		if(!$errors){
			return $result;
		}else{
			return $this->curl($url,$method, $data);
		}
		
		
	}
}
