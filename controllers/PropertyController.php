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
use app\models\UserProfiles;
use app\models\SavedAgent;
use app\models\TblAuthAssignment;
use app\models\Zipcode;
use app\models\City;
use app\models\County;
use app\models\State;
use app\components\SiteHelper;
use app\components\CPathCDN;
use app\components\EstimatedPrice;
use yii\helpers\Html;
use yii\helpers\Json;
use DateTime;
use DateTimeZone;

class PropertyController extends Controller
{
    public $layout = 'irradii_main';

    public $status_types = [
        'For Sale' => [
            'FOR SALE', 'ACTIVE', 'ACTIVE-EXCLUSIVE RIGHT', 'EXCLUSIVE AGENCY', 'OPPORTUNITY', 'FORECLOSURE', 'SHORT SALE', 'AUCTION'
        ],
        'For Rent' => [
            'FOR RENT', 'ACTIVE', 'ACTIVE-EXCLUSIVE RIGHT', 'EXCLUSIVE AGENCY', 'OPPORTUNITY', 'FORECLOSURE', 'SHORT SALE', 'AUCTION'
        ],
        'Under Contract' => [
            'PENDING OFFER', 'CONTINGENT OFFER', 'Under Contract - No Show', 'Under Contract - Show'
        ],
        'Sold' => [
            'RECENTLY SOLD', 'SOLD', 'LEASED', 'CLOSED'
        ],
        'Leased' => [
            'RECENTLY SOLD', 'SOLD', 'LEASED', 'CLOSED'
        ],
        'History' => [
            'HISTORY', 'TEMPOFF','INCOMPLETE','NOT FOR SALE', 'TEMPORARILY OFF THE MARKET', 'EXPIRED', 'WITHDRAWN', 'WITHDRAWN UNCONDITIONAL', 'WITHDRAWN CONDITIONAL'
        ]
    ];

    public $default_excluded_status_types = ['History'];

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        // Public actions — accessible by guests and logged-in users
                        'actions' => ['details', 'get-comp-property-details', 'history', 'online', 'getmoreconfidenceinfo', 'get-more-confidence-info', 'search', 'chat', 'messages'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        // Authenticated-only actions
                        'actions' => [
                            'update-user-property-status', 'updateuserpropertystatus',
                            'get-more-confidence-info', 'getmoreconfidenceinfo',
                            'add-exclude-property', 'addexcludeproperty',
                            'delete-exclude-property', 'deleteexcludeproperty',
                            'save-agent', 'saveagent',
                            'detach-agent', 'detachagent',
                            'favorites',
                            'add-favorites', 'addfavorites',
                            'delete-favorites', 'deletefavorites',
                            'update-min-stage', 'updateminstage',
                            'update-excluded-statuses', 'updateexcludedstatuses',
                            'update-props-by-shape', 'updatepropsbyshape',
                            'table2-tail', 'table2tail',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        // Deny everything else
                        'allow' => false,
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
            }], true, 'INNER JOIN')
            ->with([
                'city', 'county', 'state', 'zipcode',
                'propertyInfoAdditionalBrokerageDetails',
                'propertyInfoDetails',
                'propertyInfoPhoto',
                'propertyInfoAdditionalDetails',
                'user.profile',
            ])
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
                    'user.profile',
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
                $details->estimated_price, $details->bedrooms, $details->subdivision,
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
     * Get property details for comparables modal (AJAX).
     */
    public function actionGetCompPropertyDetails()
    {
        if (!Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }

        $id     = Yii::$app->request->post('property_id', 0);
        $result = [];
        $property_type_array = PropertyInfo::getPropertyType();

        if ($id) {
            $details = PropertyInfo::find()->with([
                'propertyInfoAdditionalBrokerageDetails',
                'propertyInfoAdditionalDetails',
                'propertyInfoDetails',
                'propertyInfoPhoto',
                'user',
                'city',
                'slug',
                'county',
                'state',
                'zipcode'
            ])->where(['property_id' => $id])->one();

            if ($details) {
                $sqFt = $details->house_square_footage;
                $acreage = $details->lot_acreage;
                $bedrooms = $details->bedrooms;
                $bathrooms = $details->bathrooms;

                $discont = $details->getDiscontValue();
                $tmv = $details->estimated_price;
                $result['discont'] = '';
                if ($discont >= (Yii::$app->params['underValueDeals'] ?? 10)) {
                    $result['discont'] = '<span class="label bg-color-greenDark">' . round($discont) . '% Below TMV</span>';
                }

                // photos
                $slider_arr = [];
                $photoArr = $this->getPhotoArr($details);

                foreach ($photoArr as $propertyInfoPhoto) {
                    $photocaption = (!empty($propertyInfoPhoto->caption)) ? "<p>{$propertyInfoPhoto->caption}</p>" : '';
                    $slider_arr[] = '<div class="item">' . CPathCDN::checkPhoto($propertyInfoPhoto, "", 0) . $photocaption . '</div>';
                }

                $result['carousel'] = implode('', $slider_arr);
                $result['property_id'] = $details->property_id;
                $result['property_street'] = $details->property_street;
                $result['url'] = Yii::$app->urlManager->createUrl(['property/details', 'slug' => $details->slug->slug ?? $details->property_id]);
                $result['city'] = ($details->city->city_name ?? '') . ', ' . ($details->state->state_code ?? '') . ' ' . ($details->zipcode->zip_code ?? '');
                $result['subdivision'] = $details->subdivision;
                $result['type'] = $property_type_array[$details->property_type] ?? '';
                $result['metrics'] = '<p>' . $sqFt . ' Sq Ft / ' . $acreage . ' Acre<br>' . $bedrooms . ' Beds / ' . $bathrooms . ' Baths';
                $result['tmv'] = $tmv ? '$' . number_format($tmv) : '';
            }
        }

        return $this->asJson($result);
    }

    /**
     * Update the minimum calculation stage stored in session.
     */
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
                $details->estimated_price, $details->bedrooms, $details->subdivision,
                $details->fundamentals_factor, $details->conditional_factor,
                $house_views, $details->sub_type
            );
            $result['status']       = 'success';
            $result['c_properties'] = $this->getCompareblesProperties($comparebles_properties, $details);
            $result['comparebles']  = $comparebles_properties;
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
     * Track that a user is online (called every 5 min by JS).
     */
    public function actionOnline()
    {
        $session = Yii::$app->session;
        if (!$session->isActive) {
            $session->open();
        }
        if (!Yii::$app->user->isGuest) {
            User::updateAll(
                ['lastvisit_at' => date('Y-m-d H:i:s')],
                ['id' => Yii::$app->user->id]
            );
        }
        return $this->asJson(['status' => 'ok']);
    }

    /**
     * Agent chat functionality (ported from legacy ChatAction)
     */
    public function actionChat()
    {
        $request = Yii::$app->request;
        $action = $request->post('action', '');
        $owner_mid = $request->post('owner_mid', 0);
        $property_zipcode = $request->post('property_zipcode', 0);
        $user_type = $request->post('user_type', '');

        $datetime_now = new DateTime();
        $result = [];
        $result['chat_users'] = [];

        if ($user_type) {
            $collocitors_list = $this->getCollocutorList();
            if ($collocitors_list) {
                foreach ($collocitors_list as $collocitor) {
                    $online = 'no';
                    $lastvisit = $collocitor->user->lastvisit_at ?? null;
                    if ($lastvisit) {
                        $datetime_exp = new DateTime($lastvisit);
                        $interval = $datetime_now->diff($datetime_exp);
                        if (($interval->days == 0) && ($interval->h == 0) && ($interval->i < 5)) {
                            $online = 'yes';
                        }
                    }
                    $profile = $collocitor->user->profile ?? null;
                    if ($profile) {
                        $this->fixProfilePhotos($profile);
                        $result['chat_users'][$collocitor->user->id] = [
                            'profile' => $profile, 
                            'user' => $online,
                            'state_code' => $profile->state
                        ];
                    }
                }
            }
            return $this->asJson($result);
        }

        $result['user_type'] = 'user';
        if ($action == 'post' || $action == 'get' || $action == '') {
            if ($property_zipcode != 0) {
                $agent_profiles = UserProfiles::find()
                    ->where(['zipcode' => $property_zipcode])
                    ->all();

                $result['advertising_agents_list'] = [];
                foreach ($agent_profiles as $profile) {
                    $agent_info = User::find()->where(['id' => $profile->mid])->with(['profile'])->one();
                    if (!$agent_info) continue;

                    if (!Yii::$app->user->isGuest && $result['user_type'] === 'user') {
                        $result['user_type'] = $this->checkUserType($profile->mid);
                    }

                    $online = 'no';
                    $lastvisit = $agent_info->lastvisit_at ?? null;
                    if ($lastvisit) {
                        $datetime_exp = new DateTime($lastvisit);
                        $interval = $datetime_now->diff($datetime_exp);
                        if (($interval->days == 0) && ($interval->h == 0) && ($interval->i < 5)) {
                            $online = 'yes';
                        }
                    }

                    $this->fixProfilePhotos($profile);
                    
                    $agent_data = [
                        'profile' => $profile,
                        'user' => $online,
                        'state_code' => $profile->state
                    ];
                    $result['advertising_agents_list'][] = $agent_data;
                    $result['chat_users'][$profile->mid] = $agent_data;
                }
            }

            // Owner info
            $owner_info = User::find()->where(['id' => $owner_mid])->with(['profile'])->one();
            $result['owner'] = [];
            if ($owner_info && $owner_info->profile) {
                $online = 'no';
                $lastvisit = $owner_info->lastvisit_at ?? null;
                if ($lastvisit) {
                    $datetime_exp = new DateTime($lastvisit);
                    $interval = $datetime_now->diff($datetime_exp);
                    if (($interval->days == 0) && ($interval->h == 0) && ($interval->i < 5)) {
                        $online = 'yes';
                    }
                }
                $this->fixProfilePhotos($owner_info->profile);
                $owner_data = [
                    'profile' => $owner_info->profile, 
                    'user' => $online,
                    'state_code' => $owner_info->profile->state
                ];
                $result['owner'][] = $owner_data;
                $result['chat_users'][$owner_info->id] = $owner_data;
                
                if (!Yii::$app->user->isGuest && $result['user_type'] === 'user') {
                    $result['user_type'] = $this->checkUserType($owner_mid);
                }
            }

            // Current user
            $result['current_user'] = [];
            if (!Yii::$app->user->isGuest) {
                $current_user = User::find()->where(['id' => Yii::$app->user->id])->with(['profile'])->one();
                if ($current_user && $current_user->profile) {
                    $online = 'yes'; // Current user is online
                    $result['current_user'] = [
                        'profile' => $current_user->profile, 
                        'user' => $online,
                        'state_code' => $current_user->profile->state
                    ];
                }

                // Saved agents
                $result['saved_agents'] = [];
                $saved_agents = SavedAgent::find()->where(['mid' => Yii::$app->user->id])->all();
                foreach ($saved_agents as $saved) {
                    $agent_u = User::find()->where(['id' => $saved->agent_id])->with(['profile'])->one();
                    if ($agent_u && $agent_u->profile) {
                        $online = 'no';
                        $lastvisit = $agent_u->lastvisit_at ?? null;
                        if ($lastvisit) {
                            $datetime_exp = new DateTime($lastvisit);
                            $interval = $datetime_now->diff($datetime_exp);
                            if (($interval->days == 0) && ($interval->h == 0) && ($interval->i < 5)) {
                                $online = 'yes';
                            }
                        }
                        $this->fixProfilePhotos($agent_u->profile);
                        $agent_data = [
                            'profile' => $agent_u->profile,
                            'user' => $online,
                            'state_code' => $agent_u->profile->state
                        ];
                        $result['saved_agents'][] = $agent_data;
                        $result['chat_users'][$agent_u->id] = $agent_data;
                    }
                }
            }
        }

        return $this->asJson($result);
    }

    /**
     * Agent messages functionality (ported from legacy ChatMessages)
     */
    public function actionMessages()
    {
        $request = Yii::$app->request;
        $owner_room = $request->post('owner_room', '');
        $collocutor = $request->post('collocutor', '');
        $message = $request->post('message', '');
        $m_type = $request->post('m_type', '');

        if (!empty($message) && $collocutor != 0) {
            $model = new TblChat();
            $model->owner_room = (int)$owner_room;
            $model->collocutor_id = (int)$collocutor;
            $model->author_id = Yii::$app->user->id;
            $model->chat_message = $message;
            $model->chat_created = date("Y-m-d H:i:s");
            $model->type = ($m_type == 'yes' ? 'chat' : 'message');
            
            if ($model->save()) {
                if ($model->type == 'message') {
                    $to = User::findOne($owner_room);
                    $from = User::findOne(Yii::$app->user->id);
                    if ($to && $from) {
                        $subject = Yii::$app->params['chatMessage'] ?? 'New Message';
                        Yii::$app->mailer->compose()
                            ->setFrom($from->username)
                            ->setTo($to->username)
                            ->setSubject($subject)
                            ->setTextBody($message)
                            ->send();
                    }
                }
            }
        }

        $result = [];
        if ($collocutor != 0) {
            $result = $this->getMessagesList($collocutor, $owner_room);
        }

        return $this->asJson($result);
    }

    private function getMessagesList($collocutor, $owner_room)
    {
        $result['messages'] = [];
        $messages = TblChat::find()
            ->where(['collocutor_id' => $collocutor, 'owner_room' => $owner_room])
            ->with(['user.profile'])
            ->all();

        $datetime_now = new DateTime();
        foreach ($messages as $msg) {
            $online = 'no';
            $lastvisit = $msg->user->lastvisit_at ?? null;
            if ($lastvisit) {
                $datetime_exp = new DateTime($lastvisit);
                $interval = $datetime_now->diff($datetime_exp);
                if (($interval->days == 0) && ($interval->h == 0) && ($interval->i < 5)) {
                    $online = 'yes';
                }
            }
            $result['messages'][] = ['message' => $msg, 'user' => $online];
        }
        return $result;
    }

    private function checkUserType($mid)
    {
        if ($mid == Yii::$app->user->id) {
            return 'owner';
        }

        $user = User::findOne(Yii::$app->user->id);
        if ($user) {
            $assignments = TblAuthAssignment::find()->where(['userid' => $user->id])->all();
            foreach ($assignments as $assignment) {
                if ($assignment->itemname === 'Agent') {
                    return 'agent';
                }
            }
        }

        return 'user';
    }

    private function getCollocutorList()
    {
        return TblChat::find()
            ->where(['owner_room' => Yii::$app->user->id])
            ->groupBy('collocutor_id')
            ->with(['user.profile'])
            ->all();
    }

    private function fixProfilePhotos($profile)
    {
        $ext = ['.png', '.jpg', '.gif', '.jpeg'];
        
        // upload_photo
        $photo = $profile->upload_photo;
        $ext_f = strrpos($photo, '.');
        if ($ext_f !== false) {
            $sud_str_f = substr($photo, $ext_f);
            if (in_array(strtolower($sud_str_f), $ext)) {
                $path = Yii::getAlias('@app/web/images/avatars/') . $photo;
                if (!is_file($path)) $profile->upload_photo = 'male.png';
            } else {
                $profile->upload_photo = 'male.png';
            }
        } else {
            $profile->upload_photo = 'male.png';
        }

        // office_logo
        $logo = $profile->office_logo;
        $ext_f = strrpos($logo, '.');
        if ($ext_f !== false) {
            $sud_str_f = substr($logo, $ext_f);
            if (in_array(strtolower($sud_str_f), $ext)) {
                $path = Yii::getAlias('@app/web/images/office_logo/') . $logo;
                if (!is_file($path)) $profile->office_logo = 'male.png';
            } else {
                $profile->office_logo = 'male.png';
            }
        } else {
            $profile->office_logo = 'male.png';
        }
    }

    /**
     * Save agent for a property.
     */
    public function actionSaveAgent()
    {
        if (Yii::$app->request->isAjax) {
            if (!Yii::$app->user->isGuest) {
                $agent_id = Yii::$app->request->post('agent_id', 0);
                if ($agent_id != 0) {
                    $model = SavedAgent::find()->where(['agent_id' => $agent_id, 'mid' => Yii::$app->user->id])->one();
                    if (!$model) {
                        $model = new SavedAgent();
                        $model->agent_id = (int)$agent_id;
                        $model->mid = Yii::$app->user->id;
                        $model->saved_timestamp = time();
                        if ($model->save()) {
                            return $this->asJson(['status' => 'success']);
                        } else {
                            return $this->asJson(['status' => 'error', 'errors' => $model->getErrors()]);
                        }
                    } else {
                        return $this->asJson(['status' => 'already_saved']);
                    }
                }
            }
        }
        return $this->asJson(['status' => 'error']);
    }

    /**
     * Detach agent from a property.
     */
    public function actionDetachAgent()
    {
        if (Yii::$app->request->isAjax) {
            if (!Yii::$app->user->isGuest) {
                $agent_id = Yii::$app->request->post('agent_id', 0);
                if ($agent_id != 0) {
                    $deleted = SavedAgent::deleteAll(['agent_id' => $agent_id, 'mid' => Yii::$app->user->id]);
                    if ($deleted) {
                        return $this->asJson(['status' => 'success']);
                    }
                }
            }
        }
        return $this->asJson(['status' => 'error']);
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

        foreach ($comparebles_properties->result_queryAllRows as $comparebles_property) {
            $comparebles_property = (object)$comparebles_property;
            $result[] = $this->makeComparableRowForProperty($comparebles_property, $details, $excludedPropertiesIDs);
        }
        $result[] = $this->makeOwnComparableRow($details, $excludedPropertiesIDs);

        return $result;
    }

    /**
     * Build a single row of comparable property data for the table.
     */
    private function makeComparableRowForProperty($comparebles_property, $details, $excludedPropertiesIDs = [])
    {
        // Build the exclude/include tool button
        $isExcluded = in_array($comparebles_property->property_id, $excludedPropertiesIDs);
        $isSelf     = isset($comparebles_property->selfProp) && $comparebles_property->selfProp;

        if ($isSelf) {
            // Current subject property — no exclude button
            $toolBtn = '';
        } else {
            $excludeClass = $isExcluded ? 'fa-reply' : 'fa-times';
            $toolBtn = '<button class="btn btn-xs btn-danger exclude_reinclude ' . $excludeClass . '" data-property_id="' . $comparebles_property->property_id . '"></button>'
                . ' <a href="' . \yii\helpers\Url::to(['property/details', 'slug' => ($comparebles_property->slug ?? $comparebles_property->property_id)]) . '" class="btn btn-xs btn-success"><i class="fa fa-map-marker"></i></a>';
        }

        // Disabled/row-disable class if excluded
        $rowDataAttr = '';
        $rowDataAttr .= ' data-property_id="' . $comparebles_property->property_id . '"';
        $rowDataAttr .= ' data-lat="' . ($comparebles_property->getlatitude ?? '') . '"';
        $rowDataAttr .= ' data-lon="' . ($comparebles_property->getlongitude ?? '') . '"';
        $rowDataAttr .= ' data-status="' . ($comparebles_property->status ?? '') . '"';
        $rowDataAttr .= ' data-address="' . htmlspecialchars($comparebles_property->property_street ?? '') . '"';
        $rowDataAttr .= $isSelf ? ' data-self="1"' : '';
        $rowDataAttr .= $isExcluded ? ' data-excluded="1"' : ' data-excluded="0"';

        // col 1: row indicator span (carries map data-attrs for JS)
        $col_indicator = '<span class="property_info_row"' . $rowDataAttr . '></span>';

        // col 2: Address link
        $slug_val = $isSelf
            ? ($details->slug ? $details->slug->slug : $details->property_id)
            : ($comparebles_property->slug ?? $comparebles_property->property_id);
        $propUrl  = \yii\helpers\Url::to(['property/details', 'slug' => $slug_val]);
        $col_address = '<a data-property_id="' . $comparebles_property->property_id . '" href="' . $propUrl . '">'
            . Html::encode($comparebles_property->property_street);
        if (!empty($comparebles_property->photo1)) {
            if (!isset($comparebles_property->fullAddress)) {
                $comparebles_property->fullAddress = $this->getFullAddress($comparebles_property);
            }
            $col_address .= \app\components\CPathCDN::checkPhoto($comparebles_property, "thumb-img");
        }
        $col_address .= '</a>';

        // col 3: Status badge
        $statusVal   = $isSelf
            ? $details->getStatus()
            : ($comparebles_property->status ?? '');
        $similar_stat = strtoupper($statusVal);
        $col_status = '<span class="label label-default label_status">' . strtoupper($similar_stat) . '</span>';
        if (in_array($similar_stat, ['RECENTLY SOLD', 'CLOSED', 'SOLD', 'LEASED', 'UNDER CONTRACT - NO SHOW'])) {
            $col_status = '<span class="label label-default label_status_sold">' . strtoupper($similar_stat) . '</span>';
        } elseif (in_array($similar_stat, ['FOR SALE', 'ACTIVE', 'ACTIVE EXCLUSIVE RIGHT', 'EXCLUSIVE AGENCY'])) {
            $col_status = '<span class="label label-primary label_status_active">' . strtoupper($similar_stat) . '</span>';
        }

        // col 4: List price
        $col_list_price = $comparebles_property->property_price ? '$' . number_format($comparebles_property->property_price) : '-';

        // col 5: Sale price (closed/sold only)
        $soldStatuses = ['RECENTLY SOLD', 'CLOSED', 'SOLD', 'LEASED'];
        $col_sale_price = in_array($similar_stat, $soldStatuses)
            ? (!empty($comparebles_property->sale_price) ? '$' . number_format($comparebles_property->sale_price) : (!empty($comparebles_property->property_price) ? '$' . number_format($comparebles_property->property_price) : '-'))
            : '-';

        // col 6: TMV
        $col_tmv = !empty($comparebles_property->estimated_price)
            ? '$' . number_format($comparebles_property->estimated_price)
            : '';

        // col 7: Date (entry_date or uploaded_date)
        $dateVal = !empty($comparebles_property->entry_date)
            ? $comparebles_property->entry_date
            : ($comparebles_property->property_uploaded_date ?? '');
        $col_date = $dateVal ? date('Y-m-d', strtotime($dateVal)) : '';

        // col 8: $/SqFt
        $col_ppsqft = (!empty($comparebles_property->property_price) && !empty($comparebles_property->house_square_footage))
            ? '$' . number_format($comparebles_property->property_price / $comparebles_property->house_square_footage, 2)
            : '';

        // col 9: Sq Ft
        $col_sqft = $comparebles_property->house_square_footage ?? '';

        // col 10: Bed
        $col_bed = $comparebles_property->bedrooms ?? '';

        // col 11: Bath
        $col_bath = $comparebles_property->bathrooms ?? '';

        // col 12 (hidden): Lot
        $col_lot = $comparebles_property->lot_acreage ?? '';

        // col 13: Yr Blt
        $col_yr = $comparebles_property->year_biult_id ?? '';

        // col 14: Distance
        $col_dist = '';
        if (!$isSelf
            && !empty($comparebles_property->getlatitude) && !empty($details->getlatitude)
            && $comparebles_property->getlatitude != '0.000000'
        ) {
            $distMiles = $this->getDistance(
                $details->getlatitude, $details->getlongitude,
                $comparebles_property->getlatitude, $comparebles_property->getlongitude
            );
            $col_dist = number_format($distMiles, 2) . 'm';
        }

        // Days on market
        $dtz          = new DateTimeZone('UTC');
        $datetime_now = new DateTime('now', $dtz);
        $propertyDate = $isSelf
            ? (!empty($details->propertyInfoAdditionalBrokerageDetails->entry_date)
                ? $details->propertyInfoAdditionalBrokerageDetails->entry_date
                : $details->property_uploaded_date)
            : (!empty($comparebles_property->entry_date)
                ? $comparebles_property->entry_date
                : ($comparebles_property->property_uploaded_date ?? ''));
        $datetime_exp = new DateTime($propertyDate ?: 'now', $dtz);
        $col_dom      = $datetime_now->diff($datetime_exp)->days;

        // Hidden detail columns (15-28)
        $col_garage    = $comparebles_property->garages ?? '';
        $col_pool      = $comparebles_property->pool ?? '';
        $col_spa       = '';
        $col_condition = '';
        $col_faces     = $isSelf ? ($details->propertyInfoDetails->house_faces ?? '') : ($comparebles_property->house_faces ?? '');
        $col_views     = $isSelf ? ($details->propertyInfoDetails->house_views ?? '') : ($comparebles_property->house_views ?? '');
        $col_flooring  = $isSelf ? ($details->propertyInfoAdditionalDetails->flooring_description ?? '') : ($comparebles_property->flooring_description ?? '');
        $col_furnish   = $isSelf ? ($details->propertyInfoAdditionalDetails->furnishings_description ?? '') : ($comparebles_property->furnishings_description ?? '');
        $col_financing = $isSelf ? ($details->propertyInfoAdditionalBrokerageDetails->financing_considered ?? '') : ($comparebles_property->financing_considered ?? '');
        $col_forecl    = '';
        $col_shortsale = '';
        $col_bankowned = '';
        $col_origprice = '';

        // Column order MUST match the DataTable aoColumns definition in the view:
        // 0:indicator, 1:address, 2:status, 3:list_price, 4:sale_price, 5:tmv, 6:date,
        // 7:$/sqft, 8:sqft, 9:bed, 10:bath, 11:garage(h), 12:lot, 13:yr, 14:dist,
        // 15:stories(h), 16:pool(h), 17:spa(h), 18:condition(h), 19:faces(h),
        // 20:views(h), 21:flooring(h), 22:furnish(h), 23:financing(h), 24:forecl(h),
        // 25:shortsale(h), 26:bankowned(h), 27:origprice(h), 28:dom(h), 29:tool_options
        return [
            $col_indicator, $col_address, $col_status,
            $col_list_price, $col_sale_price, $col_tmv, $col_date,
            $col_ppsqft, $col_sqft, $col_bed, $col_bath,
            $col_garage, $col_lot, $col_yr, $col_dist,
            '', $col_pool, $col_spa, $col_condition, $col_faces,
            $col_views, $col_flooring, $col_furnish, $col_financing, $col_forecl,
            $col_shortsale, $col_bankowned, $col_origprice, $col_dom,
            $toolBtn,
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
     * Main compare property info calculator (wraps EstimatedPrice logic).
     */
    private function getComparePropertyInfo(
        $del_id, $property_id, $property_type, $property_zipcode,
        $property_lat, $property_lon, $year_biult_id,
        $lot_sq_footage, $house_sq_footage, $bathrooms, $garages, $pool,
        $percentage_depreciation_value, $estimated_price, $bedrooms,
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

        $house_views_list_full = \app\models\MarketTrendTable::houseViewsList();
        $house_views_list      = $this->getHouseViewsArr($house_views_list_full, $house_views);

        $estimatedPrice = new \app\components\EstimatedPrice();
        $estimatedPrice->calculateEstimatedPriceStage(
            $curStage,
            $result, $gettoday_date, $comp_time, $session_id,
            $property_id, $property_type, $property_zipcode,
            $property_lat, $property_lon, $year_biult_id,
            $lot_sq_footage, $house_sq_footage, $bathrooms, $garages, $pool,
            $percentage_depreciation_value, $estimated_price, $bedrooms,
            $subdivision, $fundamentals_factor, $conditional_factor,
            $house_views_list, $sub_type
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
        if (trim((string)$house_views) === '') {
            return [];
        }

        foreach ($house_views_list as $key => $view) {
            if (strpos($house_views, $view) === false) {
                $house_views_ret[] = $view;
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
        if (isset($comparebles_properties->exclude)) {
            foreach ($comparebles_properties->exclude as $exclude) {
                // Handle $exclude as array or object
                $pid = is_array($exclude) ? ($exclude['product_id'] ?? null) : ($exclude->product_id ?? null);
                if ($pid) {
                    $excludedPropertiesIDs[$pid] = $pid;
                }
            }
        }

        $countExcludeProperties = 0;
        if (isset($comparebles_properties->result_queryAllRows) && !empty($excludedPropertiesIDs)) {
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
     * Build the photo array from the property model.
     * Falls back to the property's own photo1 field and builds photo objects
     * iterating from photo2..photo40 if the separate photo table is missing.
     */
    public function getPhotoArr($modelArr)
    {
        $photoArr    = [];
        $urls        = []; // To prevent duplicates
        $fullAddress = method_exists($modelArr, 'getFullAddress') ? $modelArr->getFullAddress() : '';

        // 1. Fetch from relation (proper property_id join)
        try {
            $photos = $modelArr->propertyInfoPhoto;
            if (empty($photos) && !empty($modelArr->mls_sysid)) {
                // Secondary fallback: check if photos are linked via MLS system ID string
                $photos = \app\models\PropertyInfoPhoto::find()->where(['property_id' => $modelArr->mls_sysid])->all();
            }
        } catch (\Exception $e) {
            $photos = [];
        }

        if (!empty($photos)) {
            foreach ($photos as $propertyInfoPhoto) {
                for ($i = 1; $i <= 40; $i++) {
                    $field = 'photo' . $i;
                    if (!empty($propertyInfoPhoto->$field)) {
                        $photoUrl = $propertyInfoPhoto->$field;
                        if (strpos($photoUrl, 'irradii') !== false) {
                            $photoUrl = str_replace('irradii', 'ippraisall', $photoUrl);
                        }
                        
                        if (!in_array($photoUrl, $urls)) {
                            $caption = '';
                            if ($i <= 5) {
                                $captionField = 'caption' . $i;
                                $caption = $propertyInfoPhoto->$captionField ?? '';
                            }
                            $photoArr[] = (object)[
                                'property_id' => $propertyInfoPhoto->property_id ?? $modelArr->property_id,
                                'caption'     => $caption,
                                'photo1'      => $photoUrl,
                                'fullAddress' => $fullAddress,
                            ];
                            $urls[] = $photoUrl;
                        }
                    }
                }
            }
        }

        // 2. Fetch from main model fields (Fallback / Additional)
        for ($i = 1; $i <= 40; $i++) {
            $field = 'photo' . $i;
            if (isset($modelArr->$field) && !empty($modelArr->$field)) {
                $photoUrl = $modelArr->$field;
                if (strpos($photoUrl, 'irradii') !== false) {
                    $photoUrl = str_replace('irradii', 'ippraisall', $photoUrl);
                }
                
                if (!in_array($photoUrl, $urls)) {
                    $photoArr[] = (object)[
                        'property_id' => $modelArr->property_id,
                        'caption'     => ($i == 1 && isset($modelArr->caption1)) ? $modelArr->caption1 : '',
                        'photo1'      => $photoUrl,
                        'fullAddress' => $fullAddress,
                    ];
                    $urls[] = $photoUrl;
                }
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

    /**
     * Calculate distance between two lat/lon points.
     */
    private function getDistance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $dist  = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist  = acos($dist);
        $dist  = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        return ($miles * 1.609344);
    }

    /**
     * Property search action
     */
    public function actionSearch()
    {
        $request = Yii::$app->request;
        
        // Initial defaults for search page
        $search_results = [];
        $params = $request->isPost ? $request->post() : $request->get();
        
        // If we have search parameters, perform the search
        if (!empty($params)) {
            $query = PropertyInfo::find()->with([
                'city', 'state', 'zipcode', 'slug', 'propertyInfoPhoto',
                'propertyInfoAdditionalBrokerageDetails', 'propertyInfoDetails'
            ]);

            // Filter by Sale Type
            if (!empty($params['sale_type'])) {
                $this->applySaleTypeFilter($query, $params['sale_type']);
            }

            // Filter by Property Type (with mapping)
            if (!empty($params['property_type']) && is_array($params['property_type'])) {
                $mapped_types = [];
                $sub_types = [];
                foreach ($params['property_type'] as $type_code) {
                    if ($type_code == 'AK') {
                        $mapped_types[] = 1; $sub_types[] = 'Attached';
                    } elseif ($type_code == 'HI') {
                        $mapped_types[] = 1; $sub_types[] = 'Detached';
                    } else {
                        // Dummy SavedSearch to get mapping
                        $ssDummy = new \app\models\SavedSearch();
                        // Accessing private mapPropertyTypeCode is tricky, let's hardcode it for now based on SavedSearch.php:905
                        $hard_mapping = [
                            'NV' => 1, 'OR' => 16, 'CA1' => 2, 'TH' => 3, 
                            'DP' => 4, 'TP' => 4, 'FP' => 4, 'AZ' => 6, 
                            'CO' => 7, 'AL' => 5, 'Rental' => 9,
                        ];
                        if (isset($hard_mapping[$type_code])) {
                            $mapped_types[] = $hard_mapping[$type_code];
                        } elseif (is_numeric($type_code)) {
                            $mapped_types[] = (int)$type_code;
                        }
                    }
                }
                $mapped_types = array_unique($mapped_types);
                if (!empty($mapped_types)) {
                    $query->andWhere(['property_info.property_type' => $mapped_types]);
                }
                if (!empty($sub_types)) {
                    $query->andWhere(['property_info.sub_type' => $sub_types]);
                }
            }

            // Price Filters
            if (!empty($params['min_price'])) {
                $query->andWhere(['>=', 'property_info.property_price', (int)preg_replace('/[^0-9]/', '', $params['min_price'])]);
            }
            if (!empty($params['max_price'])) {
                $query->andWhere(['<=', 'property_info.property_price', (int)preg_replace('/[^0-9]/', '', $params['max_price'])]);
            }

            // Price per Sq Ft
            if (!empty($params['min_price_sqft'])) {
                $query->andWhere(['>=', 'property_info.price_sqft', (float)preg_replace('/[^0-9.]/', '', $params['min_price_sqft'])]);
            }
            if (!empty($params['max_price_sqft'])) {
                $query->andWhere(['<=', 'property_info.price_sqft', (float)preg_replace('/[^0-9.]/', '', $params['max_price_sqft'])]);
            }

            // Sq Ft Filters
            if (!empty($params['min_sqft'])) {
                $query->andWhere(['>=', 'property_info.house_square_footage', (int)preg_replace('/[^0-9]/', '', $params['min_sqft'])]);
            }
            if (!empty($params['max_sqft'])) {
                $query->andWhere(['<=', 'property_info.house_square_footage', (int)preg_replace('/[^0-9]/', '', $params['max_sqft'])]);
            }

            // Stories Filter
            if (!empty($params['stories']) && is_array($params['stories'])) {
                $stories = array_filter($params['stories']);
                if (!empty($stories)) {
                    $query->joinWith('propertyInfoDetails')->andWhere(['in', 'property_info_details.stories', $stories]);
                }
            }

            // Garages Filter
            if (!empty($params['garage']) && is_array($params['garage'])) {
                $garages = array_filter($params['garage']);
                if (!empty($garages)) {
                    $query->andWhere(['in', 'property_info.garages', $garages]);
                }
            }

            // Year Built Filters (e.g., "Yr 1990")
            if (!empty($params['min_year_built']) && $params['min_year_built'] != 'undefined') {
                $val = (int)preg_replace('/[^0-9]/', '', $params['min_year_built']);
                if ($val > 0) $query->andWhere(['>=', 'property_info.year_biult_id', $val]);
            }
            if (!empty($params['max_year_built']) && $params['max_year_built'] != 'undefined') {
                $val = (int)preg_replace('/[^0-9]/', '', $params['max_year_built']);
                if ($val > 0) $query->andWhere(['<=', 'property_info.year_biult_id', $val]);
            }

            // Lot Size (Acreage)
            if (!empty($params['min_lot_size'])) {
                $query->andWhere(['>=', 'property_info.lot_acreage', (float)preg_replace('/[^0-9.]/', '', $params['min_lot_size'])]);
            }
            if (!empty($params['max_lot_size'])) {
                $query->andWhere(['<=', 'property_info.lot_acreage', (float)preg_replace('/[^0-9.]/', '', $params['max_lot_size'])]);
            }

            // Keywords / Remarks
            if (!empty($params['keywords'])) {
                $query->andWhere(['or',
                    ['like', 'property_info.property_street', $params['keywords']],
                    ['like', 'property_info.public_remarks', $params['keywords']]
                ]);
            }

            // Location Filters (City, State, Zipcode)
            if (!empty($params['city'])) {
                $query->joinWith('city')->andWhere(['city.city_name' => $params['city']]);
            }
            if (!empty($params['state'])) {
                $query->joinWith('state')->andWhere(['state.state_code' => $params['state']]);
            }
            if (!empty($params['zipcode'])) {
                $query->andWhere(['property_info.property_zipcode' => $params['zipcode']]);
            }

            // Bed/Bath
            if (!empty($params['bed']) && $params['bed'] > 0) {
                $query->andWhere(['>=', 'property_info.bedrooms', (int)$params['bed']]);
            }
            if (!empty($params['bath']) && $params['bath'] > 0) {
                $query->andWhere(['>=', 'property_info.bathrooms', (float)$params['bath']]);
            }

            // Below Market filter
            if (!empty($params['bmarket']) && (float)$params['bmarket'] > 0) {
                $query->andWhere(['>=', 'property_info.percentage_depreciation_value', (float)$params['bmarket']]);
            }
            
            // Coordinate/Map Boundary Filters
            if (!empty($params['geodistance_rectangle'])) {
                $lat1 = $params['latitude1'] ?? 0;
                $lat2 = $params['latitude2'] ?? 0;
                $lon1 = $params['longitude1'] ?? 0;
                $lon2 = $params['longitude2'] ?? 0;
                if ($lat1 && $lat2 && $lon1 && $lon2) {
                    $min_lat = min($lat1, $lat2);
                    $max_lat = max($lat1, $lat2);
                    $min_lon = min($lon1, $lon2);
                    $max_lon = max($lon1, $lon2);
                    $query->andWhere(['between', 'property_info.getlatitude', $min_lat, $max_lat])
                          ->andWhere(['between', 'property_info.getlongitude', $min_lon, $max_lon]);
                }
            } elseif (!empty($params['geodistance_circle'])) {
                $lat = $params['latitude'] ?? 0;
                $lon = $params['longitude'] ?? 0;
                $radius = $params['radius'] ?? 0; // In meters usually from JS
                if ($lat && $lon && $radius) {
                    // Approximate distance filter: 1 degree latitude ~= 111km (111000m)
                    // This is a rough estimation for database fallback
                    $r_deg = $radius / 111000;
                    $query->andWhere(['between', 'property_info.getlatitude', $lat - $r_deg, $lat + $r_deg])
                          ->andWhere(['between', 'property_info.getlongitude', $lon - $r_deg, $lon + $r_deg]);
                }
            } elseif (!empty($params['geodistance_polygon'])) {
                $lats = $params['latitude'] ?? [];
                $lons = $params['longitude'] ?? [];
                if (!empty($lats) && !empty($lons)) {
                    $query->andWhere(['between', 'property_info.getlatitude', min($lats), max($lats)])
                          ->andWhere(['between', 'property_info.getlongitude', min($lons), max($lons)]);
                }
            }

            // Address/Search Field
            if (!empty($params['address'])) {
                $query->andWhere(['like', 'property_info.property_street', $params['address']]);
            } elseif (!empty($params['searchfld'])) {
                $query->andWhere(['like', 'property_info.property_street', $params['searchfld']]);
            }

            // Result handling
            $count = (int)$query->count();
            $search_results = $query->limit(200)->all();
            
            $latlon = SiteHelper::getLatLonResult($search_results);
            $formattedResults = SiteHelper::getSearchMapResult($search_results);

            if ($request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return [
                    'status' => ($count > 0) ? 'success' : 'nothing',
                    'count_result' => $count,
                    'latlon' => $latlon,
                    'result' => $formattedResults,
                    'res_map_layout' => $formattedResults,
                ];
            }
        }

        return $this->render('search', [
            'search_results' => $search_results,
            'general_search_fields' => $params,
            'top_search' => $params,
            'profile' => SiteHelper::getUserProfile(),
        ]);
    }

    /**
     * Helper to apply sale type filters (mirrors SavedSearch logic)
     */
    private function applySaleTypeFilter($query, $sale_type)
    {
        $activeStatuses = ['Active', 'Active Exclusive Right', 'Active-Exclusive Right', 'Auction', 'Exclusive Agency', 'For Sale'];

        switch ($sale_type) {
            case 'For Sale':
                $query->joinWith('propertyInfoAdditionalBrokerageDetails')
                      ->andWhere(['property_info_additional_brokerage_details.status' => $activeStatuses])
                      ->andWhere(['not', ['property_info.property_type' => 9]]);
                break;
            case 'Under Value':
                $query->andWhere(['>=', 'percentage_depreciation_value', 5])
                      ->andWhere(['<', 'percentage_depreciation_value', 15]);
                break;
            case 'Equity Deals':
                $query->andWhere(['>=', 'percentage_depreciation_value', 15]);
                break;
            case 'Foreclosures':
                $query->joinWith('propertyInfoAdditionalBrokerageDetails')
                      ->andWhere(['property_info_additional_brokerage_details.foreclosure' => 'yes']);
                break;
            case 'Shortsales':
                $query->joinWith('propertyInfoAdditionalBrokerageDetails')
                      ->andWhere(['property_info_additional_brokerage_details.short_sale' => 'yes']);
                break;
            case 'For Rent':
                $query->andWhere(['property_info.property_type' => 9]);
                break;
        }
    }

    /**
     * Get minimum component requirement for a calculation stage.
     */
    private function getCompMin($stage)
    {
        $comp_min = 0;
        if ($stage <= (Yii::$app->params['maxCalcStages'] ?? 5)) {
            $comps_min = (new \yii\db\Query())
                ->select('min_comp')
                ->from('compare_estimated_price_table')
                ->where(['stage' => $stage])
                ->one();

            if ($comps_min) {
                $comp_min = $comps_min['min_comp'];
            }
        }
        return (int)$comp_min;
    }

    /**
     * Get the list of excluded properties for the current session.
     */
    private function getExcludeProperty($session_id)
    {
        return \app\models\ExcludeProperty::find()
            ->where(['session_id' => $session_id])
            ->all();
    }

    /**
     * Internal helper to exclude a property if it's not already excluded.
     */
    private function actionExcludeProperty($property_id, $session_id)
    {
        $exists = \app\models\ExcludeProperty::find()
            ->where(['product_id' => $property_id, 'session_id' => $session_id])
            ->exists();

        if (!$exists) {
            $exclude = new \app\models\ExcludeProperty();
            $exclude->product_id = $property_id;
            $exclude->session_id = $session_id;
            $exclude->mid        = Yii::$app->user->id ?? 0;
            $exclude->save();
        }
    }
}
