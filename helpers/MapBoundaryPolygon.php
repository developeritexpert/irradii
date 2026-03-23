<?php

namespace app\helpers;

class MapBoundaryPolygon extends MapBoundary{

    protected $type='polygon';

    protected $points;

    public function __construct(Array $points){

        $this->points = $points;
    }

    public function getPoints(){
        return $this->points;
    }
}