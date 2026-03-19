<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PropertyInfoSlug */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="property-info-slug-form">

<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'post',
]); ?>

<?= $form->field($model, 'property_id')->textInput() ?>

<?= $form->field($model, 'mls_sysid')->textInput() ?>

<div class="form-group">
    <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>

</div>