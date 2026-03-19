<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "profession_field_collection".
 */
class ProfessionFieldCollection extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'profession_field_collection';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['office_logo', 'office', 'website_url', 'phone_office', 'phone_fax', 'upload_logo', 'about_me', 'tagline', 'years_of_experience_text', 'area_expertise', 'area_expertise_text'], 'string', 'max' => 5],
        ];
    }
}
