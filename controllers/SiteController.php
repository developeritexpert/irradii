<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\RegistrationForm;

class SiteController extends Controller
{
    public $layout = 'column1';

    // body properties
    public $body_ID;
    public $body_onload;
    public $title;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'error', 'sitemap', 'terms', 'logout', 'login', 'registration'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'error', 'sitemap', 'terms', 'login', 'registration'],
                        'roles' => ['?', '@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['logout'],
                        'roles' => ['@'],
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

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->redirect(['/user/profile']);
    }

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

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function actionSitemap()
    {
        $nameRedisId = Yii::$app->params['sitemapRedisId'] ?? 'siteMapCron';

        $urlsStr = Yii::$app->redis->get($nameRedisId);
        $urls = $urlsStr ? json_decode($urlsStr, true) : [];

        Yii::$app->response->format = Response::FORMAT_XML;

        return $this->renderPartial('xmlindex', [
            'urls' => $urls
        ]);
    }

    public function actionRegistration()
    {
        $model = new RegistrationForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // TODO: Save user to database here
            Yii::$app->session->setFlash('registration', 'Registration successful! Please sign in.');
            return $this->redirect(['/site/login']);
        }

        return $this->render('registration', [
            'model' => $model,
        ]);
    }

    public function actionTerms()
    {
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('termsmodal');
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}