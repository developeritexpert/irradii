<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "exclude_property".
 *
 * @property integer $exid
 * @property integer $mid
 * @property string $session_id
 * @property integer $product_id
 */
class ExcludeProperty extends ActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'exclude_property';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['mid', 'session_id', 'product_id'], 'required'],
			[['mid', 'product_id'], 'integer'],
			[['session_id'], 'string', 'max' => 50],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'exid' => 'Exid',
			'mid' => 'Mid',
			'session_id' => 'Session',
			'product_id' => 'Product',
		];
	}
}
