<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "compare_estimated_price_table".
 *
 * @property integer $compare_estimate_id
 * @property integer $property_type
 * @property integer $stage
 * @property string $year_estimated
 * @property string $lot_estimated
 * @property string $house_estimated
 * @property string $lot_weighted
 * @property string $house_weighted
 * @property string $amenties_weighted
 * @property string $distance
 * @property integer $beds_estimated
 * @property integer $baths_estimated
 * @property integer $subdivision_comp
 * @property integer $min_comp
 * @property integer $house_views_comp
 */
class CompareEstimatedPriceTable extends ActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'compare_estimated_price_table';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['property_type', 'stage', 'year_estimated', 'lot_estimated', 'house_estimated', 'lot_weighted', 'house_weighted', 'amenties_weighted', 'distance'], 'required'],
			[['property_type', 'stage', 'beds_estimated', 'baths_estimated', 'subdivision_comp', 'min_comp', 'house_views_comp'], 'integer'],
			[['year_estimated', 'lot_estimated', 'house_estimated', 'lot_weighted', 'house_weighted', 'amenties_weighted'], 'string', 'max' => 30],
			[['distance'], 'string', 'max' => 10],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'compare_estimate_id' => 'Compare Estimate',
			'property_type' => 'Property Type',
			'stage' => 'Stage',
			'year_estimated' => 'Year Estimated',
			'lot_estimated' => 'Lot Estimated',
			'house_estimated' => 'House Estimated',
			'lot_weighted' => 'Lot Weighted',
			'house_weighted' => 'House Weighted',
			'amenties_weighted' => 'Amenties Weighted',
			'distance' => 'Distance',
			'beds_estimated' => 'Beds Estimated',
			'baths_estimated' => 'Baths Estimated',
			'subdivision_comp' => 'Subdivision Comp',
			'min_comp' => 'Min Comp',
			'house_views_comp' => 'House Views Comp',
		];
	}
}
