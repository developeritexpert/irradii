<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\LandingPage */

$session = Yii::$app->session;
$uri_page = '@' . Yii::$app->request->url;
$recent_pages = 'Update Landing Page' . $uri_page;
if (!isset($session['recent_pages']) || count($session['recent_pages']) == 0) {
    $session['recent_pages'] = array($recent_pages);
} else {
    $sess_arr = $session['recent_pages'];
    $sess_arr[] = $recent_pages;
    $session['recent_pages'] = $sess_arr;
}
?>

<!-- Left panel : Navigation area -->
<?php if (!Yii::$app->user->isGuest): ?>
    <?= $this->render('/layouts/aside', ['profile' => $profile]) ?>
<?php endif; ?>
<!-- END NAVIGATION -->

<!-- MAIN PANEL -->
<div id="main" role="main">
    
    <?php if (Yii::$app->session->hasFlash('profileMessage')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('profileMessage') ?>
        </div>
    <?php endif; ?>

    <!-- MAIN CONTENT -->
    <div id="content">

        <div class="row">
            <div class="col-xs-12 col-sm-7 col-md-7 col-lg-4">
                <h1 class="page-title txt-color-blueDark">
                    Update Landing Page
                </h1>
            </div>
        </div>

        <?= $this->render('_form', ['model' => $model]) ?>

    </div>
</div>
