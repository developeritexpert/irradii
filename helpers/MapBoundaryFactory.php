<?php

namespace app\helpers;

class MapBoundaryFactory {

    /**
     * @param $type
     * @param $params
     * @return SearchCriteria
     */
    public function create($params){

        if(isset($params['latitude']) && isset($params['longitude']) && isset($params['radius'])){

            $center = new MapPoint($params['latitude'], $params['longitude']);
            $radius = floatval($params['radius']);
            return new MapBoundaryCircle($center, $radius);

        }elseif(isset($params['latitude1']) && isset($params['longitude1']) && isset($params['latitude2']) && isset($params['longitude2'])){

            $leftTop = new MapPoint($params['latitude1'], $params['longitude1']);
            $rightBottom = new MapPoint($params['latitude2'], $params['longitude2']);
            return new MapBoundaryRectangle($leftTop, $rightBottom);

        }elseif(isset($params['latitude']) &&
            isset($params['longitude']) &&
            is_array($params['latitude']) &&
            is_array($params['longitude']) &&
            (count($params['latitude']) == count($params['longitude']) &&
            count($params['latitude']) >= 3) // 3 - is the minimum points for polygon
        ){

            $mapPoints = array();

            for($i = 0; $i < count($params['latitude']); $i++){
                $mapPoints[$i] = new MapPoint($params['latitude'][$i], $params['longitude'][$i]);
            }

            return new MapBoundaryPolygon($mapPoints);
        }else{

            return new MapBoundaryEmpty();
            //throw new InvalidParamException('Invalid params when creating Map Boundary: "'.json_encode($params).'"');
        }
    }
} 