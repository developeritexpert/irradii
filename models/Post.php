<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * This is the model class for table "{{post}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property string $tags
 * @property integer $status
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $author_id
 */
class Post extends ActiveRecord
{
    const STATUS_DRAFT = 1;
    const STATUS_PUBLISHED = 2;
    const STATUS_ARCHIVED = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_post';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content', 'status'], 'required'],
            [['content', 'tags'], 'string'],
            [['status', 'create_time', 'update_time', 'author_id'], 'integer'],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PUBLISHED, self::STATUS_ARCHIVED]],
            [['title'], 'string', 'max' => 128],
            [['tags'], 'match', 'pattern' => '/^[\w\s,]+$/', 'message' => 'Tags can only contain word characters.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'title' => 'Title',
            'content' => 'Content',
            'tags' => 'Tags',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'author_id' => 'Author',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        // Assuming Comment model exists and has approved status
        // return $this->hasMany(Comment::class, ['post_id' => 'id'])->where(['status' => Comment::STATUS_APPROVED])->orderBy('create_time DESC');
        return $this->hasMany(Comment::class, ['post_id' => 'id']);
    }

    /**
     * Generates a URL for the post.
     */
    public function getUrl()
    {
        return Url::to(['/post/view', 'id' => $this->id, 'title' => $this->title]);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->create_time = $this->update_time = time();
                $this->author_id = Yii::$app->user->id;
            } else {
                $this->update_time = time();
            }
            return true;
        }
        return false;
    }
}
