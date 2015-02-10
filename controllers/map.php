<?php

class cMap extends Controller
{
	public function get() {
		
		$url = str_replace('/api/maps', '', "http://0.ashbu.cartocdn.com".$_SERVER['REQUEST_URI']);
		preg_match("/\/([0-9]+)\/([0-9]+)\.grid\.json/",$_SERVER['REQUEST_URI'],$matches);
		
		if (!isset($matches[1])) { // we are loading an image
			$content = Util::curl($url);
			
			header ('Content-Type: image/png');
			die($content);
		} else {
			$key = $matches[0];
			
			$obj = Cache::get($key);
			
			if (!$obj) {
				$content = Util::curl($url);
				
				preg_match("/grid\((.*)?\)\;/",$content,$matches);
				if (!isset($matches[1])) {
					throw new Exception('invalid carto query');
				}
				$obj = json_decode($matches[1]);
				
				Cache::set($key, $obj, Cache::WEEK);
			}
			
			/*if (!empty((array)$obj->data)) {
				
				foreach ($obj->data as $k => $v) {
					$building = new Building($obj->data->{$k});
			
					$cBuilding = new cBuilding();
					$obj->data->{$k} = $cBuilding->parse($building);
				}
			}*/
			
			die('grid('.json_encode($obj).');');
		}
	}
}
