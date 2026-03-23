<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "city".
 *
 * @property integer $cityid
 * @property string $city_name
 * @property integer $county_id
 */
class City extends ActiveRecord
{
    public static function tableName()
    {
        return 'city';
    }

    public function rules()
    {
        return [
            [['county_id'], 'integer'],
            [['city_name'], 'string', 'max' => 80],
        ];
    }

    public function getCounty()
    {
        return $this->hasOne(County::class, ['county_id' => 'county_id']);
    }

    public function getZipcodes()
    {
        return $this->hasMany(Zipcode::class, ['cityid' => 'cityid']);
    }
}
