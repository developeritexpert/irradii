<?php
use app\components\SiteHelper;
?>

<?php
// Relations can be null depending on what data is available for the property.
$detailsInfo = $details->propertyInfoDetails ?? null;
$brokerage = $details->propertyInfoAdditionalBrokerageDetails ?? null;
$additional = $details->propertyInfoAdditionalDetails ?? null;

$checkHttp = function($string) {
    if (empty($string)) return $string;
    return (strpos($string, 'http') === 0) ? $string : 'http://' . $string;
};
?>

<div class="col-xs-12 padding-top-10 padding-bottom-10">
    <div class="panel-group smart-accordion-default" id="accordion">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" class="collapsed font-md">
                        <i class="fa fa-lg fa-angle-down pull-right"></i>
                        <i class="fa fa-lg fa-angle-up pull-right"></i>
                        Building and Construction
                    </a>
                </h4>
            </div>
            <div id="collapseTwo" class="panel-collapse collapse">
                <div class="panel-body no-padding table-responsive">
                    <?= $this->render('/property/_property_description_item', ['array' => [
                        $details->getAttributeLabel('property_street') => $details->property_street,
                        $details->getAttributeLabel('street_number') => $details->street_number,
                        $details->getAttributeLabel('street_name') => $details->street_name,
                        $details->getAttributeLabel('building_number') => $details->building_number,
                        ($details->propertyInfoDetails->getAttributeLabel('apt_suite') ?? 'Apt Suite') => $details->propertyInfoDetails->apt_suite ?? null,
                        $details->getAttributeLabel('property_title') => $details->property_title,
                        $details->getAttributeLabel('description') => $details->description,
                        $details->getAttributeLabel('property_fetatures') => $details->property_fetatures,
                        $details->getAttributeLabel('house_square_footage') => $details->house_square_footage,
                        $details->getAttributeLabel('property_type') => $details->getPropertyTypeStr(),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('over_all_property') ?? 'Over All Property') => $details->propertyInfoAdditionalDetails->over_all_property ?? null,
                        $details->getAttributeLabel('property_id') => $details->property_id,
                        $details->getAttributeLabel('property_type_mls') => $details->property_type_mls,
                        ($details->propertyInfoDetails->getAttributeLabel('built_desc') ?? 'Built Desc') => $details->propertyInfoDetails->built_desc ?? null,
                        $details->getAttributeLabel('sub_type') => $details->sub_type,
                        $details->getAttributeLabel('building_description') => $details->building_description,
                        ($details->propertyInfoDetails->getAttributeLabel('stories') ?? 'Stories') => $details->propertyInfoDetails->stories ?? null,
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('unit_description') ?? 'Unit Description') => $details->propertyInfoAdditionalDetails->unit_description ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('model') ?? 'Model') => $details->propertyInfoDetails->model ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('unit_desc') ?? 'Unit Desc') => $details->propertyInfoDetails->unit_desc ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('compass_point') ?? 'Compass Point') => $details->propertyInfoDetails->compass_point ?? null,
                        $details->getAttributeLabel('lot_acreage') => $details->lot_acreage,
                        $details->getAttributeLabel('year_biult_id') => $details->year_biult_id,
                        $details->getAttributeLabel('elevator_floor') => $details->elevator_floor,
                        ($details->propertyInfoDetails->getAttributeLabel('prop_desc') ?? 'Prop Desc') => $details->propertyInfoDetails->prop_desc ?? null,

                        ($details->propertyInfoDetails->getAttributeLabel('studio') ?? 'Studio') => SiteHelper::forMembersOnly($details->propertyInfoDetails->studio ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('condo_conversion') ?? 'Condo Conversion') => SiteHelper::forMembersOnly($details->propertyInfoDetails->condo_conversion ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('converted_garage') ?? 'Converted Garage') => SiteHelper::forMembersOnly($details->propertyInfoDetails->converted_garage ?? null),
                        $details->getAttributeLabel('manufactured') => SiteHelper::forMembersOnly($details->manufactured ?? null),
                        $details->getAttributeLabel('converted_to_real_property') => SiteHelper::forMembersOnly($details->converted_to_real_property ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('mh_year_built') ?? 'MH Year Built') => SiteHelper::forMembersOnly($details->propertyInfoDetails->mh_year_built ?? null),
                        ($details->propertyInfoAdditionalBrokerageDetails->getAttributeLabel('exterior_construction_features') ?? 'Exterior Construction Features') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalBrokerageDetails->exterior_construction_features ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('roof') ?? 'Roof') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->roof ?? null),
                        ($details->propertyInfoAdditionalBrokerageDetails->getAttributeLabel('roofing_features') ?? 'Roofing Features') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalBrokerageDetails->roofing_features ?? null),
                        $details->getAttributeLabel('bedrooms') => $details->bedrooms,
                        ($details->propertyInfoDetails->getAttributeLabel('beds_total_poss') ?? 'Beds Total Poss') => $details->propertyInfoDetails->beds_total_poss ?? null,
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('bedroom_downstairs') ?? 'Bedroom Downstairs') => $details->propertyInfoAdditionalDetails->bedroom_downstairs ?? null,

                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('master_bedroom_description') ?? 'Master Bedroom Description') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->master_bedroom_description ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('master_bedroom_dimensions') ?? 'Master Bedroom Dimensions') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->master_bedroom_dimensions ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('bedroom_2nd_description') ?? 'Bedroom 2nd Description') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->bedroom_2nd_description ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('bedroom_2nd_dimensions') ?? 'Bedroom 2nd Dimensions') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->bedroom_2nd_dimensions ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('bedroom_3rd_description') ?? 'Bedroom 3rd Description') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->bedroom_3rd_description ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('bedroom_3rd_dimensions') ?? 'Bedroom 3rd Dimensions') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->bedroom_3rd_dimensions ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('bedroom_4th_description') ?? 'Bedroom 4th Description') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->bedroom_4th_description ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('bedroom_4th_dimensions') ?? 'Bedroom 4th Dimensions') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->bedroom_4th_dimensions ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('bedroom_5th_description') ?? 'Bedroom 5th Description') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->bedroom_5th_description ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('bedroom_5th_dimensions') ?? 'Bedroom 5th Dimensions') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->bedroom_5th_dimensions ?? null),
                        $details->getAttributeLabel('bathrooms') => $details->bathrooms,
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('full_baths') ?? 'Full Baths') => $details->propertyInfoAdditionalDetails->full_baths ?? null,
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('baths_34') ?? 'Baths 3/4') => $details->propertyInfoAdditionalDetails->baths_34 ?? null,
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('half_bath') ?? 'Half Bath') => $details->propertyInfoAdditionalDetails->half_bath ?? null,
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('master_bath_description') ?? 'Master Bath Description') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->master_bath_description ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('bath_downstairs') ?? 'Bath Downstairs') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->bath_downstairs ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('bath_downstairs_description') ?? 'Bath Downstairs Description') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->bath_downstairs_description ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('kitchen') ?? 'Kitchen') => $details->propertyInfoAdditionalDetails->kitchen ?? null,
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('kitchen_countertops') ?? 'Kitchen Countertops') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->kitchen_countertops ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('kitchen_flooring') ?? 'Kitchen Flooring') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->kitchen_flooring ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('dining_room_dimensions') ?? 'Dining Room Dimensions') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->dining_room_dimensions ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('dining_room_description') ?? 'Dining Room Description') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->dining_room_description ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('great_room') ?? 'Great Room') => $details->propertyInfoAdditionalDetails->great_room ?? null,
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('great_room_dimensions') ?? 'Great Room Dimensions') => $details->propertyInfoAdditionalDetails->great_room_dimensions ?? null,
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('living_room_dimensions') ?? 'Living Room Dimensions') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->living_room_dimensions ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('living_room_description') ?? 'Living Room Description') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->living_room_description ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('family_room_dimensions') ?? 'Family Room Dimensions') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->family_room_dimensions ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('family_room_description') ?? 'Family Room Description') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->family_room_description ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('numdenother') ?? 'Num Den Other') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->numdenother ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('denother_dimensions') ?? 'Den Other Dimensions') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->denother_dimensions ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('num_of_loft_areas') ?? 'Num of Loft Areas') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->num_of_loft_areas ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('numloft') ?? 'Num Loft') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->numloft ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('loft_dimensions') ?? 'Loft Dimensions') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->loft_dimensions ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('loft_description') ?? 'Loft Description') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->loft_description ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('loft_dimensions_1st_floor') ?? 'Loft Dimensions 1st Floor') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->loft_dimensions_1st_floor ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('loft_dimensions_2nd_floor') ?? 'Loft Dimensions 2nd Floor') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->loft_dimensions_2nd_floor ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('interior_description') ?? 'Interior Description') => $details->propertyInfoAdditionalDetails->interior_description ?? null,
                        ($details->propertyInfoAdditionalBrokerageDetails->getAttributeLabel('interior_features') ?? 'Interior Features') => $details->propertyInfoAdditionalBrokerageDetails->interior_features ?? null,
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('interior_structure') ?? 'Interior Structure') => $details->propertyInfoAdditionalDetails->interior_structure ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('interior_features') ?? 'Interior Features') => $details->propertyInfoDetails->interior_features ?? null,
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('flooring_description') ?? 'Flooring Description') => $details->propertyInfoAdditionalDetails->flooring_description ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('amenities_fireplace_id') ?? 'Amenities Fireplace') => $details->propertyInfoDetails->amenities_fireplace_id ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('fireplace_location') ?? 'Fireplace Location') => $details->propertyInfoDetails->fireplace_location ?? null,
                        ($details->propertyInfoAdditionalBrokerageDetails->getAttributeLabel('fireplace_features') ?? 'Fireplace Features') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalBrokerageDetails->fireplace_features ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('furnishings_description') ?? 'Furnishings Description') => $details->propertyInfoAdditionalDetails->furnishings_description ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('add_liv_area') ?? 'Add Liv Area') => $details->propertyInfoDetails->add_liv_area ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('total_liv_area') ?? 'Total Liv Area') => $details->propertyInfoDetails->total_liv_area ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('pool_indoor') ?? 'Pool Indoor') => $details->propertyInfoDetails->pool_indoor ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('spa_indoor') ?? 'Spa Indoor') => $details->propertyInfoDetails->spa_indoor ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('amenities_stove_id') ?? 'Amenities Stove') => $details->propertyInfoDetails->amenities_stove_id ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('amenities_refrigerator') ?? 'Amenities Refrigerator') => $details->propertyInfoDetails->amenities_refrigerator ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('refrigerator_description') ?? 'Refrigerator Description') => $details->propertyInfoDetails->refrigerator_description ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('amenities_dishwasher') ?? 'Amenities Dishwasher') => $details->propertyInfoDetails->amenities_dishwasher ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('dishwasher_description') ?? 'Dishwasher Description') => $details->propertyInfoDetails->dishwasher_description ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('amenities_washer_id') ?? 'Amenities Washer') => $details->propertyInfoDetails->amenities_washer_id ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('amenities_microwave') ?? 'Amenities Microwave') => $details->propertyInfoDetails->amenities_microwave ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('disposal_included') ?? 'Disposal Included') => $details->propertyInfoDetails->disposal_included ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('dryer_included') ?? 'Dryer Included') => $details->propertyInfoDetails->dryer_included ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('washer_dryer_location') ?? 'Washer/Dryer Location') => $details->propertyInfoDetails->washer_dryer_location ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('energy_description') ?? 'Energy Description') => SiteHelper::forMembersOnly($details->propertyInfoDetails->energy_description ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('electrical_system') ?? 'Electrical System') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->electrical_system ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('plumbing_system') ?? 'Plumbing System') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->plumbing_system ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('dryer_utilities') ?? 'Dryer Utilities') => SiteHelper::forMembersOnly($details->propertyInfoDetails->dryer_utilities ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('new_water_heater_qty') ?? 'New Water Heater Qty') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->new_water_heater_qty ?? null),
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('ac_system') ?? 'AC System') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalDetails->ac_system ?? null),
                        ($details->propertyInfoAdditionalBrokerageDetails->getAttributeLabel('heating_features') ?? 'Heating Features') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalBrokerageDetails->heating_features ?? null),
                        ($details->propertyInfoAdditionalBrokerageDetails->getAttributeLabel('exterior_features') ?? 'Exterior Features') => $details->propertyInfoAdditionalBrokerageDetails->exterior_features ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('exterior_features') ?? 'Exterior Features') => $details->propertyInfoDetails->exterior_features ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('prop_amenities_description') ?? 'Prop Amenities Description') => $details->propertyInfoDetails->prop_amenities_description ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('num_terraces') ?? 'Num Terraces') => $details->propertyInfoDetails->num_terraces ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('terrace_location') ?? 'Terrace Location') => $details->propertyInfoDetails->terrace_location ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('terrace_total_sqft') ?? 'Terrace Total Sqft') => $details->propertyInfoDetails->terrace_total_sqft ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('lot_description') ?? 'Lot Description') => $details->propertyInfoDetails->lot_description ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('lot_sqft') ?? 'Lot Sqft') => $details->propertyInfoDetails->lot_sqft ?? null,

                        ($details->propertyInfoDetails->getAttributeLabel('lot_depth') ?? 'Lot Depth') => SiteHelper::forMembersOnly($details->propertyInfoDetails->lot_depth ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('lot_frontage') ?? 'Lot Frontage') => SiteHelper::forMembersOnly($details->propertyInfoDetails->lot_frontage ?? null),

                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('exterior_grounds') ?? 'Exterior Grounds') => $details->propertyInfoAdditionalDetails->exterior_grounds ?? null,
                        ($details->propertyInfoAdditionalDetails->getAttributeLabel('exterior_structure') ?? 'Exterior Structure') => $details->propertyInfoAdditionalDetails->exterior_structure ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('fence') ?? 'Fence') => $details->propertyInfoDetails->fence ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('fence_type') ?? 'Fence Type') => $details->propertyInfoDetails->fence_type ?? null,
                        $details->getAttributeLabel('pool') => $details->pool,
                        ($details->propertyInfoDetails->getAttributeLabel('pool_description') ?? 'Pool Description') => $details->propertyInfoDetails->pool_description ?? null,

                        ($details->propertyInfoDetails->getAttributeLabel('pool_length') ?? 'Pool Length') => SiteHelper::forMembersOnly($details->propertyInfoDetails->pool_length ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('pool_width') ?? 'Pool Width') => SiteHelper::forMembersOnly($details->propertyInfoDetails->pool_width ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('spa') ?? 'Spa') => SiteHelper::forMembersOnly($details->propertyInfoDetails->spa ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('spa_description') ?? 'Spa Description') => SiteHelper::forMembersOnly($details->propertyInfoDetails->spa_description ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('equestrian_description') ?? 'Equestrian Description') => SiteHelper::forMembersOnly($details->propertyInfoDetails->equestrian_description ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('fall_spectacular') ?? 'Fall Spectacular') => SiteHelper::forMembersOnly($details->propertyInfoDetails->fall_spectacular ?? null),
                        $details->getAttributeLabel('garages') => $details->garages,
                        ($details->propertyInfoDetails->getAttributeLabel('carport_type') ?? 'Carport Type') => $details->propertyInfoDetails->carport_type ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('carport') ?? 'Carport') => $details->propertyInfoDetails->carport ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('amenities_parking_id') ?? 'Amenities Parking') => $details->propertyInfoDetails->amenities_parking_id ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('parking_spaces') ?? 'Parking Spaces') => $details->propertyInfoDetails->parking_spaces ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('parking_description') ?? 'Parking Description') => $details->propertyInfoDetails->parking_description ?? null,
                    ]]) ?>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapse16" class="collapsed font-md">
                        <i class="fa fa-lg fa-angle-down pull-right"></i>
                        <i class="fa fa-lg fa-angle-up pull-right"></i>
                        Community Features
                    </a>
                </h4>
            </div>
            <div id="collapse16" class="panel-collapse collapse">
                <div class="panel-body no-padding table-responsive">
                    <?= $this->render('/property/_property_description_item', ['array' => [
                        $details->getAttributeLabel('community_name') => $details->community_name,
                        $details->getAttributeLabel('community_features') => SiteHelper::forMembersOnly($details->community_features ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('amenities_gated_community') ?? 'Amenities Gated Community') => SiteHelper::forMembersOnly($details->propertyInfoDetails->amenities_gated_community ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('age_restricted_community') ?? 'Age Restricted Community') => SiteHelper::forMembersOnly($details->propertyInfoDetails->age_restricted_community ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('house_faces') ?? 'House Faces') => $details->propertyInfoDetails->house_faces ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('house_views') ?? 'House Views') => $details->propertyInfoDetails->house_views ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('subdivision_name_xp') ?? 'Subdivision Name XP') => $details->propertyInfoDetails->subdivision_name_xp ?? null,
                        $details->getAttributeLabel('location') => $details->location,
                        $details->getAttributeLabel('area') => $details->area,
                        $details->getAttributeLabel('subdivision') => $details->subdivision,
                        ($details->propertyInfoDetails->getAttributeLabel('town') ?? 'Town') => $details->propertyInfoDetails->town ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('city') ?? 'City') => $details->propertyInfoDetails->city ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('county') ?? 'County') => $details->propertyInfoDetails->county ?? null,
                        $details->getAttributeLabel('schools') => $details->schools,
                        ($details->propertyInfoDetails->getAttributeLabel('elementary_school') ?? 'Elementary School') => $details->propertyInfoDetails->elementary_school ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('jr_high_school') ?? 'Jr High School') => $details->propertyInfoDetails->jr_high_school ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('high_school') ?? 'High School') => $details->propertyInfoDetails->high_school ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('association_features_available') ?? 'Association Features Available') => SiteHelper::forMembersOnly($details->propertyInfoDetails->association_features_available ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('association_fee_1') ?? 'Association Fee 1') => SiteHelper::forMembersOnly($details->propertyInfoDetails->association_fee_1 ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('association_fee_1_type') ?? 'Association Fee 1 Type') => SiteHelper::forMembersOnly($details->propertyInfoDetails->association_fee_1_type ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('association_name') ?? 'Association Name') => SiteHelper::forMembersOnly($details->propertyInfoDetails->association_name ?? null),

                        ($details->propertyInfoDetails->getAttributeLabel('association_fee_includes') ?? 'Association Fee Includes') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->association_fee_includes ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('association_fee_2') ?? 'Association Fee 2') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->association_fee_2 ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('association_fee_2_type') ?? 'Association Fee 2 Type') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->association_fee_2_type ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('master_plan_fee_amount') ?? 'Master Plan Fee Amount') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->master_plan_fee_amount ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('master_plan_fee_type') ?? 'Master Plan Fee Type') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->master_plan_fee_type ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('security') ?? 'Security') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->security ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('hoa_minimum_rental_cycle') ?? 'HOA Minimum Rental Cycle') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->hoa_minimum_rental_cycle ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('services_available_on_site') ?? 'Services Available on Site') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->services_available_on_site ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('on_site_staff') ?? 'On Site Staff') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->on_site_staff ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('on_site_staff_includes') ?? 'On Site Staff Includes') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->on_site_staff_includes ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('association_phone') ?? 'Association Phone') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->association_phone ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('restrictions') ?? 'Restrictions') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->restrictions ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('maintenance') ?? 'Maintenance') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->maintenance ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('management') ?? 'Management') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->management ?? null),
                        $details->getAttributeLabel('ownership') => $details->ownership,
                        ($details->propertyInfoDetails->getAttributeLabel('subdivision_number') ?? 'Subdivision Number') => $details->propertyInfoDetails->subdivision_number ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('subdivision_num_search') ?? 'Subdivision Num Search') => $details->propertyInfoDetails->subdivision_num_search ?? null,
                        ($details->propertyInfoDetails->getAttributeLabel('assessment') ?? 'Assessment') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->assessment ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('assessment_amount') ?? 'Assessment Amount') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->assessment_amount ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('assessment_amount_type') ?? 'Assessment Amount Type') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->assessment_amount_type ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('sidlid') ?? 'SID/LID') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->sidlid ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('sidlid_annual_amount') ?? 'SID/LID Annual Amount') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->sidlid_annual_amount ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('sidlid_balance') ?? 'SID/LID Balance') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoDetails->sidlid_balance ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('metro_map_coor') ?? 'Metro Map Coor') => SiteHelper::forMembersOnly($details->propertyInfoDetails->metro_map_coor ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('metro_map_page') ?? 'Metro Map Page') => SiteHelper::forMembersOnly($details->propertyInfoDetails->metro_map_page ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('metro_map_coor_xp') ?? 'Metro Map Coor XP') => SiteHelper::forMembersOnly($details->propertyInfoDetails->metro_map_coor_xp ?? null),
                        ($details->propertyInfoDetails->getAttributeLabel('metro_map_page_xp') ?? 'Metro Map Page XP') => SiteHelper::forMembersOnly($details->propertyInfoDetails->metro_map_page_xp ?? null),
                        ($details->propertyInfoAdditionalBrokerageDetails->getAttributeLabel('parcel_num') ?? 'Parcel Num') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalBrokerageDetails->parcel_num ?? null),
                        ($details->propertyInfoAdditionalBrokerageDetails->getAttributeLabel('legal_location_range') ?? 'Legal Location Range') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoAdditionalBrokerageDetails->legal_location_range ?? null),
                        ($details->propertyInfoAdditionalBrokerageDetails->getAttributeLabel('legal_lctn_range_search') ?? 'Legal Lctn Range Search') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoAdditionalBrokerageDetails->legal_lctn_range_search ?? null),
                        ($details->propertyInfoAdditionalBrokerageDetails->getAttributeLabel('legal_location_section') ?? 'Legal Location Section') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoAdditionalBrokerageDetails->legal_location_section ?? null),
                        ($details->propertyInfoAdditionalBrokerageDetails->getAttributeLabel('legal_lctn_section_search') ?? 'Legal Lctn Section Search') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoAdditionalBrokerageDetails->legal_lctn_section_search ?? null),
                        ($details->propertyInfoAdditionalBrokerageDetails->getAttributeLabel('legal_location_township') ?? 'Legal Location Township') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoAdditionalBrokerageDetails->legal_location_township ?? null),
                        ($details->propertyInfoAdditionalBrokerageDetails->getAttributeLabel('legal_lctntownship_search') ?? 'Legal Lctntownship Search') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoAdditionalBrokerageDetails->legal_lctntownship_search ?? null),
                        ($details->propertyInfoAdditionalBrokerageDetails->getAttributeLabel('tax_district') ?? 'Tax District') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalBrokerageDetails->tax_district ?? null),
                        ($details->propertyInfoAdditionalBrokerageDetails->getAttributeLabel('assessed_imp_value') ?? 'Assessed Imp Value') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoAdditionalBrokerageDetails->assessed_imp_value ?? null),
                        ($details->propertyInfoAdditionalBrokerageDetails->getAttributeLabel('assessed_land_value') ?? 'Assessed Land Value') => SiteHelper::forFullPaidMembersOnly($details->propertyInfoAdditionalBrokerageDetails->assessed_land_value ?? null),
                        ($details->propertyInfoAdditionalBrokerageDetails->getAttributeLabel('block_number') ?? 'Block Number') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalBrokerageDetails->block_number ?? null),
                        ($details->propertyInfoAdditionalBrokerageDetails->getAttributeLabel('lot_number') ?? 'Lot Number') => SiteHelper::forMembersOnly($details->propertyInfoAdditionalBrokerageDetails->lot_number ?? null),
                    ]]) ?>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapse19" class="collapsed font-md">
                        <i class="fa fa-lg fa-angle-down pull-right"></i>
                        <i class="fa fa-lg fa-angle-up pull-right"></i>
                        Sales Information
                    </a>
                </h4>
            </div>
            <div id="collapse19" class="panel-collapse collapse">
                <div class="panel-body no-padding table-responsive">
                    <?= $this->render('/property/_property_description_item', ['array' => [
                        ($brokerage->getAttributeLabel('status') ?? 'Status') => $brokerage->status ?? null,
                        $details->getAttributeLabel('property_price') => $details->property_price,
                        $details->getAttributeLabel('mls_sysid') => SiteHelper::forMembersOnly($details->mls_sysid ?? null),
                        ($detailsInfo->getAttributeLabel('first_sale_type') ?? 'First Sale Type') => SiteHelper::forMembersOnly($detailsInfo->first_sale_type ?? null),
                        ($detailsInfo->getAttributeLabel('second_sale_type') ?? 'Second Sale Type') => SiteHelper::forMembersOnly($detailsInfo->second_sale_type ?? null),
                        ($brokerage->getAttributeLabel('list_date') ?? 'List Date') => $brokerage->list_date ?? null,
                        ($brokerage->getAttributeLabel('list_price') ?? 'List Price') => $brokerage->list_price ?? null,
                        ($brokerage->getAttributeLabel('original_list_price') ?? 'Original List Price') => $brokerage->original_list_price ?? null,
                        ($brokerage->getAttributeLabel('pricechgdate') ?? 'Price Change Date') => $brokerage->pricechgdate ?? null,
                        ($brokerage->getAttributeLabel('previous_price') ?? 'Previous Price') => $brokerage->previous_price ?? null,
                        ($brokerage->getAttributeLabel('sale_price') ?? 'Sale Price') => $brokerage->sale_price ?? null,
                        ($brokerage->getAttributeLabel('status_updates') ?? 'Status Updates') => SiteHelper::forMembersOnly($brokerage->status_updates ?? null),
                        ($brokerage->getAttributeLabel('t_status_date') ?? 'T Status Date') => SiteHelper::forMembersOnly($brokerage->t_status_date ?? null),
                        ($brokerage->getAttributeLabel('internet') ?? 'Internet') => $brokerage->internet ?? null,
                        ($brokerage->getAttributeLabel('idx') ?? 'IDX') => $brokerage->idx ?? null,
                        ($brokerage->getAttributeLabel('images') ?? 'Images') => $brokerage->images ?? null,
                        ($brokerage->getAttributeLabel('photo_excluded') ?? 'Photo Excluded') => SiteHelper::forMembersOnly($brokerage->photo_excluded ?? null),
                        ($brokerage->getAttributeLabel('last_image_trans_date') ?? 'Last Image Trans Date') => SiteHelper::forMembersOnly($brokerage->last_image_trans_date ?? null),
                        ($brokerage->getAttributeLabel('lpsqft_wcents') ?? 'Lpsqft Wcents') => SiteHelper::forMembersOnly($brokerage->lpsqft_wcents ?? null),
                        ($brokerage->getAttributeLabel('lpsqft') ?? 'Lpsqft') => SiteHelper::forMembersOnly($brokerage->lpsqft ?? null),
                        ($brokerage->getAttributeLabel('spsqft_wcents') ?? 'Spsqft Wcents') => SiteHelper::forMembersOnly($brokerage->spsqft_wcents ?? null),
                        ($brokerage->getAttributeLabel('splp') ?? 'Splp') => SiteHelper::forMembersOnly($brokerage->splp ?? null),
                        ($brokerage->getAttributeLabel('directions') ?? 'Directions') => SiteHelper::forMembersOnly($brokerage->directions ?? null),
                        ($brokerage->getAttributeLabel('contingency_desc') ?? 'Contingency Desc') => SiteHelper::forMembersOnly($brokerage->contingency_desc ?? null),
                        ($brokerage->getAttributeLabel('temp_off_mrkt_status_desc') ?? 'Temp Off Mrkt Status Desc') => SiteHelper::forMembersOnly($brokerage->temp_off_mrkt_status_desc ?? null),
                        ($brokerage->getAttributeLabel('possession_description') ?? 'Possession Description') => SiteHelper::forMembersOnly($brokerage->possession_description ?? null),
                        ($brokerage->getAttributeLabel('statuschangedate') ?? 'Status Change Date') => SiteHelper::forMembersOnly($brokerage->statuschangedate ?? null),
                        ($brokerage->getAttributeLabel('entry_date') ?? 'Entry Date') => SiteHelper::forMembersOnly($brokerage->entry_date ?? null),
                        ($brokerage->getAttributeLabel('acceptance_date') ?? 'Acceptance Date') => SiteHelper::forMembersOnly($brokerage->acceptance_date ?? null),
                        ($brokerage->getAttributeLabel('dom') ?? 'DOM') => SiteHelper::forMembersOnly($brokerage->dom ?? null),
                        ($brokerage->getAttributeLabel('active_dom') ?? 'Active DOM') => SiteHelper::forMembersOnly($brokerage->active_dom ?? null),
                        ($brokerage->getAttributeLabel('est_clolse_dt') ?? 'Est Close Dt') => SiteHelper::forMembersOnly($brokerage->est_clolse_dt ?? null),
                        ($brokerage->getAttributeLabel('actual_close_date') ?? 'Actual Close Date') => SiteHelper::forMembersOnly($brokerage->actual_close_date ?? null),
                        ($brokerage->getAttributeLabel('days_from_listing_to_close') ?? 'Days From Listing To Close') => SiteHelper::forMembersOnly($brokerage->days_from_listing_to_close ?? null),
                        ($brokerage->getAttributeLabel('package_available') ?? 'Package Available') => SiteHelper::forMembersOnly($brokerage->package_available ?? null),
                        ($brokerage->getAttributeLabel('financing_considered') ?? 'Financing Considered') => SiteHelper::forMembersOnly($brokerage->financing_considered ?? null),
                        ($brokerage->getAttributeLabel('auction_date') ?? 'Auction Date') => SiteHelper::forMembersOnly($brokerage->auction_date ?? null),
                        ($brokerage->getAttributeLabel('auction_type') ?? 'Auction Type') => SiteHelper::forMembersOnly($brokerage->auction_type ?? null),
                        ($brokerage->getAttributeLabel('additional_au_sold_terms') ?? 'Additional AU Sold Terms') => SiteHelper::forMembersOnly($brokerage->additional_au_sold_terms ?? null),
                        ($additional->getAttributeLabel('avg_sqft_amt_for_a_1_bd') ?? 'Avg Sqft Amt for 1 BD') => SiteHelper::forMembersOnly($additional->avg_sqft_amt_for_a_1_bd ?? null),
                        ($additional->getAttributeLabel('avg_sqft_amt_for_a_2_bd') ?? 'Avg Sqft Amt for 2 BD') => SiteHelper::forMembersOnly($additional->avg_sqft_amt_for_a_2_bd ?? null),
                        ($additional->getAttributeLabel('avg_sqft_amt_for_a_3_bd') ?? 'Avg Sqft Amt for 3 BD') => SiteHelper::forMembersOnly($additional->avg_sqft_amt_for_a_3_bd ?? null),
                        ($additional->getAttributeLabel('avg_sqft_amt_for_a_stud') ?? 'Avg Sqft Amt for Studio') => SiteHelper::forMembersOnly($additional->avg_sqft_amt_for_a_stud ?? null),
                        ($detailsInfo->getAttributeLabel('reference') ?? 'Reference') => $detailsInfo->reference ?? null,
                        ($brokerage->getAttributeLabel('mls_id') ?? 'MLS ID') => $brokerage->mls_id ?? null,
                        ($brokerage->getAttributeLabel('pagent_name') ?? 'Pagent Name') => $brokerage->pagent_name ?? null,
                        ($brokerage->getAttributeLabel('list_agent_public_id') ?? 'List Agent Public ID') => $brokerage->list_agent_public_id ?? null,
                        ($brokerage->getAttributeLabel('email') ?? 'Email') => $brokerage->email ?? null,
                        ($brokerage->getAttributeLabel('pagent_phone') ?? 'Pagent Phone') => $brokerage->pagent_phone ?? null,
                        ($brokerage->getAttributeLabel('pagent_phone_fax') ?? 'Pagent Phone Fax') => $brokerage->pagent_phone_fax ?? null,
                        ($brokerage->getAttributeLabel('pagent_phone_home') ?? 'Pagent Phone Home') => $brokerage->pagent_phone_home ?? null,
                        ($brokerage->getAttributeLabel('pagent_phone_mobile') ?? 'Pagent Phone Mobile') => $brokerage->pagent_phone_mobile ?? null,
                        ($brokerage->getAttributeLabel('pagent_website') ?? 'Pagent Website') => $brokerage->pagent_website ?? null,
                        ($brokerage->getAttributeLabel('page_link') ?? 'Page Link') => $checkHttp($brokerage->page_link ?? null),
                        ($brokerage->getAttributeLabel('buyer_broker_code') ?? 'Buyer Broker Code') => SiteHelper::forFullPaidMembersOnly($brokerage->buyer_broker_code ?? null),
                        ($brokerage->getAttributeLabel('buyer_agent_public_id') ?? 'Buyer Agent Public ID') => SiteHelper::forFullPaidMembersOnly($brokerage->buyer_agent_public_id ?? null),
                        ($brokerage->getAttributeLabel('lo_phone') ?? 'LO Phone') => $brokerage->lo_phone ?? null,
                        ($brokerage->getAttributeLabel('list_office_code') ?? 'List Office Code') => SiteHelper::forFullPaidMembersOnly($brokerage->list_office_code ?? null),
                        ($brokerage->getAttributeLabel('owner_licensee') ?? 'Owner Licensee') => SiteHelper::forFullPaidMembersOnly($brokerage->owner_licensee ?? null),
                        ($brokerage->getAttributeLabel('realtor') ?? 'Realtor') => SiteHelper::forFullPaidMembersOnly($brokerage->realtor ?? null),
                        ($brokerage->getAttributeLabel('sale_office_bonus') ?? 'Sale Office Bonus') => SiteHelper::forFullPaidMembersOnly($brokerage->sale_office_bonus ?? null),
                        ($brokerage->getAttributeLabel('commission_excluded') ?? 'Commission Excluded') => SiteHelper::forFullPaidMembersOnly($brokerage->commission_excluded ?? null),
                        ($brokerage->getAttributeLabel('commission_variable') ?? 'Commission Variable') => SiteHelper::forFullPaidMembersOnly($brokerage->commission_variable ?? null),
                        ($brokerage->getAttributeLabel('additional_showing') ?? 'Additional Showing') => SiteHelper::forFullPaidMembersOnly($brokerage->additional_showing ?? null),
                        ($brokerage->getAttributeLabel('ladom') ?? 'Ladom') => SiteHelper::forFullPaidMembersOnly($brokerage->ladom ?? null),
                        ($brokerage->getAttributeLabel('home_protection_plan') ?? 'Home Protection Plan') => SiteHelper::forFullPaidMembersOnly($brokerage->home_protection_plan ?? null),
                        ($brokerage->getAttributeLabel('open_house_flag') ?? 'Open House Flag') => SiteHelper::forFullPaidMembersOnly($brokerage->open_house_flag ?? null),
                        ($additional->getAttributeLabel('miscellaneous_description') ?? 'Miscellaneous Description') => $additional->miscellaneous_description ?? null,
                        $details->getAttributeLabel('property_updated_date') => SiteHelper::forFullPaidMembersOnly($details->property_updated_date ?? null),
                        $details->getAttributeLabel('property_uploaded_date') => SiteHelper::forFullPaidMembersOnly($details->property_uploaded_date ?? null),
                    ]]) ?>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#collapse20" class="collapsed font-md">
                        <i class="fa fa-lg fa-angle-down pull-right"></i>
                        <i class="fa fa-lg fa-angle-up pull-right"></i>
                        Financial Information
                    </a>
                </h4>
            </div>
            <div id="collapse20" class="panel-collapse collapse">
                <div class="panel-body no-padding table-responsive">
                    <?= $this->render('/property/_property_description_item', ['array' => [
                        ($brokerage->getAttributeLabel('foreclosure') ?? 'Foreclosure') => $brokerage->foreclosure ?? null,
                        ($brokerage->getAttributeLabel('nod_date') ?? 'NOD Date') => SiteHelper::forMembersOnly($brokerage->nod_date ?? null),
                        ($brokerage->getAttributeLabel('reporeo') ?? 'Reporeo') => $brokerage->reporeo ?? null,
                        ($brokerage->getAttributeLabel('short_sale') ?? 'Short Sale') => $brokerage->short_sale ?? null,
                        ($detailsInfo->getAttributeLabel('court_approval') ?? 'Court Approval') => SiteHelper::forMembersOnly($detailsInfo->court_approval ?? null),
                        ($brokerage->getAttributeLabel('litigation') ?? 'Litigation') => SiteHelper::forMembersOnly($brokerage->litigation ?? null),
                        ($brokerage->getAttributeLabel('litigation_type') ?? 'Litigation Type') => SiteHelper::forFullPaidMembersOnly($brokerage->litigation_type ?? null),
                        ($brokerage->getAttributeLabel('property_insurance') ?? 'Property Insurance') => SiteHelper::forFullPaidMembersOnly($brokerage->property_insurance ?? null),
                        ($brokerage->getAttributeLabel('sold_appraisal') ?? 'Sold Appraisal') => SiteHelper::forFullPaidMembersOnly($brokerage->sold_appraisal ?? null),
                        ($brokerage->getAttributeLabel('sold_down_payment') ?? 'Sold Down Payment') => SiteHelper::forFullPaidMembersOnly($brokerage->sold_down_payment ?? null),
                        ($brokerage->getAttributeLabel('earnest_deposit') ?? 'Earnest Deposit') => SiteHelper::forFullPaidMembersOnly($brokerage->earnest_deposit ?? null),
                        ($brokerage->getAttributeLabel('sellers_contribution') ?? 'Sellers Contribution') => SiteHelper::forFullPaidMembersOnly($brokerage->sellers_contribution ?? null),
                        ($brokerage->getAttributeLabel('other_encumbrance_desc') ?? 'Other Encumbrance Desc') => SiteHelper::forFullPaidMembersOnly($brokerage->other_encumbrance_desc ?? null),
                        ($brokerage->getAttributeLabel('other_income_description') ?? 'Other Income Description') => SiteHelper::forFullPaidMembersOnly($brokerage->other_income_description ?? null),
                        ($brokerage->getAttributeLabel('owner_will_carry') ?? 'Owner Will Carry') => SiteHelper::forFullPaidMembersOnly($brokerage->owner_will_carry ?? null),
                        ($brokerage->getAttributeLabel('amount_owner_will_carry') ?? 'Amount Owner Will Carry') => SiteHelper::forFullPaidMembersOnly($brokerage->amount_owner_will_carry ?? null),
                        ($brokerage->getAttributeLabel('amt_owner_will_carry') ?? 'Amt Owner Will Carry') => SiteHelper::forFullPaidMembersOnly($brokerage->amt_owner_will_carry ?? null),
                        ($brokerage->getAttributeLabel('existing_rent') ?? 'Existing Rent') => SiteHelper::forFullPaidMembersOnly($brokerage->existing_rent ?? null),
                        ($brokerage->getAttributeLabel('cap_rate') ?? 'Cap Rate') => SiteHelper::forFullPaidMembersOnly($brokerage->cap_rate ?? null),
                        ($brokerage->getAttributeLabel('cash_to_assume') ?? 'Cash To Assume') => SiteHelper::forFullPaidMembersOnly($brokerage->cash_to_assume ?? null),
                        ($brokerage->getAttributeLabel('cost_per_unit') ?? 'Cost Per Unit') => SiteHelper::forFullPaidMembersOnly($brokerage->cost_per_unit ?? null),
                        ($brokerage->getAttributeLabel('current_loan_assumable') ?? 'Current Loan Assumable') => SiteHelper::forFullPaidMembersOnly($brokerage->current_loan_assumable ?? null),
                        ($brokerage->getAttributeLabel('expense_source') ?? 'Expense Source') => SiteHelper::forFullPaidMembersOnly($brokerage->expense_source ?? null),
                        ($brokerage->getAttributeLabel('tenant_pays') ?? 'Tenant Pays') => SiteHelper::forFullPaidMembersOnly($brokerage->tenant_pays ?? null),
                        ($brokerage->getAttributeLabel('noi') ?? 'NOI') => SiteHelper::forFullPaidMembersOnly($brokerage->noi ?? null),
                        ($additional->getAttributeLabel('pet_description') ?? 'Pet Description') => SiteHelper::forFullPaidMembersOnly($additional->pet_description ?? null),
                        ($additional->getAttributeLabel('pets_allowed') ?? 'Pets Allowed') => SiteHelper::forFullPaidMembersOnly($additional->pets_allowed ?? null),
                        ($additional->getAttributeLabel('number_of_pets') ?? 'Number of Pets') => SiteHelper::forFullPaidMembersOnly($additional->number_of_pets ?? null),
                        ($brokerage->getAttributeLabel('service_contract_inc') ?? 'Service Contract Inc') => SiteHelper::forFullPaidMembersOnly($brokerage->service_contract_inc ?? null),
                        ($detailsInfo->getAttributeLabel('storage_secure') ?? 'Storage Secure') => SiteHelper::forFullPaidMembersOnly($detailsInfo->storage_secure ?? null),
                        ($detailsInfo->getAttributeLabel('storage_unit_desc') ?? 'Storage Unit Desc') => SiteHelper::forFullPaidMembersOnly($detailsInfo->storage_unit_desc ?? null),
                        ($detailsInfo->getAttributeLabel('storage_unit_dim') ?? 'Storage Unit Dim') => SiteHelper::forFullPaidMembersOnly($detailsInfo->storage_unit_dim ?? null),
                        ($detailsInfo->getAttributeLabel('storage_units_num') ?? 'Storage Units Num') => SiteHelper::forFullPaidMembersOnly($detailsInfo->storage_units_num ?? null),
                        ($additional->getAttributeLabel('studio_1_12_bath') ?? 'Studio 1 1/2 Bath') => SiteHelper::forFullPaidMembersOnly($additional->studio_1_12_bath ?? null),
                        ($additional->getAttributeLabel('studio_1_bath') ?? 'Studio 1 Bath') => SiteHelper::forFullPaidMembersOnly($additional->studio_1_bath ?? null),
                        ($additional->getAttributeLabel('studio_2_bath') ?? 'Studio 2 Bath') => SiteHelper::forFullPaidMembersOnly($additional->studio_2_bath ?? null),
                        ($brokerage->getAttributeLabel('studio_rent') ?? 'Studio Rent') => SiteHelper::forFullPaidMembersOnly($brokerage->studio_rent ?? null),
                        ($brokerage->getAttributeLabel('vacancy') ?? 'Vacancy') => SiteHelper::forFullPaidMembersOnly($brokerage->vacancy ?? null),
                        ($additional->getAttributeLabel('weight_limit') ?? 'Weight Limit') => SiteHelper::forFullPaidMembersOnly($additional->weight_limit ?? null),
                        ($brokerage->getAttributeLabel('yearly_operating_expense') ?? 'Yearly Operating Expense') => SiteHelper::forFullPaidMembersOnly($brokerage->yearly_operating_expense ?? null),
                        ($brokerage->getAttributeLabel('yearly_operating_income') ?? 'Yearly Operating Income') => SiteHelper::forFullPaidMembersOnly($brokerage->yearly_operating_income ?? null),
                        ($brokerage->getAttributeLabel('yearly_other_income') ?? 'Yearly Other Income') => SiteHelper::forFullPaidMembersOnly($brokerage->yearly_other_income ?? null),
                        ($additional->getAttributeLabel('bedroom_1_1_12_bath') ?? 'Bedroom 1 1/12 Bath') => SiteHelper::forFullPaidMembersOnly($additional->bedroom_1_1_12_bath ?? null),
                        ($additional->getAttributeLabel('bedroom_1_1_bath') ?? 'Bedroom 1 1 Bath') => SiteHelper::forFullPaidMembersOnly($additional->bedroom_1_1_bath ?? null),
                        ($additional->getAttributeLabel('bedroom_1_2_bath') ?? 'Bedroom 1 2 Bath') => SiteHelper::forFullPaidMembersOnly($additional->bedroom_1_2_bath ?? null),
                        ($additional->getAttributeLabel('bedroom_1_num_unfurn') ?? 'Bedroom 1 Num Unfurn') => SiteHelper::forFullPaidMembersOnly($additional->bedroom_1_num_unfurn ?? null),
                        ($additional->getAttributeLabel('bedroom_1_rent') ?? 'Bedroom 1 Rent') => SiteHelper::forFullPaidMembersOnly($additional->bedroom_1_rent ?? null),
                        ($additional->getAttributeLabel('bedroom_2_1_12_bath') ?? 'Bedroom 2 1/12 Bath') => SiteHelper::forFullPaidMembersOnly($additional->bedroom_2_1_12_bath ?? null),
                        ($additional->getAttributeLabel('bedroom_2_1_bath') ?? 'Bedroom 2 1 Bath') => SiteHelper::forFullPaidMembersOnly($additional->bedroom_2_1_bath ?? null),
                        ($additional->getAttributeLabel('bedroom_2_2_bath') ?? 'Bedroom 2 2 Bath') => SiteHelper::forFullPaidMembersOnly($additional->bedroom_2_2_bath ?? null),
                        ($additional->getAttributeLabel('bedroom_2_num_unfurn') ?? 'Bedroom 2 Num Unfurn') => SiteHelper::forFullPaidMembersOnly($additional->bedroom_2_num_unfurn ?? null),
                        ($additional->getAttributeLabel('bedroom_2_rent') ?? 'Bedroom 2 Rent') => SiteHelper::forFullPaidMembersOnly($additional->bedroom_2_rent ?? null),
                        ($additional->getAttributeLabel('bedroom_3_1_12_bath') ?? 'Bedroom 3 1/12 Bath') => SiteHelper::forFullPaidMembersOnly($additional->bedroom_3_1_12_bath ?? null),
                        ($additional->getAttributeLabel('bedroom_3_1_bath') ?? 'Bedroom 3 1 Bath') => SiteHelper::forFullPaidMembersOnly($additional->bedroom_3_1_bath ?? null),
                        ($additional->getAttributeLabel('bedroom_3_2_bath') ?? 'Bedroom 3 2 Bath') => SiteHelper::forFullPaidMembersOnly($additional->bedroom_3_2_bath ?? null),
                        ($additional->getAttributeLabel('bedroom_3_num_unfurn') ?? 'Bedroom 3 Num Unfurn') => SiteHelper::forFullPaidMembersOnly($additional->bedroom_3_num_unfurn ?? null),
                        ($additional->getAttributeLabel('bedroom_3_rent') ?? 'Bedroom 3 Rent') => SiteHelper::forFullPaidMembersOnly($additional->bedroom_3_rent ?? null),
                        ($brokerage->getAttributeLabel('gross_operating_income') ?? 'Gross Operating Income') => SiteHelper::forFullPaidMembersOnly($brokerage->gross_operating_income ?? null),
                        ($brokerage->getAttributeLabel('gross_rent_multiplier') ?? 'Gross Rent Multiplier') => SiteHelper::forFullPaidMembersOnly($brokerage->gross_rent_multiplier ?? null),
                        ($brokerage->getAttributeLabel('sales_history') ?? 'Sales History') => SiteHelper::forMembersOnly($brokerage->sales_history ?? null),
                        ($brokerage->getAttributeLabel('tax_history') ?? 'Tax History') => SiteHelper::forMembersOnly($brokerage->tax_history ?? null),
                        $details->getAttributeLabel('estimated_price') => $details->estimated_price,
                        $details->getAttributeLabel('percentage_depreciation_value') => $details->percentage_depreciation_value,
                        ($details->getAttributeLabel('comp_stage') ?? 'Comp Stage') => SiteHelper::forMembersOnly($details->comp_stage ?? null),
                        $details->getAttributeLabel('comps') => $details->comps,
                        $details->getAttributeLabel('low_range') => $details->low_range,
                        $details->getAttributeLabel('high_range') => $details->high_range,
                        ($details->getAttributeLabel('fundamentals_factor') ?? 'Fundamentals Factor') => SiteHelper::forMembersOnly($details->fundamentals_factor ?? null),
                        ($details->getAttributeLabel('conditional_factor') ?? 'Conditional Factor') => SiteHelper::forMembersOnly($details->conditional_factor ?? null),
                    ]]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

