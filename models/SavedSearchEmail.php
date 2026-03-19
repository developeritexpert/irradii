<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class SavedSearchEmail extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%tbl_saved_search_emails}}';
    }

    public function rules()
    {
        return [
            [['email', 'saved_search_id'], 'required'],
            [['saved_search_id'], 'integer'],
            [['email'], 'string', 'max' => 255],
            [['email'], 'email'],
        ];
    }

    public function getSavedSearch()
    {
        return $this->hasOne(SavedSearch::class, ['id' => 'saved_search_id']);
    }
}
