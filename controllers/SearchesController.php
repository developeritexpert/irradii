<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use app\models\User;
use app\models\SavedSearch;
use app\models\SavedSearchEmail;
use app\models\PropertyInfo;

class SearchesController extends Controller
{
    public $layout = 'irradii';
    public $defaultAction = 'alerts';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['alerts', 'delete', 'editable', 'unsubscribe'],
                'rules' => [
                    [
                        'actions' => ['alerts', 'delete', 'editable'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['unsubscribe'],
                        'allow' => true,
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    // 'delete' => ['POST'], // Yii1 didn't strict enforce POST here but good practice
                ],
            ],
        ];
    }

    public function actionAlerts()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/user/login']);
        }

        $model = User::find()
            ->with(['profile', 'profession'])
            ->where(['id' => Yii::$app->user->id])
            ->one();

        if (!$model) {
            return $this->redirect(['/user/login']);
        }
        $profile = $model->profile;

        $pageConfig = [
            'totalSearchResultTables' => 5,
            'rowsInSearchResultTable' => 3,
        ];

        $savedSearches = SavedSearch::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->orderBy(['id' => SORT_DESC])
            ->with(['savedSearchCriteria', 'alertEmails'])
            ->all();

        $count = 0;
        $partials = [];
        
        foreach ($savedSearches as $savedSearch) {
            if ($count >= $pageConfig['totalSearchResultTables']) {
                break;
            }

            $searchResults = $savedSearch->makeSearch([
                'limit' => $pageConfig['rowsInSearchResultTable']
            ]);

            // Stubs out missing relations from Phase 3: 'city', 'county', 'state', 'zipcode', 'propertyInfoAdditionalBrokerageDetails', 'brokerage_join', 'slug'
            $property_models = [];
            if (class_exists(PropertyInfo::class) && !empty($searchResults)) {
                $property_models = PropertyInfo::find()
                    ->where(['property_id' => $searchResults])
                    ->with(['slug', 'city', 'state', 'zipcode', 'propertyInfoAdditionalBrokerageDetails', 'brokerageJoin'])
                    ->orderBy(['property_id' => SORT_DESC])
                    ->all();
            }

            $partialViewData = [
                'table_header' => $savedSearch->name,
                'property_models' => $property_models,
            ];

            // In Yii2, renderPartial returns string. We can use renderAjax or renderPartial.
            $partials[] = $this->renderPartial('_recentSearchResultTable', $partialViewData);

            $count++;
        }

        $viewData = [
            'profile' => $profile,
            'savedSearches' => $savedSearches,
            'partials' => $partials,
        ];

        return $this->render('alerts', $viewData);
    }

    public function actionDelete()
    {
        if (!YII_DEBUG && !Yii::$app->request->isAjax) {
            throw new ForbiddenHttpException('Forbidden access.');
        }

        $id = Yii::$app->request->post('id') ?? Yii::$app->request->get('id');
        if (!$id) {
            throw new NotFoundHttpException('Missing "id" parameter.');
        }

        $model = SavedSearch::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if ($model->user_id != Yii::$app->user->id) {
            throw new ForbiddenHttpException('Forbidden access.');
        }

        $model->delete();

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['success' => true];
    }

    public function actionEditable()
    {
        if (!YII_DEBUG && !Yii::$app->request->isAjax) {
            throw new ForbiddenHttpException('Forbidden access.');
        }

        $name = Yii::$app->request->post('name') ?? Yii::$app->request->get('name');
        if (!$name) {
            throw new NotFoundHttpException('Missing "name" parameter.');
        }

        $additionalResponse = [];
        switch ($name) {
            case 'name':
                $additionalResponse = $this->editableName();
                break;
            case 'email':
                $additionalResponse = $this->editableEmail();
                break;
            case 'email_alert_freq':
                $additionalResponse = $this->editableEmailAlertFreq();
                break;
            case 'expiry_date':
                $additionalResponse = $this->editableExpiryDate();
                break;
            default:
                throw new NotFoundHttpException('Unknown "name" parameter.');
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $additionalResponse;
    }

    protected function editableName()
    {
        $request = Yii::$app->request;
        $pk = intval($request->post('pk') ?? $request->get('pk'));
        $value = trim($request->post('value') ?? $request->get('value'));

        $model = SavedSearch::findOne($pk);
        if($model) {
            $model->name = $value;
            if (!$model->validate()) {
                return [
                    'success' => false,
                    'errors' => $model->getErrors(),
                ];
            }
            $model->save(false);
        }

        return [];
    }

    protected function editableExpiryDate()
    {
        $request = Yii::$app->request;
        $pk = intval($request->post('pk') ?? $request->get('pk'));
        $value = trim($request->post('value') ?? $request->get('value'));

        $expiriDateTime = \DateTime::createFromFormat('Y-m-d', $value);

        $model = SavedSearch::findOne($pk);
        if($model) {
            $model->expiry_date = $expiriDateTime->format('Y-m-d H:i:s');
            $model->save(false);
        }

        return [];
    }

    protected function editableEmailAlertFreq()
    {
        $request = Yii::$app->request;
        $pk = intval($request->post('pk') ?? $request->get('pk'));
        $value = trim($request->post('value') ?? $request->get('value'));

        $model = SavedSearch::findOne($pk);
        if($model) {
            $model->email_alert_freq = $value;
            $model->save(false);
        }

        return [];
    }

    protected function editableEmail()
    {
        $request = Yii::$app->request;
        $pk = intval($request->post('pk') ?? $request->get('pk'));
        $value = trim($request->post('value') ?? $request->get('value'));

        if ($pk == 0) { // add new record
            return $this->editableAddEmail();
        } elseif ($value == '') { // delete record
            return $this->editableDeleteEmail();
        } else { // update record
            return $this->editableUpdateEmail();
        }
    }

    protected function editableAddEmail()
    {
        $request = Yii::$app->request;
        $saved_search_id = intval($request->post('saved_search_id') ?? $request->get('saved_search_id'));
        $value = trim($request->post('value') ?? $request->get('value'));

        $model = new SavedSearchEmail();
        $model->saved_search_id = $saved_search_id;
        $model->email = $value;

        if ($model->save()) {
            return [
                'success' => true,
                'new_id' => $model->id,
            ];
        } else {
            return [
                'success' => false,
                'errors' => $model->getErrors(),
            ];
        }
    }

    protected function editableUpdateEmail()
    {
        $request = Yii::$app->request;
        $pk = intval($request->post('pk') ?? $request->get('pk'));
        $value = trim($request->post('value') ?? $request->get('value'));

        $model = SavedSearchEmail::findOne($pk);
        if($model) {
            $model->email = $value;
            if ($model->save()) {
                return ['success' => true];
            } else {
                return [
                    'success' => false,
                    'errors' => $model->getErrors(),
                ];
            }
        }
        return ['success' => false];
    }

    protected function editableDeleteEmail()
    {
        $request = Yii::$app->request;
        $pk = intval($request->post('pk') ?? $request->get('pk'));

        SavedSearchEmail::deleteAll(['id' => $pk]);

        return [
            'success' => true,
            'deleted_id' => $pk,
        ];
    }

    public function actionUnsubscribe($email)
    {
        $email = trim($email);

        if (!$email) {
            return $this->redirect(['/searches/alerts']);
        }

        $user = User::findOne(['username' => $email]);

        if ($user) {
            // find saved searches by this user and set their frequency to never
            SavedSearch::updateAll(['email_alert_freq' => SavedSearch::EMAIL_FREQ_NEVER], ['user_id' => $user->id]);
            return $this->redirect(['/searches/alerts']);
        }

        SavedSearchEmail::deleteAll(['email' => $email]);

        return $this->redirect(['/searches/alerts']);
    }
}