<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "state".
 *
 * @property integer $stid
 * @property string $state_name
 * @property string $state_code
 * @property integer $country_id
 */
class State extends ActiveRecord
{
    public static function tableName()
    {
        return 'state';
    }

    public function rules()
    {
        return [
            [['country_id'], 'integer'],
            [['state_name'], 'string', 'max' => 80],
            [['state_code'], 'string', 'max' => 5],
        ];
    }

    public function getCounties()
    {
        return $this->hasMany(County::class, ['state_id' => 'stid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCities()
    {
        return $this->hasMany(City::class, ['county_id' => 'county_id'])->via('counties');
    }
}
