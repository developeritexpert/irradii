<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "market_trend_table".
 *
 * @property integer $id
 * @property integer $property_type
 * @property integer $property_zipcode
 * @property integer $t_count
 * @property string $avg_percentage_diff
 * @property string $fundamentals_factor
 * @property string $conditional_factor
 * @property string $compass_point
 * @property string $house_faces
 * @property string $house_views
 * @property string $street_name
 * @property integer $pool
 * @property string $spa
 * @property string $stories
 * @property string $lot_description
 * @property string $building_description
 * @property string $carport_type
 * @property string $converted_garage
 * @property string $exterior_structure
 * @property string $roof
 * @property string $electrical_system
 * @property string $plumbing_system
 * @property string $built_desc
 * @property string $exterior_grounds
 * @property string $prop_desc
 * @property string $over_all_property
 * @property string $foreclosure
 * @property string $short_sale
 * @property string $sub_type
 * @property integer $factor_included
 * @property string $studio
 * @property string $condo_conversion
 * @property string $association_features_available
 * @property integer $association_fee_1
 * @property string $assessment
 * @property string $sidlid
 * @property string $parking_description
 * @property string $fence_type
 * @property string $court_approval
 * @property string $bath_downstairs
 * @property string $bedroom_downstairs
 * @property string $great_room
 * @property string $bath_downstairs_description
 * @property string $flooring_description
 * @property string $furnishings_description
 * @property string $heating_features
 * @property string $possession_description
 * @property string $financing_considered
 * @property string $reporeo
 * @property string $litigation
 * @property string $created_at
 * @property string $updated_at
 */
class MarketTrendTable extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'market_trend_table';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['property_type', 'property_zipcode', 't_count', 'pool', 'factor_included', 'association_fee_1'], 'integer'],
            [['avg_percentage_diff', 'compass_point', 'house_faces'], 'string', 'max' => 5],
            [['fundamentals_factor', 'conditional_factor', 'plumbing_system', 'over_all_property'], 'string', 'max' => 12],
            [['house_views', 'lot_description', 'roof', 'electrical_system', 'exterior_grounds'], 'string', 'max' => 100],
            [['street_name', 'sub_type', 'association_features_available', 'parking_description', 'heating_features'], 'string', 'max' => 50],
            [['spa', 'converted_garage', 'foreclosure', 'short_sale', 'studio', 'condo_conversion', 'assessment', 'sidlid', 'court_approval', 'bath_downstairs', 'bedroom_downstairs', 'great_room', 'reporeo'], 'string', 'max' => 3],
            [['stories'], 'string', 'max' => 30],
            [['building_description'], 'string', 'max' => 64],
            [['carport_type'], 'string', 'max' => 16],
            [['exterior_structure'], 'string', 'max' => 10],
            [['built_desc'], 'string', 'max' => 18],
            [['prop_desc'], 'string', 'max' => 128],
            [['fence_type'], 'string', 'max' => 60],
            [['bath_downstairs_description', 'flooring_description', 'financing_considered'], 'string', 'max' => 20],
            [['furnishings_description'], 'string', 'max' => 36],
            [['possession_description'], 'string', 'max' => 25],
            [['litigation'], 'string', 'max' => 7],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'property_type' => 'Property Type',
            'property_zipcode' => 'Property Zipcode',
            't_count' => 'T Count',
            'avg_percentage_diff' => 'Avg Percentage Diff',
            'fundamentals_factor' => 'Fundamentals Factor',
            'conditional_factor' => 'Conditional Factor',
            'compass_point' => 'Compass Point',
            'house_faces' => 'House Faces',
            'house_views' => 'House Views',
            'street_name' => 'Street Name',
            'pool' => 'Pool',
            'spa' => 'Spa',
            'stories' => 'Stories',
            'lot_description' => 'Lot Description',
            'building_description' => 'Building Description',
            'carport_type' => 'Carport Type',
            'converted_garage' => 'Converted Garage',
            'exterior_structure' => 'Exterior Structure',
            'roof' => 'Roof',
            'electrical_system' => 'Electrical System',
            'plumbing_system' => 'Plumbing System',
            'built_desc' => 'Built Desc',
            'exterior_grounds' => 'Exterior Grounds',
            'prop_desc' => 'Prop Desc',
            'over_all_property' => 'Over All Property',
            'foreclosure' => 'Foreclosure',
            'short_sale' => 'Short Sale',
            'sub_type' => 'Sub Type',
            'factor_included' => 'Factor Included',
            'studio' => 'Studio',
            'condo_conversion' => 'Condo Conversion',
            'association_features_available' => 'Association Features Available',
            'association_fee_1' => 'Association Fee 1',
            'assessment' => 'Assessment',
            'sidlid' => 'Sidlid',
            'parking_description' => 'Parking Description',
            'fence_type' => 'Fence Type',
            'court_approval' => 'Court Approval',
            'bath_downstairs' => 'Bath Downstairs',
            'bedroom_downstairs' => 'Bedroom Downstairs',
            'great_room' => 'Great Room',
            'bath_downstairs_description' => 'Bath Downstairs Description',
            'flooring_description' => 'Flooring Description',
            'furnishings_description' => 'Furnishings Description',
            'heating_features' => 'Heating Features',
            'possession_description' => 'Possession Description',
            'financing_considered' => 'Financing Considered',
            'reporeo' => 'Reporeo',
            'litigation' => 'Litigation',
        ];
    }

    /**
     * @return array
     */
    public static function houseViewsList()
    {
        $rows = self::find()
            ->select('house_views')
            ->where(['and', ['not', ['house_views' => '']], ['not', ['house_views' => null]]])
            ->groupBy('house_views')
            ->asArray()
            ->all();

        return array_map(function ($row) {
            return strtolower($row['house_views']);
        }, $rows);
    }

    /**
     * @param int $id
     * @return array
     */
    public static function getPropertyInfo($id)
    {
        $details = PropertyInfo::find()
            ->with([
                'propertyInfoAdditionalBrokerageDetails',
                'propertyInfoAdditionalDetails',
                'propertyInfoDetails'
            ])
            ->where(['property_id' => $id])
            ->one();

        if (!$details) {
            return [];
        }

        return [
            'fundamentals_factor' => $details->fundamentals_factor,
            'conditional_factor' => $details->conditional_factor,
            'property_price' => $details->property_price,
            'estimated_price' => $details->estimated_price,
            'comp_stage' => $details->comp_stage,
            'comps' => $details->comps,
            'house_square_footage_gravity' => $details->house_square_footage_gravity,
            'lot_footage_gravity' => $details->lot_footage_gravity,
            'property_type' => $details->property_type,
            'property_zipcode' => $details->property_zipcode,
            'compass_point' => $details->propertyInfoDetails->compass_point ?? null,
            'house_faces' => $details->propertyInfoDetails->house_faces ?? null,
            'house_views' => $details->propertyInfoDetails->house_views ?? null,
            'street_name' => $details->property_street, // Check if street_name exists or use property_street
            'pool' => $details->pool,
            'spa' => $details->propertyInfoDetails->spa ?? null,
            'stories' => $details->propertyInfoDetails->stories ?? null,
            'lot_description' => $details->propertyInfoDetails->lot_description ?? null,
            'building_description' => $details->building_description,
            'carport_type' => $details->propertyInfoDetails->carport_type ?? null,
            'converted_garage' => $details->propertyInfoDetails->converted_garage ?? null,
            'exterior_structure' => $details->propertyInfoAdditionalDetails->exterior_structure ?? null,
            'roof' => $details->propertyInfoAdditionalDetails->roof ?? null,
            'electrical_system' => $details->propertyInfoAdditionalDetails->electrical_system ?? null,
            'plumbing_system' => $details->propertyInfoAdditionalDetails->plumbing_system ?? null,
            'built_desc' => $details->propertyInfoDetails->built_desc ?? null,
            'exterior_grounds' => $details->propertyInfoAdditionalDetails->exterior_grounds ?? null,
            'prop_desc' => $details->propertyInfoDetails->prop_desc ?? null,
            'over_all_property' => $details->propertyInfoAdditionalDetails->over_all_property ?? null,
            'foreclosure' => $details->propertyInfoAdditionalBrokerageDetails->foreclosure ?? null,
            'short_sale' => $details->propertyInfoAdditionalBrokerageDetails->short_sale ?? null,
            'sub_type' => $details->sub_type,

            'studio' => $details->propertyInfoDetails->studio ?? null,
            'condo_conversion' => $details->propertyInfoDetails->condo_conversion ?? null,
            'association_features_available' => $details->propertyInfoDetails->association_features_available ?? null,
            'association_fee_1' => $details->propertyInfoDetails->association_fee_1 ?? null,
            'assessment' => $details->propertyInfoDetails->assessment ?? null,
            'sidlid' => $details->propertyInfoDetails->sidlid ?? null,
            'parking_description' => $details->propertyInfoDetails->parking_description ?? null,
            'fence_type' => $details->propertyInfoDetails->fence_type ?? null,
            'court_approval' => $details->propertyInfoDetails->court_approval ?? null,

            'bath_downstairs' => $details->propertyInfoAdditionalDetails->bath_downstairs ?? null,
            'bedroom_downstairs' => $details->propertyInfoAdditionalDetails->bedroom_downstairs ?? null,
            'great_room' => $details->propertyInfoAdditionalDetails->great_room ?? null,
            'bath_downstairs_description' => $details->propertyInfoAdditionalDetails->bath_downstairs_description ?? null,
            'flooring_description' => $details->propertyInfoAdditionalDetails->flooring_description ?? null,
            'furnishings_description' => $details->propertyInfoAdditionalDetails->furnishings_description ?? null,

            'heating_features' => $details->propertyInfoAdditionalBrokerageDetails->heating_features ?? null,
            'possession_description' => $details->propertyInfoAdditionalBrokerageDetails->possession_description ?? null,
            'financing_considered' => $details->propertyInfoAdditionalBrokerageDetails->financing_considered ?? null,
            'reporeo' => $details->propertyInfoAdditionalBrokerageDetails->reporeo ?? null,
            'litigation' => $details->propertyInfoAdditionalBrokerageDetails->litigation ?? null,
        ];
    }

    /**
     * @param array $maths
     * @param bool $newMode
     * @return array
     */
    public static function searchFactors($maths = [], $newMode = false)
    {
        $conditions = ['and'];
        if (empty($maths)) {
            return $conditions;
        }

        foreach ($maths as $key => $value) {
            $orEmpty    = ['or', [$key => ''], [$key => null]];
            $orEmptyNum = ['or', [$key => 0], [$key => null]];

            switch ($key) {
                case 'house_views':
                case 'stories':
                case 'lot_description':
                case 'building_description':
                case 'exterior_structure':
                case 'roof':
                case 'electrical_system':
                case 'plumbing_system':
                case 'exterior_grounds':
                case 'prop_desc':
                case 'association_features_available':
                case 'flooring_description':
                case 'heating_features':
                case 'financing_considered':
                    // In Yii2 query builder, we use 'like'
                    // For the "LOWER(:key) LIKE CONCAT('%', LOWER(col), '%')" logic:
                    // This is a bit unique. It seems to check if the column value is contained within the search value.
                    $conditions[] = ['or', ['like', new Expression("LOWER('{$value}')"), new Expression("LOWER([[{$key}]])")], [$key => ''], [$key => null]];
                    break;

                case 'pool':
                case 'association_fee_1':
                    $conditions[] = ['or', [$key => $value], $orEmptyNum];
                    break;

                case 'property_type':
                    $conditions[] = [$key => $value];
                    break;

                case 'property_zipcode':
                    if (!$newMode) {
                        $conditions[] = ['or', [$key => $value], $orEmptyNum];
                    }
                    break;

                case 'property_id':
                case 'fundamentals_factor':
                case 'conditional_factor':
                case 'estimated_price':
                case 'property_price':
                case 'comp_stage':
                case 'comps':
                case 'house_square_footage_gravity':
                case 'lot_footage_gravity':
                case 'estimated_price_recalc_at':
                    break;

                default:
                    $conditions[] = ['or', [new Expression("LOWER([[{$key}]])") => strtolower($value)], $orEmpty];
                    break;
            }
        }
        return $conditions;
    }

    /**
     * @param array $property
     * @return array
     */
    public static function getFactorsRow($property)
    {
        $excludeCols = ['id', 't_count', 'avg_percentage_diff', 'fundamentals_factor', 'conditional_factor', 'factor_included', 'property_zipcode', 'factor_min', 'factor_max', 'estimated_price', 'property_price', 'factor_type', 'factor_value', 'comp_stage', 'comps', 'created_at', 'updated_at'];
        $sqlTcount = Yii::$app->params['minTcountResearch'] ?? 8;
        $whereConditions = self::searchFactors($property, true);

        $rowSum = [];
        foreach (['fundamentals_factor', 'conditional_factor'] as $factor) {
            $query0 = self::find()
                ->where(['and',
                    ['not', ['factor_value' => null]],
                    ['not', ['factor_value' => 0.0]],
                    ['or', ['property_zipcode' => 0], ['property_zipcode' => null]],
                    ['factor_type' => $factor],
                    ['>', 'factor_included', 0],
                    ['>=', 't_count', $sqlTcount]
                ])
                ->andWhere($whereConditions);
            $row0 = $query0->all();

            $row = [];
            if (!empty($property['property_zipcode'])) {
                $query = self::find()
                    ->where(['and',
                        ['not', ['factor_value' => null]],
                        ['not', ['factor_value' => 0.0]],
                        ['property_zipcode' => $property['property_zipcode']],
                        ['factor_type' => $factor],
                        ['>', 'factor_included', 0],
                        ['>=', 't_count', $sqlTcount]
                    ])
                    ->andWhere($whereConditions);
                $row = $query->all();
            }

            foreach ($row0 as $value0) {
                $equal = false;
                foreach ($row as $value) {
                    $equal = true;
                    foreach ($value->attributes as $col => $valueCol) {
                        if (in_array($col, $excludeCols)) continue;
                        if ($value0->$col !== $value->$col) {
                            $equal = false;
                            break;
                        }
                    }
                    if ($equal) break;
                }
                if (!$equal) {
                    $rowSum[] = $value0;
                }
            }
            foreach ($row as $value) {
                $rowSum[] = $value;
            }
        }

        if (!empty($rowSum)) {
            usort($rowSum, function ($a, $b) {
                return $a->id - $b->id;
            });
        }
        return $rowSum;
    }

    /**
     * @param array $property
     * @return array
     */
    public static function getFactors($property)
    {
        $excludeCols = ['id', 't_count', 'avg_percentage_diff', 'fundamentals_factor', 'conditional_factor', 'factor_included', 'property_zipcode', 'factor_min', 'factor_max', 'estimated_price', 'property_price', 'factor_type', 'factor_value', 'comp_stage', 'comps', 'created_at', 'updated_at'];
        $sqlTcount = Yii::$app->params['minTcountResearch'] ?? 8;
        $whereConditions = self::searchFactors($property, true);

        $factors = ['fundamentals_factor' => 0.0, 'conditional_factor' => 0.0];

        foreach (['fundamentals_factor', 'conditional_factor'] as $factor) {
            $rowSum = [];
            $row0 = self::find()
                ->where(['and',
                    ['not', ['factor_value' => null]],
                    ['not', ['factor_value' => 0.0]],
                    ['or', ['property_zipcode' => 0], ['property_zipcode' => null]],
                    ['factor_type' => $factor],
                    ['>', 'factor_included', 0],
                    ['>=', 't_count', $sqlTcount]
                ])
                ->andWhere($whereConditions)
                ->all();

            $row = [];
            if (!empty($property['property_zipcode'])) {
                $row = self::find()
                    ->where(['and',
                        ['not', ['factor_value' => null]],
                        ['not', ['factor_value' => 0.0]],
                        ['property_zipcode' => $property['property_zipcode']],
                        ['factor_type' => $factor],
                        ['>', 'factor_included', 0],
                        ['>=', 't_count', $sqlTcount]
                    ])
                    ->andWhere($whereConditions)
                    ->all();
            }

            if (!empty($row) && !empty($row0)) {
                foreach ($row0 as $value0) {
                    $equal = false;
                    foreach ($row as $value) {
                        $equal = true;
                        foreach ($value->attributes as $col => $valueCol) {
                            if (in_array($col, $excludeCols)) continue;
                            if ($value0->$col !== $value->$col) {
                                $equal = false;
                                break;
                            }
                        }
                        if ($equal) break;
                    }
                    if (!$equal) {
                        $rowSum[] = $value0;
                    }
                }
                foreach ($row as $value) {
                    $rowSum[] = $value;
                }
            } else {
                if (!empty($row) && empty($row0)) {
                    $rowSum = $row;
                } else {
                    $rowSum = $row0;
                }
            }

            $sum = 0;
            foreach ($rowSum as $value0) {
                $sum += $value0->factor_value;
            }
            $factors[$factor] = (float)$sum;
        }

        return $factors;
    }
}
