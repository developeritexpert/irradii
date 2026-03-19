<?php
use yii\helpers\Url;
use app\components\CPathCDN;

/* @var $property_model app\models\PropertyInfo */

$slug = $property_model->slug ? $property_model->slug->slug : '';
$url = Url::to(['property/details', 'slug' => $slug], true);

$content = "<a href=\"" . $url . "\" >";
$content .= CPathCDN::checkPhoto($property_model, "thumb-img-140", 0 );
$content .= "</a>";

$discont = $property_model->getDiscontValue();

if ($discont >= Yii::$app->params['underValueDeals']) {
    $content .= '<br><span class="label bg-color-greenDark">' . round($discont) . '% Below TMV</span>';
}

echo $content;
