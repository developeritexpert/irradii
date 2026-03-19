<?php
// basic stub for PropertyInfo
namespace app\models;

use yii\db\ActiveRecord;

class PropertyInfo extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%property_info}}';
    }
}
