<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\RegistrationForm;
use app\models\RegistrationStep1;

class RegistrationController extends Controller
{
    public $layout = 'irradii_main';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['agent', 'seller', 'buyer', 'investor', 'create', 'update', 'delete', 'index', 'admin'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['agent', 'seller', 'buyer', 'investor'],
                        'roles' => ['?'], // ALLOW GUESTS
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'create', 'update', 'admin', 'delete'],
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

    public function actionAgent()
    {
        return $this->processRegistration('Agent');
    }

    public function actionSeller()
    {
        return $this->processRegistration('Seller');
    }

    public function actionBuyer()
    {
        return $this->processRegistration('Buyer');
    }

    public function actionInvestor()
    {
        return $this->processRegistration('Investor');
    }

    protected function processRegistration($role)
    {
        $this->layout = 'irradii_main';
        $model = new RegistrationForm();
        $model->professionRole = $role;

        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup($role)) {
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

    // CRUD actions for admin...
    public function actionIndex()
    {
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => RegistrationStep1::find(),
        ]);
        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    public function actionView($id)
    {
        return $this->render('view', ['model' => RegistrationStep1::findOne($id)]);
    }

    public function actionDelete($id)
    {
        RegistrationStep1::findOne($id)->delete();
        return $this->redirect(['index']);
    }
}