<?php

namespace app\helpers;

abstract class MapBoundary{

    protected $type;

    public function getType(){
        return $this->type;
    }
}