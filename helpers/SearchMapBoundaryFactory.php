<?php

namespace app\helpers;

class SearchMapBoundaryFactory {

    /**
     * @param $type
     * @param $params
     * @return SearchMapBoundary
     */
    public function create(MapBoundary $mapBoundary){
        switch($mapBoundary->getType()){

            case 'empty':
                return new SearchMapBoundaryEmpty($mapBoundary);
                break;

            case 'circle':
                return new SearchMapBoundaryCircle($mapBoundary);
                break;

            case 'rectangle':
                return new SearchMapBoundaryRectangle($mapBoundary);
                break;

            case 'polygon':
                return new SearchMapBoundaryPolygon($mapBoundary);
                break;

            default:
                throw new UnknownSearchCriteriaException('Unknown search criteria type: "'.json_encode($mapBoundary).'"');
                break;
        }
    }
} 