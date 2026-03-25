<?php
use yii\helpers\Html;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$session = Yii::$app->session;
$uri_page = '@' . Yii::$app->request->url;
$recent_pages = 'Landing Pages' . $uri_page;
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
                    Landing Pages
                </h1>
            </div>
        </div>

        <?= ListView::widget([
            'dataProvider' => $dataProvider,
            'itemView' => '_view',
        ]) ?>

    </div>
</div>