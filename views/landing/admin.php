<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\LandingPage */

$session = Yii::$app->session;
$uri_page = '@' . Yii::$app->request->url;
$recent_pages = 'Manage Landing Pages' . $uri_page;
if (!isset($session['recent_pages']) || count($session['recent_pages']) == 0) {
    $session['recent_pages'] = array($recent_pages);
} else {
    $sessArr = $session['recent_pages'];
    $sessArr[] = $recent_pages;
    $session['recent_pages'] = $sessArr;
}
?>

<!-- Left panel : Navigation area -->
<?php if (!Yii::$app->user->isGuest): ?>
    <?= $this->render('/layouts/aside', ['profile' => $profile]) ?>
<?php endif; ?>
<!-- END NAVIGATION -->

<!-- MAIN PANEL -->
<div id="main" role="main">
    
    <div id="ribbon">
        <ol class="breadcrumb">
            <li>
                <a href="<?= Url::to(['/landing']) ?>">Landing</a>
            </li>
            <li>
                Manage
            </li>
        </ol>
    </div>

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
                    Manage Landing Pages
                </h1>
            </div>
        </div>

        <div id="widget-grid">
            <div class="jarviswidget" id="wid-id-0" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                <header>
                    <span class="widget-icon"> <i class="fa fa-table"></i> </span>
                    <h2>Landing Pages </h2>
                </header>
                <div>
                    <div class="jarviswidget-editbox">
                        <!-- This area used as dropdown edit box -->
                    </div>
                    <div class="pull-right" style="padding: 10px;">
                        <?= Html::a('Create Landing Page', ['create'], ['class' => 'btn btn-primary']) ?>
                    </div>

                    <?= GridView::widget([
                        'id' => 'landing-page-grid',
                        'dataProvider' => $dataProvider,
                        'filterModel' => $model,
                        'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                        'summary' => false,
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'filter' => false,
                            ],
                            [
                                'attribute' => 'title',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Html::a(Html::encode($data->title), $data->getUrl());
                                },
                            ],
                            [
                                'attribute' => 'status',
                                'value' => function ($data) {
                                    $statusMap = [1 => 'Draft', 2 => 'Published', 3 => 'Archived'];
                                    return $statusMap[$data->status] ?? 'Unknown';
                                },
                                'filter' => [1 => 'Draft', 2 => 'Published', 3 => 'Archived'],
                            ],
                            [
                                'attribute' => 'search_id',
                                'label' => 'Search Name',
                                'value' => function ($data) {
                                    return $data->search ? $data->search->name : null;
                                },
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{view} {update} {delete}',
                                'buttons' => [
                                    'view' => function ($url, $data) {
                                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', $data->getUrl(), [
                                            'title' => 'View',
                                            'target' => '_blank',
                                        ]);
                                    }
                                ],
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>

    </div>
</div>




