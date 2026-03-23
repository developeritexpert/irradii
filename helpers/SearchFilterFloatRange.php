<?php

namespace app\helpers;

class SearchFilterFloatRange extends SearchRangeBased{


    public function setFilter($search, $attribute){

        if($this->getMin() == null && $this->getMax() == null)
            return;

        if($this->getMin() != null && $this->getMax() != null){
            $search->setFilterFloatRange($attribute, floatval($this->getMin()), floatval($this->getMax()));
            return;
        }

        if($this->getMin() == null && $this->getMax() != null){
            $search->setFilterFloatRange($attribute, 0.0, floatval($this->getMax()));
            return;
        }

        if($this->getMin() != null && $this->getMax() == null){
            $search->setFilterFloatRange($attribute, floatval($this->getMin()), 9999999999999.99);
            return;
        }
    }
}