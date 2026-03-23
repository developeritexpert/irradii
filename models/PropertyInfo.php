<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "property_info".
 *
 * @property integer $property_id
 * @property string $property_street
 * @property integer $property_price
 * @property integer $estimated_price
 * @property integer $house_square_footage
 * @property double $lot_acreage
 * @property integer $bedrooms
 * @property integer $bathrooms
 * @property integer $garages
 * @property string $property_updated_date
 * @property string $photo1
 * @property string $community_name
 * @property string $subdivision
 * @property string $area
 * @property string $public_remarks
 * @property integer $percentage_depreciation_value
 */
class PropertyInfo extends ActiveRecord
{
    public static function tableName()
    {
        return 'property_info';
    }

    public function getSlug()
    {
        return $this->hasOne(PropertyInfoSlug::class, ['property_id' => 'property_id']);
    }

    public function getCity()
    {
        return $this->hasOne(City::class, ['cityid' => 'property_city_id']);
    }

    public function getState()
    {
        return $this->hasOne(State::class, ['stid' => 'property_state_id']);
    }

    public function getZipcode()
    {
        return $this->hasOne(Zipcode::class, ['zip_id' => 'property_zipcode']);
    }

    public function getCounty()
    {
        return $this->hasOne(County::class, ['county_id' => 'property_county_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'mid']);
    }

    public function getUserprofile()
    {
        return $this->hasOne(UserProfiles::class, ['mid' => 'mid']);
    }

    public function getPropertyInfoAdditionalBrokerageDetails()
    {
        return $this->hasOne(PropertyInfoAdditionalBrokerageDetails::class, ['property_id' => 'property_id']);
    }

    public function getPropertyInfoAdditionalDetails()
    {
        return $this->hasOne(PropertyInfoAdditionalDetails::class, ['property_id' => 'property_id']);
    }

    public function getPropertyInfoDetails()
    {
        return $this->hasOne(PropertyInfoDetails::class, ['property_id' => 'property_id']);
    }

    public function getPropertyInfoPhoto()
    {
        return $this->hasMany(PropertyInfoPhoto::class, ['property_id' => 'property_id']);
    }

    public function getBrokerageJoin()
    {
        return $this->hasOne(BrokerageJoin::class, ['brokerage_id' => 'brokerage_mid'])
            ->via('propertyInfoAdditionalBrokerageDetails');
    }

    public static function getPropertyType($key = null)
    {
        $property_type_array = [
            '0' => 'Unknown',
            '1' => 'Single Family Home',
            '2' => 'Condo',
            '3' => 'Townhouse',
            '4' => 'Multi Family',
            '5' => 'Land',
            '6' => 'Mobile Home',
            '7' => 'Manufactured Home',
            '8' => 'Time Share',
            '9' => 'Rental',
            '16' => 'High Rise'
        ];
        if ($key !== null && array_key_exists($key, $property_type_array)) {
            return $property_type_array[$key];
        }
        return $property_type_array;
    }

    public function getPropertyTypeStr()
    {
        $types = self::getPropertyType();
        return $types[$this->property_type] ?? '';
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

    public function getEstimatedEquity($trueMV, $price)
    {
        if ($trueMV > $price) {
            return $trueMV - $price;
        }
        return 0;
    }

    public function countPhoto()
    {
        $count = 0;
        if (!empty($this->photo1)) {
            $count++;
        }
        $photos = $this->propertyInfoPhoto;
        if (empty($photos) && !empty($this->mls_sysid)) {
            // Fallback for cases where join is on MLS system ID string
            try {
                $photos = PropertyInfoPhoto::find()->where(['property_id' => $this->mls_sysid])->all();
            } catch (\Exception $e) {
                $photos = [];
            }
        }

        if (!empty($photos)) {
            foreach ($photos as $photo) {
                for ($i = 2; $i <= 40; $i++) {
                    $name = 'photo' . $i;
                    if (!empty($photo->$name)) {
                        $count++;
                    } else {
                        break;
                    }
                }
            }
        }
        return $count;
    }

    public function getStatus()
    {
        return $this->propertyInfoAdditionalBrokerageDetails->status ?? '';
    }

    public function getUpdatedDateViaStatus()
    {
        $updatedDate = $this->property_updated_date;
        $details = $this->propertyInfoAdditionalBrokerageDetails;
        
        if ($details && isset($details->status)) {
            $status = strtoupper($details->status);
            if ($status == 'HISTORY' || $status == 'CLOSED') {
                $updatedDate = $details->actual_close_date;
            } elseif (in_array($status, ['FOR SALE', 'ACTIVE', 'ACTIVE-EXCLUSIVE RIGHT', 'EXCLUSIVE AGENCY', 'CONTINGENT OFFER', 'PENDING OFFER', 'EXPIRED'])) {
                $updatedDate = $details->list_date;
            }
        }

        if ($updatedDate === null) {
            $updatedDate = $this->property_updated_date;
        }

        return date("Y-m-d", strtotime($updatedDate));
    }

    public function getFullAddress()
    {
        $address = $this->property_street;
        if ($this->city) {
            $address .= ', ' . $this->city->city_name;
        }
        if ($this->state) {
            $address .= ' ' . $this->state->state_code;
        }
        if ($this->zipcode) {
            $address .= ' ' . $this->zipcode->zip_code;
        }
        return $address;
    }
}
