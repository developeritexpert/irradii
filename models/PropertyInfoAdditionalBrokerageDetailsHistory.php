<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "property_info_additional_brokerage_details_history".
 *
 * @property integer $property_info_brokerage_details
 * @property integer $property_id
 * @property string $status
 * @property string $fireplace_features
 * @property string $heating_features
 * @property string $exterior_construction_features
 * @property string $roofing_features
 * @property string $interior_features
 * @property string $exterior_features
 * @property string $sales_history
 * @property string $tax_history
 * @property string $foreclosure
 * @property string $short_sale
 * @property string $page_link
 * @property string $updated_mid
 * @property integer $brokerage_mid
 * @property string $mls_id
 * @property string $pagent_name
 * @property string $pagent_phone
 * @property string $pagent_phone_fax
 * @property string $pagent_phone_home
 * @property string $pagent_phone_mobile
 * @property string $pagent_website
 */
class PropertyInfoAdditionalBrokerageDetailsHistory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'property_info_additional_brokerage_details_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['property_id', 'status', 'fireplace_features', 'heating_features', 'exterior_construction_features', 'roofing_features', 'interior_features', 'exterior_features', 'sales_history', 'tax_history', 'foreclosure', 'short_sale', 'page_link', 'updated_mid', 'brokerage_mid', 'mls_id', 'pagent_name', 'pagent_phone', 'pagent_phone_fax', 'pagent_phone_home', 'pagent_phone_mobile', 'pagent_website'], 'required'],
            [['property_id', 'brokerage_mid'], 'integer'],
            [['status', 'sales_history', 'tax_history'], 'string', 'max' => 50],
            [['fireplace_features', 'heating_features', 'exterior_construction_features', 'roofing_features', 'interior_features', 'exterior_features'], 'string', 'max' => 250],
            [['foreclosure', 'short_sale'], 'string', 'max' => 15],
            [['page_link', 'pagent_website'], 'string', 'max' => 150],
            [['updated_mid'], 'string', 'max' => 1],
            [['mls_id', 'pagent_phone_fax', 'pagent_phone_home', 'pagent_phone_mobile'], 'string', 'max' => 30],
            [['pagent_name'], 'string', 'max' => 100],
            [['pagent_phone'], 'string', 'max' => 20],
        ];
    }

    /**
     * Gets query for [[Property]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPropertyInfo()
    {
        return $this->hasOne(PropertyInfo::class, ['property_id' => 'property_id']);
    }
}
