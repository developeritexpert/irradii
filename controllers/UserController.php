<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\User;
use app\models\LoginForm;
use app\models\RegistrationForm;
use app\models\UserProfiles;
use app\models\AuthItem;
use app\models\AuthAssignment;
use app\models\ProfessionFieldCollection;
use app\models\ChangePasswordForm;
use app\components\SiteHelper;

class UserController extends Controller
{
    public $layout = 'irradii_main';
    public $body_ID;
    public $body_onload;

    public function actions()
    {
        return [
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
    }

    public function onAuthSuccess($client)
    {
        // (new AuthHandler($client))->handle();
        // Since AuthHandler is not implemented yet, just redirect to login for now
        // or implement a simple login logic if possible.
        // For now, let's at least make the action exist so no 404.
        Yii::$app->session->setFlash('info', 'Social login successful, but account linking is not yet implemented.');
        return $this->redirect(['login']);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login','index','view','registration','auth'],
                        'allow' => true,
                        'roles' => ['?'], // guests
                    ],
                    [
                        'actions' => ['profile','logout','closeinvites'],
                        'allow' => true,
                        'roles' => ['@'], // logged users
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * LOGIN PAGE
     */
public function actionLogin()
{
    $this->view->params['body_ID'] = 'login';

    if (!Yii::$app->user->isGuest) {
        return $this->redirect(['/user/profile']);
    }

    $model = new LoginForm();

    if ($model->load(Yii::$app->request->post()) && $model->login()) {
        return $this->redirect(['/user/profile']);
    }

    return $this->render('login', [
        'model' => $model,
    ]);
}

    /**
     * USER PROFILE
     */
    /**
     * USER PROFILE
     */
    public function actionProfile($id = null)
    {
        $this->layout = 'irradii_main';
        $model = ($id !== null && SiteHelper::isAdmin()) ? User::findOne($id) : Yii::$app->user->identity;

        if ($model === null) {
            return $this->redirect(['login']);
        }

        $profile = $model->profile;
        if ($profile === null) {
            $profile = new UserProfiles();
            $profile->mid = $model->id;
            $profile->save();
        }

        // Fetch all professions (Roles in tbl_AuthItem where type = 2)
        $all_profession = AuthItem::find()
            ->where(['type' => 2])
            ->andWhere(['not in', 'name', ['Admin', 'Guest', 'Superadmin', 'Authenticated']]) 
            ->all();
        
        // Fetch current user assignments from tbl_AuthAssignment
        $my_profession = AuthAssignment::find()
            ->where(['userid' => (string)$model->id])
            ->all();
        
        // Extract profession names for collection lookup
        $profession_names = [];
        foreach ($my_profession as $p) {
            $profession_names[] = $p->itemname;
        }

        // Fetch appearance configuration based on active roles
        $profession_collection = ProfessionFieldCollection::find()
            ->where(['in', 'authitem_name', $profession_names])
            ->all();

        $modelChangePassword = new ChangePasswordForm();
        
        // Check for invites in session
        $invites = Yii::$app->session->get('closeinvites', false);
        $anotherUserId = ($id !== null && SiteHelper::isAdmin()) ? $id : null;
        
        // Handle post request for profile saving...
        if ($model->load(Yii::$app->request->post()) && $profile->load(Yii::$app->request->post())) {
            if ($model->save() && $profile->save()) {
                // Save many-to-many professions (Roles in tbl_AuthAssignment)
                AuthAssignment::deleteAll(['userid' => (string)$model->id]);
                if (!empty($model->professionsArray) && is_array($model->professionsArray)) {
                    foreach ($model->professionsArray as $pName) {
                        $pAssign = new AuthAssignment();
                        $pAssign->userid = (string)$model->id;
                        $pAssign->itemname = $pName;
                        $pAssign->save();
                    }
                }
                Yii::$app->session->setFlash('profileMessage', 'Profile has been updated.');
                return $this->refresh();
            }
        }
        
        // Subscription form data (PLACEHOLDER)
        $subscr_form_data = [
            'subscriptions_left' => 10,
            'amount' => 19,
            'linkToBuyingSubscr' => '#'
        ];

        return $this->render('profile/profile', [
            'model' => $model,
            'profile' => $profile,
            'all_profession' => $all_profession,
            'my_profession' => $my_profession,
            'profession_collection' => $profession_collection,
            'modelChangePassword' => $modelChangePassword,
            'invites' => $invites,
            'anotherUserId' => $anotherUserId,
            'subscr_form_data' => $subscr_form_data,
        ]);
    }

    /**
     * AJAX action to close/ignore invites modal
     */
    public function actionCloseinvites()
    {
        Yii::$app->session->set('closeinvites', true);
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['status' => 'success'];
    }

    /**
     * REGISTRATION PAGE
     */
    public function actionRegistration()
    {
        $this->layout = 'irradii_main';
        $model = new RegistrationForm();

        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                Yii::$app->session->setFlash('registration', 'Registration successful! You can now log in.');
                return $this->redirect(['/user/login']);
            } else {
                Yii::$app->session->setFlash('registration_error', 'Your account could not be created at this time.');
            }
        }

        return $this->render('registration', [
            'model' => $model,
        ]);
    }

    /**
     * VIEW USER
     */
    public function actionView($id)
    {
        $this->body_ID = 'user-view';

        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model
        ]);
    }


    /**
     * FIND MODEL
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}