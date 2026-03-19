<?php
/* @var $property_model app\models\PropertyInfo */

$content = '';
$updatedDate = $property_model->getUpdatedDateViaStatus();
$content .= str_replace("-", "/", $updatedDate) . '<br>';

$datetime_now = new DateTime();
$datetime_exp = new DateTime($updatedDate);
$interval = $datetime_now->diff($datetime_exp);
$quantity = $interval->days;

$content .= ($quantity > 0) ? $quantity . ' DOM' : '0 DOM';
$content .= '<br>';

echo $content;
