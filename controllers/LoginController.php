<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\helpers\Json;
use app\models\UserLogin;
use app\models\User;
use yii\filters\AccessControl;
use app\components\SiteHelper;

class LoginController extends Controller
{
    public $defaultAction = 'login';

    public $layout = 'irradii_main';

    /**
     * @return array behaviors
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['oauthadmin'],
                        'roles' => ['admin'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['login', 'oauth', 'logout'],
                        'roles' => ['?', '@'], // allow all (guest and logged) for these
                    ],
                    [
                        'allow' => false,
                        'actions' => ['oauthadmin'],
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'oauth' => [
                'class' => 'app\components\HOAuthAction',
            ],
        ];
    }

    /**
     * Displays the login page
     */
    public function actionLogin()
    {
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->user->isGuest) {
                $model = new UserLogin();
                
                // collect user input data
                $post = Yii::$app->request->post();
                if ($model->load($post)) {
                    // Adapt the rememberMe logic if necessary (snippet used $_POST['remember'])
                    if (isset($post['remember'])) {
                        $model->rememberMe = ($post['remember'] === 'on');
                    }

                    // validate user input and return success if valid
                    if ($model->login()) {
                        $this->lastViset();
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return ['login' => 'success'];
                    } else {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return ActiveForm::validate($model);
                    }
                }
                // display the login form modal
                return $this->renderPartial('/user/loginmodal', [
                    'model' => $model,
                    'title' => '',
                    'content' => '',
                ]);
            } else {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['login' => 'success'];
            }
        } else {
            if (Yii::$app->user->isGuest) {
                $model = new UserLogin();
                
                // collect user input data
                $post = Yii::$app->request->post();
                if ($model->load($post)) {
                    // Update rememberMe from post if it's there as 'remember'
                    if (isset($post['remember'])) {
                        $model->rememberMe = ($post['remember'] === 'on');
                    }
                    
                    $trialPeriod = isset($post['yt0']);

                    // validate user input and redirect
                    if ($model->login()) {
                        $this->lastViset();
                        
                        $forFullPaidMembersOnly = SiteHelper::forFullPaidMembersOnly(1);

                        if ($forFullPaidMembersOnly === 1) {
                            return $this->redirect(['/user/profile']);
                        } else {
                            if ($trialPeriod) {
                                return $this->redirect(Yii::$app->params['linkToBuyingSubscrFreeTrial30days'] ?? ['/site/index']);
                            } else {
                                return $this->redirect(Yii::$app->params['linkToBuyingSubscr'] ?? ['/site/index']);
                            }
                        }
                    }
                }
                // display the login form
                return $this->render('/user/login', [
                    'model' => $model,
                    'title' => '',
                    'content' => '',
                ]);
            } else {
                return $this->redirect(['/user/profile']);
            }
        }
    }

    private function lastViset()
    {
        if (!Yii::$app->user->isGuest) {
            $user = User::findOne(Yii::$app->user->id);
            if ($user) {
                $user->lastvisit_at = date('Y-m-d H:i:s');
                $user->save(false);
            }
        }
    }

    /**
     * Logs out the current user and redirects to homepage.
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }
}
