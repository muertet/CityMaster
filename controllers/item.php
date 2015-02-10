<?php

class cItem extends Controller {
	public function getByBuilding () {
		$id = (int)$this->param('id');
		
		if ($id < 1) {
			throw new Exception(ApiException::MISSING_DATA);
		}
		
		$key = "itemBuildingList". $id;
		
		$list = Cache::get($key);
		
        if (!$list) {
		
			$where = array(
				array('building', 'IN', '('.$id.', 0)')
			);
			
			$item = new Item();
			$list = $item->find($where);
			
			if (!$list) {
				return false;
			}
			
			$list = $item->getArrayView($list);
			
			Cache::set($key, $list, Cache::WEEK);
		}
		
		return $list;
	}
}