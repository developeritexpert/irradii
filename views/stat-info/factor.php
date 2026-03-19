<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Stat Factors';

?>

<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>

<h1><?= Html::encode($this->title) ?></h1>

<h2>Property #<?= $property_id ?></h2>

<?php Pjax::begin(); ?>

<div class="card">
<div class="card-body">

<?= GridView::widget([
    'dataProvider' => $property,
    'columns' => [
        'id',
        'fundamentals_factor',
        'conditional_factor',
        'property_price',
        'estimated_price',
        'comp_stage',
        'comps',
        'house_square_footage_gravity',
        'lot_footage_gravity',
        'property_type',
        'property_zipcode',
        'compass_point',
        'house_faces',
        'house_views',
        'street_name',
        'pool',
        'spa',
        'stories',
        'lot_description',
        'building_description',
        'carport_type',
        'converted_garage',
        'exterior_structure',
        'roof',
        'electrical_system',
        'plumbing_system',
    ],

    'tableOptions' => [
        'style' => 'overflow-x:auto'
    ]

]); ?>

</div>
</div>

<?php Pjax::end(); ?>