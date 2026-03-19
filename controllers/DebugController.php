<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\PropertyInfo;
use app\models\SavedSearch;

class DebugController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['email-alert-check'],
                        'roles' => ['admin'],
                    ],
                ],
            ],

            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionEmailAlertCheck()
    {
        $db_format = 'Y-m-d';
        $db_full_format = 'Y-m-d H:i:s';

        $date = Yii::$app->request->get('date');
        if (!$date) {
            die("no 'date' in request");
        }

        $savedSearchID = intval(Yii::$app->request->get('searchid'));
        if (!$savedSearchID) {
            die("no 'searchid' in request");
        }

        $from_data = \DateTime::createFromFormat($db_format, $date);
        $to_data = \DateTime::createFromFormat($db_format, $date);

        if (!$from_data || !$to_data) {
            die('Invalid date param');
        }

        $propertyModels = PropertyInfo::find()
            ->where("(DATE(property_uploaded_date) >= :from AND DATE(property_uploaded_date) <= :to)
                OR (DATE(property_updated_date) >= :from AND DATE(property_updated_date) <= :to)", [
                ':from' => $from_data->format($db_format),
                ':to' => $to_data->format($db_format),
            ])
            ->orderBy(['property_id' => SORT_DESC])
            ->all();

        $savedSearchModel = SavedSearch::findOne($savedSearchID);

        if (!$savedSearchModel) {
            die('Saved Search Model not Found');
        }

        foreach ($propertyModels as $propertyModel) {

            $match = $savedSearchModel->isMatch($propertyModel);

            if ($match === true) {
                echo '<span style="color:green">'.$propertyModel->property_id.': true</span><br>';
            } else {
                if ($match !== false) {
                    var_dump($match);
                    var_dump('BIG ERROR');
                }

                echo '<span style="color:gray"><small>'.$propertyModel->property_id.': false</small></span><br>';
            }
        }
    }
}