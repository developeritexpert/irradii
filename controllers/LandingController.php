<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

use app\models\User;
use app\models\Post;
use app\models\LandingPage;
use app\models\SavedSearch;
use app\models\SavedSearchCriteria;
use app\models\MembershipOptions;
use app\models\PropertyInfo;
use app\models\SearchForm;

class LandingController extends Controller
{
    public $layout = 'irradii';

    private $_lat;
    private $_lon;

    public function behaviors()
    {
        return [

            'access' => [
                'class' => AccessControl::class,
                'rules' => [

                    [
                        'allow' => true,
                        'actions' => ['landing'],
                        'roles' => ['?', '@'],
                    ],

                    [
                        'allow' => true,
                        'actions' => ['update','delete','create','show','index'],
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

    public function actionIndex()
    {
        $user = null;
        $profile = null;
        $user_id = null;

        if (!Yii::$app->user->isGuest) {

            $user = User::find()
                ->with(['profile','profession'])
                ->where(['id'=>Yii::$app->user->id])
                ->one();

            $profile = $user->profile;
            $user_id = $user->id;
        }

        $model = LandingPage::find()->all();

        return $this->render('page/view',[
            'user_id'=>$user_id,
            'profile'=>$profile,
            'user'=>$user,
            'model'=>$model
        ]);
    }

    public function actionDelete($id)
    {
        $modelLanding = LandingPage::findOne($id);

        if($modelLanding){
            $modelLanding->delete();
        }

        Yii::$app->session->setFlash('success',"Deleted successfully!");

        return $this->redirect(['/landing']);
    }

    public function actionUpload()
    {
        if(Yii::$app->request->isPost){

            $uploadedFile = UploadedFile::getInstanceByName('file');

            $rnd = rand(0,9876543210);
            $timeStamp = time();

            $fileName = "{$rnd}-{$timeStamp}-".$uploadedFile->name;

            $uploadedFile->saveAs(Yii::getAlias('@webroot').'/upload/'.$fileName);

            return json_encode([
                'location'=>Yii::$app->request->baseUrl.'/upload/'.$fileName
            ]);
        }

        return json_encode(['status'=>'400 Bad Request']);
    }

    public function loadUserData()
    {
        if(Yii::$app->user->id){

            $_user = User::find()
                ->with(['profile','profession'])
                ->where(['id'=>Yii::$app->user->id])
                ->one();

        }else{
            $_user = false;
        }

        return $_user;
    }

    public function slugify($text)
    {
        $text = preg_replace('~[^\pL\d]+~u','-',$text);
        $text = iconv('utf-8','us-ascii//TRANSLIT',$text);
        $text = preg_replace('~[^-\w]+~','',$text);
        $text = trim($text,'-');
        $text = preg_replace('~-+~','-',$text);
        $text = strtolower($text);

        if(empty($text)){
            return 'n-a';
        }

        return $text;
    }

}