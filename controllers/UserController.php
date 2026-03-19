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
                        'actions' => ['profile','logout'],
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

        // Fetch professions (Removied per user request)
        $all_profession = [];
        $my_profession = [];
        $profession_collection = [];

        $modelChangePassword = new ChangePasswordForm();
        
        // Check for invites and other flags
        $invites = true; // Temporary
        $anotherUserId = ($id !== null && SiteHelper::isAdmin()) ? $id : null;
        
        // Temporary subscr_form_data
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