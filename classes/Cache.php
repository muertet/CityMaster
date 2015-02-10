<?php

class Cache
{
	const WEEK = 604800;
	const DAY = 86400;
	const HOUR = 3600;
	/**
	* Recovers data from cache
	* @param string $key
	* 
	* @return mixed
	*/
	public static function get ($key)
	{
		$file = Config::get('cache').md5($key);
		
		if (!file_exists($file)) {
			return null;
		}
            
        $obj = unserialize(file_get_contents($file));
        
        if ($obj->expires < time()) {
            @unlink($file);
            return null;
        }
        
        return $obj->contents;
	}
	
	/**
	* Caches data during a certain time
	* @param string $key
	* @param mixed $content
	* @param integer $seconds cache duration in seconds
	* 
	* @return boolean
	*/
	public static function set ($key, $content, $seconds = 21600)
	{
		$file = Config::get('cache').md5($key);
		
		$obj = new stdClass();
        $obj->expires = time() + $seconds;
        $obj->contents = $content;
        
        file_put_contents ($file, serialize($obj));
	}
	
	public static function del ($key) {
		self::delete($key);
	}
	
	/**
	* Deletes a key from cache
	* @param string $key
	* 
	* @return void
	*/
	public static function delete ($key) {
		$file = Config::get('cache').md5($key);
        @unlink($file);
    }
}