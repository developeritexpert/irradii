<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "t_table_2_tail".
 *
 * @property integer $df
 * @property double $tail_50
 * @property double $tail_60
 * @property double $tail_70
 * @property double $tail_80
 * @property double $tail_90
 * @property double $tail_95
 * @property double $tail_96
 * @property double $tail_98
 * @property double $tail_99
 * @property double $tail_99_5
 * @property double $tail_99_8
 * @property double $tail_99_9
 * @property double $tail_99_975
 * @property double $tail_99_99
 * @property double $tail_99_995
 */
class TTable2Tail extends ActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 't_table_2_tail';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['df', 'tail_50', 'tail_60', 'tail_70', 'tail_80', 'tail_90', 'tail_95', 'tail_96', 'tail_98', 'tail_99', 'tail_99_5', 'tail_99_8', 'tail_99_9', 'tail_99_975', 'tail_99_99', 'tail_99_995'], 'required'],
			[['df'], 'integer'],
			[['tail_50', 'tail_60', 'tail_70', 'tail_80', 'tail_90', 'tail_95', 'tail_96', 'tail_98', 'tail_99', 'tail_99_5', 'tail_99_8', 'tail_99_9', 'tail_99_975', 'tail_99_99', 'tail_99_995'], 'number'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'df' => 'Df',
			'tail_50' => 'Tail 50',
			'tail_60' => 'Tail 60',
			'tail_70' => 'Tail 70',
			'tail_80' => 'Tail 80',
			'tail_90' => 'Tail 90',
			'tail_95' => 'Tail 95',
			'tail_96' => 'Tail 96',
			'tail_98' => 'Tail 98',
			'tail_99' => 'Tail 99',
			'tail_99_5' => 'Tail 99 5',
			'tail_99_8' => 'Tail 99 8',
			'tail_99_9' => 'Tail 99 9',
			'tail_99_975' => 'Tail 99 975',
			'tail_99_99' => 'Tail 99 99',
			'tail_99_995' => 'Tail 99 995',
		];
	}
}
