<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class SavedSearchCriteria extends ActiveRecord
{
    public static $labels = [
        'address' => 'Address',
        'keywords'=> 'Keywords',

        'property_type' => 'Property Type',
        'sale_type' => 'Sale Type',

        'min_sqft' => 'Min Sq Ft',
        'max_sqft' => 'Max Sq Ft',

        'min_year_built'=>'Min Year Built',
        'max_year_built'=>'Max Year Built',

        'min_price'=>'Min Price',
        'max_price'=>'Max Price',

        'min_lot_size'=>'Min Lot Size',
        'max_lot_size'=>'Max Lot Size',

        'bed'=>'Beds',
        'bath'=>'Baths',

        'geodistance_rectangle'=>'Rectangle Map Boundary',
        'geodistance_circle'=>'Circle Map Boundary',
        'geodistance_polygon'=>'Polygon Map Boundary',

        'pool' => 'Pool',
        'bmarket' => 'Below Market',
        'garage' => "Garage",
        'stories' => 'Stories',
    ];

    public static function tableName()
    {
        return '{{%saved_search_criteria}}';
    }

    public function rules()
    {
        return [
            [['saved_search_id'], 'required'],
            [['saved_search_id'], 'integer'],
            [['attr_name'], 'string', 'max' => 255],
            [['attr_value'], 'safe'],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function getSavedSearch()
    {
        return $this->hasOne(SavedSearch::class, ['id' => 'saved_search_id']);
    }

    public static function getLabel($attr_name)
    {
        if (!array_key_exists($attr_name, self::$labels)) {
            return '';
        }

        return self::$labels[$attr_name];
    }
}
