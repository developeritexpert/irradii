<?php

namespace app\helpers;

class MapBoundaryCircle extends MapBoundary{

    protected $type='circle';

    private $center;
    private $radius;

    public function __construct(MapPoint $center, $radius){
        $this->center = $center;
        $this->radius = $radius;
    }

    public function getCenter(){
        return $this->center;
    }

    public function getRadius(){
        return $this->radius;
    }
}