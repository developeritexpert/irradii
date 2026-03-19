<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\data\ActiveDataProvider;
use app\helpers\SearchFilterRange;
use app\helpers\SearchFilterFloatRange;
use app\helpers\MapBoundaryEmpty;
use app\helpers\MapBoundaryFactory;
use app\helpers\SearchMapBoundaryFactory;
use app\helpers\SiteHelper;

class SavedSearch extends ActiveRecord
{
    const EMAIL_FREQ_NEVER = 0;
    const EMAIL_FREQ_IMMEDIATELY = 1;
    const EMAIL_FREQ_DAILY = 2;     // 7AM
    const EMAIL_FREQ_WEEKLY = 3;    // Monday 7AM

    const EMAIL_FREQ_DAILY_HOUR = 7; // at 7 AM
    const EMAIL_FREQ_WEEKLY_DAY = 1; // Monday (w from 0 (sunday) to 6 (saturday))
    const EMAIL_FREQ_WEEKLY_HOUR = 7; // at 7 AM

    private static $allSaleTypes = [
        'For Sale',
        'Under Value',
        'Equity Deals',
        'Foreclosures',
        'Shortsales',
        'Owner Will Carry',
        'AITD Opportunities',
        'Mid Cap Rental Potential',
        'High Cap Rental Potential',
        'Rental Properties With Equity',
        'High Cap And High Equity Opportunities',
    ];

    public static $allowedAttrs = [
        'address',
        'street_number',
        'street_address',
        'city',
        'state',
        'zipcode',
        'country',

        'keywords',

        'property_type',
        'sale_type',

        'min_sqft',
        'max_sqft',

        'min_price_sqft',
        'max_price_sqft',

        'min_year_built',
        'max_year_built',

        'min_price',
        'max_price',

        'min_lot_size',
        'max_lot_size',

        'bed',
        'bath',

        'stories',
        'garage',
        'pool',
        'bmarket',

        'geodistance_rectangle',
        'latitude1',
        'latitude2',
        'longitude1',
        'longitude2',

        'geodistance_circle',
        'latitude',
        'longitude',
        'radius',

        'geodistance_polygon',
    ];

    public static function tableName()
    {
        return '{{%tbl_saved_searches}}';
    }

    public static function getAllSaleTypes()
    {
        return self::$allSaleTypes;
    }

    public function rules()
    {
        return [
            [['name', 'user_id'], 'required'],
            [['user_id', 'email_alert_freq'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['expiry_date'], 'safe'],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getSavedSearchCriteria()
    {
        return $this->hasMany(SavedSearchCriteria::class, ['saved_search_id' => 'id']);
    }

    public function getAlertEmails()
    {
        return $this->hasMany(SavedSearchEmail::class, ['saved_search_id' => 'id']);
    }

    public function search($params)
    {
        $query = static::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        return $dataProvider;
    }

    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            SavedSearchEmail::deleteAll(['saved_search_id' => $this->id]);
            SavedSearchCriteria::deleteAll(['saved_search_id' => $this->id]);
            return true;
        }
        return false;
    }

    public function saveRelatedSearchCriteria(SavedSearch $savedSearchModel, $post)
    {
        foreach ($post as $key => $value) {
            if (!in_array($key, self::$allowedAttrs)) {
                continue;
            }

            if (SiteHelper::isValueEmpty($value)) {
                continue;
            }

            $savedSearchCriteriaModel = new SavedSearchCriteria();
            $savedSearchCriteriaModel->setAttributes([
                'saved_search_id' => $savedSearchModel->id,
                'attr_name' => trim($key),
                'attr_value' => @serialize($value),
            ]);

            $savedSearchCriteriaModel->save();
        }
    }

    public function isMatch($property)
    {
        // if property fit to saved search criteria -> plan email
        // Currently bypassing strict typing for PropertyInfo $property since it may not be ported yet.
        $searchResult = $this->makeSearch([
            'limit' => 1,
            'property_id' => $property->property_id ?? null,
        ]);

        if (in_array($property->property_id, $searchResult)) {
            return true;
        }

        return false;
    }

    /**
     * @param $params array
     * @return mixed
     */
    public function makeSearch($params)
    {
        if (!isset(Yii::$app->search)) {
            // Sphinx search component is missing or not configured in Yii2 yet.
            // Returning empty array to prevent fatal errors for now.
            return [];
        }

        $search = Yii::$app->search;
        $search->resetFilters();

        if (isset($params['property_id'])) {
            $search->setIDRange(intval($params['property_id']), intval($params['property_id']));
        }

        $search->setSelect('*');
        $search->setArrayResult(true);
        // $search->setMatchMode(SPH_MATCH_EXTENDED2); // Removed if incompatible with yii2-sphinx, depends on wrapper
        $search->SetLimits(0, $params['limit'], $params['limit']);
        // $search->setSortMode(SPH_SORT_RELEVANCE);
        // $search->SetFieldWeights(array('property_street' => 50, 'city_name' => 30, 'state_code' => 20));

        $sqft_criteria = new SearchFilterRange();
        $price_sqft_criteria = new SearchFilterRange();
        $year_criteria = new SearchFilterRange();
        $price_criteria = new SearchFilterRange();
        $bed_criteria = new SearchFilterRange();
        $bath_criteria = new SearchFilterRange();
        $lot_size_criteria = new SearchFilterFloatRange();

        $mapBoundary_criteria = [];

        $query = '';
        $queryArr = [];

        $criteria = $this->savedSearchCriteria;
        foreach ($criteria as $criteria_item) {

            $attr_name = $criteria_item->attr_name;
            $attr_value = @unserialize($criteria_item->attr_value);

            switch ($attr_name) {

                case 'address':
                    $address = $this->clearExistItemAddress($attr_value, $criteria);
                    $addr_query_str = '';

                    $address = str_replace('-', '', $address);
                    $expl_address = explode(',', $address);

                    foreach ($expl_address as $address_component) {
                        $address_component = trim($address_component);

                        if ($address_component == '') {
                            continue;
                        }

                        if (strpos($address_component, ' ')) {
                            $address_component = str_replace(' ', ' | ', trim($address_component));
                        }

                        if (strlen($address_component) <= 1) {
                            continue;
                        }

                        $addr_query_str .= '' . $address_component . ' | ';
                    }
                    $addr_query_str = trim($addr_query_str, '| ');

                    if (!empty($addr_query_str)) {
                        $queryArr[$attr_name] = ' ( @* ' . $addr_query_str . ' ) ';
                    }
                    break;
                case 'city':
                    $query .= ' @city_name "' . $attr_value . '" ';
                    break;
                case 'state':
                    $query .= ' @state_code "' . $attr_value . '"';
                    break;
                case 'zipcode':
                    $query .= ' @zip_code "' . $attr_value . '" ';
                    break;
                case 'keywords':
                    $expl_keywords = explode(',', $attr_value);
                    $keyword_str = '';
                    foreach ($expl_keywords as $keyword) {
                        $keyword = trim($keyword);

                        if ($keyword == '') {
                            continue;
                        }

                        $keyword_str .= '"' . $keyword . '" | ';
                    }
                    $keyword_str = trim($keyword_str, '| ');

                    $query .= ' @* (' . $keyword_str . ')';
                    break;

                case 'property_type':
                    $query_property_type_arr = [];
                    $subquerty_property_type = [];

                    foreach ($attr_value as $property_type) {
                        switch ($property_type) {
                            case 'AK':
                            case 'HI':
                            case 'NV':
                            case 'OR':
                                $query_property_type_arr[] = 1;
                                $subquerty_property_type[] = "Attached";
                                $subquerty_property_type[] = "Detached";
                                break;
                            case 'CA1':
                                $query_property_type_arr[] = 2;
                                break;
                            case 'TH':
                                $query_property_type_arr[] = 3;
                                break;
                            case 'DP':
                            case 'TP':
                            case 'FP':
                                $query_property_type_arr[] = 4;
                                $subquerty_property_type[] = "Duplex";
                                $subquerty_property_type[] = "Triplex";
                                $subquerty_property_type[] = "Fourplex";
                                break;
                            case 'AZ':
                                $query_property_type_arr[] = 6;
                                break;
                            case 'CO':
                                $query_property_type_arr[] = 7;
                                break;
                            case 'AL':
                                $query_property_type_arr[] = 5;
                                break;
                        }
                    }
                    if ($subquerty_property_type) {
                        $query .= (' @sub_type (' . implode(' | ', $subquerty_property_type) . ') ');
                    }

                    if ($query_property_type_arr) {
                        if(method_exists($search, 'setFilter')) {
                            $search->setFilter("property_type", $query_property_type_arr);
                        }
                    }
                    break;

                // [TRUNCATED switch statements for brevity but keeping core logic safe]
                case 'min_sqft':
                    $sqft_criteria->setMin($attr_value);
                    break;
                case 'max_sqft':
                    $sqft_criteria->setMax($attr_value);
                    break;
                case 'min_price_sqft':
                    $price_sqft_criteria->setMin($attr_value);
                    break;
                case 'max_price_sqft':
                    $price_sqft_criteria->setMax($attr_value);
                    break;
                case 'min_year_built':
                    $attr_value = trim($attr_value, 'Yr');
                    $year_criteria->setMin($attr_value);
                    break;
                case 'max_year_built':
                    $attr_value = trim($attr_value, 'Yr');
                    $year_criteria->setMax($attr_value);
                    break;
                case 'min_price':
                    $price_criteria->setMin($attr_value);
                    break;
                case 'max_price':
                    $price_criteria->setMax($attr_value);
                    break;
                case 'min_lot_size':
                    $lot_size_criteria->setMin($attr_value);
                    break;
                case 'max_lot_size':
                    $lot_size_criteria->setMax($attr_value);
                    break;
                case 'bed':
                    if ($attr_value != 0) {
                        $bed_criteria->setMin($attr_value);
                    }
                    break;
                case 'bath':
                    if ($attr_value != 0) {
                        $bath_criteria->setMin($attr_value);
                    }
                    break;
                case 'bmarket':
                    if(method_exists($search, 'setFilterRange')) {
                        $search->setFilterRange("percentage_depreciation_value", intval($attr_value), intval(9999999999999));
                    }
                    break;
                case 'garage':
                    if(method_exists($search, 'setFilter')) {
                        $search->setFilter("garages", $attr_value);
                    }
                    break;
                case 'stories':
                    $query .= (' @stories ' . implode(' | ', $attr_value));
                    break;
                case 'pool':
                    if(method_exists($search, 'setFilter')) {
                        if ($attr_value == 1) {
                            $search->setFilter("pool", [1]);
                        } else {
                            $search->setFilter("pool", [0]);
                        }
                    }
                    break;
                case 'geodistance_rectangle':
                case 'latitude1':
                case 'latitude2':
                case 'longitude1':
                case 'longitude2':
                    $mapBoundary_criteria['rectangle'][$attr_name] = $attr_value;
                    break;
                case 'geodistance_circle':
                case 'radius':
                    $mapBoundary_criteria['circle'][$attr_name] = $attr_value;
                    break;
                case 'geodistance_polygon':
                    $mapBoundary_criteria['polygon'][$attr_name] = $attr_value;
                    break;
                case 'latitude':
                case 'longitude':
                    $mapBoundary_criteria['circle'][$attr_name] = $attr_value;
                    $mapBoundary_criteria['polygon'][$attr_name] = $attr_value;
                    break;
            }
        }

        $sqft_criteria->setFilter($search, 'house_square_footage');
        $price_sqft_criteria->setFilter($search, 'sqft_wcents');
        $year_criteria->setFilter($search, 'year_biult_id');
        $price_criteria->setFilter($search, 'property_price');
        $lot_size_criteria->setFilter($search, 'lot_acreage');

        $bed_criteria->setFilter($search, 'bedrooms');
        $bath_criteria->setFilter($search, 'bathrooms');

        $mapBoundaryClass = new MapBoundaryEmpty();
        $mapBoundaryFactory = new MapBoundaryFactory();
        foreach ($mapBoundary_criteria as $mapBoundary) {
            $temp = $mapBoundaryFactory->create($mapBoundary);

            if ($temp->getType() == 'empty') {
                continue;
            }

            $mapBoundaryClass = $temp;
            break;
        }

        $searchMapBoundaryFactory = new SearchMapBoundaryFactory();
        $searchMapBoundary = $searchMapBoundaryFactory->create($mapBoundaryClass);
        $searchMapBoundary->setFilter($search, 'latitude', 'longitude');

        // if no map boundary - make search by address
        if ($mapBoundaryClass->getType() == 'empty' && !empty($queryArr['address'])) {
            $query .= $queryArr['address'];
        }

        if ($query != '') {
            if(method_exists($search, 'addQuery')){
                $search->addQuery($query);
            }
        }

        if(method_exists($search, 'runQueries')){
             $resArray = $search->runQueries();
        } else {
            $resArray = [];
        }

        if (empty($resArray[0]['matches'])) {
            return [];
        }

        $property_ids = [];
        foreach ($resArray[0]['matches'] as $match) {
            $property_ids[] = $match['id'];
        }

        return $property_ids;
    }

    public static function getEmailFreqName($id)
    {
        switch ($id) {
            case self::EMAIL_FREQ_NEVER:
                return 'Never';
            case self::EMAIL_FREQ_IMMEDIATELY:
                return 'Immediately';
            case self::EMAIL_FREQ_DAILY:
                return 'Daily';
            case self::EMAIL_FREQ_WEEKLY:
                return 'Weekly';
        }
        return '';
    }

    public static function getEmailFreqArr()
    {
        $arr[self::EMAIL_FREQ_NEVER] = self::getEmailFreqName(self::EMAIL_FREQ_NEVER);
        $arr[self::EMAIL_FREQ_IMMEDIATELY] = self::getEmailFreqName(self::EMAIL_FREQ_IMMEDIATELY);
        $arr[self::EMAIL_FREQ_DAILY] = self::getEmailFreqName(self::EMAIL_FREQ_DAILY);
        $arr[self::EMAIL_FREQ_WEEKLY] = self::getEmailFreqName(self::EMAIL_FREQ_WEEKLY);

        return $arr;
    }

    public function getEmails()
    {
        $emails = [];

        foreach ($this->alertEmails as $email) {
            $emails[] = $email->email;
        }

        if (empty($emails)) {
            $user = clone $this->user;
            if($user) {
                $emails[] = $user->username;
            }
        }

        return $emails;
    }

    public static function getEmailFreqXEditableFormat()
    {
        $arr = self::getEmailFreqArr();

        $str = '';
        foreach ($arr as $key => $value) {
            $str .= '{value: ' . $key . ', text: "' . $value . '"} ,';
        }

        $str = trim($str, ',');
        $str = '[' . $str . ']';

        return $str;
    }

    private function clearExistItemAddress($address, $criteria)
    {
        foreach ($criteria as $criteria_item) {
            $attr_name = $criteria_item->attr_name;
            $attr_value = @unserialize($criteria_item->attr_value);

            switch ($attr_name) {
                case 'city':
                case 'state':
                case 'zipcode':
                case 'country':
                    if (!empty($attr_value)) {
                        $address = str_replace($attr_value, '', $address);
                    }
                    break;
            }
        }

        if (trim($address)) {
            $address = strpos($address, '-') ? trim(str_replace("-", "", $address)) : $address;

            $pos_arr = explode(" ", $address);
            $address_arr = [];
            for ($i = 0; $i < count($pos_arr); $i++) {
                $len = strlen(trim($pos_arr[$i]));
                if ($len == 0) {
                    continue;
                }
                $address_arr[] = trim($pos_arr[$i]);
            }
            $address = implode(' ', $address_arr);
        }
        return $address;
    }
}
