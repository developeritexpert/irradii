<?php

namespace app\helpers;

class SearchMapBoundaryRectangle extends SearchMapBoundary{


    public function setFilter($search, $lat_attr, $lon_attr){

        $mapBoundary = $this->getMapBoundary();

        $leftTopPoint = $mapBoundary->getLeftTopPoint();
        $rightBottomPoint = $mapBoundary->getRightBottomPoint();

        $lat1 = (float) deg2rad($leftTopPoint->getLatitude());
        $lon1 = (float) deg2rad($leftTopPoint->getLongitude());
        $lat2 = (float) deg2rad($rightBottomPoint->getLatitude());
        $lon2 = (float) deg2rad($rightBottomPoint->getLongitude());

        if (( $lat1 != 0.00) && ( $lon1 != 0.00 ) && ( $lat2 != 0.00 ) && ( $lon2 != 0.00 )) {
            $min_lat = $lat1 < $lat2 ? $lat1 : $lat2;
            $max_lat = $lat1 < $lat2 ? $lat2 : $lat1;
            $min_lon = $lon1 < $lon2 ? $lon1 : $lon2;
            $max_lon = $lon1 < $lon2 ? $lon2 : $lon1;

            $search->SetFilterFloatRange($lat_attr, (float) $min_lat, (float) $max_lat);
            $search->SetFilterFloatRange($lon_attr, (float) $min_lon, (float) $max_lon);
        }

    }
} 