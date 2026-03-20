<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\PropertyInfo;
use app\models\PropertyInfoHistory;
use app\models\User;
use app\models\TblUserPropertyInfo;
use app\models\TblCronMarketInfoSubdivision;
use app\models\TblCronMarketInfoArea;
use app\models\TblCronMarketInfoCity;
use app\models\TblCronMarketInfoCounty;
use app\models\TblCronMarketInfoState;
use app\models\TblCronMarketInfoZipcode;
use app\models\DetailsMapShape;
use app\models\ExcludeProperty;
use app\models\CompareEstimatedPriceTable;
use app\models\TTable2Tail;
use app\models\MarketTrendTable;
use app\components\SiteHelper;
use app\components\CPathCDN;
use app\components\EstimatedPrice;
use DateTime;
use DateTimeZone;

class PropertyController extends Controller
{
    public $layout = 'irradii';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['details', 'get-comp-property-details', 'history'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => [
                            'update-user-property-status',
                            'get-more-confidence-info',
                            'add-exclude-property',
                            'delete-exclude-property',
                            'save-agent',
                            'detach-agent',
                            'favorites',
                            'add-favorites',
                            'delete-favorites',
                            'update-min-stage',
                            'update-excluded-statuses',
                            'update-props-by-shape',
                            'table2-tail',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
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
     * Shows property details
     * @param string $slug
     * @throws NotFoundHttpException
     */
    public function actionDetails($slug)
    {
        $user_id = null;
        $profile = null;
        $model = null;

        if (!Yii::$app->user->isGuest) {
            $user_id = Yii::$app->user->id;
            $model = User::find()->where(['id' => $user_id])->with(['profile'])->one();
            if ($model) {
                $profile = $model->profile;
            }
        }

        // Try to find by slug
        $details = PropertyInfo::find()
            ->joinWith(['slug' => function ($query) use ($slug) {
                $query->andWhere(['tbl_property_info_slug.slug' => $slug]);
            }], true, 'LEFT JOIN')
            ->with([
                'city', 'county', 'state', 'zipcode',
                'propertyInfoAdditionalBrokerageDetails',
                'propertyInfoDetails',
                'propertyInfoPhoto',
                'propertyInfoAdditionalDetails',
                'user',
            ])
            ->where(['tbl_property_info_slug.slug' => $slug])
            ->one();

        // Fall back to ID-based lookup for legacy URLs
        if ($details === null && is_numeric($slug)) {
            $details = PropertyInfo::find()
                ->where(['property_id' => $slug])
                ->with([
                    'city', 'county', 'state', 'zipcode',
                    'propertyInfoAdditionalBrokerageDetails',
                    'propertyInfoDetails',
                    'propertyInfoPhoto',
                    'propertyInfoAdditionalDetails',
                    'user',
                    'slug',
                ])
                ->one();
        }

        if ($details === null) {
            throw new NotFoundHttpException('This property could be gone already!');
        }

        // Track user-property interaction
        $user_property_info = null;
        if ($user_id !== null) {
            $mls_sysid = $details->mls_sysid;
            $mls_name  = $details->mls_name;
            $user_property_info = TblUserPropertyInfo::findOne([
                'user_id'   => $user_id,
                'mls_sysid' => $mls_sysid,
                'mls_name'  => $mls_name,
            ]);

            if ($user_property_info === null) {
                $user_property_info = new TblUserPropertyInfo();
                $user_property_info->user_id              = $user_id;
                $user_property_info->mls_sysid            = $mls_sysid;
                $user_property_info->mls_name             = $mls_name;
                $user_property_info->user_property_status = 'New';
                $user_property_info->create_date          = date('Y-m-d H:i:s');
                $user_property_info->last_viewed_date     = date('Y-m-d H:i:s');
                $user_property_info->last_changed_date    = date('Y-m-d H:i:s');
                $user_property_info->save(false);
            } else {
                // Update status if property data changed since last visit
                if (strtotime($details->property_uploaded_date) > strtotime($user_property_info->last_viewed_date)) {
                    $user_property_info->user_property_status = 'Updated';
                }
                $user_property_info->last_viewed_date = date('Y-m-d H:i:s');
                $user_property_info->save(false);
            }
        }

        // Market information
        $market_info = [];
        $today     = date('Y-m-d');
        $yesterday = (new DateTime())->modify('-1 day')->format('Y-m-d');

        if ($details->subdivision != '') {
            $market_info['subdivision'] =
                TblCronMarketInfoSubdivision::find()->where(['subdivision' => $details->subdivision, 'date' => $today])->one()
                ?: TblCronMarketInfoSubdivision::find()->where(['subdivision' => $details->subdivision, 'date' => $yesterday])->one();
        } elseif ($details->area != '') {
            $market_info['subdivision'] =
                TblCronMarketInfoArea::find()->where(['area' => $details->area, 'date' => $today])->one()
                ?: TblCronMarketInfoArea::find()->where(['area' => $details->area, 'date' => $yesterday])->one();
        }

        $market_info['zipcode'] =
            TblCronMarketInfoZipcode::find()->where(['zipcode_id' => $details->property_zipcode, 'date' => $today])->one()
            ?: TblCronMarketInfoZipcode::find()->where(['zipcode_id' => $details->property_zipcode, 'date' => $yesterday])->one();

        $market_info['city'] =
            TblCronMarketInfoCity::find()->where(['city_id' => $details->property_city_id, 'date' => $today])->one()
            ?: TblCronMarketInfoCity::find()->where(['city_id' => $details->property_city_id, 'date' => $yesterday])->one();

        $market_info['county'] =
            TblCronMarketInfoCounty::find()->where(['county_id' => $details->property_county_id, 'date' => $today])->one()
            ?: TblCronMarketInfoCounty::find()->where(['county_id' => $details->property_county_id, 'date' => $yesterday])->one();

        $market_info['state'] =
            TblCronMarketInfoState::find()->where(['state_id' => $details->property_state_id, 'date' => $today])->one()
            ?: TblCronMarketInfoState::find()->where(['state_id' => $details->property_state_id, 'date' => $yesterday])->one();

        // Similar homes
        $similar_homes = [];
        $s_homes       = [];
        $similar_homes = $this->getSimilarHomesForSale(
            $details->property_id, $details->property_zipcode, $details->property_price,
            $details->house_square_footage, $details->property_type, $details->year_biult_id
        );
        if ($similar_homes) {
            $s_homes = $this->getSimilarHomes($similar_homes);
        }

        // Comparables / estimated price
        $comparebles_properties = [];
        $c_properties = '';
        if ($details->getlatitude != 0.000000 && $details->getlongitude != 0.000000) {
            $house_views = !empty($details->propertyInfoDetails->house_views)
                ? $details->propertyInfoDetails->house_views : '';
            $comparebles_properties = $this->getComparePropertyInfo(
                '',
                $details->property_id,
                $details->property_type,
                $details->property_zipcode,
                $details->getlatitude, $details->getlongitude, $details->year_biult_id,
                $details->lot_acreage, $details->house_square_footage, $details->bathrooms,
                $details->garages, $details->pool, $details->percentage_depreciation_value,
                $details->estimated_price, $details->property_price, $details->bedrooms, $details->subdivision,
                $details->fundamentals_factor, $details->conditional_factor,
                $house_views, $details->sub_type
            );

            // Ensure we have an object with an object for result_query
            if (is_array($comparebles_properties) && isset($comparebles_properties['result_query']) && is_array($comparebles_properties['result_query'])) {
                $comparebles_properties['result_query'] = (object)$comparebles_properties['result_query'];
            }
            $comparebles_properties = (object)$comparebles_properties;

            // In some cases `getComparePropertyInfo()` may return an object
            // whose `result_query` property is still an array.
            // The view checks `is_object($comparebles_properties->result_query ...)`,
            // so ensure it is always an object when present.
            if (isset($comparebles_properties->result_query) && is_array($comparebles_properties->result_query)) {
                $comparebles_properties->result_query = (object)$comparebles_properties->result_query;
            }

            $c_properties = $this->getCompareblesProperties($comparebles_properties, $details);
        }

        $countExcludeProperties = $this->countPropertiesExclude(is_object($comparebles_properties) ? $comparebles_properties : (object)$comparebles_properties);

        // Map shape from session
        $shape            = '{}';
        $excluded_by_shape = '[]';
        $session          = Yii::$app->session;
        if (!$session->isActive) {
            $session->open();
        }
        $session_id = $session->id;
        if ($session_id) {
            $mapShape = DetailsMapShape::findOne(['session_id' => $session_id, 'prop_id' => $details->property_id]);
            if ($mapShape) {
                $shape            = $mapShape->shape;
                $excluded_by_shape = $mapShape->excluded_props_by_shape;
            }
        }

        return $this->render('property_details', [
            'model'                  => $model,
            'profile'                => $profile,
            'details'                => $details,
            'user_property_info'     => $user_property_info,
            'market_info'            => (object)$market_info,
            'similar_homes'          => $similar_homes,
            's_homes'                => $s_homes,
            'comparebles_properties' => is_object($comparebles_properties) ? $comparebles_properties : (object)$comparebles_properties,
            'c_properties'           => $c_properties,
            'countExcludeProperties' => $countExcludeProperties,
            'shape'                  => $shape,
            'excluded_by_shape'      => $excluded_by_shape,
        ]);
    }

    /**
     * Returns comp property details as JSON for AJAX calls.
     */
    public function actionGetCompPropertyDetails()
    {
        if (!Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }

        $id = Yii::$app->request->post('property_id', 0);
        $result = [];
        $property_type_array = [
            '0' => 'Unknown', '1' => 'Single Family Home', '2' => 'Condo',
            '3' => 'Townhouse', '4' => 'Multi Family', '5' => 'Land',
            '6' => 'Mobile Home', '7' => 'Manufactured Home', '8' => 'Time Share',
            '9' => 'Rental', '16' => 'High Rise',
        ];

        if ($id) {
            $details = PropertyInfo::find()
                ->with(['propertyInfoAdditionalBrokerageDetails', 'propertyInfoAdditionalDetails',
                    'propertyInfoDetails', 'propertyInfoPhoto', 'user', 'city', 'slug', 'county', 'state', 'zipcode'])
                ->where(['property_id' => $id])
                ->one();

            if ($details) {
                $sqFt      = $details->house_square_footage;
                $acreage   = $details->lot_acreage;
                $bedrooms  = $details->bedrooms;
                $bathrooms = $details->bathrooms;

                $discont = 0;
                $tmv     = $details->estimated_price;
                $underValueDeals = Yii::$app->params['underValueDeals'] ?? 5;

                if (method_exists($details, 'getDiscontValue')) {
                    $discont = $details->getDiscontValue();
                }
                $result['discont'] = '';
                if ($discont >= $underValueDeals) {
                    $result['discont'] = '<span class="label bg-color-greenDark">' . round($discont) . '% Below TMV</span>';
                }

                $slider_arr = [];
                $photoArr = $this->getPhotoArr($details);
                foreach ($photoArr as $propertyInfoPhoto) {
                    $photocaption  = $propertyInfoPhoto->caption ? "<p>{$propertyInfoPhoto->caption}</p>" : '';
                    $slider_arr[] = '<div class="item">' . CPathCDN::checkPhoto($propertyInfoPhoto, '', 0) . $photocaption . '</div>';
                }
                $result['carousel']        = implode('', $slider_arr);
                $result['property_id']     = $details->property_id;
                $result['property_street'] = $details->property_street;
                $slugVal = $details->slug ? $details->slug->slug : '';
                $result['url']             = \yii\helpers\Url::to(['property/details', 'slug' => $slugVal]);
                $result['city']            = ($details->city ? $details->city->city_name : '')
                    . ', ' . ($details->state ? $details->state->state_code : '')
                    . ' ' . ($details->zipcode ? $details->zipcode->zip_code : '');
                $result['subdivision']     = $details->subdivision;
                $result['type']            = $property_type_array[$details->property_type] ?? '';
                $result['metrics']         = "<p>{$sqFt} Sq Ft / {$acreage} Acre<br>{$bedrooms} Beds / {$bathrooms} Baths";
                $result['tmv']             = $tmv ? '$' . number_format($tmv) : '';
            }
        }

        return $this->asJson($result);
    }

    /**
     * Update the minimum calculation stage stored in session.
     */
    public function actionUpdateMinStage()
    {
        if (!Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }

        $session    = Yii::$app->session;
        $property_id = Yii::$app->request->post('property_id', 0);
        $min_stage  = Yii::$app->request->post('min_stage', 0);
        $min_stages = $session->get('min_stages', []);

        if ($property_id) {
            $min_stages[$property_id] = $min_stage;
            $session->set('min_stages', $min_stages);
        }

        return $this->asJson([
            'session_min_stages' => $session->get('min_stages', []),
            'min_stage'          => $this->getMinStage($property_id),
        ]);
    }

    /**
     * Update a user's property status or note.
     */
    public function actionUpdateUserPropertyStatus()
    {
        if (!Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }

        $response = [];
        $raw_slug = explode('slug=', Yii::$app->request->post('property_slug', ''));
        $slug     = isset($raw_slug[1]) ? $raw_slug[1] : Yii::$app->request->post('property_slug', '');
        $user_id  = Yii::$app->user->id;

        $details = PropertyInfo::find()
            ->joinWith(['slug' => function ($query) use ($slug) {
                $query->andWhere(['tbl_property_info_slug.slug' => $slug]);
            }], true, 'INNER JOIN')
            ->where(['tbl_property_info_slug.slug' => $slug])
            ->one();

        if (!$details) {
            return $this->asJson(['status' => 404]);
        }

        $mls_sysid = $details->mls_sysid;
        $mls_name  = $details->mls_name;
        $type      = Yii::$app->request->post('type', '');

        if ($type === 'status') {
            $userPropertyStatus = Yii::$app->request->post('user_property_status', '');
            $rows = Yii::$app->db->createCommand()
                ->update('tbl_user_property_info',
                    ['user_property_status' => $userPropertyStatus, 'last_changed_date' => date('Y-m-d H:i:s')],
                    'user_id=:user_id AND mls_sysid=:mls_sysid',
                    [':user_id' => $user_id, ':mls_sysid' => $mls_sysid]
                )->execute();
            if ($rows) {
                $response['status'] = 200;
                $response['scheme'] = SiteHelper::getColorSchemeOfUserPropertyStatus($userPropertyStatus);
            }
        } elseif ($type === 'note') {
            $userPropertyNote = htmlspecialchars(Yii::$app->request->post('user_property_note', ''));
            $rows = Yii::$app->db->createCommand()
                ->update('tbl_user_property_info',
                    ['user_property_note' => $userPropertyNote, 'last_changed_date' => date('Y-m-d H:i:s')],
                    'user_id=:user_id AND mls_name=:mls_name AND mls_sysid=:mls_sysid',
                    [':user_id' => $user_id, ':mls_sysid' => $mls_sysid, ':mls_name' => $mls_name]
                )->execute();
            if ($rows >= 1) {
                $response['status'] = 200;
                $response['note']   = $userPropertyNote;
            }
        }

        return $this->asJson($response);
    }

    /**
     * Update the excluded statuses stored in session for a property.
     */
    public function actionUpdateExcludedStatuses()
    {
        if (!Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }

        $session                      = Yii::$app->session;
        $property_id                  = Yii::$app->request->post('property_id', 0);
        $excluded_statuses_for_prop   = Yii::$app->request->post('excluded_statuses', []);
        $excluded_statuses            = $session->get('excluded_statuses', []);

        if ($property_id) {
            $excluded_statuses[$property_id] = $excluded_statuses_for_prop;
            $session->set('excluded_statuses', $excluded_statuses);
        }

        return $this->asJson([
            'session_excluded_statuses' => $session->get('excluded_statuses', []),
            'string'                    => $this->getExcludedStatusesStr($property_id),
        ]);
    }

    /**
     * Return updated comparables / confidence info via AJAX.
     */
    public function actionGetMoreConfidenceInfo()
    {
        if (!Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }

        $id     = Yii::$app->request->post('property_id', 0);
        $result = ['status' => 'error', 'c_properties' => '', 'comparebles' => ''];

        $details = PropertyInfo::find()
            ->with(['propertyInfoAdditionalBrokerageDetails', 'propertyInfoAdditionalDetails',
                'propertyInfoDetails', 'propertyInfoPhoto', 'user', 'city', 'county', 'state', 'zipcode'])
            ->where(['property_id' => $id])
            ->one();

        if ($details && $details->getlatitude != 0.000000 && $details->getlongitude != 0.000000) {
            $house_views = !empty($details->propertyInfoDetails->house_views)
                ? $details->propertyInfoDetails->house_views : '';
            $comparebles_properties = $this->getComparePropertyInfo(
                '',
                $details->property_id,
                $details->property_type,
                $details->property_zipcode,
                $details->getlatitude, $details->getlongitude, $details->year_biult_id,
                $details->lot_acreage, $details->house_square_footage, $details->bathrooms,
                $details->garages, $details->pool, $details->percentage_depreciation_value,
                $details->estimated_price, $details->property_price, $details->bedrooms, $details->subdivision,
                $details->fundamentals_factor, $details->conditional_factor,
                $house_views, $details->sub_type
            );
            $result['status']      = 'success';
            $result['c_properties'] = $this->getCompareblesProperties((object)$comparebles_properties, $details);
            $result['comparebles'] = $comparebles_properties;
        }

        return $this->asJson($result);
    }

    /**
     * T-table lookup (AJAX).
     */
    public function actionTable2Tail()
    {
        if (!Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }

        $count_property = (int)Yii::$app->request->post('count', 0);
        $result_t_score = $this->actionTtable2Tail($count_property);
        $t_score        = EstimatedPrice::getTail($result_t_score);

        return $this->asJson(['t_score' => $t_score]);
    }

    /**
     * Update map shape in session/DB.
     */
    public function actionUpdatePropsByShape()
    {
        if (!Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }

        $session = Yii::$app->session;
        if (!$session->isActive) {
            $session->open();
        }
        $session_id  = $session->id;
        $property_id = Yii::$app->request->post('property_id', 0);
        $shape       = Yii::$app->request->post('shape', '{}');

        $model = DetailsMapShape::findOne(['session_id' => $session_id, 'prop_id' => $property_id]);
        if ($model === null) {
            $model              = new DetailsMapShape();
            $model->session_id  = $session_id;
            $model->prop_id     = $property_id;
            $model->mid         = Yii::$app->user->isGuest ? 0 : Yii::$app->user->id;
        }
        $model->shape = $shape;
        $model->save(false);

        return $this->asJson(['status' => 'ok']);
    }

    /**
     * Add a property to the exclude list.
     */
    public function actionAddExcludeProperty()
    {
        if (!Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }

        $session = Yii::$app->session;
        if (!$session->isActive) {
            $session->open();
        }
        $session_id = $session->id;
        $del_id     = Yii::$app->request->post('del_id', 0);

        if ($del_id) {
            $this->actionExcludeProperty($del_id, $session_id);
        }

        return $this->asJson(['status' => 'ok']);
    }

    /**
     * Remove a property from the exclude list.
     */
    public function actionDeleteExcludeProperty()
    {
        if (!Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }

        $session = Yii::$app->session;
        if (!$session->isActive) {
            $session->open();
        }
        $session_id = $session->id;
        $prop_id    = Yii::$app->request->post('prop_id', 0);

        if ($prop_id) {
            ExcludeProperty::deleteAll(['session_id' => $session_id, 'product_id' => $prop_id]);
        }

        return $this->asJson(['status' => 'ok']);
    }

    /**
     * Save property to favorites.
     */
    public function actionFavorites()
    {
        $response = [];
        if (Yii::$app->user->isGuest) {
            $response[] = 'This option is available only for authenticated users.';
        } else {
            // Favorites logic (SavedListing model) — kept for future use
            $response[] = 'Favorites feature coming soon.';
        }
        return $this->asJson($response);
    }

    /**
     * Save agent for a property.
     */
    public function actionSaveAgent()
    {
        if (!Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }
        // Agent save logic goes here
        return $this->asJson(['status' => 'ok']);
    }

    /**
     * Detach agent from a property.
     */
    public function actionDetachAgent()
    {
        if (!Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }
        // Agent detach logic goes here
        return $this->asJson(['status' => 'ok']);
    }

    /**
     * Show property history page.
     * @param int $id
     */
    public function actionHistory($id)
    {
        $profile = '';
        if (!Yii::$app->user->isGuest) {
            $userModel = User::find()->with(['profile'])->where(['id' => Yii::$app->user->id])->one();
            if ($userModel) {
                $profile = $userModel->profile;
            }
        }

        $property = PropertyInfoHistory::find()
            ->with(['propertyInfoDetails', 'propertyInfoAdditionalDetails',
                'propertyInfoAdditionalBrokerageDetails', 'city', 'county', 'state', 'zipcode'])
            ->where(['property_id' => $id])
            ->one();

        if ($property === null) {
            throw new NotFoundHttpException('Property history not found.');
        }

        $actualInfo = PropertyInfo::find()
            ->with(['propertyInfoAdditionalBrokerageDetails', 'propertyInfoAdditionalDetails',
                'propertyInfoDetails', 'propertyInfoPhoto', 'user', 'city', 'county', 'state', 'zipcode'])
            ->where(['mls_sysid' => $property->mls_sysid])
            ->one();

        return $this->render('propertyHistoryDetailsView', [
            'property'   => $property,
            'actualInfo' => $actualInfo,
            'profile'    => $profile,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // Private / Helper Methods
    // ─────────────────────────────────────────────────────────────────

    /**
     * Get minimum calculation stage from session.
     */
    private function getMinStage($property_id)
    {
        $min_stages = Yii::$app->session->get('min_stages', []);
        return isset($min_stages[$property_id]) ? (int)$min_stages[$property_id] : 1;
    }

    /**
     * Get excluded statuses string for a property (from session).
     */
    private function getExcludedStatusesStr($property_id)
    {
        $excluded_statuses = Yii::$app->session->get('excluded_statuses', []);
        if (isset($excluded_statuses[$property_id]) && is_array($excluded_statuses[$property_id])) {
            return implode(',', $excluded_statuses[$property_id]);
        }
        return '';
    }

    /**
     * Query for similar homes for sale based on key property attributes.
     */
    private function getSimilarHomesForSale($id, $zipcode, $price, $square, $property_type, $year_biult)
    {
        $sql = "SELECT t1.*, t2.status, t3.zip_code, t4.city_name, t5.county_name, t6.state_code, t7.slug
                FROM `property_info` AS t1
                LEFT JOIN `property_info_additional_brokerage_details` AS t2
                    ON `t2`.`property_id` = `t1`.`property_id`
                    AND (`t2`.`status` NOT IN ('HISTORY','LEASED','SOLD','CLOSED','','EXPIRED','Contingent Offer','Pending Offer')
                         AND `t2`.`status` IS NOT NULL)
                LEFT JOIN `zipcode` AS t3 ON `t3`.`zip_id` = `t1`.`property_zipcode`
                LEFT JOIN `city` AS t4 ON `t4`.`cityid` = `t1`.`property_city_id`
                LEFT JOIN `county` AS t5 ON `t5`.`county_id` = `t1`.`property_county_id`
                LEFT JOIN `state` AS t6 ON `t6`.`stid` = `t1`.`property_state_id`
                LEFT JOIN `tbl_property_info_slug` AS t7 ON `t7`.`property_id` = `t1`.`property_id`
                WHERE `t1`.`property_zipcode` = :zipcode
                AND (`t1`.`property_price` BETWEEN :price_low AND :price_high)
                AND (`t1`.`house_square_footage` BETWEEN :sq_low AND :sq_high)
                AND (`t1`.`property_type` = :prop_type)
                AND (`t1`.`year_biult_id` BETWEEN :yr_low AND :yr_high)
                AND (`t1`.`property_id` != :id)";

        return Yii::$app->db->createCommand($sql, [
            ':zipcode'    => $zipcode,
            ':price_low'  => $price - $price * 20 / 100,
            ':price_high' => $price + $price * 20 / 100,
            ':sq_low'     => $square - $square * 30 / 100,
            ':sq_high'    => $square + $square * 30 / 100,
            ':prop_type'  => $property_type,
            ':yr_low'     => $year_biult - 10,
            ':yr_high'    => $year_biult + 10,
            ':id'         => $id,
        ])->queryAll();
    }

    /**
     * Format similar homes for display.
     */
    private function getSimilarHomes($similar_homes)
    {
        $result          = [];
        $underValueDeals = Yii::$app->params['underValueDeals'] ?? 5;

        foreach ($similar_homes as $similar_home) {
            $discont = 0;
            if ($similar_home['percentage_depreciation_value'] >= $underValueDeals) {
                $discont = $similar_home['percentage_depreciation_value'];
            }
            if ($discont == 0 && $similar_home['estimated_price'] > 0) {
                $calc = 100 - ($similar_home['property_price'] * 100 / $similar_home['estimated_price']);
                if ($calc > 0) {
                    $discont = $calc;
                }
            }

            $similar_home                = (object)$similar_home;
            $similar_home->fullAddress   = $this->getFullAddress($similar_home);
            $slugUrl = \yii\helpers\Url::to(['property/details', 'slug' => $similar_home->slug ?? '']);
            $col1 = '<a href="' . $slugUrl . '">'
                . CPathCDN::checkPhoto($similar_home, 'thumb-img-140', 0)
                . '</a>';

            $col2 = '$' . number_format($similar_home->property_price) . '<br>';
            if (isset($similar_home->status)) {
                $similar_stat = strtoupper($similar_home->status);
                $condition    = $discont >= $underValueDeals;
                $colorScheme  = SiteHelper::getColorScheme($similar_stat, $condition);
                $col2 .= '<span class="label ' . $colorScheme['label-color'] . '">'
                    . ucfirst($similar_home->status)
                    . '</span>';
            }

            $col3 = $similar_home->property_street . '<br>'
                . ($similar_home->city_name ? $similar_home->city_name . ', ' : '')
                . ($similar_home->state_code ? $similar_home->state_code . ' ' : '')
                . ($similar_home->zip_code ?? '');

            $col4 = $similar_home->house_square_footage . ' SqFt.<br>'
                . $similar_home->bedrooms . ' Bed / '
                . $similar_home->bathrooms . ' Bath<br>'
                . $similar_home->garages . ' Car Garage';

            $result[] = [$col1, $col2, $col3, $col4];
        }

        return $result;
    }

    /**
     * Build the comparables properties array for display.
     */
    private function getCompareblesProperties($comparebles_properties, $details)
    {
        $result = [];
        if (!property_exists($comparebles_properties, 'result_query')) {
            return $result;
        }

        $excludedPropertiesIDs = [];
        if (property_exists($comparebles_properties, 'exclude')) {
            foreach ($comparebles_properties->exclude as $exclude) {
                $excludedPropertiesIDs[] = $exclude['product_id'];
            }
        }

        $comps = array_slice($comparebles_properties->result_queryAllRows, 0, 5);
        foreach ($comps as $comparebles_property) {
            $comparebles_property = (object)$comparebles_property;
            $result[] = $this->makeComparableRowForProperty($comparebles_property, $details, $excludedPropertiesIDs);
        }
        $result[] = $this->makeOwnComparableRow($details, $excludedPropertiesIDs);

        return $result;
    }

    /**
     * Build a single row of comparable property data for the table.
     */
    // private function makeComparableRowForProperty($comparebles_property, $details, $excludedPropertiesIDs = [])
    // {
    //     $underValueDeals = Yii::$app->params['underValueDeals'] ?? 5;

    //     $slug_val = ($comparebles_property->property_id != $details->property_id)
    //         ? ($comparebles_property->slug ?? '')
    //         : ($details->slug ? $details->slug->slug : '');
    //     $propUrl  = \yii\helpers\Url::to(['property/details', 'slug' => $slug_val]);

    //     $photo = CPathCDN::checkPhoto($comparebles_property, 'thumb-img-80', 0);
    //     $col1  = '<a class="property_info_row" data-property="' . $propUrl . '" href="' . $propUrl . '">'
    //         . $comparebles_property->property_street . $photo . '</a>';
    //     // Excluded checkbox
    //     // $col1 = '';
    //     // $isExcluded = in_array($comparebles_property->property_id, $excludedPropertiesIDs);
    //     // if (isset($comparebles_property->selfProp) && $comparebles_property->selfProp) {
    //     //     $col1 = '<input type="checkbox" disabled>';
    //     // } else {
    //     //     $checked = $isExcluded ? 'checked' : '';
    //     //     $col1 = '<input type="checkbox" class="exclude_property" data-id="' . $comparebles_property->property_id . '" ' . $checked . '>';
    //     // }

    //     // TMV / Estimated price
    //     $discont = 0;
    //     if ($comparebles_property->percentage_depreciation_value >= $underValueDeals) {
    //         $discont = $comparebles_property->percentage_depreciation_value;
    //     }
    //     if ($discont == 0 && !empty($comparebles_property->estimated_price) && $comparebles_property->estimated_price > 0) {
    //         $calc = 100 - ($comparebles_property->property_price * 100 / $comparebles_property->estimated_price);
    //         if ($calc > 0) {
    //             $discont = $calc;
    //         }
    //     }

    //     $condition   = $discont >= $underValueDeals;
    //     $statusVal  = $comparebles_property->property_id != $details->property_id
    //         ? ($comparebles_property->status ?? '')
    //         : $details->getStatus();
    //     $similar_stat = strtoupper($statusVal);
    //     $colorScheme  = SiteHelper::getColorScheme($similar_stat, $condition);

    //     $slug_val = ($comparebles_property->property_id != $details->property_id)
    //         ? ($comparebles_property->slug ?? '')
    //         : ($details->slug ? $details->slug->slug : '');
    //     $propUrl  = \yii\helpers\Url::to(['property/details', 'slug' => $slug_val]);

    //     $col2  = '<span class="label ' . $colorScheme['label-color'] . '">' . ucfirst($statusVal) . '</span>';
    //     $col3  = number_format($comparebles_property->property_price);
    //     // $photo = CPathCDN::checkPhoto($comparebles_property, 'thumb-img-80', 0);
    //     // $col4  = '<a class="property_info_row" data-property="' . $propUrl . '" href="' . $propUrl . '">'
    //     //     . $comparebles_property->property_street . $photo . '</a>';
    //     $col4  = number_format($comparebles_property->property_price);

    //     // $col5  = $comparebles_property->bedrooms;
    //     $col5 = !empty($comparebles_property->estimated_price) ? '$' . number_format($comparebles_property->estimated_price) : '';

    //     // $col6  = $comparebles_property->bathrooms;
    //     $col6 = $comparebles_property->year_biult_id;

    //     $col7  = $comparebles_property->lot_acreage;
    //     $col8  = $comparebles_property->house_square_footage;
    //     $col9  = $comparebles_property->bedrooms;

    //     // $col9  = !empty($comparebles_property->property_price) && !empty($comparebles_property->house_square_footage)
    //     //     ? number_format($comparebles_property->property_price / $comparebles_property->house_square_footage, 2)
    //     //     : '';
    //     $col10  = $comparebles_property->bathrooms;

    //     $col11 = !empty($comparebles_property->estimated_price) ? '$' . number_format($comparebles_property->estimated_price) : '';
    //     $col12 = $discont > 0 ? round($discont, 2) . '%' : '';
    //     $col13 = $comparebles_property->subdivision ?? '';
    //     $col14 = '';

    //     // Days on market
    //     $dtz          = new DateTimeZone('UTC');
    //     $datetime_now = new DateTime();
    //     $datetime_now->setTimezone($dtz);
    //     $propertyDate = $comparebles_property->property_id != $details->property_id
    //         ? (!empty($comparebles_property->entry_date)
    //             ? $comparebles_property->entry_date
    //             : $comparebles_property->property_uploaded_date)
    //         : (!empty($details->propertyInfoAdditionalBrokerageDetails->entry_date)
    //             ? $details->propertyInfoAdditionalBrokerageDetails->entry_date
    //             : $details->property_uploaded_date);
    //     $datetime_exp = new DateTime($propertyDate, $dtz);
    //     $col22        = $datetime_now->diff($datetime_exp)->days;

    //     $col23 = $comparebles_property->garages;
    //     $col24 = $comparebles_property->pool;

    //     $col25 = $comparebles_property->property_id != $details->property_id
    //         ? ($comparebles_property->house_faces ?? '')
    //         : ($details->propertyInfoDetails->house_faces ?? '');
    //     $col26 = $comparebles_property->property_id != $details->property_id
    //         ? ($comparebles_property->house_views ?? '')
    //         : ($details->propertyInfoDetails->house_views ?? '');
    //     $col27 = $comparebles_property->property_id != $details->property_id
    //         ? ($comparebles_property->flooring_description ?? '')
    //         : ($details->propertyInfoAdditionalDetails->flooring_description ?? '');
    //     $col28 = $comparebles_property->property_id != $details->property_id
    //         ? ($comparebles_property->furnishings_description ?? '')
    //         : ($details->propertyInfoAdditionalDetails->furnishings_description ?? '');
    //     $col29 = $comparebles_property->property_id != $details->property_id
    //         ? ($comparebles_property->financing_considered ?? '')
    //         : ($details->propertyInfoAdditionalBrokerageDetails->financing_considered ?? '');

    //     $col15 = '';  // placeholder for additional fields
    //     $col16 = '';
    //     $col17 = '';
    //     $col18 = '';
    //     $col19 = '';
    //     $col20 = '';
    //     $col21 = '';

    //     return [$col1, $col2, $col3, $col4, $col5, $col6, $col7, $col8, $col9, $col10,
    //         $col23, $col11, $col12, $col13, $col15, $col24, $col16, $col17,
    //         $col25, $col26, $col27, $col28, $col29, $col18, $col19, $col20, $col21, $col22, $col14];
    // }

    private function makeComparableRowForProperty($comparebles_property, $details, $excludedPropertiesIDs = [])
{
    $underValueDeals = Yii::$app->params['underValueDeals'] ?? 5;

    /* ===================== BASIC ===================== */

    $slug = ($comparebles_property->property_id != $details->property_id)
        ? ($comparebles_property->slug ?? '')
        : ($details->slug->slug ?? '');

    $propertyUrl = \yii\helpers\Url::to(['property/details', 'slug' => $slug]);

    $photo = CPathCDN::checkPhoto($comparebles_property, 'thumb-img-80', 0);

    /* ===================== ADDRESS ===================== */
    $addressHtml = '<a class="property_info_row" href="' . $propertyUrl . '">' .
        $comparebles_property->property_street . $photo . '</a>';

    /* ===================== DISCOUNT ===================== */
    $discountPercent = 0;

    if ($comparebles_property->percentage_depreciation_value >= $underValueDeals) {
        $discountPercent = $comparebles_property->percentage_depreciation_value;
    }

    if ($discountPercent == 0 && !empty($comparebles_property->estimated_price)) {
        $calc = 100 - ($comparebles_property->property_price * 100 / $comparebles_property->estimated_price);
        if ($calc > 0) {
            $discountPercent = $calc;
        }
    }

    /* ===================== STATUS ===================== */
    $statusValue = ($comparebles_property->property_id != $details->property_id)
        ? ($comparebles_property->status ?? '')
        : $details->getStatus();

    $colorScheme = SiteHelper::getColorScheme(strtoupper($statusValue), $discountPercent >= $underValueDeals);

    $statusLabel = '<span class="label ' . $colorScheme['label-color'] . '">' . ucfirst($statusValue) . '</span>';

    /* ===================== PRICES ===================== */
    $listPrice      = number_format($comparebles_property->property_price);
    $salePrice      = number_format($comparebles_property->property_price);
    $estimatedPrice = !empty($comparebles_property->estimated_price)
        ? '$' . number_format($comparebles_property->estimated_price)
        : '';

    /* ===================== PROPERTY DETAILS ===================== */
    $pricePerSqFt = (!empty($comparebles_property->house_square_footage))
        ? number_format($comparebles_property->property_price / $comparebles_property->house_square_footage, 2)
        : '';

    $squareFootage = $comparebles_property->house_square_footage;
    $bedrooms      = $comparebles_property->bedrooms;
    $bathrooms     = $comparebles_property->bathrooms;
    $lotSize       = $comparebles_property->lot_acreage;
    $yearBuilt     = $comparebles_property->year_biult_id;

    /* ===================== EXTRA ===================== */
    $distance = ''; // (you removed logic, keep placeholder)

    $stories = '';
    $spa     = '';
    $condition = '';

    $foreclosure = '';
    $shortSale   = '';
    $bankOwned   = '';

    $originalPrice = '';
    $subdivision   = $comparebles_property->subdivision ?? '';

    /* ===================== DAYS ON MARKET ===================== */
    $dtz = new DateTimeZone('UTC');
    $now = new DateTime();
    $now->setTimezone($dtz);

    $propertyDate = ($comparebles_property->property_id != $details->property_id)
        ? ($comparebles_property->entry_date ?? $comparebles_property->property_uploaded_date)
        : ($details->propertyInfoAdditionalBrokerageDetails->entry_date ?? $details->property_uploaded_date);

    $daysOnMarket = (new DateTime($propertyDate, $dtz))->diff($now)->days;

    /* ===================== FEATURES ===================== */
    $garage = $comparebles_property->garages;
    $pool   = $comparebles_property->pool;

    $houseFaces = ($comparebles_property->property_id != $details->property_id)
        ? ($comparebles_property->house_faces ?? '')
        : ($details->propertyInfoDetails->house_faces ?? '');

    $houseViews = ($comparebles_property->property_id != $details->property_id)
        ? ($comparebles_property->house_views ?? '')
        : ($details->propertyInfoDetails->house_views ?? '');

    $flooring = ($comparebles_property->property_id != $details->property_id)
        ? ($comparebles_property->flooring_description ?? '')
        : ($details->propertyInfoAdditionalDetails->flooring_description ?? '');

    $furnishing = ($comparebles_property->property_id != $details->property_id)
        ? ($comparebles_property->furnishings_description ?? '')
        : ($details->propertyInfoAdditionalDetails->furnishings_description ?? '');

    $financing = ($comparebles_property->property_id != $details->property_id)
        ? ($comparebles_property->financing_considered ?? '')
        : ($details->propertyInfoAdditionalBrokerageDetails->financing_considered ?? '');

    /* ===================== TOOL BUTTON ===================== */
    $actions = '';

    /* ===================== RETURN (MATCH OLD ORDER) ===================== */
    return [
        $addressHtml,        // col1
        $statusLabel,        // col2
        $listPrice,          // col3
        $salePrice,          // col4
        $estimatedPrice,     // col5
        $yearBuilt,          // col6 (date replaced)
        $pricePerSqFt,       // col7
        $squareFootage,      // col8
        $bedrooms,           // col9
        $bathrooms,          // col10
        $garage,             // col23
        $lotSize,            // col11
        $yearBuilt,          // col12
        $distance,           // col13
        $stories,            // col15
        $pool,               // col24
        $spa,                // col16
        $condition,          // col17
        $houseFaces,         // col25
        $houseViews,         // col26
        $flooring,           // col27
        $furnishing,         // col28
        $financing,          // col29
        $foreclosure,        // col18
        $shortSale,          // col19
        $bankOwned,          // col20
        $originalPrice,      // col21
        $daysOnMarket,       // col22
        $actions             // col14
    ];
}
    /**
     * Build current property's own comparable row.
     */
    private function makeOwnComparableRow(PropertyInfo $details, $excludedPropertiesIDs = [])
    {
        $comparebles_property          = (object)$details->getAttributes();
        $comparebles_property->status  = $details->getStatus();
        $comparebles_property->selfProp = true;
        return $this->makeComparableRowForProperty($comparebles_property, $details, $excludedPropertiesIDs);
    }

    /**
     * Add a property to the exclude list in the DB.
     */
    private function actionExcludeProperty($del_id, $session_id)
    {
        if ($del_id) {
            $existing = ExcludeProperty::find()
                ->select('exid')
                ->where(['session_id' => $session_id, 'product_id' => $del_id])
                ->one();
            if (!$existing) {
                $model             = new ExcludeProperty();
                $model->mid        = Yii::$app->user->isGuest ? 0 : Yii::$app->user->id;
                $model->session_id = $session_id;
                $model->product_id = $del_id;
                if ($model->validate()) {
                    $model->save();
                }
            }
        }
    }

    /**
     * Retrieve all excluded properties for this session.
     */
    private function getExcludeProperty($session_id)
    {
        return (object)Yii::$app->db->createCommand(
            "SELECT `exclude_property`.`product_id` FROM `exclude_property`
             WHERE `exclude_property`.`session_id` = :sid",
            [':sid' => $session_id]
        )->queryAll();
    }

    /**
     * Look up the T-table row for given count.
     */
    private function actionTtable2Tail($count_property)
    {
        $df = $count_property - 1;
        return TTable2Tail::find()->where(['df' => $df])->one();
    }

    /**
     * Main compare property info calculator (wraps EstimatedPrice logic).
     */
    private function getComparePropertyInfo(
        $del_id, $property_id, $property_type, $property_zipcode,
        $property_lat, $property_lon, $year_biult_id,
        $lot_sq_footage, $house_sq_footage, $bathrooms, $garages, $pool,
        $percentage_depreciation_value, $estimated_price, $property_price, $bedrooms,
        $subdivision, $fundamentals_factor, $conditional_factor,
        $house_views, $sub_type
    ) {
        $result   = [];
        $curStage = $this->getMinStage($property_id);

        $gettoday_date = date('Y-m-d');
        $comp_time     = date('Y-m-d', time() - 200 * 24 * 60 * 60);

        $session = Yii::$app->session;
        if (!$session->isActive) {
            $session->open();
        }
        $session_id = $session->id;

        if ($del_id) {
            $this->actionExcludeProperty($del_id, $session_id);
        }

        // Only calculate for supported property types
        if (($property_type == '') || ($property_type == 0) || ($property_type == 4) || ($property_type == 5)) {
            return $result;
        }

        $house_views_list_full = MarketTrendTable::houseViewsList();
        $house_views_list      = $this->getHouseViewsArr($house_views_list_full, $house_views);

        $estimatedPrice = new EstimatedPrice();
        $estimatedPrice->calculateEstimatedPriceStage(
            $curStage,
            $result, $gettoday_date, $comp_time, $session_id,
            $property_id, $property_type, $property_zipcode,
            $property_lat, $property_lon, $year_biult_id,
            $lot_sq_footage, $house_sq_footage, $bathrooms, $garages, $pool,
            $percentage_depreciation_value, $estimated_price, $bedrooms,
            $subdivision, $fundamentals_factor, $conditional_factor,
            $house_views_list, $sub_type, $property_price
        );

        $result['estimated_value_subject_property_stage'] = $result['estimated_value_subject_property'] ?? 0;

        if ((($result['estimated_value_subject_property'] ?? 0) < 500)
            || (($result['percentage_depreciation_value'] ?? 0) < -1000)
            || (($result['percentage_depreciation_value'] ?? 0) > 95)
        ) {
            $result['estimated_price']                   = 0;
            $result['estimated_price_dollar']            = 0;
            $result['estimated_value_subject_property']  = 0;
            $result['percentage_depreciation_value']     = 0;
        }

        $result['exclude']                    = $this->getExcludeProperty($session_id);
        $result['comparable_price_sparkline'] = [];

        return $result;
    }

    /**
     * Filter house views list for comparison.
     */
    private function getHouseViewsArr($house_views_list, $house_views)
    {
        $house_views_ret   = [];
        // If we don't have any house-views text for this property, don't build a restrictive
        // NOT LIKE filter (empty string would otherwise mark every view as "not present").
        if (trim((string)$house_views) === '') {
            return [];
        }
        $house_views_lower = strtolower($house_views);
        foreach ($house_views_list as $value) {
            if (strpos($house_views_lower, $value) === false) {
                $house_views_ret[] = $value;
            }
        }
        return $house_views_ret;
    }

    /**
     * Count properties that have been excluded from comps.
     */
    private function countPropertiesExclude($comparebles_properties)
    {
        $excludedPropertiesIDs = [];
        if (property_exists($comparebles_properties, 'exclude')) {
            foreach ($comparebles_properties->exclude as $exclude) {
                $excludedPropertiesIDs[$exclude['product_id']] = $exclude['product_id'];
            }
        }

        $countExcludeProperties = 0;
        if (property_exists($comparebles_properties, 'result_queryAllRows') && !empty($excludedPropertiesIDs)) {
            foreach ($comparebles_properties->result_queryAllRows as $comparebles_property) {
                $comparebles_property = (object)$comparebles_property;
                if (!empty($excludedPropertiesIDs[$comparebles_property->property_id])) {
                    $countExcludeProperties++;
                }
            }
        }
        return $countExcludeProperties;
    }

    /**
     * Build the photo array from propertyInfoPhoto relation.
     */
    public function getPhotoArr($modelArr)
    {
        if (!isset($modelArr->propertyInfoPhoto[0])) {
            return [];
        }
        $model    = $modelArr->propertyInfoPhoto[0];
        $photoArr = [];

        for ($j = 2; $j <= 40; $j++) {
            $photo_key   = 'photo' . $j;
            $caption_key = 'caption' . $j;
            if (!empty($model->$photo_key)) {
                $caption    = property_exists($model, $caption_key) ? $model->$caption_key : '';
                $photoArr[] = (object)[
                    'property_id' => $model->property_id,
                    'caption'     => $caption,
                    'photo1'      => $model->$photo_key,
                    'fullAddress' => $modelArr->getFullAddress() . ' ' . $photo_key,
                ];
            }
        }

        if (isset($photoArr[0]) && strtolower(substr($photoArr[0]->photo1, 0, 4)) === 'http') {
            $file_headers = CPathCDN::checkS3Photo($photoArr[0]->photo1);
            if (isset($file_headers[0]) && $file_headers[0] === 'HTTP/1.1 404 Not Found') {
                return [];
            }
        }

        return $photoArr;
    }

    /**
     * Build a full address string from a property object or model.
     */
    public function getFullAddress($property = false)
    {
        if (!$property) {
            return '';
        }
        $address = $property->property_street ?? '';
        $address .= !empty($address) ? ' ' : '';
        $city_name = $property->city_name ?? ($property->city->city_name ?? '');
        $address   .= $city_name;
        $address   = ucwords(strtolower($address));
        $state_code = $property->state_code ?? ($property->state->state_code ?? '');
        $address   .= !empty($address) ? ', ' : '';
        $address   .= strtoupper($state_code);
        $zip_code   = $property->zip_code ?? ($property->zipcode->zip_code ?? '');
        $address   .= !empty($address) ? ' ' : '';
        $address   .= strtoupper($zip_code);

        return $address;
    }
}
