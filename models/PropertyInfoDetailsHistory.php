<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "property_info_details_history".
 *
 * @property integer $property_detail_id
 * @property integer $property_id
 * @property string $stories
 * @property integer $spa
 * @property string $apt_suite
 * @property integer $amenities_stove_id
 * @property integer $amenities_refrigerator
 * @property integer $amenities_dishwasher
 * @property integer $amenities_washer_id
 * @property integer $amenities_fireplace_id
 * @property integer $amenities_parking_id
 * @property integer $amenities_microwave
 * @property integer $amenities_gated_community
 * @property string $photo2
 * @property string $caption2
 * @property string $photo3
 * @property string $caption3
 * @property string $photo4
 * @property string $caption4
 * @property string $photo5
 * @property string $caption5
 * @property string $interior_features
 * @property string $exterior_features
 * @property integer $first_sale_type
 * @property integer $second_sale_type
 * @property double $property_repair_price
 * @property string $reference
 */
class PropertyInfoDetailsHistory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'property_info_details_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['property_id', 'stories', 'spa', 'apt_suite', 'amenities_stove_id', 'amenities_refrigerator', 'amenities_dishwasher', 'amenities_washer_id', 'amenities_fireplace_id', 'amenities_parking_id', 'amenities_microwave', 'amenities_gated_community', 'caption2', 'caption3', 'caption4', 'caption5', 'interior_features', 'exterior_features', 'first_sale_type', 'second_sale_type', 'property_repair_price', 'reference'], 'required'],
            [['property_id', 'spa', 'amenities_stove_id', 'amenities_refrigerator', 'amenities_dishwasher', 'amenities_washer_id', 'amenities_fireplace_id', 'amenities_parking_id', 'amenities_microwave', 'amenities_gated_community', 'first_sale_type', 'second_sale_type'], 'integer'],
            [['property_repair_price'], 'number'],
            [['stories'], 'string', 'max' => 30],
            [['apt_suite', 'caption2', 'caption3', 'caption4', 'caption5'], 'string', 'max' => 100],
            [['photo2', 'photo3', 'photo4', 'photo5'], 'string', 'max' => 250],
            [['reference'], 'string', 'max' => 15],
            [['interior_features', 'exterior_features'], 'string'],
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
