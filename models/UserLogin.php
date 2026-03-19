<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * UserLogin is the login form model translated from Yii1.
 */
class UserLogin extends Model
{
    public $username;
    public $password;
    public $rememberMe;

    private $_user = false;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            } elseif ($user->status == User::STATUS_NOACTIVE) {
                $this->addError($attribute, 'You account is not activated.');
            } elseif ($user->status == User::STATUS_BANNED) {
                $this->addError($attribute, 'You account is blocked.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'rememberMe' => 'Remember me next time',
            'username' => 'email',
            'password' => 'password',
        ];
    }
}
