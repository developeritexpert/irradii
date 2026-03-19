<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\HttpException;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class StatInfoController extends Controller
{
    public $layout = '//layouts/column1';
    public $dollar_value = [];

    public function behaviors()
    {
        return [

            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'index',
                            'view',
                            'factor',
                            'factor-update',
                            'property-update',
                            'history',
                            'upload-alerts-messages'
                        ],
                        'roles' => ['admin']
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

        $rows = Yii::$app->db->createCommand("
            SELECT *, COUNT(*) as count_by
            FROM tbl_property_info_cron_load_photo
            GROUP BY process_at
        ")->queryAll();

        $modelPhoto = new ArrayDataProvider([
            'allModels' => $rows
        ]);

        $totalPhoto = Yii::$app->db->createCommand("
            SELECT COUNT(*) 
            FROM tbl_property_info_cron_load_photo
        ")->queryScalar();


        $rows = Yii::$app->db->createCommand("
            SELECT property_info.property_id as id,
                   property_info.property_street,
                   property_info.property_zipcode,
                   property_updated_date,
                   COUNT(property_updated_date) as count_by
            FROM property_info
            INNER JOIN property_info_details
                ON property_info_details.property_id = property_info.property_id
            WHERE property_info.getlongitude = '0.000000'
            AND property_info.getlatitude = '0.000000'
            AND UPPER(property_info.property_status)='ACTIVE'
            GROUP BY property_updated_date
            ORDER BY property_info.property_id DESC
        ")->queryAll();

        $modelCoord = new ArrayDataProvider([
            'allModels'=>$rows
        ]);

        $totalCoord = Yii::$app->db->createCommand("
            SELECT COUNT(*)
            FROM property_info
            INNER JOIN property_info_details
                ON property_info_details.property_id = property_info.property_id
            WHERE property_info.getlongitude = '0.000000'
            AND property_info.getlatitude = '0.000000'
            AND UPPER(property_info.property_status)='ACTIVE'
        ")->queryScalar();


        $maxEstimatedPriceRecalc =
            Yii::$app->params['maxEstimatedPriceRecalc'] ?? 2;

        $needRecalculate = Yii::$app->db->createCommand("
            SELECT COUNT(*)
            FROM property_info
            WHERE UPPER(property_status)='ACTIVE'
            AND property_type NOT IN (0,4,5)
            AND estimated_price_recalc_at <=
                DATE_ADD(CURDATE(), INTERVAL -{$maxEstimatedPriceRecalc} DAY)
        ")->queryScalar();


        return $this->render('index',[
            'modelPhoto'=>$modelPhoto,
            'totalPhoto'=>$totalPhoto,
            'modelCoord'=>$modelCoord,
            'totalCoord'=>$totalCoord,
            'needRecalculate'=>$needRecalculate
        ]);
    }


    /**
     * Property history search
     */
    public function actionHistory()
    {

        $model = new \app\models\PropertyInfoHistory();

        if(Yii::$app->request->get('PropertyInfoHistory')){
            $model->attributes =
                Yii::$app->request->get('PropertyInfoHistory');
        }

        return $this->render('propertyHistorySearch',[
            'model'=>$model
        ]);
    }


    public function actionFactor($id=null)
    {

        if(!$id){

            $model = new \app\models\PropertyInfoSlug();

            if(Yii::$app->request->get('PropertyInfoSlug')){
                $model->attributes =
                    Yii::$app->request->get('PropertyInfoSlug');
            }

            return $this->render('admin',[
                'model'=>$model
            ]);
        }

        $property =
            \app\models\MarketTrendTable::getPropertyInfo($id);

        if(!$property){
            throw new HttpException(404,'The requested page does not exist.');
        }

        $factorsStr =
            \app\models\MarketTrendTable::searchFactors($property);

        $factorsNew =
            (array)\app\models\MarketTrendTable::getFactorsRow($property);

        $newFactors =
            \app\models\MarketTrendTable::getFactors($property);

        $details =
            \app\models\PropertyInfo::find()
                ->with('propertyInfoDetails')
                ->where(['property_id'=>$id])
                ->one();

        $estimatedValues =
            $this->recalcEstimatedPrice(
                $details,
                $newFactors['fundamentals_factor'],
                $newFactors['conditional_factor']
            );

        return $this->render('factor',[
            'property_id'=>$id,
            'property'=>$property,
            'factorsStr'=>$factorsStr,
            'factorsNew'=>new ArrayDataProvider([
                'allModels'=>$factorsNew
            ]),
            'newFactors'=>$newFactors,
            'estimatedValues'=>$estimatedValues
        ]);
    }


    private function recalcEstimatedPrice(
        $details,
        $fundamentals_factor,
        $conditional_factor
    ){

        if(!$details){
            return [];
        }

        return \app\models\EstimatedPrice::getComparePropertyInfo(
            '',
            $details->property_id,
            $details->property_type,
            $details->property_zipcode,
            $details->getlatitude,
            $details->getlongitude,
            $details->year_biult_id,
            $details->lot_acreage,
            $details->house_square_footage,
            $details->bathrooms,
            $details->garages,
            $details->pool,
            $details->percentage_depreciation_value,
            $details->estimated_price,
            $details->bedrooms,
            $details->subdivision,
            $fundamentals_factor,
            $conditional_factor
        );
    }


    public function actionFactorUpdate($id)
    {

        $property =
            \app\models\PropertyInfo::findOne($id);

        if(!$property){
            throw new HttpException(404,'Not found');
        }

        $post = Yii::$app->request->post();

        if(!isset($post['pk']) || !isset($post['name'])){
            throw new HttpException(400,'Invalid request');
        }

        \app\models\MarketTrendTable::updateAll(
            [$post['name']=>$post['value']],
            ['id'=>$post['pk']]
        );
    }


    public function actionPropertyUpdate($id)
    {

        $details =
            \app\models\PropertyInfo::findOne($id);

        if(!$details){
            throw new HttpException(404,'Not found');
        }

        $factors = Yii::$app->request->post('factors');

        if($factors){

            $estimatedValues =
                $this->recalcEstimatedPrice(
                    $details,
                    $factors['fundamentals_factor'],
                    $factors['conditional_factor']
                );

            $details->updateAttributes([
                'fundamentals_factor'=>$factors['fundamentals_factor'],
                'conditional_factor'=>$factors['conditional_factor'],
                'estimated_price'=>$estimatedValues['estimated_price'] ?? 0
            ]);
        }
    }


    public function actionUploadAlertsMessages()
    {

        $user =
            \app\models\User::find()
            ->with(['profile','profession'])
            ->where(['id'=>Yii::$app->user->id])
            ->one();

        $profile = $user->profile;

        $model = new \app\models\AlertsMessages();

        if($model->load(Yii::$app->request->post())
            && $model->save()){

            Yii::$app->session->setFlash(
                'success',
                'File successfully uploaded.'
            );

            return $this->refresh();
        }

        return $this->render('uploadAlertsMessView',[
            'model'=>$model,
            'profile'=>$profile
        ]);
    }


    public function actionError()
    {
        $error = Yii::$app->errorHandler->error;

        if($error){
            return $this->render('error',$error);
        }
    }

} 