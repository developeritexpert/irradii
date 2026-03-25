<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\LandingPage */
?>

<div class="view">
    <?= Html::a(Html::encode($model->title), $model->getUrl()) ?>

    <?php /*/ ?>
    <b><?= Html::encode($model->getAttributeLabel('id')) ?>:</b>
    <?= Html::a(Html::encode($model->id), ['view', 'id' => $model->id]) ?>
    <br />

    <b><?= Html::encode($model->getAttributeLabel('title')) ?>:</b>
    <?= Html::encode($model->title) ?>
    <br />

    <b><?= Html::encode($model->getAttributeLabel('status')) ?>:</b>
    <?= Html::encode($model->status) ?>
    <br />

    <b><?= Html::encode($model->getAttributeLabel('search_id')) ?>:</b>
    <?= Html::encode($model->search_id) ?>
    <br />

    <b><?= Html::encode($model->getAttributeLabel('post_top_id')) ?>:</b>
    <?= Html::encode($model->post_top_id) ?>
    <br />

    <b><?= Html::encode($model->getAttributeLabel('post_bottom_id')) ?>:</b>
    <?= Html::encode($model->post_bottom_id) ?>
    <br />

    <b><?= Html::encode($model->getAttributeLabel('created_at')) ?>:</b>
    <?= Html::encode($model->created_at) ?>
    <br />
    <?php /*/ ?>
</div>