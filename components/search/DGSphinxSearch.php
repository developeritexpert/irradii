<?php

namespace app\components\search;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use app\components\search\models\DGSphinxSearchResult;

require_once(__DIR__ . '/sphinxapi.php');

class DGSphinxSearchException extends Exception {}

class DGSphinxSearch extends Component
{
    public $server = 'localhost';
    public $port = 9312;
    public $matchMode = SPH_MATCH_EXTENDED;
    public $rankMode = SPH_RANK_SPH04;
    public $maxQueryTime = 3000;
    public $fieldWeights = array();
    public $enableProfiling = false;
    public $enableResultTrace = false;

    protected $criteria;
    protected $lastCriteria;
    private $client;

    public function init()
    {
        parent::init();

        $this->client = new \SphinxClient();
       
        $this->client->setServer($this->server, $this->port);
        $this->client->setMaxQueryTime($this->maxQueryTime);
         
        $this->resetCriteria();
    }

    public function query($query, $index='*', $comment='')
    {
        return $this->doSearch($index, $query, $comment);
    }

    public function search($criteria = null)
    {
        if ($criteria === null) {
            $res = $this->doSearch($this->criteria->from, $this->criteria->query);
        } else {
            $res = $this->searchByCriteria($criteria);
        }
        return $this->initIterator($res, $this->lastCriteria);
    }

    public function searchRaw($criteria = null)
    {
        if ($criteria === null) {
            $res = $this->doSearch($this->criteria->from, $this->criteria->query);
        } else {
            $res = $this->searchByCriteria($criteria);
        }
        return $res;
    }

    public function select($select)
    {
        $this->criteria->select = $select;
        $this->client->SetSelect($select);
        return $this;
    }

    public function from($index)
    {
        $this->criteria->from = $index;
        return $this;
    }

    public function where($query)
    {
        $this->criteria->query = $query;
        return $this;
    }

    public function filters($filters)
    {
        $this->criteria->filters = $filters;
        if ($filters && is_array($filters)) {
            foreach ($filters as $fil => $vol) {
                if ($fil == 'geo') {
                    $min = (float) (isset($vol['min']) ? $vol['min'] : 0);
                    $point = explode(' ', str_replace('POINT(', '', trim($vol['point'], ')')));
                    $this->client->setGeoAnchor('latitude', 'longitude', (float) $point[1] * ( pi() / 180 ), (float) $point[0] * ( pi() / 180 ));
                    $this->client->setFilterFloatRange('@geodist', $min, (float) $vol['buffer']);
                } else if ($vol) {
                    $this->client->SetFilter($fil, (is_array($vol)) ? $vol : array($vol));
                }
            }
        }
        return $this;
    }

    public function groupby($groupby = null)
    {
        $this->criteria->groupby = $groupby;
        if ($groupby && is_array($groupby)) {
            $this->client->setGroupBy($groupby['field'], $groupby['mode'], $groupby['order']);
        }
        return $this;
    }

    public function orderby($orders = null)
    {
        $this->criteria->orders = $orders;
        // In Yii1 this took DGSort, in Yii2 it might be different but we keep the logic
        if ($orders && method_exists($orders, 'getOrderBy') && $orders->getOrderBy()) {
            $this->client->SetSortMode(SPH_SORT_EXTENDED, $orders->getOrderBy());
        } elseif (is_string($orders)) {
             $this->client->SetSortMode(SPH_SORT_EXTENDED, $orders);
        }
        return $this;
    }

    public function limit($offset=null, $limit=null)
    {
        $this->criteria->limit = array(
            'offset' => $offset,
            'limit' => $limit
        );
        if (isset($offset) && isset($limit)) {
            $this->client->setLimits($offset, $limit);
        }
        return $this;
    }

    public function getLastError()
    {
        return $this->client->getLastError();
    }

    public function resetCriteria()
    {
        if (is_object($this->criteria)) {
            $this->lastCriteria = clone($this->criteria);
        } else {
            $this->lastCriteria = new \stdClass();
        }
        $this->criteria = new \stdClass();
        $this->criteria->query = '';
        $this->client->resetFilters();
        $this->client->resetGroupBy();
        $this->client->setArrayResult(false);
        $this->client->setMatchMode($this->matchMode);
        $this->client->setRankingMode($this->rankMode);
        $this->client->setSortMode(SPH_SORT_RELEVANCE, '@relevance DESC');
        $this->client->setLimits(0, 1000, 1000);
        if (!empty($this->fieldWeights)) {
            $this->client->setFieldWeights($this->fieldWeights);
        }
    }

    public function setCriteria($criteria)
    {
        if (!is_object($criteria)) {
            throw new DGSphinxSearchException('Criteria does not set.');
        }
        if (isset($criteria->paginator)) {
            $this->limit($criteria->paginator->getOffset(), $criteria->paginator->getLimit());
            $this->criteria->paginator = $criteria->paginator;
        }

        if (isset($criteria->select)) {
            $this->select($criteria->select);
        }
        if (isset($criteria->from)) {
            $this->from($criteria->from);
        }
        if (isset($criteria->query)) {
            $this->where($criteria->query);
        }
        if (isset($criteria->groupby)) {
            $this->groupby($criteria->groupby);
        }
        if (isset($criteria->filters)) {
            $this->filters($criteria->filters);
        }
        if (isset($criteria->orders) && $criteria->orders) {
            $this->orderby($criteria->orders);
        }
    }

    public function getCriteria()
    {
        return $this->criteria;
    }

    public function __call($name, $parameters)
    {
        if (method_exists($this->client, $name)) {
            $res = call_user_func_array(array($this->client, $name), $parameters);
            
            if (strtolower(substr($name, 0, 3)) === 'set' || strtolower(substr($name, 0, 5)) === 'reset') {
                return $this;
            }
            return $res;
        }
        return parent::__call($name, $parameters);
    }

    protected function doSearch($index, $query = '', $comment = '')
    {
        if (!$index) {
            throw new DGSphinxSearchException('Index search criteria invalid');
        }

        $res = $this->client->query($query, $index, $comment);

        if ($this->getLastError()) {
            throw new DGSphinxSearchException($this->getLastError());
        }

        if (!isset($res['matches'])) {
            $res['matches'] = array();
        }
        $this->resetCriteria();
        return $res;
    }

    protected function searchByCriteria($criteria)
    {
        if (!is_object($criteria)) {
            throw new DGSphinxSearchException('Criteria does not set.');
        }

        $this->setCriteria($criteria);

        $res = $this->doSearch($this->criteria->from, $this->criteria->query);

        if ($criteria->paginator) {
            if (isset($res['total'])) {
                $criteria->paginator->totalCount = $res['total'];
            } else {
                $criteria->paginator->totalCount = 0;
            }
        }

        return $res;
    }

    protected function initIterator(array $data, $criteria = NULL)
    {
        $iterator = new DGSphinxSearchResult($data, $criteria);
        $iterator->enableProfiling = $this->enableProfiling;
        $iterator->enableResultTrace = $this->enableResultTrace;
        return $iterator;
    }
}
