<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PropertyInfoSlugSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Factors';
?>

<h1><?= Html::encode($this->title) ?></h1>

<div class="card">
<div class="card-header">

<?= Html::button('Advanced Search', [
    'class' => 'btn btn-primary search-button'
]) ?>

</div>

<div class="card-body">

<p>
You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>,
<b>&lt;&gt;</b> or <b>=</b>) at the beginning of each search value.
</p>

<div class="search-form" style="display:none">

<?= $this->render('_search', [
    'model' => $searchModel,
]) ?>

</div>

<?php Pjax::begin(); ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,

    'columns' => [

        'id',
        'property_id',
        'slug',

        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view}',
            'buttons' => [
                'view' => function ($url, $model) {
                    return Html::a(
                        'View',
                        ['stat-info/factor', 'id' => $model->property_id],
                        ['class' => 'btn btn-info btn-sm']
                    );
                }
            ],
        ],

    ],
]); ?>

<?php Pjax::end(); ?>

</div>
</div>

<?php
$script = <<< JS

$('.search-button').click(function(){
    $('.search-form').toggle();
});

JS;

$this->registerJs($script);
?>