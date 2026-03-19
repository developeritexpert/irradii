<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "property_info_additional_details".
 *
 * @property integer $property_additional_detail_id
 * @property integer $property_id
 * @property integer $over_all_property
 * @property string $exterior_grounds
 * @property string $exterior_structure
 * @property string $roof
 * @property string $ac_system
 * @property string $electrical_system
 * @property string $interior_structure
 * @property string $plumbing_system
 * @property string $kitchen
 * @property integer $bath_sink_qty
 * @property integer $bath_sink_top_qty
 * @property integer $bath_faucets_standard_qty
 * @property integer $bath_faucets_upgraded_qty
 * @property integer $bath_medicine_cabinet_qty
 * @property integer $bath_wall_mirrors_qty
 * @property integer $bath_plas_shower_surround_qty
 * @property integer $bath_shower_wall_surrounds_qty
 * @property integer $bath_shower_doorset_qty
 * @property integer $bath_tub_shower_pan_qty
 * @property integer $bath_toilet_qty
 * @property integer $bath_upgraded_kitchen_cabinet_qty
 * @property integer $bath_stand_kitchen_cabinet_qty
 * @property integer $door_replace_garage_qty
 * @property integer $door_replace_interior_qty
 * @property integer $door_replace_garage_motor_qty
 * @property integer $door_replace_new_windows_qty
 * @property integer $new_water_heater_qty
 * @property integer $kitchen_dishwasher_qty
 * @property integer $kitchen_garbage_disposal_qty
 * @property integer $kitchen_microwave_qty
 * @property integer $kitchen_refridgerator_qty
 * @property integer $kitchen_sink_faucet_qty
 * @property integer $kitchen_sink_qty
 * @property integer $kitchen_stove_qty
 * @property integer $kitchen_sink_hoods_qty
 * @property integer $flooring_carpeting_covers_per
 * @property string $floor_carpeting_covers_select
 * @property integer $floor_vinyl_covers_per
 * @property string $floor_vinyl_covers_select
 * @property integer $floor_ceramic_tile_covers_per
 * @property string $floor_ceramic_tile_covers_select
 * @property integer $floor_porcelain_tile_covers_per
 * @property string $floor_porcelain_tile_covers_select
 * @property integer $floor_stone_tile_covers_per
 * @property string $floor_stone_tile_covers_select
 * @property integer $floor_wood_pergo_covers_per
 * @property string $floor_wood_pergo_covers_select
 * @property integer $floor_other_finish_covers_per
 */
class PropertyInfoAdditionalDetails extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'property_info_additional_details';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['property_id', 'over_all_property', 'exterior_grounds', 'exterior_structure', 'roof', 'ac_system', 'electrical_system', 'interior_structure', 'plumbing_system', 'kitchen', 'bath_sink_qty', 'bath_sink_top_qty', 'bath_faucets_standard_qty', 'bath_faucets_upgraded_qty', 'bath_medicine_cabinet_qty', 'bath_wall_mirrors_qty', 'bath_plas_shower_surround_qty', 'bath_shower_wall_surrounds_qty', 'bath_shower_doorset_qty', 'bath_tub_shower_pan_qty', 'bath_toilet_qty', 'bath_upgraded_kitchen_cabinet_qty', 'bath_stand_kitchen_cabinet_qty', 'door_replace_garage_qty', 'door_replace_interior_qty', 'door_replace_garage_motor_qty', 'door_replace_new_windows_qty', 'new_water_heater_qty', 'kitchen_dishwasher_qty', 'kitchen_garbage_disposal_qty', 'kitchen_microwave_qty', 'kitchen_refridgerator_qty', 'kitchen_sink_faucet_qty', 'kitchen_sink_qty', 'kitchen_stove_qty', 'kitchen_sink_hoods_qty', 'flooring_carpeting_covers_per', 'floor_carpeting_covers_select', 'floor_vinyl_covers_per', 'floor_vinyl_covers_select', 'floor_ceramic_tile_covers_per', 'floor_ceramic_tile_covers_select', 'floor_porcelain_tile_covers_per', 'floor_porcelain_tile_covers_select', 'floor_stone_tile_covers_per', 'floor_stone_tile_covers_select', 'floor_wood_pergo_covers_per', 'floor_wood_pergo_covers_select', 'floor_other_finish_covers_per'], 'required'],
            [['property_id', 'over_all_property', 'bath_sink_qty', 'bath_sink_top_qty', 'bath_faucets_standard_qty', 'bath_faucets_upgraded_qty', 'bath_medicine_cabinet_qty', 'bath_wall_mirrors_qty', 'bath_plas_shower_surround_qty', 'bath_shower_wall_surrounds_qty', 'bath_shower_doorset_qty', 'bath_tub_shower_pan_qty', 'bath_toilet_qty', 'bath_upgraded_kitchen_cabinet_qty', 'bath_stand_kitchen_cabinet_qty', 'door_replace_garage_qty', 'door_replace_interior_qty', 'door_replace_garage_motor_qty', 'door_replace_new_windows_qty', 'new_water_heater_qty', 'kitchen_dishwasher_qty', 'kitchen_garbage_disposal_qty', 'kitchen_microwave_qty', 'kitchen_refridgerator_qty', 'kitchen_sink_faucet_qty', 'kitchen_sink_qty', 'kitchen_stove_qty', 'kitchen_sink_hoods_qty', 'flooring_carpeting_covers_per', 'floor_vinyl_covers_per', 'floor_ceramic_tile_covers_per', 'floor_porcelain_tile_covers_per', 'floor_stone_tile_covers_per', 'floor_wood_pergo_covers_per', 'floor_other_finish_covers_per'], 'integer'],
            [['exterior_grounds', 'exterior_structure', 'roof', 'ac_system', 'electrical_system', 'interior_structure', 'plumbing_system', 'kitchen', 'floor_carpeting_covers_select', 'floor_vinyl_covers_select', 'floor_ceramic_tile_covers_select', 'floor_porcelain_tile_covers_select', 'floor_stone_tile_covers_select', 'floor_wood_pergo_covers_select'], 'string', 'max' => 30],
        ];
    }

    /**
     * Gets query for [[Property]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPropertyInfo()
    {
        return $this->hasOne(PropertyInfo::class, ['property_id' => 'property_id']);
    }
}
