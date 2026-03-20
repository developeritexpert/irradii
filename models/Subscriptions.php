<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tbl_subscriptions".
 *
 * @property int $id
 * @property int $user_id
 * @property string $trans_id
 * @property string $status
 * @property string $start_date
 * @property string $end_date
 */
class Subscriptions extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_subscriptions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['start_date', 'end_date'], 'safe'],
            [['trans_id', 'status'], 'string', 'max' => 255],
        ];
    }
}
