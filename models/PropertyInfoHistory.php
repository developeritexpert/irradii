<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "property_info_history".
 *
 * @property integer $history_id
 * @property integer $property_id
 * @property integer $year_biult_id
 * @property integer $pool
 * @property integer $garages
 * @property integer $mid
 * @property string $property_title
 * @property integer $house_square_footage
 * @property double $lot_acreage
 * @property integer $property_type
 * @property integer $property_price
 * @property integer $bathrooms
 * @property integer $bedrooms
 * @property string $description
 * @property string $property_street
 * @property integer $property_state_id
 * @property integer $property_county_id
 * @property integer $property_city_id
 * @property integer $property_zipcode
 * @property string $property_uploaded_date
 * @property string $property_updated_date
 * @property string $property_expire_date
 * @property string $photo1
 * @property string $caption1
 * @property double $getlongitude
 * @property double $getlatitude
 * @property integer $estimated_price
 * @property integer $percentage_depreciation_value
 * @property string $property_status
 * @property string $user_session_id
 * @property string $visible
 * @property string $sub_type
 * @property string $area
 * @property string $subdivision
 * @property string $schools
 * @property string $community_name
 * @property string $community_features
 * @property string $property_fetatures
 * @property integer $mls_sysid
 */
class PropertyInfoHistory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'property_info_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['year_biult_id', 'pool', 'garages', 'mid', 'property_title', 'house_square_footage', 'lot_acreage', 'property_type', 'property_price', 'bathrooms', 'bedrooms', 'description', 'property_street', 'property_state_id', 'property_county_id', 'property_city_id', 'property_zipcode', 'property_uploaded_date', 'property_updated_date', 'property_expire_date', 'photo1', 'caption1', 'getlongitude', 'getlatitude', 'estimated_price', 'percentage_depreciation_value', 'user_session_id', 'sub_type', 'area', 'subdivision', 'schools', 'community_name', 'community_features', 'property_fetatures'], 'required'],
            [['year_biult_id', 'pool', 'garages', 'mid', 'house_square_footage', 'property_type', 'property_price', 'bathrooms', 'bedrooms', 'property_state_id', 'property_county_id', 'property_city_id', 'property_zipcode', 'estimated_price', 'percentage_depreciation_value', 'mls_sysid'], 'integer'],
            [['lot_acreage', 'getlongitude', 'getlatitude'], 'number'],
            [['property_title', 'property_street', 'caption1'], 'string', 'max' => 100],
            [['photo1', 'area', 'subdivision', 'schools'], 'string', 'max' => 250],
            [['property_status'], 'string', 'max' => 8],
            [['user_session_id'], 'string', 'max' => 40],
            [['visible'], 'string', 'max' => 1],
            [['sub_type', 'community_name'], 'string', 'max' => 200],
            [['description', 'community_features', 'property_fetatures'], 'string'],
        ];
    }

    public function getCity()
    {
        return $this->hasOne(City::class, ['cityid' => 'property_city_id']);
    }

    public function getCounty()
    {
        return $this->hasOne(County::class, ['countyid' => 'property_county_id']);
    }

    public function getState()
    {
        return $this->hasOne(State::class, ['stid' => 'property_state_id']);
    }

    public function getZipcode()
    {
        return $this->hasOne(Zipcode::class, ['zip_id' => 'property_zipcode']);
    }

    public function getDiscontValue()
    {
        $discont = 0;
        $underValueDeals = Yii::$app->params['underValueDeals'] ?? 10;
        
        if ($this->percentage_depreciation_value >= $underValueDeals) {
            $discont = $this->percentage_depreciation_value;
        }
        
        if ($discont == 0) {
            if ($this->estimated_price > 0 && (100 - ($this->property_price * 100 / $this->estimated_price)) > 0) {
                $discont = 100 - ($this->property_price * 100 / $this->estimated_price);
            }
        }

        return $discont;
    }
}
