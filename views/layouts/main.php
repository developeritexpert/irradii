<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\assets\AppAsset;
use yii\helpers\Html;
use yii\widgets\Menu;
use yii\widgets\Breadcrumbs;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= Yii::$app->language ?>" lang="<?= Yii::$app->language ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>" />
    <meta name="language" content="en" />
    <meta name="robots" content="noindex,nofollow">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="container" id="page">

    <div id="header">
        <div id="logo"><?php echo Html::encode(Yii::$app->name); ?></div>
    </div><div id="mainmenu">
        <?php echo Menu::widget([
            'items' => [
                ['label' => 'Home', 'url' => ['/site/index']],
                ['label' => 'About', 'url' => ['/site/page', 'view' => 'about']],
                ['label' => 'Contact', 'url' => ['/site/contact']],
                ['label' => 'Login', 'url' => ['/site/login'], 'visible' => Yii::$app->user->isGuest],
                [
                    'label' => 'Logout (' . (!Yii::$app->user->isGuest ? Yii::$app->user->identity->username : '') . ')',
                    'url' => ['/site/logout'],
                    'template' => '<a href="{url}" data-method="post">{label}</a>',
                    'visible' => !Yii::$app->user->isGuest
                ],
            ],
        ]); ?>
    </div><?php if (isset($this->params['breadcrumbs'])): ?>
        <?= Breadcrumbs::widget([
            'links' => $this->params['breadcrumbs'],
        ]) ?>
    <?php endif ?>

    <div id="content">
        <?= $content ?>
    </div>

    <div class="clear"></div>

    <div id="footer">
        Copyright &copy; <?= date('Y') ?> by My Company.<br/>
        All Rights Reserved.<br/>
        <?= Yii::powered() ?>
    </div></div><?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>