<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;

use app\models\User;
use app\models\Post;
use app\models\LandingPage;
use app\models\SavedSearch;
use app\models\SavedSearchCriteria;
use app\models\MembershipOptions;
use app\models\PropertyInfo;
use app\models\SearchForm;
use app\components\SiteHelper;

class LandingController extends Controller
{
    public $layout = 'irradii';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['landing'],
                        'roles' => ['?', '@'], // Allow guests and authenticated users
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'admin', 'create', 'show', 'update', 'delete', 'upload'],
                        'roles' => ['@'], // Authenticated users for administrative actions
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
     * Lists all LandingPage models.
     */
    public function actionIndex()
    {
        $allPages = LandingPage::find()->all();
        $user = $this->loadUserData();
        $profile = $user ? $user->profile : null;

        return $this->render('view', [
            'model' => $allPages,
            'profile' => $profile,
            'user' => $user,
        ]);
    }

    public function actionAdmin()
    {
        return $this->actionIndex();
    }

    /**
     * Public Landing Page view.
     */
    public function actionLanding($slug = null)
    {
        if ($slug === null) {
            $slug = Yii::$app->request->pathInfo;
        }

        $modelLanding = null;
        $allLandings = LandingPage::find()->all();
        foreach ($allLandings as $model) {
            if ($this->slugify($model->title) === $this->slugify($slug)) {
                $modelLanding = $model;
                break;
            }
        }

        if (!$modelLanding) {
            // Check if it's an ID
            if (is_numeric($slug)) {
                $modelLanding = LandingPage::findOne($slug);
            }
        }

        if (!$modelLanding) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $user = $this->loadUserData();
        $profile = $user ? $user->profile : null;
        $user_id = $user ? $user->id : null;

        $membershipOptions = $modelLanding->membershipOptions;
        
        // Load search results logic from legacy
        $search_results = [
            'count_result' => 0,
            'result' => [],
            'status' => 'failed'
        ];

        if ($modelLanding->search_id) {
            $search_criteria = [];
            $criteria_raw = SavedSearchCriteria::find()->where(['saved_search_id' => $modelLanding->search_id])->all();
            foreach ($criteria_raw as $item) {
                $search_criteria[$item->attr_name] = @unserialize($item->attr_value);
            }

            $query = PropertyInfo::find()->with([
                'city', 'state', 'zipcode', 'slug', 'propertyInfoPhoto',
                'propertyInfoAdditionalBrokerageDetails', 'propertyInfoDetails'
            ]);

            // Apply criteria (simplified mapping from property/search)
            if (!empty($search_criteria['address'])) {
                $query->andWhere(['like', 'property_info.property_street', $search_criteria['address']]);
            }
            if (!empty($search_criteria['city'])) {
                $query->joinWith('city')->andWhere(['city.city_name' => $search_criteria['city']]);
            }
            if (!empty($search_criteria['state'])) {
                $query->joinWith('state')->andWhere(['state.state_code' => $search_criteria['state']]);
            }
            if (!empty($search_criteria['zipcode'])) {
                $query->andWhere(['property_info.property_zipcode' => $search_criteria['zipcode']]);
            }
            if (!empty($search_criteria['min_price'])) {
                $query->andWhere(['>=', 'property_info.property_price', (int)$search_criteria['min_price']]);
            }
            if (!empty($search_criteria['max_price'])) {
                $query->andWhere(['<=', 'property_info.property_price', (int)$search_criteria['max_price']]);
            }

            $count = (int)$query->count();
            $results = $query->limit(200)->all();
            
            $search_results = [
                'status' => ($count > 0) ? 'success' : 'nothing',
                'count_result' => $count,
                'latlon' => SiteHelper::getLatLonResult($results),
                'result' => SiteHelper::getSearchMapResult($results),
            ];
        }

        $postTop = $modelLanding->postTop ?: new Post();
        $postBottom = $modelLanding->postBottom ?: new Post();

        return $this->render('page', [
            'model' => $modelLanding,
            'user' => $user,
            'profile' => $profile,
            'membershipOptions' => $membershipOptions,
            'postTop' => $postTop,
            'postBottom' => $postBottom,
            'search_results' => $search_results,
        ]);
    }

    public function actionCreate()
    {
        $model = new LandingPage();
        $user = $this->loadUserData();
        $profile = $user ? $user->profile : null;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['show', 'id' => $model->id]);
        }

        $savedSearches = SavedSearch::find()->where(['user_id' => Yii::$app->user->id])->all();

        return $this->render('create', [
            'model' => $model,
            'user' => $user,
            'profile' => $profile,
            'savedSearches' => $savedSearches,
        ]);
    }

    /**
     * Edit/Show action (legacy name was show, but it was used for update)
     */
    public function actionShow($id)
    {
        return $this->handleUpdate($id);
    }

    public function actionUpdate($id)
    {
        return $this->handleUpdate($id);
    }

    protected function handleUpdate($id)
    {
        $model = $this->findModel($id);
        $user = $this->loadUserData();
        $profile = $user ? $user->profile : null;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            
            // Handle membership options update
            $memberData = Yii::$app->request->post('memberOptions');
            if ($memberData) {
                $options = $model->membershipOptions;
                if (!$options) {
                    $options = new MembershipOptions();
                    $options->landing_id = $model->id;
                }
                
                $options->first_title = $memberData['first']['title'] ?? '';
                $options->first_color = $memberData['first']['color'] ?? '';
                $options->first_price = $memberData['first']['price'] ?? '';
                $options->first_text = $memberData['first']['featureList'] ?? '';
                
                $options->second_title = $memberData['second']['title'] ?? '';
                $options->second_color = $memberData['second']['color'] ?? '';
                $options->second_price = $memberData['second']['price'] ?? '';
                $options->second_text = $memberData['second']['featureList'] ?? '';
                
                $options->third_title = $memberData['third']['title'] ?? '';
                $options->third_color = $memberData['third']['color'] ?? '';
                $options->third_price = $memberData['third']['price'] ?? '';
                $options->third_text = $memberData['third']['featureList'] ?? '';
                
                $options->save();
            }

            // Handle post contents
            $topContent = Yii::$app->request->post('postContentPartOne');
            $bottomContent = Yii::$app->request->post('postContentPartTwo');
            
            if ($model->postTop) {
                $model->postTop->content = $topContent;
                $model->postTop->save();
            }
            if ($model->postBottom) {
                $model->postBottom->content = $bottomContent;
                $model->postBottom->save();
            }

            Yii::$app->session->setFlash('success', "Updated successfully!");
            return $this->redirect(['show', 'id' => $model->id]);
        }

        $membershipOptions = $model->membershipOptions;
        if (!$membershipOptions) {
            $membershipOptions = new MembershipOptions();
            $membershipOptions->landing_id = $model->id;
        }

        $savedSearches = SavedSearch::find()->where(['user_id' => Yii::$app->user->id])->all();

        return $this->render('show', [
            'model' => $model,
            'user' => $user,
            'profile' => $profile,
            'membershipOptions' => $membershipOptions,
            'postTop' => $model->postTop,
            'postBottom' => $model->postBottom,
            'savedSearches' => $savedSearches,
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', "Deleted successfully!");
        return $this->redirect(['index']);
    }

    public function actionUpload()
    {
        if (Yii::$app->request->isPost) {
            $uploadedFile = UploadedFile::getInstanceByName('file');
            if ($uploadedFile) {
                $rnd = rand(0, 9876543210);
                $timeStamp = time();
                $fileName = "{$rnd}-{$timeStamp}-" . $uploadedFile->name;
                $path = Yii::getAlias('@webroot') . '/upload/' . $fileName;
                
                if (!is_dir(dirname($path))) {
                    mkdir(dirname($path), 0777, true);
                }
                
                if ($uploadedFile->saveAs($path)) {
                    return json_encode([
                        'location' => Yii::$app->request->baseUrl . '/upload/' . $fileName
                    ]);
                }
            }
        }
        return json_encode(['status' => '400 Bad Request']);
    }

    protected function loadUserData()
    {
        if (!Yii::$app->user->isGuest) {
            return User::find()
                ->with(['profile', 'profession'])
                ->where(['id' => Yii::$app->user->id])
                ->one();
        }
        return false;
    }

    protected function findModel($id)
    {
        if (($model = LandingPage::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}