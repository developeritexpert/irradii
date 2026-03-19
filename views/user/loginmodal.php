<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\UserLogin */
?>

<div class="well no-padding">
    <?php $form = ActiveForm::begin([
        'id' => 'login-form-modal',
        'options' => ['class' => 'smart-form client-form'],
        'enableAjaxValidation' => true,
        'action' => ['login/login'],
    ]); ?>

    <header>Sign In</header>

    <fieldset>
        <section>
            <label class="label">E-mail</label>
            <label class="input">
                <i class="icon-append fa fa-user"></i>
                <?= $form->field($model, 'username')->textInput(['placeholder' => 'Email or Username'])->label(false) ?>
            </label>
        </section>

        <section>
            <label class="label">Password</label>
            <label class="input">
                <i class="icon-append fa fa-lock"></i>
                <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Password'])->label(false) ?>
            </label>
        </section>

        <section>
            <label class="checkbox">
                <?= $form->field($model, 'rememberMe')->checkbox([], false) ?>
                <i></i>Stay signed in
            </label>
        </section>
    </fieldset>

    <footer>
        <?= Html::submitButton('Sign in', ['class' => 'btn btn-primary']) ?>
    </footer>

    <?php ActiveForm::end(); ?>
</div>
