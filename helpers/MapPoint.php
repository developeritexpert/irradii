<?php

namespace app\helpers;

class MapPoint {

    private $latitude;
    private $longitude;

    public function __construct($latitude, $longitude){

        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function getStringPresentation($separator = ', '){
        return $this->getLatitude().$separator.$this->getLongitude();
    }

    public function getLatitude(){
        return $this->latitude;
    }

    public function getLongitude(){
        return $this->longitude;
    }
}