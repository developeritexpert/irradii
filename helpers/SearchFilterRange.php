<?php

namespace app\helpers;

class SearchFilterRange extends SearchRangeBased{


    public function setFilter($search, $attribute){

        if($this->getMin() == null && $this->getMax() == null)
            return;

        if($this->getMin() != null && $this->getMax() != null){
            $search->setFilterRange($attribute, intval($this->getMin()), intval($this->getMax()));
            return;
        }

        if($this->getMin() == null && $this->getMax() != null){
            $search->setFilterRange($attribute, 0, intval($this->getMax()));
            return;
        }

        if($this->getMin() != null && $this->getMax() == null){
            $search->setFilterRange($attribute, intval($this->getMin()), PHP_INT_MAX);
            return;
        }
    }
}