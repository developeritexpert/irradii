<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "property_info_additional_brokerage_details".
 */
class PropertyInfoAdditionalBrokerageDetails extends ActiveRecord
{
    public static function tableName()
    {
        return 'property_info_additional_brokerage_details';
    }

    public function getPropertyInfo()
    {
        return $this->hasOne(PropertyInfo::class, ['property_id' => 'property_id']);
    }

    public function getBrokerageJoin()
    {
        return $this->hasOne(BrokerageJoin::class, ['brokerage_id' => 'brokerage_mid']);
    }
}
