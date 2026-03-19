<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\captcha\Captcha;

/* @var $this yii\web\View */
/* @var $model app\models\ContactForm */

$this->title = 'Contact Us';
$this->params['breadcrumbs'][] = $this->title;

?>

<h1><?= Html::encode($this->title) ?></h1>

<?php if (Yii::$app->session->hasFlash('contact')): ?>

<div class="alert alert-success">
    <?= Yii::$app->session->getFlash('contact') ?>
</div>

<?php else: ?>

<p>
If you have business inquiries or other questions, please fill out the following form to contact us. Thank you.
</p>

<div class="form">

<?php $form = ActiveForm::begin([
    'id' => 'contact-form',
    'enableClientValidation' => true,
    'validateOnSubmit' => true,
]); ?>

<p class="note">
Fields with <span class="required">*</span> are required.
</p>

<?= $form->errorSummary($model) ?>

<div class="row">
<?= $form->field($model, 'name')->textInput() ?>
</div>

<div class="row">
<?= $form->field($model, 'email')->textInput() ?>
</div>

<div class="row">
<?= $form->field($model, 'subject')->textInput(['maxlength' => 128]) ?>
</div>

<div class="row">
<?= $form->field($model, 'body')->textarea(['rows' => 6]) ?>
</div>

<div class="row">

<?= $form->field($model, 'verifyCode')->widget(Captcha::class, [
    'template' => '{image} {input}',
]) ?>

<div class="hint">
Please enter the letters as they are shown in the image above.<br>
Letters are not case-sensitive.
</div>

</div>

<div class="form-group">
<?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>

</div>

<?php endif; ?>