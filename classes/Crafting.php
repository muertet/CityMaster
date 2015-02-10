<?php

class Crafting extends BasicClass {
	public static $craftingRecipes = array(
		BuildingHelper::GARAGE => array(
			array(
				'ingredients' => array(
					1 => 1,
					2 => 1,
					3 => 4,
				),
				'result' => 4
			),
			array(
				'ingredients' => array(
					1 => 1,
					2 => 1,
					3 => 4,
					6 => 6,
				),
				'result' => 5
			),
		),
		BuildingHelper::TANK_FACTORY => array(
			array(
				'ingredients' => array(
					1 => 1,
					2 => 1,
				),
				'result' => 7
			),
			array(
				'ingredients' => array(
					1 => 1,
					2 => 1,
				),
				'result' => 8
			),
		),
	);
	
	public function __construct($array = null)
	{
        if ($array != null) {
            foreach ($array as $row => $v) {
                    $this->$row = $v;
            }
        }
	}
	
	public function getByResult ($id) {
		$crafting = new Crafting();
		$recipes = $crafting->getList();
		
		foreach ($recipes as $recipe) {
			if ($recipe->result == $id) {
				return $recipe;
				break;
			}
		}
		return false;
	}
	
	public function getList () {
		
		$list = array();
		
		foreach (self::$craftingRecipes as $building) {
			foreach ($building as $recipe) {
				$list[] = new Crafting($recipe);
			}
		}
		
		return $list;
	}
	
	public function getView () {
		
		$iClass = new Item();
		$recipe = array(
			'ingredients' => array()
		);
		
		foreach ($this->ingredients as $item => $quantity) {
			$item = $iClass->get($item);
			
			$recipe['ingredients'][] = array(
				'item' => $item->getView(),
				'quantity' => $quantity,
			);
		}
		
		$item = $iClass->get($this->result);
		$recipe['result'] = array(
				'item' => $item->getView(),
				'quantity' => 1,
			);
		
		return $recipe;
	}
	
	public function getByBuilding ($id) {
		return self::$craftingRecipes[$id];
	}
}