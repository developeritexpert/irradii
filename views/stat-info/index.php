<?php

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Stat Infos';
?>

<h1><?= Html::encode($this->title) ?></h1>

<div class="row">
<div class="card">
<div class="card-body">

<div class="col-md-3">
    <h3>Loaded Properties <?= !empty($totalProperty) ? $totalProperty : '-' ?></h3>
</div>

<div class="col-md-3">
    <h3>Need to Recalculate <?= !empty($needRecalculate) ? $needRecalculate : '-' ?></h3>
</div>

<div class="col-md-2">
    <h3>Loading Photos <?= !empty($totalPhoto) ? $totalPhoto : '-' ?></h3>
</div>

<div class="col-md-2">
    <h3>Detecting Coords <?= !empty($totalCoord) ? $totalCoord : '-' ?></h3>
</div>

<div class="col-md-2">
    <h3>Estimating Price <?= !empty($totalPrice) ? $totalPrice : '-' ?></h3>
</div>

</div>
</div>
</div>

<br>

<h2>Loaded Properties</h2>

<div class="row">
<div class="card">
<div class="card-body">

<div class="col-md-6">

<?= GridView::widget([
    'dataProvider' => $modelProperty,
    'columns' => [
        'id',
        'property_updated_date',
        'property_expire_date',
        'count_by',
    ],
]); ?>

</div>

<div class="col-md-6">

<?= GridView::widget([
    'dataProvider' => $modelProperty1,
    'columns' => [
        'id',
        'property_uploaded_date',
        'property_expire_date',
        'count_by',
    ],
]); ?>

</div>

</div>
</div>
</div>

<br>

<h2>Recalculated Prices</h2>

<div class="row">
<div class="card">
<div class="card-body">

<?= GridView::widget([
    'dataProvider' => $modelPriceDate,
    'columns' => [
        'id',
        'estimated_price_recalc_at',
        'count_by',
    ],
]); ?>

</div>
</div>
</div>

<br>

<h2>Loading Photos</h2>

<div class="row">
<div class="card">
<div class="card-body">

<?= GridView::widget([
    'dataProvider' => $modelPhoto,
    'columns' => [
        'id',
        'mls_sysid',
        'process',
        'created_at',
        'process_at',
        'count_by',
    ],
]); ?>

</div>
</div>
</div>

<br>

<h2>Detecting Coords</h2>

<div class="row">
<div class="card">
<div class="card-body">

<?= GridView::widget([
    'dataProvider' => $modelCoord,
    'columns' => [
        'id',
        'property_street',
        'property_zipcode',
        'property_updated_date',
        'count_by',
    ],
]); ?>

</div>
</div>
</div>

<br>

<h2>Estimating Price</h2>

<div class="row">
<div class="card">
<div class="card-body">

<?= GridView::widget([
    'dataProvider' => $modelPrice,
    'columns' => [
        'id',
        'property_zipcode',
        'last_property_id',
        'created_at',
        'count_by',
    ],
]); ?>

</div>
</div>
</div>