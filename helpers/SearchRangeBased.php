<?php

namespace app\helpers;

class SearchRangeBased extends SearchCriteria{

    protected $min = null;
    protected $max = null;


    public function __construct(){

    }

    public function setMin($value){
        $this->min = $value;
    }

    public function setMax($value){
        $this->max = $value;
    }

    public function getMin(){
        return $this->min;
    }

    public function getMax(){
        return $this->max;
    }
}