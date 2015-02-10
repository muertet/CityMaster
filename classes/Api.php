<?php
/**
* cutreAPI
*
*/
class Api
{
	private $authToken;
	private $userToken;


	public static $caller = false;
	public static $method = false;
	public static $class = false;
	private $controlerFolder = '';

	public function __construct($authToken = false, $userToken = false)
	{
		$this->controlerFolder = Config::get('root').'/controllers/';

		if (!empty($authToken)) {
			$this->authToken = $authToken;
		}
		if (!empty($userToken)) {
			$this->userToken = $userToken;
		}
	}

	/*
	* Checks if user has the required permissions for current call
	* @param boolean $boss is Boss or not
	*
	* @return void
	*/
	public function checkPermissions($boss = false) {
		if ($boss && !$this->user->isBoss()) {
			die('tu p*** madre');
		}
	}

	/**
	 * Verifies call requirements like, auth & user token or if is for official app only
	 * @param boolean $authToken
	 * @param boolean $userToken
	 * @param boolean $mustBeOfficial
	 *
	 * @return mixed (boolean/void)
	*/
	public function checkSecurity($authToken = true, $userToken = false, $mustBeOfficial = false)
	{
		if($authToken)
		{
			if (empty($this->authToken)) {
				return $this->replyError('Missing authToken');
			}

			$tokenClass = new AuthToken();

			try{
				$app = $tokenClass->validate($this->authToken);
			} catch(Exception $e) {
				return $this->replyError('Invalid authToken');
			}

			$this->app = $app;

			if ($mustBeOfficial && !$this->app->isOfficial()) {
				return $this->replyError('Permission denied');
			}
		}
		if ($userToken && empty($this->userToken)) {
			return $this->replyError('Missing userToken');
		}

		if (!empty($this->userToken))
		{
			$token = new UserToken();
			$tokenData = $token->get($this->userToken);

			if (!$tokenData || $tokenData->app != $this->app->id) {
				return $this->replyError('Invalid userToken');
			}

			$userClass = new User();
			$user = $userClass->get($tokenData->uid);
			$this->user = $user;
		}
		return true;
	}

	/**
	* Calls required class and method
	* @param string $class Class name
	* @param string $method method name
	* @param array $args arguments for mentioned method
	*
	* @return string
	*/
	public function exec($class, $method)
	{
		if ($class == '' || $method == '') {
			return $this->replyError('missing required information');
		}

		$controllerFile=$this->controlerFolder.strtolower($class).'.php';

		if (!file_exists($controllerFile)) {
			return $this->replyError('Controller not found');
		}

		include_once($controllerFile);

		$controllerClassName = 'c'.$class;

		if (!method_exists($controllerClassName, $method)) {
			return $this->replyError('Method not found');
		}

		try
		{
			$controllerClass = new $controllerClassName($_REQUEST);

			// SET IMPORTANT VARS
			$controllerClass->app = $this->app;
			$controllerClass->user = $this->user;

			return $this->reply($controllerClass->$method());
		} catch(Exception $e) {
			return $this->replyError($e->getMessage());
		}
	}

	/**
	* Prepares a correct API response
	* @param array $data called method response
	*
	* @return string
	*/
	private function reply($data)
	{
		$array = array(
			'data' => $data,
			'status' => 1,
		);
		return json_encode($array);
	}
	/**
	* Prepares a wrong API response
	* @param string $message error message to return
	*
	* @return string
	*/
	public function replyError($message,$code=0)
	{
		$array = array(
			'data' => $message,
			'error_code' => $code,
			'status' => 0,
		);
		return json_encode($array);
	}
}
