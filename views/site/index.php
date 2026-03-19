<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\LoginForm */

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;

?>

<h1><?= Html::encode($this->title) ?></h1>

<p>Please fill out the following form with your login credentials:</p>

<div class="site-login">

<?php $form = ActiveForm::begin([
    'id' => 'login-form',
    'enableClientValidation' => true,
]); ?>

<p class="note">
Fields with <span class="required">*</span> are required.
</p>

<div class="row">
<?= $form->field($model, 'username')->textInput() ?>
</div>

<div class="row">
<?= $form->field($model, 'password')->passwordInput() ?>

<p class="hint">
Hint: You may login with <kbd>demo</kbd>/<kbd>demo</kbd> or 
<kbd>admin</kbd>/<kbd>admin</kbd>.
</p>
</div>

<div class="row rememberMe">
<?= $form->field($model, 'rememberMe')->checkbox() ?>
</div>

<div class="form-group">
<?= Html::submitButton('Login', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>

</div>