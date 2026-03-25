<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tbl_membership_options".
 *
 * @property int $id
 * @property int $landing_id
 * @property string $first_title
 * @property string $first_color
 * @property string $first_price
 * @property string $first_text
 * @property string $second_title
 * @property string $second_color
 * @property string $second_price
 * @property string $second_text
 * @property string $third_title
 * @property string $third_color
 * @property string $third_price
 * @property string $third_text
 */
class MembershipOptions extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_membership_options';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['landing_id'], 'required'],
            [['landing_id'], 'integer'],
            [['first_text', 'second_text', 'third_text'], 'string'],
            [['first_title', 'first_color', 'first_price', 'second_title', 'second_color', 'second_price', 'third_title', 'third_color', 'third_price'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'landing_id' => 'Landing ID',
            'first_title' => 'First Title',
            'first_color' => 'First Color',
            'first_price' => 'First Price',
            'first_text' => 'First Text',
            'second_title' => 'Second Title',
            'second_color' => 'Second Color',
            'second_price' => 'Second Price',
            'second_text' => 'Second Text',
            'third_title' => 'Third Title',
            'third_color' => 'Third Color',
            'third_price' => 'Third Price',
            'third_text' => 'Third Text',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanding()
    {
        return $this->hasOne(LandingPage::class, ['id' => 'landing_id']);
    }
}
