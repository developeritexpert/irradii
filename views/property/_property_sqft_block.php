<?php
/* @var $property_model app\models\PropertyInfo */

$content = '';
if ($property_model->house_square_footage) {
    $content .= $property_model->house_square_footage . ' Sq Ft<br>';
}
if ($property_model->lot_acreage) {
    $content .= $property_model->lot_acreage . ' Acre Lot<br>';
}
$content .= $property_model->getPropertyTypeStr();

echo $content;
