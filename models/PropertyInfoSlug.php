<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "tbl_property_info_slug".
 *
 * @property integer $id
 * @property integer $property_id
 * @property string $slug
 * @property string $created_at
 * @property string $updated_at
 */
class PropertyInfoSlug extends ActiveRecord
{
    public static function tableName()
    {
        return 'tbl_property_info_slug';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('UTC_TIMESTAMP()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['property_id'], 'integer'],
            [['slug'], 'string', 'max' => 255],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function getProperty()
    {
        return $this->hasOne(PropertyInfo::class, ['property_id' => 'property_id']);
    }
}
