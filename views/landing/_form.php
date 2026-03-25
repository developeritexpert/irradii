<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\SavedSearch;
use app\models\Post;

/* @var $this yii\web\View */
/* @var $model app\models\LandingPage */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="landing-page-form">

    <?php $form = ActiveForm::begin([
        'id' => 'landing-page-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-10\">{input}</div>\n<div class=\"col-lg-12\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-2 control-label'],
        ],
    ]); ?>

    <p class="help-block">Fields with <span class="required">*</span> are required.</p>

    <?= $form->errorSummary($model) ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'search_id')->dropDownList(
        ArrayHelper::map(SavedSearch::find()->orderBy('name')->all(), 'id', 'name'),
        ['prompt' => 'Select Search']
    ) ?>

    <?= $form->field($model, 'post_top_id')->dropDownList(
        ArrayHelper::map(Post::find()->where(['status' => Post::STATUS_PUBLISHED])->orderBy('title')->all(), 'id', 'title'),
        ['prompt' => 'Select Post (Top)']
    ) ?>

    <?= $form->field($model, 'post_bottom_id')->dropDownList(
        ArrayHelper::map(Post::find()->where(['status' => Post::STATUS_PUBLISHED])->orderBy('title')->all(), 'id', 'title'),
        ['prompt' => 'Select Post (Bottom)']
    ) ?>

    <?= $form->field($model, 'status')->dropDownList(
        [1 => 'Draft', 2 => 'Published', 3 => 'Archived'],
        ['prompt' => 'Select Status']
    ) ?>

    <div class="form-group">
        <div class="col-lg-offset-2 col-lg-10">
            <?= Html::submitButton('Submit', ['class' => 'btn btn-primary pull-right']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

