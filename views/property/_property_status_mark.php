<?php
use app\helpers\SiteHelper;

/* @var $property_model app\models\PropertyInfo */

$details = $property_model->propertyInfoAdditionalBrokerageDetails;
if ($details && isset($details->status)) {
    $status = $details->status;
    $discont = $property_model->getDiscontValue();
    $is_discount = $discont >= Yii::$app->params['underValueDeals'];
    $colorScheme = SiteHelper::getColorScheme($status, $is_discount);
    $statusColor = $colorScheme['label-color'] ?? '';

    echo '<span class="label ' . $statusColor . '">' . $status . '</span>';
}
