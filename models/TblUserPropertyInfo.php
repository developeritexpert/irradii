<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tbl_user_property_info".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $mls_sysid
 * @property string $mls_name
 * @property string $user_property_status
 * @property string $user_property_note
 * @property string $create_date
 * @property string $last_viewed_date
 * @property string $last_changed_date
 */
class TblUserPropertyInfo extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_user_property_info';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'mls_sysid', 'mls_name', 'user_property_status', 'create_date', 'last_viewed_date', 'last_changed_date'], 'required'],
            [['user_id', 'mls_sysid'], 'integer'],
            [['user_property_status', 'user_property_note'], 'string', 'max' => 255],
            [['mls_name'], 'string', 'max' => 50],
            [['create_date', 'last_viewed_date', 'last_changed_date'], 'safe'],
        ];
    }
}
