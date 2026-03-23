<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class PropertyInfoEventLog extends ActiveRecord
{
    const EVENT_TYPE_CREATE = 0;
    const EVENT_TYPE_UPDATE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'property_info_event_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'property_id', 'run_at'], 'safe'],
            [['property_id', 'type'], 'integer'],
        ];
    }
}
