<?php

class BuildingProduction extends BasicClass
{
    public $table = 'building_production';
    public $attributes = array
    (
        array('id', 'integer'),
        array('building', 'integer'),
        array('item', 'integer'),
        array('quantity', 'integer'),
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

    public function get ($id, $rawResult = false, $cacheable = false) {
        $where = array('building', '=', $id);
        $r = $this->find($where);
        return $r[0];
    }

    public function saveNew () {
        $where = array(
            array('building', '=', $this->building),
            array('item', '=', $this->item),
        );
        $r = $this->find($where);

        if (!$r) {
            return parent::saveNew();
        } else {
            $r[0]->quantity += $this->quantity;
            return $r[0]->save();
        }
    }
}