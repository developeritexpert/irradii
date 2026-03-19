<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\AlertsMessages */

$themePath = Yii::$app->view->theme->baseUrl;
$this->layout = '//layouts/irradii';

?>

<!-- aside -->
<?php
if (!Yii::$app->user->isGuest) {
    echo $this->render('/layouts/aside', ['profile' => $profile]);
}
?>

<div id="main" role="main" class="<?= Yii::$app->user->isGuest ? 'guest-variant' : '' ?>">

<div id="ribbon" class="<?= Yii::$app->user->isGuest ? 'ribbon-guest-variant' : '' ?>">

<span class="ribbon-button-alignment">
<span id="refresh" class="btn btn-ribbon">
<i class="fa fa-refresh"></i>
</span>
</span>

<ol class="breadcrumb">
<li><a href="/">Home</a></li>
<li>Import Email Alert Messages</li>
</ol>

</div>

<div id="content">

<div class="row">
<div class="col-md-6">
<h1 class="page-title txt-color-blueDark">
Import Email Alerts Message File
</h1>
</div>
</div>


<?php if(Yii::$app->session->hasFlash('success')): ?>

<div class="alert alert-success">
<?= Yii::$app->session->getFlash('success') ?>
</div>

<?php endif; ?>


<?php if(Yii::$app->session->hasFlash('error')): ?>

<div class="alert alert-danger">
<?= Yii::$app->session->getFlash('error') ?>
</div>

<?php endif; ?>


<div class="row">

<div class="col-md-6">

<div class="smart-form">

<?php $form = ActiveForm::begin([
'action' => '',
'method' => 'post',
'options' => ['enctype' => 'multipart/form-data']
]); ?>

<section>

<label class="label">Browse file</label>

<div class="input input-file">

<span class="button">

<?= $form->field($model, 'document')->fileInput([
'id' => 'file',
'onchange' => 'document.getElementById("avatar_picture_text_input").value = this.value'
])->label(false) ?>

Browse File

</span>

<input type="text"
id="avatar_picture_text_input"
placeholder="Upload text file in .csv format only"
readonly>

</div>

</section>

<?= Html::submitButton('Upload', [
'class' => 'btn btn-primary btn-lg'
]) ?>

<?php ActiveForm::end(); ?>

</div>

</div>

</div>

</div>

</div>