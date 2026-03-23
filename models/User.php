<?php

namespace app\models;

use Yii;

class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    const STATUS_NOACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_BANNED = -1;
    const STATUS_BANED = -1; // for backward compatibility
    
    public $avatar_image;
    public $company_logo;
    public $certifications;
    public $professionsArray = [];
    public $state;
    public $street_number;
    public $street_address;
    public $country;
    public $city;
    public $zipcode;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%users}}';
    }

    public static function model($className = __CLASS__)
    {
        return new static();
    }

    public static function findByPk($pk)
    {
        return static::findOne($pk);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username'], 'required'],
            [['username'], 'email'],
            [['username'], 'unique'],
            [['superuser', 'status'], 'integer'],
            [['password', 'activkey', 'accessToken', 'authKey'], 'string', 'max' => 255],
            [['create_at', 'lastvisit_at'], 'safe'],
            [['state', 'country', 'city', 'zipcode', 'street_number', 'street_address'], 'safe'],
            [['avatar_image', 'company_logo', 'certifications', 'professionsArray'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['accessToken' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['username' => $email]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->activkey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->activkey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        if (strlen($this->password) === 32) {
            return $this->password === md5($password);
        }
        return Yii::$app->security->validatePassword($password, $this->password);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(UserProfiles::class, ['mid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfession()
    {
        return $this->hasMany(TblAuthAssignment::class, ['userid' => 'id']);
    }
}
