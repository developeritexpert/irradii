<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tbl_users_profiles".
 */
class UserProfiles extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_users_profiles';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mid'], 'required'],
            [['mid', 'zipcode', 'listing_type', 'payment_type', 'timestamp', 'agent_last_login'], 'integer'],
            [['tagline', 'years_of_experience_text', 'area_expertise', 'area_expertise_text', 'about_me', 'profile_notification', 'website_notification', 'listings_notification', 'subscription'], 'string'],
            [['join_date', 'join_only_date', 'membership_expire_date', 'membership_subscription_date', 'audit_expire_date'], 'safe'],
            [['rating_average'], 'number'],
            [['first_name', 'last_name', 'middle_name'], 'string', 'max' => 50],
            [['office', 'country', 'city', 'office_logo', 'upload_logo', 'street_address', 'address2', 'state'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 20],
            [['phone_office', 'phone_fax', 'phone_home', 'phone_mobile'], 'string', 'max' => 30],
            [['website_url'], 'string', 'max' => 130],
            [['upload_photo'], 'string', 'max' => 50],
            [['agent_comments'], 'string', 'max' => 200],
            [['street_number'], 'string', 'max' => 20],
        ];
    }

    /**
     * Gets query for [[Mid]].
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'mid']);
    }
}
