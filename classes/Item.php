<?php

class Item extends BasicClass {
	public $attributes = array
	(
        array('id','integer'),
        array('attack','integer'),
        array('defense','integer'),
        array('salable','integer'),
        array('vehicle','integer'),
        array('building','integer'),
        array('date','date'),
	);
	
	private $basicInfo = array(
		1 => array(
			'name' => "Chásis",
			'description' => "Estructura básica de un vehículo",
		),
		2 => array(
			'name' => "Motor básico",
			'description' => "No es muy rápido pero servirá para vehículos simples.",
		),
		3 => array(
			'name' => "Rueda de coche",
			'description' => "Apta para vehículos pequeños o jeeps.",
		),
		4 => array(
			'name' => "Jeep",
			'description' => "Transporte de mecarderías o personas.",
		),
		5 => array(
			'name' => "Jeep blindado",
			'description' => "Vehículo seguro para el transporte de personas.",
		),
		6 => array(
			'name' => "Cristal antibalas",
			'description' => "Refuerza la seguridad de distintos vehículos",
		),
		7 => array(
			'name' => "Tanque panzer",
			'description' => "Refuerza la seguridad de distintos vehículos",
		),
		8 => array(
			'name' => "Tanque kuza",
			'description' => "Refuerza la seguridad de distintos vehículos",
		),
	);

	public function __construct($array = null)
	{
        parent::__construct();

        if ($array != null) {
            foreach ($array as $row => $v) {
                    $this->$row = $v;
            }
        }
	}

	public function getView () {
		$raw = $this->getRaw();
		
		$raw = array_merge($raw, $this->basicInfo[$this->id]);
		
		$raw['image'] = 'http://'.Config::get('domain').'/assets/images/items/'.$this->id.'.jpg';
		
		unset($raw['date']);
		
		return $raw;
	}
}
