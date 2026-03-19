<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "property_info".
 */
class PropertyInfo extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'property_info';
    }

    /**
     * For backward compatibility with Yii 1.1 model()
     */
    public static function model($className = __CLASS__)
    {
        return new static();
    }

    /**
     * For backward compatibility with Yii 1.1 findByPk()
     */
    public static function findByPk($pk)
    {
        return static::findOne($pk);
    }

    /**
     * For backward compatibility with Yii 1.1 find()
     */
    public function find($condition = '', $params = [])
    {
        return static::findByCondition($condition)->one();
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

    public function getCounty()
    {
        return $this->hasOne(County::class, ['county_id' => 'property_county_id']);
    }

    public function getZipcode()
    {
        return $this->hasOne(Zipcode::class, ['zip_id' => 'property_zipcode']);
    }

    public static function getPropertyType($type_id)
    {
        $types = [
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
        return $types[$type_id] ?? $type_id;
    }
}
