<?php
$files = [
    'models/MarketTrendTable.php',
    'models/CompareEstimatedPriceTable.php',
    'models/ExcludeProperty.php',
    'models/TTable2Tail.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace('<?php', "<?php\n\nnamespace app\models;\n\nuse Yii;\n", $content);
        $content = str_replace('extends CActiveRecord', 'extends \yii\db\ActiveRecord', $content);
        $content = str_replace('Yii::app()->db', 'Yii::$app->db', $content);
        $content = str_replace('Yii::app()->session', 'Yii::$app->session', $content);
        $content = str_replace('Yii::app()->user', 'Yii::$app->user', $content);
        $content = str_replace('Yii::app()->params', 'Yii::$app->params', $content);
        // Replace model()->find...
        $content = preg_replace('/self::model\(\)->find\(/', 'self::find()->where(', $content);
        $content = preg_replace('/self::model\(\)->findAll\(/', 'self::find()->where(', $content);
        file_put_contents($file, $content);
        echo "Refactored $file\n";
    }
}

$helperFile = 'components/EstimatedPrice.php';
if (file_exists($helperFile)) {
    $content = file_get_contents($helperFile);
    $content = str_replace('<?php', "<?php\n\nnamespace app\components;\n\nuse Yii;\nuse app\models\ExcludeProperty;\nuse app\models\CompareEstimatedPriceTable;\nuse app\models\TTable2Tail;\nuse app\models\MarketTrendTable;\n", $content);
    $content = str_replace('Yii::app()->db', 'Yii::$app->db', $content);
    $content = str_replace('Yii::app()->session', 'Yii::$app->session', $content);
    $content = str_replace('Yii::app()->user', 'Yii::$app->user', $content);
    $content = str_replace('Yii::app()->params', 'Yii::$app->params', $content);
    
    // Replace model() calls in Helper
    $content = preg_replace('/CompareEstimatedPriceTable::model\(\)->find\((.*?)\);/s', 'CompareEstimatedPriceTable::find()->where($1)->one();', $content);
    $content = preg_replace('/ExcludeProperty::model\(\)->find\((.*?)\);/s', 'ExcludeProperty::find()->where($1)->one();', $content);
    $content = preg_replace('/TTable2Tail::model\(\)->findByAttributes\((.*?)\);/s', 'TTable2Tail::find()->where($1)->one();', $content);
    
    file_put_contents($helperFile, $content);
    echo "Refactored $helperFile\n";
}
