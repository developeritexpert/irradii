<?php

namespace app\controllers;

use Yii;
use app\models\Post;
use app\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

class PostController extends Controller
{
    public $layout = 'irradii_main';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'suggest-tags'],
                        'roles' => ['?', '@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create', 'update', 'delete', 'admin'],
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

    /**
     * Displays a particular post.
     */
    public function actionView($id, $title = null)
    {
        $model = $this->findModel($id);
        $user = $this->loadUserData();

        return $this->render('view', [
            'model' => $model,
            'profile' => $user,
        ]);
    }

    /**
     * Lists all posts.
     */
    public function actionIndex()
    {
        $query = Post::find()->where(['status' => Post::STATUS_PUBLISHED])->orderBy(['update_time' => SORT_DESC]);

        if ($tag = Yii::$app->request->get('tag')) {
            $query->andFilterWhere(['like', 'tags', $tag]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        $user = $this->loadUserData();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'profile' => $user,
        ]);
    }

    /**
     * Deletes a particular post.
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Loads the User data for profile display in layout.
     */
    public function loadUserData()
    {
        if (!Yii::$app->user->isGuest) {
            return User::find()
                ->with(['profile', 'profession'])
                ->where(['id' => Yii::$app->user->id])
                ->one();
        }
        return false;
    }

    /**
     * Finds the Post model based on its primary key.
     */
    protected function findModel($id)
    {
        if (($model = Post::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested post does not exist.');
    }
}
