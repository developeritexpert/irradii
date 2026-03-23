<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "property_info_photo".
 *
 * @property integer $property_id
 * @property string $photo2
 * @property string $caption2
 * @property string $photo3
 * @property string $caption3
 * @property string $photo4
 * @property string $caption4
 * @property string $photo5
 * @property string $caption5
 * @property string $photo6
 * @property string $photo7
 * @property string $photo8
 * @property string $photo9
 * @property string $photo10
 * @property string $photo11
 * @property string $photo12
 * @property string $photo13
 * @property string $photo14
 * @property string $photo15
 * @property string $photo16
 * @property string $photo17
 * @property string $photo18
 * @property string $photo19
 * @property string $photo20
 * @property string $photo21
 * @property string $photo22
 * @property string $photo23
 * @property string $photo24
 * @property string $photo25
 * @property string $photo26
 * @property string $photo27
 * @property string $photo28
 * @property string $photo29
 * @property string $photo30
 * @property string $photo31
 * @property string $photo32
 * @property string $photo33
 * @property string $photo34
 * @property string $photo35
 * @property string $photo36
 * @property string $photo37
 * @property string $photo38
 * @property string $photo39
 * @property string $photo40
 */
class PropertyInfoPhoto extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'property_info_details';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['property_id', 'photo2', 'caption2', 'photo3', 'caption3', 'photo4', 'caption4', 'photo5', 'caption5'], 'required'],
            [['property_id'], 'integer'],
            [['photo2', 'photo3', 'photo4', 'photo5', 'photo6', 'photo7', 'photo8', 'photo9', 'photo10', 'photo11', 'photo12', 'photo13', 'photo14', 'photo15', 'photo16', 'photo17', 'photo18', 'photo19', 'photo20', 'photo21', 'photo22', 'photo23', 'photo24', 'photo25', 'photo26', 'photo27', 'photo28', 'photo29', 'photo30', 'photo31', 'photo32', 'photo33', 'photo34', 'photo35', 'photo36', 'photo37', 'photo38', 'photo39', 'photo40'], 'string', 'max' => 250],
            [['caption2', 'caption3', 'caption4', 'caption5'], 'string', 'max' => 100],
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
