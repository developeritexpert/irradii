<?php

use yii\widgets\DetailView;

$this->params['breadcrumbs'][] = [
    'label' => UserModule::t('Users'),
    'url' => ['index']
];

$this->params['breadcrumbs'][] = $model->username;

$this->context->layout = '@app/views/layouts/column2';

$this->params['menu'] = [
    ['label' => UserModule::t('List User'), 'url' => ['index']],
];

?>

<h1><?= UserModule::t('View User') . ' "' . $model->username . '"' ?></h1>

<?php

// For all users
$attributes = [
    'username',
];

$profileFields = ProfileField::find()
    ->forAll()
    ->orderBy('position')
    ->all();

if ($profileFields) {
    foreach ($profileFields as $field) {

        $attributes[] = [
            'label' => UserModule::t($field->title),
            'attribute' => $field->varname,
            'value' => (
                $field->widgetView($model->profile)
                    ? $field->widgetView($model->profile)
                    : (
                        $field->range
                        ? Profile::range($field->range, $model->profile->getAttribute($field->varname))
                        : $model->profile->getAttribute($field->varname)
                    )
            ),
        ];
    }
}

$attributes[] = 'create_at';

$attributes[] = [
    'attribute' => 'lastvisit_at',
    'value' => (
        $model->lastvisit_at != '0000-00-00 00:00:00'
        ? $model->lastvisit_at
        : UserModule::t('Not visited')
    ),
];

echo DetailView::widget([
    'model' => $model,
    'attributes' => $attributes,
]);

?>