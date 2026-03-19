<?php

use yii\helpers\Html;
use yii\grid\GridView;

$this->params['breadcrumbs'][] = UserModule::t("Users");

if (UserModule::isAdmin()) {
    $this->context->layout = '@app/views/layouts/column2';

    $this->params['menu'] = [
        ['label' => UserModule::t('Manage Users'), 'url' => ['/user/admin']],
        ['label' => UserModule::t('Manage Profile Field'), 'url' => ['profile-field/admin']],
    ];
}
?>

<h1><?= Html::encode(UserModule::t("List User")) ?></h1>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'username',
            'format' => 'raw',
            'value' => function ($data) {
                return Html::a(
                    Html::encode($data->username),
                    ['user/view', 'id' => $data->id]
                );
            },
        ],
        'create_at',
        'lastvisit_at',
    ],
]); ?>