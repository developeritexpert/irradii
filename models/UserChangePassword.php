<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * UserChangePassword class.
 * UserChangePassword is the data structure for keeping
 * user change password form data. It is used by the 'changepassword' action of 'ProfileController'.
 */
class UserChangePassword extends Model
{
    public $oldPassword;
    public $password;
    public $verifyPassword;
    public $anotherUserId;

    public function rules()
    {
        return [
            [['password', 'verifyPassword'], 'required'],
            ['oldPassword', 'required', 'when' => function ($model) {
                return empty($model->anotherUserId);
            }],
            ['password', 'string', 'min' => 4],
            ['verifyPassword', 'compare', 'compareAttribute' => 'password'],
            [['anotherUserId'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'oldPassword' => 'Old Password',
            'password' => 'New Password',
            'verifyPassword' => 'Verify Password',
        ];
    }
}
