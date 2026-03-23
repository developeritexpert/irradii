<?php
use app\components\SiteHelper;
?>

<?php
// Relations can be null depending on what data is available for the property.
$detailsInfo = $details->propertyInfoDetails ?? null;
$brokerage = $details->propertyInfoAdditionalBrokerageDetails ?? null;
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
                        $details->getAttributeLabel('street_number') => $details->street_number ?? null,
                        $details->getAttributeLabel('street_name') => $details->street_name ?? null,
                        $details->getAttributeLabel('house_square_footage') => $details->house_square_footage ?? null,
                        // `propertyType` is a static helper that returns an array of all types;
                        // we need a scalar label for this UI row.
                        $details->getAttributeLabel('property_type') => method_exists($details, 'getPropertyTypeStr') ? $details->getPropertyTypeStr() : ($details->property_type ?? null),
                        $details->getAttributeLabel('bedrooms') => $details->bedrooms ?? null,
                        $details->getAttributeLabel('bathrooms') => $details->bathrooms ?? null,
                        $details->getAttributeLabel('garages') => $details->garages ?? null,
                        $details->getAttributeLabel('lot_acreage') => $details->lot_acreage ?? null,
                        $details->getAttributeLabel('pool') => $details->pool ?? null,
                        $details->getAttributeLabel('spa') => $details->propertyInfoDetails->spa ?? null,
                        $details->getAttributeLabel('year_biult_id') => $details->year_biult_id ?? null,
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
                        $details->getAttributeLabel('community_name') => $details->community_name ?? null,
                        $details->getAttributeLabel('subdivision') => $details->subdivision ?? null,
                        $details->getAttributeLabel('area') => $details->area ?? null,
                        'House Faces' => $detailsInfo->house_faces ?? null,
                        'House Views' => $detailsInfo->house_views ?? null,
                        $details->getAttributeLabel('schools') => $details->schools ?? null,
                        'Gated Community' => $detailsInfo->amenities_gated_community ?? null,
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
                        'Status' => $brokerage->status ?? null,
                        $details->getAttributeLabel('property_price') => $details->property_price ?? null,
                        'List Price' => $brokerage->list_price ?? null,
                        'Original List Price' => $brokerage->original_list_price ?? null,
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
                        'Financing' => $brokerage->financing_considered ?? null,
                        $details->getAttributeLabel('estimated_price') => $details->estimated_price ?? null,
                        $details->getAttributeLabel('percentage_depreciation_value') => $details->percentage_depreciation_value ?? null,
                    ]]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

