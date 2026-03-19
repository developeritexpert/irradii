<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\HttpException;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\web\Request;
use app\models\User;
use app\models\SavedSearch;
use app\models\SavedSearchEmail;
use app\models\PropertyInfo;

class SearchesController extends Controller
{
    public $layout = '//layouts/irradii';

    public $defaultAction = 'alerts';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['alerts','delete','editable'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['unsubscribe'],
                        'roles' => ['?','@'],
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [];
    }

    public function actionAlerts()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/user/login']);
        }

        $model = User::find()
            ->with(['profile','profession'])
            ->where(['id'=>Yii::$app->user->id])
            ->cache(1000)
            ->one();

        if (!is_object($model)) {
            return $this->redirect(['/user/login']);
        }

        $profile = $model->profile;

        $pageConfig = [
            'totalSearchResultTables' => 5,
            'rowsInSearchResultTable' => 3,
        ];

        $savedSearches = SavedSearch::find()
            ->where(['user_id'=>Yii::$app->user->id])
            ->with(['savedSearchCriteria','alertEmails'])
            ->orderBy(['id'=>SORT_DESC])
            ->all();

        $count = 0;
        $partials = [];

        foreach ($savedSearches as $savedSearch) {

            if ($count >= $pageConfig['totalSearchResultTables']) {
                break;
            }

            $searchResults = $savedSearch->makeSearch([
                'limit'=>$pageConfig['rowsInSearchResultTable']
            ]);

            $property_models = PropertyInfo::find()
                ->with([
                    'city','county','state','zipcode',
                    'propertyInfoAdditionalBrokerageDetails',
                    'brokerage_join','slug'
                ])
                ->where(['property_id'=>$searchResults])
                ->orderBy(['property_id'=>SORT_DESC])
                ->all();

            $partialViewData = [
                'table_header'=>$savedSearch->name,
                'property_models'=>$property_models
            ];

            $partials[] = $this->renderPartial(
                '_recentSearchResultTable',
                $partialViewData
            );

            $count++;
        }

        return $this->render('alerts',[
            'profile'=>$profile,
            'savedSearches'=>$savedSearches,
            'partials'=>$partials,
        ]);
    }

    public function actionDelete()
    {
        if (!YII_DEBUG && !Yii::$app->request->isAjax) {
            throw new HttpException(403,'Forbidden access.');
        }

        $id = Yii::$app->request->get('id');

        if (!$id) {
            throw new HttpException(404,'Missing "id" parameter.');
        }

        $model = SavedSearch::findOne($id);

        if ($model === null) {
            throw new HttpException(404,'The requested page does not exist.');
        }

        if ($model->user_id != Yii::$app->user->id) {
            throw new HttpException(403,'Forbidden access.');
        }

        $model->delete();

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'success'=>true
        ];
    }

    public function actionEditable()
    {
        if (!YII_DEBUG && !Yii::$app->request->isAjax) {
            throw new HttpException(403,'Forbidden access.');
        }

        $name = Yii::$app->request->get('name');

        if (!$name) {
            throw new HttpException(404,'Missing "name" parameter.');
        }

        switch ($name) {

            case 'name':
                $additionalResponse = $this->editableName(Yii::$app->request);
                break;

            case 'email':
                $additionalResponse = $this->editableEmail(Yii::$app->request);
                break;

            case 'email_alert_freq':
                $additionalResponse = $this->editableEmailAlertFreq(Yii::$app->request);
                break;

            case 'expiry_date':
                $additionalResponse = $this->editableExpiryDate(Yii::$app->request);
                break;

            default:
                throw new HttpException(404,'Unknown "name" parameter.');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        return $additionalResponse;
    }

    public function editableName(Request $request)
    {
        $pk = intval($request->get('pk'));
        $value = trim($request->get('value'));

        $model = SavedSearch::findOne($pk);
        $model->name = $value;

        if(!$model->validate()){
            return [
                'success'=>false,
                'errors'=>$model->getErrors()
            ];
        }

        $model->save(false);

        return [];
    }

    public function editableExpiryDate(Request $request)
    {
        $pk = intval($request->get('pk'));
        $value = trim($request->get('value'));

        $expiriDateTime = \DateTime::createFromFormat('Y-m-d',$value);

        $model = SavedSearch::findOne($pk);
        $model->expiry_date = $expiriDateTime->format('Y-m-d H:i:s');
        $model->save(false);

        return [];
    }

    public function editableEmailAlertFreq(Request $request)
    {
        $pk = intval($request->get('pk'));
        $value = trim($request->get('value'));

        $model = SavedSearch::findOne($pk);
        $model->email_alert_freq = $value;
        $model->save(false);

        return [];
    }

    public function editableEmail(Request $request)
    {
        $pk = intval($request->get('pk'));
        $value = trim($request->get('value'));

        if ($pk == 0) {
            return $this->editableAddEmail($request);
        }
        elseif ($value == '') {
            return $this->editableDeleteEmail($request);
        }
        else {
            return $this->editableUpdateEmail($request);
        }
    }

    protected function editableAddEmail(Request $request)
    {
        $saved_search_id = intval($request->get('saved_search_id'));
        $value = trim($request->get('value'));

        $model = new SavedSearchEmail();
        $model->saved_search_id = $saved_search_id;
        $model->email = $value;

        if ($model->save()) {
            return [
                'success'=>true,
                'new_id'=>$model->id
            ];
        }

        return [
            'success'=>false,
            'errors'=>$model->getErrors()
        ];
    }

    protected function editableUpdateEmail(Request $request)
    {
        $pk = intval($request->get('pk'));
        $value = trim($request->get('value'));

        $model = SavedSearchEmail::findOne($pk);
        $model->email = $value;

        if ($model->save()) {
            return ['success'=>true];
        }

        return [
            'success'=>false,
            'errors'=>$model->getErrors()
        ];
    }

    protected function editableDeleteEmail(Request $request)
    {
        $pk = intval($request->get('pk'));

        SavedSearchEmail::deleteAll(['id'=>$pk]);

        return [
            'success'=>true,
            'deleted_id'=>$pk
        ];
    }

    public function actionUnsubscribe($email)
    {
        $email = trim($email);

        if(!$email){
            return $this->redirect(['/searches/alerts']);
        }

        $user = User::find()
            ->where(['username'=>$email])
            ->one();

        if($user){

            SavedSearch::updateAll(
                ['email_alert_freq'=>SavedSearch::EMAIL_FREQ_NEVER],
                ['user_id'=>$user->id]
            );

            return $this->redirect(['/searches/alerts']);
        }

        SavedSearchEmail::deleteAll(['email'=>$email]);

        return $this->redirect(['/searches/alerts']);
    }
}