<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ChangePasswordForm is the model behind the change password form.
 */
class ChangePasswordForm extends Model
{
    public $oldPassword;
    public $password;
    public $verifyPassword;
    public $anotherUserId;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['oldPassword', 'password', 'verifyPassword'], 'required', 'on' => 'default'],
            [['password', 'verifyPassword'], 'required', 'on' => 'admin_change'],
            ['verifyPassword', 'compare', 'compareAttribute' => 'password'],
            [['password'], 'string', 'min' => 4],
            [['anotherUserId'], 'integer'],
        ];
    }
}
