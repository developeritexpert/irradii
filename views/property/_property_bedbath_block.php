<?php
/* @var $property_model app\models\PropertyInfo */

$content = '';
if ($property_model->bedrooms) {
    $content .= $property_model->bedrooms . " Beds/<br>";
}
if ($property_model->bathrooms) {
    $content .= $property_model->bathrooms . ' Baths/<br>';
}
if ($property_model->garages) {
    $content .= $property_model->garages . ' Car Gar';
}

echo $content;
