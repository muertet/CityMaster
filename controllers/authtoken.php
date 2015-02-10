<?php

class cAuthToken extends Controller
{
	public function create()
	{
		$data=array(
			'id'=>$_POST['app'],
			'secret'=>$_POST['secret']
		);
		
		if(empty($data['id']) || empty($data['secret'])){
			throw new Exception('missing id or secret');
		}
		
		
		$app=new App();
		$appInfo=$app->get($data['id']);
		
		if($appInfo->secret!=$data['secret']){
			throw new Exception('invalid authentication');
		}
		
		if($appInfo->isBanned()){
			throw new Exception("This app can't create tokens");
		}
		
		$where = array(
			array('app','=',$data['id'])
		);
		
		$token = new AuthToken(array('app'=>$data['id']));
		$result = $token->find($where);
		
		if (sizeof($result) == 1) {
			return $result[0]->token;
		} else {
			return $token->save();
		}
		
    }
	
}