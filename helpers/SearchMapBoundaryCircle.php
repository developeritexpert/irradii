<?php

namespace app\helpers;

class SearchMapBoundaryCircle extends SearchMapBoundary{


    public function setFilter($search, $lat_attr, $lon_attr){

        $center = $this->getMapBoundary()->getCenter();

        $search->SetGeoAnchor($lat_attr, $lon_attr, (float)deg2rad($center->getLatitude()), (float)deg2rad($center->getLongitude()));
        $search->SetFilterFloatRange('@geodist', 0.0, (float)(round($this->getMapBoundary()->getRadius(), 2)));
        $search->SetSortMode(SPH_SORT_EXTENDED, '@geodist ASC');
    }
}