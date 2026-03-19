<?php

namespace app\helpers;

class SearchMapBoundaryPolygon extends SearchMapBoundary{


    public function setFilter($search, $lat_attr, $lon_attr){

        $mapBoundary = $this->getMapBoundary();

        $points = $mapBoundary->getPoints();

        $coordinates_arr = array();
        foreach($points as $point){
            $coordinates_arr[] = deg2rad($point->getLatitude());
            $coordinates_arr[] = deg2rad($point->getLongitude());
        }

        $coordinates_str = implode(',', $coordinates_arr);

        $search->SetSelect("CONTAINS(GEOPOLY2D({$coordinates_str}),$lat_attr,$lon_attr) as is_inside");
        $search->setFilter('is_inside', array(1));

    }
} 