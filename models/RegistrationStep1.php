<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "registration_step1".
 *
 * @property int $mid
 * @property string $username
 * @property string $password
 * @property string $member_type
 * @property string $join_date
 */
class RegistrationStep1 extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'registration_step1';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password', 'member_type', 'join_date'], 'required'],
            [['member_type'], 'string'],
            [['join_date'], 'safe'],
            [['username'], 'string', 'max' => 130],
            [['password'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'mid' => 'Mid',
            'username' => 'Username',
            'password' => 'Password',
            'member_type' => 'Member Type',
            'join_date' => 'Join Date',
        ];
    }
}
