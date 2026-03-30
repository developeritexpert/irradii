<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tbl_chat".
 *
 * @property int $id_chat
 * @property int|null $owner_room
 * @property int $collocutor_id
 * @property int $author_id
 * @property string|null $chat_message
 * @property string|null $chat_created
 * @property string $type
 */
class TblChat extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_chat';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['owner_room', 'collocutor_id', 'author_id'], 'integer'],
            [['collocutor_id', 'author_id'], 'required'],
            [['chat_message', 'type'], 'string'],
            [['chat_created'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_chat' => 'Id Chat',
            'owner_room' => 'Owner Room',
            'collocutor_id' => 'Collocutor Id',
            'author_id' => 'Author Id',
            'chat_message' => 'Chat Message',
            'chat_created' => 'Chat Created',
            'type' => 'Type',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }
}
