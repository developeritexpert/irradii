<?php

use yii\widgets\Portlet;
use yii\widgets\Menu;

?>

<?php $this->beginContent('@app/views/layouts/main.php'); ?>

<div class="span-9">
    <div id="content">
        <?= $content ?>
    </div><!-- content -->
</div>

<div class="span-3 last">
    <div id="sidebar">

    <?php
    Portlet::begin([
        'title' => 'Operations',
    ]);

    echo Menu::widget([
        'items' => isset($this->params['menu']) ? $this->params['menu'] : [],
        'options' => ['class' => 'operations'],
    ]);

    Portlet::end();
    ?>

    </div><!-- sidebar -->
</div>

<?php $this->endContent(); ?>