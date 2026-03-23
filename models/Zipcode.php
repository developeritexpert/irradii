<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "zipcode".
 *
 * @property integer $zip_id
 * @property integer $zip_code
 * @property string $latitude
 * @property string $longitude
 * @property integer $cityid
 */
class Zipcode extends ActiveRecord
{
    public static function tableName()
    {
        return 'zipcode';
    }

    public function rules()
    {
        return [
            [['zip_code', 'cityid'], 'integer'],
            [['latitude', 'longitude'], 'string', 'max' => 15],
        ];
    }

    public function getCity()
    {
        return $this->hasOne(City::class, ['cityid' => 'cityid']);
    }

    public function getCounty()
    {
        return $this->hasOne(County::class, ['county_id' => 'county_id'])->via('city');
    }

    public function getState()
    {
        return $this->hasOne(State::class, ['stid' => 'state_id'])->via('county');
    }
}
