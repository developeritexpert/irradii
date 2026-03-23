<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "county".
 *
 * @property integer $county_id
 * @property string $county_name
 * @property integer $state_id
 */
class County extends ActiveRecord
{
    public static function tableName()
    {
        return 'county';
    }

    public function rules()
    {
        return [
            [['state_id'], 'integer'],
            [['county_name'], 'string', 'max' => 80],
        ];
    }

    public function getState()
    {
        return $this->hasOne(State::class, ['stid' => 'state_id']);
    }

    public function getCities()
    {
        return $this->hasMany(City::class, ['county_id' => 'county_id']);
    }
}
