<?php
/* @var $property_model app\models\PropertyInfo */

$content = '';
if ($property_model->property_price) {
    $content .= '$ ' . number_format($property_model->property_price, 0, '.', ',') . '<br>';
}
if ($property_model->estimated_price > 0) {
    $content .= 'TMV = $ ' . number_format($property_model->estimated_price, 0, '.', ',');
}

echo $content;
