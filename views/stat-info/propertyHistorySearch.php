<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PropertyInfoHistorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'History Search';

$this->context->layout = 'irradii';

?>

<h1><?= Html::encode($this->title) ?></h1>

<div class="card">
<div class="card-header">
<h3>Property Info History Search</h3>
</div>

<div class="card-body">

<?php Pjax::begin(); ?>

<?= GridView::widget([
    'id' => 'property-info-history-grid',
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,

    'columns' => [

        'property_id',
        'mls_sysid',

        [
            'attribute' => 'property_info_slug',
            'value' => function ($model) {
                return $model->historySlug;
            },
            'filter' => Html::textInput(
                'PropertyInfoHistory[property_info_slug]',
                $searchModel->property_info_slug,
                ['class' => 'form-control']
            ),
        ],

        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view}',
            'buttons' => [
                'view' => function ($url, $model) {
                    return Html::a(
                        'View',
                        Url::to(['stat-info/factor', 'id' => $model->property_id]),
                        ['class' => 'btn btn-info btn-sm']
                    );
                }
            ]
        ],

        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{history}',
            'buttons' => [
                'history' => function ($url, $model) {
                    return Html::a(
                        'History',
                        Url::to([
                            'property/history',
                            'id' => $model->property_id,
                            'slug' => $model->historySlug
                        ]),
                        ['class' => 'btn btn-warning btn-sm']
                    );
                }
            ]
        ],

    ],

]); ?>

<?php Pjax::end(); ?>

</div>
</div>

<?php

$this->registerJs("
$('#property-info-history-grid table').removeClass('items');
");

?>