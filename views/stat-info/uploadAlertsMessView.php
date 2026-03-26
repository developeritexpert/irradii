<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\AlertsMessages */

$this->context->layout = 'irradii';

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
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <h1 class="page-title txt-color-blueDark">
                    Import Email Alerts Message File
                </h1>
            </div>
        </div>


        <?php if(Yii::$app->session->hasFlash('success')): ?>
            <div class="well" style="background: #dff0d8; border: 1px solid #B3E0A0;">
                <?= Yii::$app->session->getFlash('success') ?>
            </div>
        <?php endif; ?>


        <?php if(Yii::$app->session->hasFlash('error')): ?>
            <div class="well" style="background: #F3C9D3; border: 1px solid #A90329;">
                <?= Yii::$app->session->getFlash('error') ?>
            </div>
        <?php endif; ?>


        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <div class="smart-form">
                    <?= Html::beginForm('', 'post', ['enctype' => 'multipart/form-data']) ?>
                        <section>
                            <label class="label">Browse file</label>
                            <div class="input input-file">
                                <span class="button">
                                    <?= Html::activeFileInput($model, 'document', [
                                        'id' => 'file',
                                        'onchange' => 'document.getElementById("avatar_picture_text_input").value = this.value'
                                    ]) ?> Browse File
                                </span>
                                <input type="text" id="avatar_picture_text_input" placeholder="Upload text file in .csv format only" readonly="">
                            </div>
                        </section>

                        <?= Html::submitButton('Upload', ['class' => 'btn btn-primary btn-lg']) ?>
                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>

    </div>

</div>