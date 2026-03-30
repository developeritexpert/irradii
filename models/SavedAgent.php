<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "saved_agent".
 *
 * @property int $saved_id
 * @property int $agent_id
 * @property int $mid
 * @property int $saved_timestamp
 */
class SavedAgent extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'saved_agent';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['agent_id', 'mid', 'saved_timestamp'], 'required'],
            [['agent_id', 'mid', 'saved_timestamp'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'saved_id' => 'Saved ID',
            'agent_id' => 'Agent ID',
            'mid' => 'Mid',
            'saved_timestamp' => 'Saved Timestamp',
        ];
    }

    /**
     * Gets query for [[Agent]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAgent()
    {
        return $this->hasOne(User::class, ['id' => 'agent_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'mid']);
    }
}
