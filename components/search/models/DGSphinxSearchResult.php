<?php

namespace app\components\search\models;

use Yii;
use stdClass;

class DGSphinxSearchResult
{
    public $enableProfiling = false;
    public $enableResultTrace = false;
    private $data = array();
    private $criteria;

    public function __construct(array $data, $criteria)
    {
        $this->criteria = $criteria;
        $ar = array();

        if (isset($data['matches'])) {
            foreach ($data['matches'] as $id => $match_data) {
                $resData = new stdClass();
                if (isset($match_data['attrs'])) {
                    foreach ($match_data['attrs'] as $key => $value) {
                        $resData->$key = $value;
                    }
                }
                $resData->id = $id;
                $resData->_weight = $match_data['weight'] ?? 0;

                $ar[$id] = $resData;
            }
        }

        $this->setIdList($ar);
    }

    private function getIdListByParam(array $list, $param, $isReturnEmpty = true)
    {
        $ret = array();
        foreach ($list as $id => $item) {
            if (isset($item->$param)) {
                if ($isReturnEmpty || $item->$param) {
                    $ret[$id] = $item->$param;
                }
            }
        }
        return array_keys(array_flip($ret));
    }

    private function getPageFromData(array $list)
    {
        if (isset($this->getCriteria()->paginator)) {
            $pageSize = $this->getCriteria()->paginator->pageSize;
            $offset = $this->getCriteria()->paginator->offset;
        } else {
            $pageSize = count($list);
            $offset = 0;
        }
        return array_slice($list, $offset, $pageSize, true);
    }

    public function setIdList($list)
    {
        $this->data = $list;
        if (isset($this->getCriteria()->paginator)) {
            $this->getCriteria()->paginator->totalCount = count($list);
        };
    }

    public function getIdList()
    {
        return array_keys($this->data);
    }

    public function getIdPage()
    {
        return array_keys($this->getPageFromData($this->data));
    }

    public function getParamIdList($param, $isReturnEmpty = true)
    {
        return $this->getIdListByParam($this->data, $param, $isReturnEmpty);
    }

    public function getParamIdPage($param)
    {
        return $this->getIdListByParam($this->getPageFromData($this->data), $param);
    }

    public function filterByParamIds($param, array $ids)
    {
        if (count($ids) == 0) {
            $this->setIdList(array());
            return false;
        }
        $ret = array();
        foreach ($this->data as $id => $item) {
            if (isset($item->$param)) {
                if (in_array($item->$param, $ids)) {
                    $ret[$id] = $item;
                }
            }
        }
        $this->setIdList($ret);
    }

    public function getTotal()
    {
        return count($this->data);
    }

    public function getCriteria()
    {
        return $this->criteria;
    }

    public function getPaginator()
    {
        $criteria = $this->getCriteria();
        if(isset($criteria->paginator))
            return $criteria->paginator;
    }

    public function getRawData()
    {
        return $this->data;
    }

    public function setRawData(array $data)
    {
        return $this->data = $data;
    }

    public function __toString()
    {
        return join(',', $this->getIdPage());
    }
}
