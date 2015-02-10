<?php

class BuildingHelper extends BasicClass
{
	public $table = 'building';
	public $attributes = array
	(
		array('id', 'integer'),
		array('public', 'integer'),
		array('type', 'integer'),
		array('build_delay', 'integer'),
		array('upgrade_delay', 'integer'),
		array('storage', 'integer'),
		array('purchase', 'integer'),
		array('item', 'integer'),
	);
	
	const TYPE_MINE = 1;
	const TYPE_FACTORY = 2;
	const TYPE_WAREHOUSE = 3;

	const TANK_FACTORY = 7;
	const WAREHOUSE = 11;
	const GARAGE = 12;

	private static $translate = array(
		1 => array(
			'name' => 'Torre petrolífera',
			'description' => 'Extrae petróleo del suelo.',
		),
		2 => array(
			'name' => 'Mina de hierro',
			'description' => 'Extrae hierro de la tierra.',
		),
		3 => array(
			'name' => 'Plantación de goma',
			'description' => 'Necesaria para la fabricación de neumáticos.',
		),
		4 => array(
			'name' => 'Mina de salitre',
			'description' => 'sssddddddd',
		),
		5 => array(
			'name' => 'Mina de aluminio',
			'description' => 'Produce aluminio',
		),
		6 => array(
			'name' => 'Mina de oro',
			'description' => 'Extrae oro en pequeñas cantidades',
		),
		7 => array(
			'name' => 'Fábrica de tanques',
			'description' => 'Ensambla todo tipo de tanques',
		),
		8 => array(
			'name' => 'Fábrica de aviones',
			'description' => 'Ensambla todo tipo de aviones',
		),
		9 => array(
			'name' => 'Fábrica de vehículos',
			'description' => 'Ensambla todo tipo de vehículos ligeros',
		),
		10 => array(
			'name' => 'Cuartel militar',
			'description' => 'Entrena a soldados para distintos propósitos',
		),
		11 => array(
			'name' => 'Almacén',
			'description' => 'Nave industrial en la que puedes almacenar objetos',
		),
		12 => array(
			'name' => 'Garaje',
			'description' => 'Lugar donde construir y reparar vehículos.',
		),
	);
	
	
	public function getView () {

		$raw = $this->getRaw();
		
		switch ($this->id) {
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
				$color = "grey";
			break;
			case 6:
				$color = "yellow";
			break;
			case 7:
			case 8:
			case 9:
			case 10:
				$color = "blue";
			break;
			case 11:
			case 12:
				$color = "brown";
			break;
			default:
				$color = "purple";
			break;
			
		}
		$raw['css'] = "building-fill:".$color.";";
		$raw['name'] = self::$translate[$this->id]['name'];
		$raw['description'] = self::$translate[$this->id]['description'];
		
		return $raw;
	}
	
	public function getArrayView ($array) {
		$list = array();
			
		foreach ($array as $obj) {
			$list[] = $obj->getView();
		}
		return $list;
	}
}