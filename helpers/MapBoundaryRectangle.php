<?php

namespace app\helpers;

class MapBoundaryRectangle extends MapBoundary {

    protected $type='rectangle';

    private $leftTopPoint;
    private $rightBottomPoint;

    public function __construct(MapPoint $leftTop, MapPoint $rightBottom){

        $this->leftTopPoint = $leftTop;
        $this->rightBottomPoint = $rightBottom;
    }

    public function getLeftTopPoint(){
        return $this->leftTopPoint;
    }

    public function getRightBottomPoint(){
        return $this->rightBottomPoint;
    }
} 