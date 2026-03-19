<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class PlannedEmailAlert extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'planned_email_alerts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['saved_search_id', 'property_id', 'email_freq'], 'safe'],
            [['saved_search_id', 'property_id'], 'integer'],
        ];
    }
}
