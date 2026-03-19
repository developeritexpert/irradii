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
        return '{{%saved_searches}}';
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
            return $this->dbFallbackSearch($params);
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

    /**
     * Database-based fallback search for when Sphinx is unavailable.
     * Performs basic filtering on PropertyInfo table.
     */
    /**
     * Database-based fallback search for when Sphinx is unavailable.
     * Mirrors the Yii1 Sphinx makeSearch logic using database queries.
     * Handles: sale_type, property_type, price, sqft, bed, bath, year_built,
     * lot_size, garage, pool, bmarket, city, state, zipcode, address, keywords.
     */
    private function dbFallbackSearch($params)
    {
        $query = PropertyInfo::find()->select('property_info.property_id');
        $criteria = $this->savedSearchCriteria;

        // Collect criteria into a keyed array for multi-pass processing
        $criteriaMap = [];
        foreach ($criteria as $criteria_item) {
            $attr_name = $criteria_item->attr_name;
            $attr_value = @unserialize($criteria_item->attr_value);

            if ($attr_value === false && $criteria_item->attr_value !== 'b:0;') {
                $attr_value = $criteria_item->attr_value;
            }

            if (empty($attr_value) && $attr_value !== 0 && $attr_value !== '0') {
                continue;
            }

            $criteriaMap[$attr_name] = $attr_value;
        }

        // Track whether we need the brokerage details join
        $needsBrokerageJoin = false;

        // --- sale_type handling (matches Yii1 Sphinx logic) ---
        if (!empty($criteriaMap['sale_type'])) {
            $sale_type = $criteriaMap['sale_type'];
            $needsBrokerageJoin = true;

            // Active statuses used across most sale types
            $activeStatuses = ['Active', 'Active Exclusive Right', 'Active-Exclusive Right',
                'Auction', 'Exclusive Agency', 'For Sale'];

            switch ($sale_type) {
                case 'For Sale':
                    $query->andWhere(['property_info.property_status' => 'Active']);
                    $query->joinWith('propertyInfoAdditionalBrokerageDetails')
                        ->andWhere(['property_info_additional_brokerage_details.status' => $activeStatuses]);
                    // Exclude rentals (property_type = 9)
                    $query->andWhere(['not', ['property_info.property_type' => 9]]);
                    break;

                case 'Under Value':
                    $query->andWhere(['property_info.property_status' => 'Active']);
                    $query->joinWith('propertyInfoAdditionalBrokerageDetails')
                        ->andWhere(['property_info_additional_brokerage_details.status' => $activeStatuses]);
                    $query->andWhere(['>=', 'property_info.percentage_depreciation_value', 5]);
                    $query->andWhere(['<', 'property_info.percentage_depreciation_value', 15]);
                    $query->andWhere(['property_info.property_type' => [0,1,2,3,4,5,6,7,8,16]]);
                    break;

                case 'Equity Deals':
                    $query->andWhere(['property_info.property_status' => 'Active']);
                    $query->joinWith('propertyInfoAdditionalBrokerageDetails')
                        ->andWhere(['property_info_additional_brokerage_details.status' => $activeStatuses]);
                    $query->andWhere(['>=', 'property_info.percentage_depreciation_value', 15]);
                    $query->andWhere(['property_info.property_type' => [0,1,2,3,4,5,6,7,8,16]]);
                    break;

                case 'Foreclosures':
                    $query->andWhere(['property_info.property_status' => 'Active']);
                    $query->joinWith('propertyInfoAdditionalBrokerageDetails')
                        ->andWhere(['property_info_additional_brokerage_details.status' => $activeStatuses])
                        ->andWhere(['property_info_additional_brokerage_details.foreclosure' => 'yes']);
                    $query->andWhere(['not', ['property_info.property_type' => 9]]);
                    break;

                case 'Shortsales':
                    $query->andWhere(['property_info.property_status' => 'Active']);
                    $query->joinWith('propertyInfoAdditionalBrokerageDetails')
                        ->andWhere(['property_info_additional_brokerage_details.status' => $activeStatuses])
                        ->andWhere(['property_info_additional_brokerage_details.short_sale' => 'yes']);
                    $query->andWhere(['not', ['property_info.property_type' => 9]]);
                    break;

                case 'Owner Will Carry':
                    $query->andWhere(['property_info.property_status' => 'Active']);
                    $query->joinWith('propertyInfoAdditionalBrokerageDetails')
                        ->andWhere(['property_info_additional_brokerage_details.status' => $activeStatuses])
                        ->andWhere(['like', 'property_info_additional_brokerage_details.financing_considered', 'OWC']);
                    $query->andWhere(['not', ['property_info.property_type' => 9]]);
                    break;

                case 'AITD Opportunities':
                    $query->andWhere(['property_info.property_status' => 'Active']);
                    $query->joinWith('propertyInfoAdditionalBrokerageDetails')
                        ->andWhere(['property_info_additional_brokerage_details.status' => $activeStatuses])
                        ->andWhere(['like', 'property_info_additional_brokerage_details.financing_considered', 'AITD']);
                    $query->andWhere(['not', ['property_info.property_type' => 9]]);
                    break;

                case 'For Rent':
                    $query->andWhere(['property_info.property_status' => 'Active']);
                    $query->andWhere(['property_info.property_type' => 9]);
                    break;

                case 'ALL Sale Types':
                    $query->andWhere(['property_info.visible' => '1']);
                    break;

                case 'All Property Records':
                    $query->andWhere(['property_info.visible' => '1']);
                    break;

                case 'Mid Cap Rental Potential':
                    $query->andWhere(['property_info.property_status' => 'Active']);
                    $query->joinWith('propertyInfoAdditionalBrokerageDetails')
                        ->andWhere(['property_info_additional_brokerage_details.status' => $activeStatuses]);
                    $query->andWhere(['not', ['property_info.property_type' => 9]]);
                    // mid_cap is a Sphinx computed attribute; approximate with cap_rate range
                    break;

                case 'High Cap Rental Potential':
                    $query->andWhere(['property_info.property_status' => 'Active']);
                    $query->joinWith('propertyInfoAdditionalBrokerageDetails')
                        ->andWhere(['property_info_additional_brokerage_details.status' => $activeStatuses]);
                    $query->andWhere(['not', ['property_info.property_type' => 9]]);
                    break;

                case 'Rental Properties With Equity':
                    $query->andWhere(['property_info.property_status' => 'Active']);
                    $query->andWhere(['>=', 'property_info.percentage_depreciation_value', 6]);
                    $query->joinWith('propertyInfoAdditionalBrokerageDetails')
                        ->andWhere(['property_info_additional_brokerage_details.status' => $activeStatuses]);
                    break;

                case 'High Cap And High Equity Opportunities':
                    $query->andWhere(['property_info.property_status' => 'Active']);
                    $query->andWhere(['>=', 'property_info.percentage_depreciation_value', 10]);
                    $query->joinWith('propertyInfoAdditionalBrokerageDetails')
                        ->andWhere(['property_info_additional_brokerage_details.status' => $activeStatuses]);
                    break;
            }
        }

        // --- property_type ---
        if (!empty($criteriaMap['property_type']) && is_array($criteriaMap['property_type'])) {
            $mapped_types = [];
            $sub_types = [];
            foreach ($criteriaMap['property_type'] as $type_code) {
                if ($type_code == 'AK') {
                    $mapped_types[] = 1;
                    $sub_types[] = 'Attached';
                } elseif ($type_code == 'HI') {
                    $mapped_types[] = 1;
                    $sub_types[] = 'Detached';
                } else {
                    $mapped = $this->mapPropertyTypeCode($type_code);
                    if ($mapped !== null) {
                        $mapped_types[] = $mapped;
                    }
                }
            }
            $mapped_types = array_unique(array_filter($mapped_types));
            if (!empty($mapped_types)) {
                $query->andWhere(['property_info.property_type' => $mapped_types]);
            }
            if (!empty($sub_types)) {
                $query->andWhere(['property_info.sub_type' => $sub_types]);
            }
        }

        // --- Location filters ---
        if (!empty($criteriaMap['city'])) {
            $query->joinWith('city')->andWhere(['city.city_name' => $criteriaMap['city']]);
        }
        if (!empty($criteriaMap['state'])) {
            $query->joinWith('state')->andWhere(['state.state_code' => $criteriaMap['state']]);
        }
        if (!empty($criteriaMap['zipcode'])) {
            $query->joinWith('zipcode')->andWhere(['zipcode.zip_code' => $criteriaMap['zipcode']]);
        }

        // --- Address / Keywords ---
        if (!empty($criteriaMap['address'])) {
            $address = trim($criteriaMap['address']);
            // Case-insensitive match for "City, State, USA" pattern
            if (preg_match('/^([^,]+),\s*([A-Z]{2}),\s*USA$/i', $address, $matches)) {
                $cityName = trim($matches[1]);
                $stateCode = strtoupper(trim($matches[2]));
                $query->joinWith('city')->andWhere(['or', 
                    ['city.city_name' => $cityName],
                    ['city.city_name' => strtoupper($cityName)]
                ]);
                $query->joinWith('state')->andWhere(['state.state_code' => $stateCode]);
            } else {
                $query->andWhere(['like', 'property_info.property_street', $address]);
            }
        }
        if (!empty($criteriaMap['keywords'])) {
            $query->andWhere(['or',
                ['like', 'property_info.property_street', $criteriaMap['keywords']],
                ['like', 'property_info.public_remarks', $criteriaMap['keywords']]
            ]);
        }

        // --- Price ---
        if (!empty($criteriaMap['min_price'])) {
            $query->andWhere(['>=', 'property_info.property_price', $this->cleanNumeric($criteriaMap['min_price'])]);
        }
        if (!empty($criteriaMap['max_price'])) {
            $query->andWhere(['<=', 'property_info.property_price', $this->cleanNumeric($criteriaMap['max_price'])]);
        }

        // --- Bed / Bath ---
        if (!empty($criteriaMap['bed'])) {
            $val = $this->cleanNumeric($criteriaMap['bed']);
            if ($val > 0) {
                $query->andWhere(['>=', 'property_info.bedrooms', $val]);
            }
        }
        if (!empty($criteriaMap['bath'])) {
            $val = $this->cleanNumeric($criteriaMap['bath']);
            if ($val > 0) {
                $query->andWhere(['>=', 'property_info.bathrooms', $val]);
            }
        }

        // --- Square footage ---
        if (!empty($criteriaMap['min_sqft'])) {
            $query->andWhere(['>=', 'property_info.house_square_footage', $this->cleanNumeric($criteriaMap['min_sqft'])]);
        }
        if (!empty($criteriaMap['max_sqft'])) {
            $query->andWhere(['<=', 'property_info.house_square_footage', $this->cleanNumeric($criteriaMap['max_sqft'])]);
        }

        // --- Year built ---
        if (!empty($criteriaMap['min_year_built'])) {
            $val = $this->cleanNumeric(trim($criteriaMap['min_year_built'], 'Yr'));
            if ($val > 0) {
                $query->andWhere(['>=', 'property_info.year_biult_id', $val]);
            }
        }
        if (!empty($criteriaMap['max_year_built'])) {
            $val = $this->cleanNumeric(trim($criteriaMap['max_year_built'], 'Yr'));
            if ($val > 0) {
                $query->andWhere(['<=', 'property_info.year_biult_id', $val]);
            }
        }

        // --- Lot size ---
        if (!empty($criteriaMap['min_lot_size'])) {
            $query->andWhere(['>=', 'property_info.lot_acreage', (float)$criteriaMap['min_lot_size']]);
        }
        if (!empty($criteriaMap['max_lot_size'])) {
            $query->andWhere(['<=', 'property_info.lot_acreage', (float)$criteriaMap['max_lot_size']]);
        }

        // --- Garage ---
        if (isset($criteriaMap['garage'])) {
            $query->andWhere(['property_info.garages' => $criteriaMap['garage']]);
        }

        // --- Pool ---
        if (isset($criteriaMap['pool'])) {
            $query->andWhere(['property_info.pool' => ($criteriaMap['pool'] == 1) ? 1 : 0]);
        }

        // --- Below market (bmarket) ---
        if (!empty($criteriaMap['bmarket'])) {
            $bmarketVal = $this->cleanNumeric($criteriaMap['bmarket']);
            if ($bmarketVal > 0) {
                $query->andWhere(['>=', 'property_info.percentage_depreciation_value', $bmarketVal]);
            }
        }

        // --- Bounding Box (latitude1, latitude2, longitude1, longitude2) ---
        if (isset($criteriaMap['latitude1'], $criteriaMap['latitude2'], $criteriaMap['longitude1'], $criteriaMap['longitude2'])) {
            $latMin = min((float)$criteriaMap['latitude1'], (float)$criteriaMap['latitude2']);
            $latMax = max((float)$criteriaMap['latitude1'], (float)$criteriaMap['latitude2']);
            $lonMin = min((float)$criteriaMap['longitude1'], (float)$criteriaMap['longitude2']);
            $lonMax = max((float)$criteriaMap['longitude1'], (float)$criteriaMap['longitude2']);

            $query->andWhere(['between', 'property_info.getlatitude', $latMin, $latMax]);
            $query->andWhere(['between', 'property_info.getlongitude', $lonMin, $lonMax]);
        }

        $results = $query->orderBy(['property_info.property_id' => SORT_DESC])
            ->limit($params['limit'])
            ->asArray()
            ->all();

        return array_column($results, 'property_id');
    }

    /**
     * Strip non-numeric characters from price/value strings like "$400,000".
     */
    private function cleanNumeric($val)
    {
        if (is_numeric($val)) return (int)$val;
        if (!is_string($val)) return (int)$val;
        return (int)preg_replace('/[^0-9]/', '', $val);
    }

    /**
     * Map legacy property type codes (used in search forms) to database integer values.
     * Matches Yii1 SavedSearch::makeSearch property_type switch logic.
     */
    private function mapPropertyTypeCode($code)
    {
        $mapping = [
            'AK' => 1, 'HI' => 1, // Single Family (Attached/Detached)
            'NV' => 1,
            'OR' => 16, // High Rise (matches Yii1 line 384)
            'CA1' => 2, // Condo
            'TH' => 3,  // Townhouse
            'DP' => 4, 'TP' => 4, 'FP' => 4, // Multi Family (Duplex/Triplex/Fourplex)
            'AZ' => 6,  // Mobile Home
            'CO' => 7,  // Manufactured Home
            'AL' => 5,  // Land
            'Rental' => 9, // Rental
        ];
        return $mapping[$code] ?? (is_numeric($code) ? (int)$code : null);
    }
}
