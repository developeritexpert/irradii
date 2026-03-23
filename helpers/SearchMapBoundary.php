<?php

namespace app\helpers;

abstract class SearchMapBoundary extends SearchCriteria{

    protected $mapBoundary;

    public function __construct(MapBoundary $mapBoundary){
        $this->mapBoundary = $mapBoundary;
    }

    abstract public function setFilter($search, $lat_attr, $lon_attr);

    public function getMapBoundary(){
        return $this->mapBoundary;
    }
}