<?php

/** @var yii\web\View $this */
/** @var app\models\Post $model */
/** @var app\models\User $profile */

use yii\helpers\Html;

$this->title = $model->title;
$this->params['breadcrumbs'][] = $model->title;
?>
<div class="post-view">

    <div class="post-header" style="margin-bottom: 20px;">
        <h1 style="color: #BA332B;"><?= Html::encode($model->title) ?></h1>
        <p class="text-muted">
            Posted on <?= date('F d, Y', $model->create_time) ?> 
            by <?= $model->author ? Html::encode($model->author->username) : 'Guest' ?>
        </p>
    </div>

    <div class="post-content" style="font-size: 16px; line-height: 1.6; margin-bottom: 40px;">
        <?= nl2br(Html::encode($model->content)) ?>
    </div>

    <?php if ($model->tags): ?>
    <div class="post-tags">
        <strong>Tags:</strong> 
        <?php foreach (explode(',', $model->tags) as $tag): ?>
            <?= Html::a(Html::encode(trim($tag)), ['index', 'tag' => trim($tag)], ['class' => 'label label-info']) ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div id="comments" style="margin-top: 50px;">
        <h3>Comments</h3>
        <p class="text-muted">Comments for this post are currently disabled.</p>
    </div>

</div>
