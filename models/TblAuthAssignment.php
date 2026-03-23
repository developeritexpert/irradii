<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "tbl_AuthAssignment".
 *
 * @property string $itemname
 * @property string $userid
 * @property string $bizrule
 * @property string $data
 */
class TblAuthAssignment extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_AuthAssignment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['itemname', 'userid'], 'required'],
            [['bizrule', 'data'], 'string'],
            [['itemname', 'userid'], 'string', 'max' => 64],
            [['itemname', 'userid'], 'unique', 'targetAttribute' => ['itemname', 'userid']],
        ];
    }
}
