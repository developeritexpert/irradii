<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "details_map_shapes".
 *
 * @property integer $id
 * @property string $session_id
 * @property integer $prop_id
 * @property string $shape
 * @property string $excluded_props_by_shape
 * @property string $created_at
 */
class DetailsMapShape extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_details_map_shapes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['session_id', 'prop_id'], 'required'],
            [['prop_id'], 'integer'],
            [['session_id'], 'string', 'max' => 50],
            [['shape', 'excluded_props_by_shape', 'created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = new Expression('NOW()');
            }
            return true;
        }
        return false;
    }
}
