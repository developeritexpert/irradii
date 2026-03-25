<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\LandingPage[] */

$this->title = Yii::$app->name . ' - Landing';

$session = Yii::$app->session;
$uri_page = '@' . Yii::$app->request->url;
$recent_pages = 'Search' . $uri_page;
if (!isset($session['recent_pages']) || count($session['recent_pages']) == 0) {
    $session['recent_pages'] = array($recent_pages);
} else {
    $sess_arr = $session['recent_pages'];
    $sess_arr[] = $recent_pages;
    $session['recent_pages'] = $sess_arr;
}

if (!Yii::$app->user->isGuest) {
    echo $this->render('/layouts/aside', array('profile' => $profile));
}
?>

<!-- MAIN PANEL -->
<div id="main" role="main">

    <div id="ribbon">
        <ol class="breadcrumb">
            <li>
                <a href="<?= Url::to(['/landing']) ?>">Landing</a>
            </li>
            <li>
                Management
            </li>
        </ol>
    </div>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <!-- MAIN CONTENT -->
    <div id="content">

        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <h1 class="page-title txt-color-blueDark">
                    Landing pages management
                </h1>
            </div>
        </div>

        <!-- widget grid -->
        <section id="widget-grid" class="">
            <div class="row">
                <article class="col-sm-12 col-md-12 col-lg-12">
                    <div class="jarviswidget" id="wid-id-2" data-widget-colorbutton="false" data-widget-editbutton="false">
                        <header class="user-profile-header">
                            <span class="widget-icon"> <i class="fa fa-eye"></i> </span>
                            <h2>Landings</h2>
                        </header>
                        <div class="form-actions">
                            <?= Html::a('<i class="fa fa-save"></i> Create', ['/landing/create'], ['class' => 'btn btn-primary btn-lg']) ?>
                        </div>
                        <div>
                            <div class="table-wrapper">
                                <table width="100%" class="table table-bordered table-striped">
                                    <tr>
                                        <th style="text-align: center">ID</th>
                                        <th width="80%" style="text-align: center;">Title</th>
                                        <th style="text-align: center;">Action</th>
                                    </tr>
                                    <?php foreach ($model as $page) : ?>
                                        <tr>
                                            <td style="text-align: center"><?= $page->id ?></td>
                                            <td width="80%" style="text-align: center;"><?= Html::encode($page->title) ?></td>
                                            <td style="text-align: center;">

                                                <?php
                                                $slug = preg_replace('~[^\pL\d]+~u', '-', $page->title);
                                                $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
                                                $slug = preg_replace('~[^-\w]+~', '', $slug);
                                                $slug = trim($slug, '-');
                                                $slug = preg_replace('~-+~', '-', $slug);
                                                $slug = strtolower($slug);
                                                if (empty($slug)) {
                                                    $slug = 'n-a';
                                                }
                                                ?>

                                                <?= Html::a('SHOW', Url::to(['/' . $slug]), [
                                                    'class' => 'btn btn-primary',
                                                    'target' => '_blank',
                                                ]) ?>
                                                <?= Html::a('EDIT', Url::to(['landing/show', 'id' => $page->id]), [
                                                    'class' => 'btn btn-warning',
                                                ]) ?>
                                                <?= Html::a('DELETE', Url::to(['landing/delete', 'id' => $page->id]), [
                                                    'class' => 'btn btn-danger',
                                                    'data' => [
                                                        'confirm' => 'Are you sure you want to delete this item?',
                                                        'method' => 'post',
                                                    ],
                                                ]) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        </div>
                        <div class="form-actions">
                             <?= Html::a('<i class="fa fa-save"></i> Create', ['/landing/create'], ['class' => 'btn btn-primary btn-lg']) ?>
                        </div>
                    </div>
                </article>
            </div>
        </section>

    </div>
</div>

