<?php

namespace app\models;

use yii\base\Model;
use Yii;
use app\models\User;
use app\models\UserProfiles;
use app\models\RegistrationStep1;

/**
 * RegistrationForm model
 * Handles user registration form data.
 */
class RegistrationForm extends Model
{
    /** @var string email address used as username */
    public $username;
    public $password;
    public $verifyPassword;
    public $verifyCode;
    public $terms;
    public $professionRole;

    // Profile fields (folded in from old Profile model)
    public $firstName;
    public $lastName;
    public $subscription = 'No';

    // Location fields (from Google Maps autocomplete)
    public $streetNumber;
    public $streetAddress;
    public $city;
    public $state;
    public $country;

    /** Profession options for dropdown */
    public static $professionList = [
        'Buyer'      => 'Buyer',
        'Seller'     => 'Seller',
        'Investor'   => 'Investor',
        'Agent'      => 'Agent',
        'Broker'     => 'Broker',
        'Landlord'   => 'Landlord',
        'Lender'     => 'Lender',
        'Contractor' => 'Contractor',
        'Other'      => 'Other',
    ];

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // Required fields
            [['username', 'password', 'verifyPassword', 'terms', 'professionRole', 'firstName', 'lastName'], 'required'],

            // Email
            ['username', 'email'],
            ['username', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This email address has already been taken.'],
            ['username', 'string', 'max' => 130],

            // Password
            ['password', 'string', 'min' => 4, 'max' => 128,
                'tooShort' => 'Incorrect password (minimal length 4 symbols).'],

            // Confirm password
            ['verifyPassword', 'compare', 'compareAttribute' => 'password',
                'message' => 'Retype Password is incorrect.'],

            // Terms must be agreed
            ['terms', 'required', 'requiredValue' => '1',
                'message' => 'You must agree with the Terms and Conditions.'],

            // Name fields
            [['firstName', 'lastName'], 'string', 'max' => 128],

            // Profession role
            ['professionRole', 'in', 'range' => array_keys(self::$professionList)],

            // Location fields (safe, filled by Google Maps)
            [['streetNumber', 'streetAddress', 'city', 'state', 'country'], 'string', 'max' => 255],

            // Optional fields
            ['subscription', 'safe'],
            ['verifyCode', 'safe'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'username'       => 'E-mail Address',
            'password'       => 'Password',
            'verifyPassword' => 'Retype Password',
            'verifyCode'     => 'Verification Code',
            'terms'          => 'Terms & Conditions',
            'professionRole' => 'Profession Role',
            'firstName'      => 'First Name',
            'lastName'       => 'Last Name',
            'city'           => 'City',
            'state'          => 'State',
            'country'        => 'Country',
            'streetNumber'   => 'Street Number',
            'streetAddress'  => 'Street Address',
            'subscription'   => 'Subscribe to newsletter',
        ];
    }

    /**
     * Complete the signup process
     * @param string|null $member_role Pre-selected role (Agent, Seller, etc.)
     * @return User|bool The created user model, or false if failed
     */
    public function signup($member_role = null)
    {
        if (!$this->validate()) {
            return false;
        }

        // Map roles to database enum values
        $roleMap = [
            'Agent'      => 'AGENT',
            'Buyer'      => 'BUYER',
            'Seller'     => 'SELLER',
            'Investor'   => 'INVESTOR',
            'Broker'     => 'BROKERAGE',
            'Brokerage'  => 'BROKERAGE',
            'Landlord'   => 'SELLER',   // Fallback
            'Lender'     => 'INVESTOR', // Fallback
            'Contractor' => 'AGENT',    // Fallback
            'Other'      => 'BUYER',    // Fallback
        ];
        
        $role = $member_role ?? $this->professionRole;
        $dbRole = isset($roleMap[$role]) ? $roleMap[$role] : 'BUYER';

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 1. Legacy Staging (RegistrationStep1)
            $step1 = new RegistrationStep1();
            $step1->username = $this->username;
            // IMPORTANT: md5 ensures it fits in varchar(32)
            $step1->password = md5($this->password); 
            $step1->member_type = $dbRole;
            $step1->join_date = date('Y-m-d');
            
            if (!$step1->save()) {
                throw new \Exception('Failed to save RegistrationStep1: ' . json_encode($step1->getErrors()));
            }

            // 2. Main User Account (tbl_users)
            $user = new User();
            $user->username = $this->username;
            // Legacy MD5 supported by User::validatePassword
            $user->password = md5($this->password);
            $user->status = User::STATUS_ACTIVE;
            $user->create_at = date('Y-m-d H:i:s');
            $user->superuser = 0;
            $user->activkey = Yii::$app->security->generateRandomString();
            
            if (!$user->save(false)) {
                throw new \Exception('Failed to save User Account: ' . json_encode($user->getErrors()));
            }

            // 3. User Profile (tbl_users_profiles)
            $profile = new UserProfiles();
            $profile->mid = $user->id;
            $profile->first_name = $this->firstName;
            $profile->last_name = $this->lastName;
            $profile->city = $this->city;
            $profile->state = $this->state;
            $profile->country = $this->country;
            $profile->street_number = $this->streetNumber;
            $profile->street_address = $this->streetAddress;
            $profile->join_date = date('Y-m-d H:i:s');
            
            if (!$profile->save(false)) {
                throw new \Exception('Failed to save User Profile: ' . json_encode($profile->getErrors()));
            }

            $transaction->commit();
            return $user;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("CRITICAL REGISTRATION FAILURE: " . $e->getMessage());
            // Add error to model so it shows on the page
            $this->addError('username', 'An error occurred during registration. Please try again later.');
            return false;
        }
    }
}
