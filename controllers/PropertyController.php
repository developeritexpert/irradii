<?php

class PropertyController extends Controller {

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    //public $defaultAction = 'details';
    public $layout = '//layouts/irradii';
    private $_lat;
    private $_lon;
    private $tail = false;

    public $yiiseo_model = array();

    public $status_types = array(
        'For Sale' => array(
            'FOR SALE', 'ACTIVE', 'ACTIVE-EXCLUSIVE RIGHT', 'EXCLUSIVE AGENCY', 'OPPORTUNITY', 'FORECLOSURE', 'SHORT SALE', 'AUCTION'
        ),
        'For Rent' => array(
            'FOR RENT', 'ACTIVE', 'ACTIVE-EXCLUSIVE RIGHT', 'EXCLUSIVE AGENCY', 'OPPORTUNITY', 'FORECLOSURE', 'SHORT SALE', 'AUCTION'
        ),
        'Under Contract' => array(
            'PENDING OFFER', 'CONTINGENT OFFER', 'Under Contract - No Show', 'Under Contract - Show'
        ),
        'Sold' => array(
            'RECENTLY SOLD', 'SOLD', 'LEASED', 'CLOSED'
        ),
        'Leased' => array(
            'RECENTLY SOLD', 'SOLD', 'LEASED', 'CLOSED'
        ),
        'History' => array(
            'HISTORY', 'TEMPOFF','INCOMPLETE','NOT FOR SALE', 'TEMPORARILY OFF THE MARKET', 'EXPIRED', 'WITHDRAWN', 'WITHDRAWN UNCONDITIONAL', 'WITHDRAWN CONDITIONAL'
        )
    );

    public $default_excluded_status_types = array('History');

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array(
                    'details', 'search', 'history',
                    'yiichat', 'chat' ,  'messages', 'online',
                    'getCompPropertyDetails','UpdateUserPropertyStatus',
                    ),
                'users' => array('*'),
            ),
                        array('allow', // allow all users to perform 'index' and 'view' actions
                'actions' => array(
//                    'index', 'view',
                    'addExcludeProperty',
                    'deleteExcludeProperty', 'saveAgent', 'detachAgent',
                    'Table2Tail', 'favorites', 'addFavorites', 'deleteFavorites', 'getMoreConfidenceInfo',
                    'updatePropsByShape',
                    'updateExcludedStatuses',
                    'updateMinStage'
                    ),
                'users' => array('@'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('SeoParams'),
		'roles'=>array('admin'),
            ),
//            array('allow', // allow admin user to perform 'admin' and 'delete' actions
//                'actions' => array('admin', 'delete'),
//                'users' => array('admin'),
//            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function actions() {
        return array(
            'yiichat'   => array('class' => 'YiiChatAction'),
            'chat'      => array('class' => 'ChatAction'),
            'messages'  => array('class' => 'ChatMessages'),
            'online'    => array('class' => 'ChatOnline'),
        );
    }

    public function actionDetails($slug) {

//$time_start1 = microtime(TRUE);
//$time_start = $time_start1;

//$time_end = microtime(TRUE);
//$time = $time_end - $time_start;
//$time_start = $time_end;
//Yii::log('Step 1-0: ' . $time ,'ERROR'); // 8.1081488132477

        if (!Yii::app()->user->isGuest  || Yii::app()->user->isGuest /**/ ) { // @TODO This for demonstration ONLY
            if (!Yii::app()->user->isGuest) {
                $model = User::model()->cache(1000, null, 3)->with('profile', 'profession')->findByPk(Yii::app()->user->id);
                if (!is_object($model)) {
                    $this->redirect(Yii::app()->createUrl('/user/login'));
                }
                $profile = $model->profile;
                $user_id = $model->id;
            }


                $details = PropertyInfo::model()->cache(1000,null,1)->with(array(
                    'propertyInfoAdditionalBrokerageDetails',
                    'propertyInfoAdditionalDetails',
                    'propertyInfoDetails',
                    'propertyInfoPhoto',
                    'user',
                    'city',
                    'county',
                    'state',
                    'zipcode',
                    'slug' => array(
                        'alias'=>'slug',
                        'joinType'=>'INNER JOIN',
                        'condition'=>'slug.slug = :slug',
                        'params' => array(':slug' => $slug)
                    )

                ))->find();

                if(isset($user_id) && !empty($details)){
                    $mls_sysid = $details->mls_sysid;
                    $mls_name = $details->mls_name;
                    $user_property_info = TblUserPropertyInfo::model()->findByAttributes(array(
                        'user_id'=>$user_id,'mls_sysid'=>$mls_sysid, 'mls_name'=>$mls_name
                    ));

                    if($user_property_info == NULL){
                        $command = Yii::app()->db->createCommand();
                        $command->insert('tbl_user_property_info', array(
                            'user_id' => $user_id,
                            'mls_sysid'=>$mls_sysid,
                            'mls_name'=>$mls_name,
                            'user_property_status' => 'Viewed',
                            'create_date'=>new CDbExpression('NOW()'),
                            'last_changed_date'=>new CDbExpression('NOW()'),
                            'last_viewed_date'=>new CDbExpression('NOW()')
                        ));
                        $user_property_info = TblUserPropertyInfo::model()->findByAttributes(array(
                            'user_id'=>$user_id,'mls_sysid'=>$mls_sysid, 'mls_name'=>$mls_name
                        ));
                        $user_property_info->user_property_status = 'New';
                    } else {
                        if(strtotime($details->property_uploaded_date) > strtotime($user_property_info->last_viewed_date)){
                            $user_property_info->user_property_status = 'Updated';
                        }
                        $command = Yii::app()->db->createCommand();
                        $command->update(
                            'tbl_user_property_info',
                            array(
                                'last_viewed_date'=>new CDbExpression('NOW()')
                            ),
                            'user_id=:user_id AND
                            mls_name=:mls_name AND
                            mls_sysid=:mls_sysid',
                            array(
                                ':user_id'=>$user_id,
                                ':mls_sysid'=>$mls_sysid,
                                ':mls_name'=>$mls_name
                            )
                        );
                    };


                }
//$time_end = microtime(TRUE);
//$time = $time_end - $time_start;
//$time_start = $time_end;
//Yii::log('Step 1: ' . $time ,'ERROR'); // 0.73820209503174
            // temprarily : for old urls with id
            if(empty($details) && is_numeric($slug)) {
                $details = PropertyInfo::model()->cache(1000,null,10)->with(
                        'propertyInfoAdditionalBrokerageDetails', 
                        'propertyInfoAdditionalDetails', 
                        'propertyInfoDetails', 
                        'propertyInfoPhoto',
                        'TblUserPropertyInfo',
                        'user', 
                        'city', 
                        'county', 
                        'state', 
                        'zipcode',
                        'slug'
                        )->findByPk($slug);
            }

            if(!empty($details)) {

                $this->yiiseo_model['PropertyInfo'] = $details;

            $this->setAnalitics($details->property_id);

            $market_info = array();
            $market_info['subdivision'] = '';
            $date = new DateTime();
            $yesterday = $date->modify('-1 day')->format('Y-m-d');
//$time_end = microtime(TRUE);
//$time = $time_end - $time_start;
//$time_start = $time_end;
//Yii::log('Step 2: ' . $time ,'ERROR'); // 0.7689750194549

            if ($details->subdivision != '') {
                $market_info['subdivision'] = TblCronMarketInfoSubdivision::model()->cache(1000,null,1)->findByAttributes(
                        array(
                            'subdivision' => $details->subdivision,
                            'date' => date('Y-m-d')
                        )
                );
                if (!$market_info['subdivision']) {
                    $market_info['subdivision'] = TblCronMarketInfoSubdivision::model()->cache(1000,null,1)->findByAttributes(
                            array(
                                'subdivision' => $details->subdivision,
                                'date' => $yesterday
                            )
                    );
                }
            } else {
                if ($details->area != '') {
                    $market_info['subdivision'] = TblCronMarketInfoArea::model()->cache(1000,null,1)->findByAttributes(
                            array(
                                'area' => $details->area,
                                'date' => date('Y-m-d')
                            )
                    );
                    if (!$market_info['subdivision']) {
                        $market_info['subdivision'] = TblCronMarketInfoArea::model()->cache(1000,null,1)->findByAttributes(
                                array(
                                    'area' => $details->area,
                                    'date' => $yesterday
                                )
                        );
                    }
                }
            }
//$time_end = microtime(TRUE);
//$time = $time_end - $time_start;
//$time_start = $time_end;
//Yii::log('Step 3: ' . $time ,'ERROR'); // 0.034250020980835


            $market_info['zipcode'] = TblCronMarketInfoZipcode::model()->cache(1000,null,1)->findByAttributes(
                    array(
                        'zipcode_id' => $details->property_zipcode,
                        'date' => date('Y-m-d')
                    )
            );
            if (!$market_info['zipcode']) {
                $market_info['zipcode'] = TblCronMarketInfoZipcode::model()->cache(1000,null,1)->findByAttributes(
                        array(
                            'zipcode_id' => $details->property_zipcode,
                            'date' => $yesterday
                        )
                );
            }

            $market_info['city'] = TblCronMarketInfoCity::model()->cache(1000,null,1)->findByAttributes(
                    array(
                        'city_id' => $details->property_city_id,
                        'date' => date('Y-m-d')
                    )
            );
            if ($market_info['city']) {
                $market_info['city'] = TblCronMarketInfoCity::model()->cache(1000,null,1)->findByAttributes(
                        array(
                            'city_id' => $details->property_city_id,
                            'date' => $yesterday
                        )
                );
            }

            $market_info['county'] = TblCronMarketInfoCounty::model()->cache(1000,null,1)->findByAttributes(
                    array(
                        'county_id' => $details->property_county_id,
                        'date' => date('Y-m-d')
                    )
            );
            if ($market_info['county']) {
                $market_info['county'] = TblCronMarketInfoCounty::model()->cache(1000,null,1)->findByAttributes(
                        array(
                            'county_id' => $details->property_county_id,
                            'date' => $yesterday
                        )
                );
            }

            $market_info['state'] = TblCronMarketInfoState::model()->cache(1000,null,1)->findByAttributes(
                    array(
                        'state_id' => $details->property_state_id,
                        'date' => date('Y-m-d')
                    )
            );
            if ($market_info['state']) {
                $market_info['state'] = TblCronMarketInfoState::model()->cache(1000,null,1)->findByAttributes(
                        array(
                            'state_id' => $details->property_state_id,
                            'date' => $yesterday
                        )
                );
            }
//$time_end = microtime(TRUE);
//$time = $time_end - $time_start;
//$time_start = $time_end;
//Yii::log('Step 4: ' . $time ,'ERROR'); // 0.42208790779114

            $similar_homes = $this->getSimilarHomesForSale($details->property_id,$details->property_zipcode, $details->property_price, $details->house_square_footage, $details->property_type, $details->year_biult_id);
            $s_homes = '';
            if($similar_homes){
                $s_homes = $this->getSimilarHomes($similar_homes);
//Yii::log('getSimilarHomes: ' . print_r($s_homes,1) ,'ERROR');
//Yii::log('getSimilarHomesForSale: ' . print_r($similar_homes,1) ,'ERROR');
            }
//$time_end = microtime(TRUE);
//$time = $time_end - $time_start;
//$time_start = $time_end;
//Yii::log('Step 5: ' . $time ,'ERROR'); // 0.012144088745117

            $c_properties = '';
            $comparebles_properties = '';
            if (($details->getlatitude != 0.000000) && ($details->getlongitude != 0.000000)) {
                $comparebles_properties = $this->getComparePropertyInfo(
                        '', $details->property_id, $details->property_type, $details->property_zipcode, $details->getlatitude, $details->getlongitude, $details->year_biult_id, $details->lot_acreage, $details->house_square_footage, $details->bathrooms, $details->garages, $details->pool, $details->percentage_depreciation_value, $details->estimated_price
                        , $details->bedrooms, $details->subdivision, $details->fundamentals_factor, $details->conditional_factor
                        , !empty($details->propertyInfoDetails->house_views)?$details->propertyInfoDetails->house_views:''
                        , $details->sub_type
                        );

                $c_properties = $this->getCompareblesProperties((object) $comparebles_properties, $details);
            }
//$time_end = microtime(TRUE);
//$time = $time_end - $time_start;
//$time_start = $time_end;
//Yii::log('Step 6: ' . $time ,'ERROR'); // 0.00029706954956055

            $countExcludeProperties = $this->countPropertiesExclude((object)$comparebles_properties);
//Yii::log('estimated_value_subject_property 2: ' . $comparebles_properties['estimated_value_subject_property'] ,'ERROR');

                $shape = '{}';
                $excluded_by_shape = '[]';
                if (Yii::app()->session->sessionID) {
                    $session_id = Yii::app()->session->sessionID;
                    $property_id = $details->property_id;

                    $mapShape = DetailsMapShape::model()->findByAttributes(array(
                        'session_id' => $session_id,
                        'prop_id' => $property_id
                    ));

                    if ($mapShape) {
                        $shape = $mapShape->shape;
                        $excluded_by_shape = $mapShape->excluded_props_by_shape;
                    }
                }

            $this->render('property_details', array(
                'model' => Yii::app()->user->isGuest ? '' : $model,
                'profile' => Yii::app()->user->isGuest ? '' : $profile,
                'details' => $details,
                'user_property_info' => isset($user_property_info) ? $user_property_info : null,
                'market_info' => (object) $market_info,
                'similar_homes' => $similar_homes,
                's_homes' => $s_homes,
                'comparebles_properties' => (object) $comparebles_properties,
                'c_properties' => $c_properties,
                'countExcludeProperties' =>$countExcludeProperties,
                'shape' => $shape,
                'excluded_by_shape' => $excluded_by_shape
            ));
//$time_end = microtime(TRUE);
//$time = $time_end - $time_start;
//$time_start = $time_end;
//Yii::log('Step 7: ' . $time ,'ERROR'); // 1.7910590171814

//$time_end = microtime(TRUE);
//$time = $time_end - $time_start1;
//Yii::log('Step All: ' . $time ,'ERROR'); // 11.875414848328
            } else {
//                throw new CHttpException(404,'Page does not exist asd.klfgans.gkln.');
                throw new CHttpException(404,'This property could be gone already!');
            }
        } else {
            $this->redirect('/user/login');
        }
    }

    public function actionGetCompPropertyDetails() {
        if(Yii::app()->request->isAjaxRequest) {
            $id = isset($_POST['property_id']) ? $_POST['property_id'] : 0;
            $result = array();
            $property_type_array = array('0' => 'Unknown', '1' => 'Single Family Home', '2' => 'Condo', '3' => 'Townhouse', '4' => 'Multi Family', '5' => 'Land', '6' => 'Mobile Home', '7' => 'Manufactured Home', '8' => 'Time Share', '9' => 'Rental', '16' => 'High Rise');

            if ($id) {
                $details = PropertyInfo::model()->with(
                    'propertyInfoAdditionalBrokerageDetails',
                    'propertyInfoAdditionalDetails',
                    'propertyInfoDetails',
                    'propertyInfoPhoto',
                    'user',
                    'city',
                    'slug',
                    'county',
                    'state',
                    'zipcode' )->findByPk($id);

                $sqFt = $details->house_square_footage;
                $acreage = $details->lot_acreage;
                $bedrooms = $details->bedrooms;
                $bathrooms = $details->bathrooms;

                $discont = $details->getDiscontValue();
                $tmv = $details->estimated_price;
                $result['discont'] = '';
                if ($discont >= Yii::app()->params['underValueDeals']) {
                    $result['discont'] = '<span class="label bg-color-greenDark">' . round($discont) . '% Below TMV</span>';
                }

                // photos
                $slider_arr = array();
                $photoArr = $this->getPhotoArr($details);

                foreach ($photoArr as $propertyInfoPhoto) {
                    $photocaption = $propertyInfoPhoto->caption ? "<p>{$propertyInfoPhoto->caption}</p>" : '';
                    $slider_arr[] = '<div class="item">' . CPathCDN::checkPhoto($propertyInfoPhoto, "", 0 ) . $photocaption . '</div>';
                    unset($photocaption);
                }

                $result['carousel'] = '';
                foreach ($slider_arr as $slider_arr_value) {
                    $result['carousel'] .= $slider_arr_value;
                }
                $result['property_id'] = $details->property_id;
                $result['property_street'] = $details->property_street;
                $result['url'] = Yii::app()->createUrl('property/details', array( 'slug'=>$details->slug->slug));
                $result['city'] = $details->city->city_name . ', ' . $details->state->state_code . ' ' . $details->zipcode->zip_code;
                $result['subdivision'] = $details->subdivision;
                $result['type'] =  (array_key_exists($details->property_type, $property_type_array)) ? $property_type_array[$details->property_type] : '';
                $result['metrics'] = '<p>' . $sqFt . ' Sq Ft / ' . $acreage . ' Acre<br>' . $bedrooms . ' Beds / ' . $bathrooms . ' Baths';
                $result['tmv'] = $tmv ? '$' . number_format($tmv) : '';
            }


//            echo CJSON::encode($details);
            echo json_encode($result);

            Yii::app()->end();
        }
    }

    public function actionUpdateMinStage() {
        if (Yii::app()->request->isAjaxRequest) {
            $session = Yii::app()->session;
            $min_stages = array();
            $property_id = isset($_POST['property_id']) ? $_POST['property_id'] : 0;
            $min_stage = isset($_POST['min_stage']) ? $_POST['min_stage'] : 0;

            if (isset($session['min_stages'])) {
                $min_stages = $session['min_stages'];
            }

            if ($property_id) {
                $min_stages[$property_id] = $min_stage;
                $session['min_stages'] = $min_stages;
            }

            $response = array(
                'session_min_stages' => $session['min_stages'],
                'min_stage' => $this->getMinStage($property_id)
            );

            echo json_encode($response);
            Yii::app()->end();
        }
    }
    
    public function actionUpdateUserPropertyStatus() {
        if (Yii::app()->request->isAjaxRequest) {
            $response = array();
            $raw_slug = explode('slug=',$_POST['property_slug']);
            $slug = isset($raw_slug[1]) ? $raw_slug[1] : $_POST['property_slug'];
            $model = User::model()->cache(1000, null, 3)->with('profile', 'profession')->findByPk(Yii::app()->user->id);
            if (!is_object($model)) {
                $this->redirect(Yii::app()->createUrl('/user/login'));
            }
            $user_id = $model->id;
            $details = PropertyInfo::model()->cache(1000,null,1)->with(array(
                'slug' => array(
                    'alias'=>'slug',
                    'joinType'=>'INNER JOIN',
                    'condition'=>'slug.slug = :slug',
                    'params' => array(':slug' => $slug)
                )

            ))->find();
            $mls_sysid = $details->mls_sysid;
            $mls_name = $details->mls_name;
            $command = Yii::app()->db->createCommand();
//            var_dump($mls_sysid, $mls_name); die('q');
            if($_POST['type'] == 'status'){
                $userPropertyStatus=$_POST['user_property_status'];
//                $userPropInfo = TblUserPropertyInfo::model()->findByAttributes(array(
//                    'user_id'=>$user_id,
//                    'mls_sysid'=>$mls_sysid,
//                    'mls_name'=>$mls_name
//                ));
//                var_dump($userPropInfo);
//                var_dump($userPropInfo);die('query');
                $query = $command->update(
                    'tbl_user_property_info',
                    array(
                        'user_property_status' => $userPropertyStatus,
                        'last_changed_date'=>new CDbExpression('NOW()')
                    ),
                    'user_id=:user_id AND
                    mls_sysid=:mls_sysid',
                    array(
                        ':user_id'=>$user_id,
                        ':mls_sysid'=>$mls_sysid,
                    )
                );
//                var_dump($query);die();
//                var_dump($userPropInfo);
//                $userPropInfo->user_property_status = $userPropertyStatus;
//                $userPropInfo->last_changed_date = date('Y-m-d h:m:s', time());
//                var_dump($userPropInfo);die('query');
//                $query = $userPropInfo->save();
//                die('q');
                if($query){
                    $response['status'] = 200;
                    $response['scheme'] = SiteHelper::getColorSchemeOfUserPropertyStatus($userPropertyStatus);
                }
            } elseif ($_POST['type'] == 'note'){
                $userPropertyNote=htmlspecialchars($_POST['user_property_note']);
                $query = $command->update(
                    'tbl_user_property_info',
                    array(
                        'user_property_note' => $userPropertyNote,
                        'last_changed_date'=>new CDbExpression('NOW()')
                    ),
                    'user_id=:user_id AND
                    mls_name=:mls_name AND
                    mls_sysid=:mls_sysid',
                    array(
                        ':user_id'=>$user_id,
                        ':mls_sysid'=>$mls_sysid,
                        ':mls_name'=>$mls_name
                    )
                );
                if($query === 1){
                    $response['status'] = 200;
                    $response['note'] = $userPropertyNote;
                }
            }
            

        }

        echo json_encode($response);
        Yii::app()->end();
    }

    private function getMinStage($property_id) {
        $session = Yii::app()->session;
        $min_stages = array();
        $min_stage = 1;

        if (isset($session['min_stages'])) {
            $min_stages = $session['min_stages'];

            if (array_key_exists($property_id, $min_stages)) {
                $min_stage = (int)$min_stages[$property_id];
            }
        }

        return $min_stage;
    }

    public function actionUpdateExcludedStatuses() {
        if (Yii::app()->request->isAjaxRequest) {
            $session = Yii::app()->session;
            $excluded_statuses = array();
            $property_id = isset($_POST['property_id']) ? $_POST['property_id'] : 0;
            $excluded_statuses_for_prop = isset($_POST['excluded_statuses']) ? $_POST['excluded_statuses'] : array();

            if (isset($session['excluded_statuses'])) {
                $excluded_statuses = $session['excluded_statuses'];
            }

            if ($property_id) {
//                if (count($excluded_statuses_for_prop) > 0) {
//                    $excluded_statuses[$property_id] = $excluded_statuses_for_prop;
//                } else {
//                    unset($excluded_statuses[$property_id]);
//                }

                $excluded_statuses[$property_id] = $excluded_statuses_for_prop;

                $session['excluded_statuses'] = $excluded_statuses;
            }

            $response = array(
                'session_excluded_statuses' => $session['excluded_statuses'],
                'string' => $this->getExcludedStatusesStr($property_id)
            );

//            echo json_encode($session['excluded_statuses']);
//            echo json_encode($this->getExcludedStatusesStr($property_id));
            echo json_encode($response);
            Yii::app()->end();
        }
    }

    public function actionGetMoreConfidenceInfo(){
        if(Yii::app()->request->isAjaxRequest){
            $id = isset($_POST['property_id']) ? $_POST['property_id'] : 0;
            $this->tail = isset($_POST['tail']) ? $_POST['tail'] : false;
            $result = array();
            $result['status'] = 'error';
            $result['c_properties'] = '';
            $result['comparebles'] = '';
            $comparebles_properties = '';
            $details = PropertyInfo::model()->with(
                        'propertyInfoAdditionalBrokerageDetails',
                        'propertyInfoAdditionalDetails',
                        'propertyInfoDetails',
                        'propertyInfoPhoto',
                        'user',
                        'city',
                        'county',
                        'state',
                        'zipcode' )->findByPk($id);
            if (($details->getlatitude != 0.000000) && ($details->getlongitude != 0.000000)) {
                $comparebles_properties = $this->getComparePropertyInfo(
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
                                                                    $details->subdivision, $details->fundamentals_factor, $details->conditional_factor
                                                                    , !empty($details->propertyInfoDetails->house_views)?$details->propertyInfoDetails->house_views:''
                                                                    , $details->sub_type
                            );

                $c_properties = $this->getCompareblesProperties((object) $comparebles_properties, $details);
                $result['status'] = 'success';
                $result['c_properties'] = $c_properties;
                $result['comparebles'] = $comparebles_properties;
            }
            echo CJSON::encode($result);
            Yii::app()->end();
        }
    }

    private function getSimilarHomes($similar_homes) {
        $result = array();
        foreach ($similar_homes as $similar_home) {

            $discont = 0;
            if (($similar_home['percentage_depreciation_value'] >= Yii::app()->params['underValueDeals'])) {
                $discont = $similar_home['percentage_depreciation_value'];
            }
            if ($discont == 0) {
                if (( ($similar_home['estimated_price'] > 0) &&
                    (100 - ($similar_home['property_price'] * 100 / $similar_home['estimated_price'])) > 0)) {
                    $discont = 100 - ($similar_home['property_price'] * 100 / $similar_home['estimated_price']);
                }
            }

            $similar_home = (object) $similar_home;
            $similar_home->fullAddress = $this->getFullAddress($similar_home);
            $col1 = "<a href=" . Yii::app()->createUrl('property/details', array('slug'=>$similar_home->slug)) . " >" 
                    . CPathCDN::checkPhoto($similar_home, "thumb-img-140", 0 ). "</a>";
            
            $col2 = '';
            $col2 .= '$';
            $col2 .= number_format($similar_home->property_price);
            $col2 .= '<br>';

            if (isset($similar_home->status)) {
                $similar_stat = strtoupper($similar_home->status);

                $conditon = $discont >= Yii::app()->params['underValueDeals'];
                $colorScheme = SiteHelper::getColorScheme($similar_stat, $conditon);

                $similar_widget__property_status = '<span class="label '.$colorScheme['label-color'].' ">';
                $col2 .= $similar_widget__property_status . ucfirst($similar_home->status) . '</span>';
            }

            $col3 = '';
            $col3 .= $similar_home->property_street;
            $col3 .= '<br>';
            $col3 .= $similar_home->city_name ? $similar_home->city_name . ', ' : '';
            $col3 .= $similar_home->state_code ? $similar_home->state_code . ' ' : '';
            $col3 .= $similar_home->zip_code ? $similar_home->zip_code : '';

            $col4 = '';
            $col4 .= $similar_home->house_square_footage;
            $col4 .= ' SqFt.';
            $col4 .= '<br>';
            $col4 .= $similar_home->bedrooms;
            $col4 .= ' Bed / ';
            $col4 .= $similar_home->bathrooms;
            $col4 .= ' Bath';
            $col4 .= '<br>';
            $col4 .= $similar_home->garages;
            $col4 .= ' Car Garage';
            $col4 .= '</td>';
            $result[] = array($col1, $col2, $col3, $col4);
        }
        return $result;
    }

    private function getCompareblesProperties($comparebles_properties, $details) {

        $result = array();

        if (!property_exists($comparebles_properties, 'result_query'))
            return $result;

        $excludedPropertiesIDs = array();

        if(property_exists($comparebles_properties, 'exclude')){
            foreach ($comparebles_properties->exclude as $exclude) {
                $excludedPropertiesIDs[] = $exclude['product_id'];
            }
        }

        foreach ($comparebles_properties->result_queryAllRows as $comparebles_property) {

            $comparebles_property = (object) $comparebles_property;
            $result[] = $this->makeComparableRowForProperty($comparebles_property, $details, $excludedPropertiesIDs);
        }

        $result[] = $this->makeOwnComparableRow($details, $excludedPropertiesIDs);

        return $result;

    }// end getCompareblesProperties



    private function makeComparableRowForProperty($comparebles_property, $details, $excludedPropertiesIDs=array()){

        $ex_class = 'fa-times';
        $excluded = 0;
        $ex_class_i = 0;
        if(in_array($comparebles_property->property_id , $excludedPropertiesIDs)){
            $ex_class = 'fa-reply';
            $ex_class_i = 1;
            $excluded = 1;
        }

        $widget__property_status='';

        $discont = 0;
        if (($comparebles_property->percentage_depreciation_value >= Yii::app()->params['underValueDeals'])) {
            $discont = $comparebles_property->percentage_depreciation_value;
        }
        if ($discont == 0) {
            if (( ($comparebles_property->estimated_price > 0) &&
                (100 - ($comparebles_property->property_price * 100 / $comparebles_property->estimated_price)) > 0)) {
                $discont = 100 - ($comparebles_property->property_price * 100 / $comparebles_property->estimated_price);
            }
        }

        if (isset($comparebles_property->status)) {
            $comp_stat = strtoupper($comparebles_property->status);
            $conditon = $discont >= Yii::app()->params['underValueDeals'];
            $colorScheme = SiteHelper::getColorScheme($comp_stat, $conditon);

            $widget__property_status = '<span class="label '.$colorScheme['label-color'].' ">';
        }

        if(!empty($colorScheme['status'])){
            $status_p = $colorScheme['status'];
        }else {
            $status_p = '';
        }
        //<!--Address -->
        if($comparebles_property->property_id == $details->property_id) {
            $col1 = '';
            $col1 .= '<a class="property_info_row"
                    data-lat="' . $comparebles_property->getlatitude . '"
                    data-lon="' . $comparebles_property->getlongitude . '"
                    data-status="'.$status_p.'"
                    data-self="'.true.'";
                    data-excluded="'.$excluded.'"
                    data-address= "' . $comparebles_property->property_street . '"
                    data-property_id= "' . $comparebles_property->property_id . '"
                    data-property="' . Yii::app()->createUrl('property/details',array('slug'=> ($comparebles_property->property_id != $details->property_id)?$comparebles_property->slug:$details->slug->slug))
                . '"     href="'. Yii::app()->createUrl('property/details',array('slug'=> ($comparebles_property->property_id != $details->property_id)?$comparebles_property->slug:$details->slug->slug)) . '">' . $comparebles_property->property_street;
        } else {

            $col1 = '';
            $col1 .= '<a class="property_info_row"
                        data-lat="' . $comparebles_property->getlatitude . '"
                        data-lon="' . $comparebles_property->getlongitude . '"
                        data-status="'.$status_p.'"
                        data-excluded="'.$excluded.'"
                        data-address= "' . $comparebles_property->property_street . '"
                        data-property_id= "' . $comparebles_property->property_id . '"
                        data-property="' . Yii::app()->createUrl('property/details',array('slug'=> ($comparebles_property->property_id != $details->property_id)?$comparebles_property->slug:$details->slug->slug))
                            . '"     href="'. Yii::app()->createUrl('property/details',array('slug'=> ($comparebles_property->property_id != $details->property_id)?$comparebles_property->slug:$details->slug->slug)) . '">' . $comparebles_property->property_street;
        }
        if ($comparebles_property->photo1) {
            $comparebles_property->fullAddress = $this->getFullAddress($comparebles_property);
            $col1 .= CPathCDN::checkPhoto($comparebles_property, "thumb-img" );
        }
        $col1 .= '</a>';
        //$col1 .= '</div>';

        //<!--Status -->
        $col2 = '';
        $col2 .= $widget__property_status;
        if(isset($comparebles_property->status)){
            $col2 .= $comparebles_property->status;
            $col2 .= $comparebles_property->status ? '</span>' : '';
        }


        //<!--List Price -->
        $col3 = '';

        if (empty($comparebles_property->selfProp) && isset($comparebles_property->status) &&
            ((strtoupper($comparebles_property->status) == 'HISTORY') ||
                (strtoupper($comparebles_property->status) == 'RECENTLY SOLD') ||
                (strtoupper($comparebles_property->status) == 'CLOSED') ||
                (strtoupper($comparebles_property->status) == 'SOLD') ||
                (strtoupper($comparebles_property->status) == 'TEMPOFF') ||
                (strtoupper($comparebles_property->status) == 'NOT FOR SALE') ||
                (strtoupper($comparebles_property->status) == 'TEMPORARILY OFF THE MARKET'))) {
                    if( (strtoupper($comparebles_property->status) == 'HISTORY') ||
                        (strtoupper($comparebles_property->status) == 'SOLD') ||
                        (strtoupper($comparebles_property->status) == 'CLOSED')) {
                        $col3 .= '$' . number_format($comparebles_property->list_price);
                    } else {
                        $col3 .= '-';
                    }
        } else {
            $col3 .= '$' . number_format($comparebles_property->property_price);
        }

        //<!--Sale Price -->
        $col4 = '';
        if (isset($comparebles_property->status) &&
            ((strtoupper($comparebles_property->status) == 'HISTORY') ||
                (strtoupper($comparebles_property->status) == 'SOLD') ||
                (strtoupper($comparebles_property->status) == 'LEASED') ||
                (strtoupper($comparebles_property->status) == 'CLOSED'))) {
            $col4 .= '$' . number_format($comparebles_property->property_price);
        } else {
            $col4 .= '-';
        }

        //<!--Sale Type -->
        $col5 = '';
        if($comparebles_property->estimated_price > 0){
            $col5 .= '$' . number_format($comparebles_property->estimated_price);
        } else {
            $col5 .= '-';
        }
        //<!--Date -->
        $col6 = '';
        if (isset($comparebles_property->status) && (strtoupper($comparebles_property->status) == 'HISTORY')) {
            $date = DateTime::createFromFormat('Y-m-d', $comparebles_property->property_updated_date)->sub(new DateInterval('P1Y'))->format('Y-m-d');
            $col6 .= $date;
        } else {
            $col6 .= $comparebles_property->property_updated_date;
        }

        //<!--$/SqFt -->
        $col7 = '';
        $col7 .= '$';
        $col7 .= ($comparebles_property->house_square_footage!=0)?number_format(($comparebles_property->property_price / $comparebles_property->house_square_footage),2,'.',','):0;

        //<!--Sq Ft -->
        $col8 = '';
        $col8 .= round($comparebles_property->house_square_footage);
        //<!--Bed -->
        $col9 = '';
        $col9 .= $comparebles_property->bedrooms;
        //<!--Bath -->
        $col10 = '';
        $col10 .= $comparebles_property->bathrooms;
        //<!--Lot -->
        $col11 = '';
        $col11 .= sprintf("%01.2f", round($comparebles_property->lot_acreage, 2));
        //<!--Yr Blt -->
        $col12 = '';
        $col12 .= $comparebles_property->year_biult_id;
        //<!--Dist -->
        $col13 = '';
        $col13 .= sprintf("%01.2f", round($this->getDistance($comparebles_property->getlatitude, $comparebles_property->getlongitude, $details->getlatitude, $details->getlongitude), 2));
        if($col13 == 'NaN' || $col13 == 0){
            $col13 = '-';
        }else{
            $col13 .= 'm';
        }
        //<!--Tool Options -->
        $col14 = '';

        if($comparebles_property->property_id != $details->property_id){
            $col14 .= '<span class=sorting_input>'.$ex_class_i.'</span>';
            $col14 .= '<button 
                           class="btn btn-danger btn-xs exclude_reinclude fa '.$ex_class.'"
                           data-property_id="' . $comparebles_property->property_id . '" 
                           data-placement="left"
                           onmouseover="showPopover(this)"
                           onmouseout="hidePopover(this)"
                           >';
            $col14 .= '</button>';
            $col14 .= '<button type="button"
                            onclick="showinmap(this)" 
                            property_id="'.$comparebles_property->property_id.'" 
                            class="show-in-map btn btn-success btn-xs fa fa-map-marker"
                            data-placement="left"
                            onmouseover="showPopover(this)"
                            onmouseleave="hidePopover(this)"
                            data-content="Show on map"
                            ></button>';
        }

        //<!--Stories -->
        $col15 = '';
        if($comparebles_property->property_id != $details->property_id){
            $col15 .= !empty($comparebles_property->stories)?$comparebles_property->stories:'';
        } else {
            $col15 .= !empty($details->propertyInfoDetails->stories)?$details->propertyInfoDetails->stories:'';
        }

        //<!--Spa -->
        $col16 = '';
        if($comparebles_property->property_id != $details->property_id){
            $col16 .= !empty($comparebles_property->spa)?$comparebles_property->spa:'';
        } else {
            $col16 .= !empty($details->propertyInfoDetails->spa)?$details->propertyInfoDetails->spa:'';
        }
        
        //<!--Condition -->
        $col17 = '';
        if($comparebles_property->property_id != $details->property_id){
            $col17 .= !empty($comparebles_property->over_all_property)?$comparebles_property->over_all_property:'';
        } else {
            $col17 .= !empty($details->propertyInfoAdditionalDetails->over_all_property)?$details->propertyInfoAdditionalDetails->over_all_property:'';
        }

        //<!--Foreclosure -->
        $col18 = '';
        if($comparebles_property->property_id != $details->property_id){
            $col18 .= !empty($comparebles_property->foreclosure)?$comparebles_property->foreclosure:'';
        } else {
            $col18 .= !empty($details->propertyInfoAdditionalBrokerageDetails->foreclosure)?$details->propertyInfoAdditionalBrokerageDetails->foreclosure:'';
        }

        //<!--Short Sale -->
        $col19 = '';
        if($comparebles_property->property_id != $details->property_id){
            $col19 .= !empty($comparebles_property->short_sale)?$comparebles_property->short_sale:'';
        } else {
            $col19 .= !empty($details->propertyInfoAdditionalBrokerageDetails->short_sale)?$details->propertyInfoAdditionalBrokerageDetails->short_sale:'';
        }

        //<!--Bank Owned -->
        $col20 = '';
        if($comparebles_property->property_id != $details->property_id){
            $col20 .= !empty($comparebles_property->reporeo)?$comparebles_property->reporeo:'';
        } else {
            $col20 .= !empty($details->propertyInfoAdditionalBrokerageDetails->reporeo)?$details->propertyInfoAdditionalBrokerageDetails->reporeo:'';
        }

        //<!--Original Price -->
        $col21 = '';
        if($comparebles_property->property_id != $details->property_id){
            $col21 .= !empty($comparebles_property->original_list_price)?'$' . number_format($comparebles_property->original_list_price):'';
        } else {
            $col21 .= !empty($details->propertyInfoAdditionalBrokerageDetails->original_list_price)?'$' . number_format($details->propertyInfoAdditionalBrokerageDetails->original_list_price):'';
        }

        //<!--Days on Market -->
        $col22 = '';
        $dtz  = new DateTimeZone(isset(Yii::app()->timeZone)?Yii::app()->timeZone:"UTC");
        $datetime_now = new DateTime();
        $datetime_now->setTimezone($dtz);
        if($comparebles_property->property_id != $details->property_id){
            $propertyDate = !empty($comparebles_property->entry_date)
                    ?$comparebles_property->entry_date:$comparebles_property->property_uploaded_date ;
        } else {
            $propertyDate = !empty($details->propertyInfoAdditionalBrokerageDetails->entry_date)
                    ?$details->propertyInfoAdditionalBrokerageDetails->entry_date:$details->property_uploaded_date ;
        }
        $datetime_exp = new DateTime($propertyDate, $dtz);
        $interval = $datetime_now->diff($datetime_exp);
        $col22 .= $interval->days;

        //<!--Garage -->
        $col23 = '';
        $col23 .= $comparebles_property->garages;

        //<!--Pool -->
        $col24 = '';
        $col24 .= $comparebles_property->pool;

        //<!--House Faces -->
        $col25 = '';
        if($comparebles_property->property_id != $details->property_id){
            $col25 .= !empty($comparebles_property->house_faces)?$comparebles_property->house_faces:'';
        } else {
            $col25 .= !empty($details->propertyInfoDetails->house_faces)?$details->propertyInfoDetails->house_faces:'';
        }
   
        //<!--House Views -->
        $col26 = '';
        if($comparebles_property->property_id != $details->property_id){
            $col26 .= !empty($comparebles_property->house_views)?$comparebles_property->house_views:'';
        } else {
            $col26 .= !empty($details->propertyInfoDetails->house_views)?$details->propertyInfoDetails->house_views:'';
        }
   
        //<!--Flooring -->
        $col27 = '';
        if($comparebles_property->property_id != $details->property_id){
            $col27 .= !empty($comparebles_property->flooring_description)?$comparebles_property->flooring_description:'';
        } else {
            $col27 .= !empty($details->propertyInfoAdditionalDetails->flooring_description)?$details->propertyInfoAdditionalDetails->flooring_description:'';
        }

        //<!--Furnishings -->
        $col28 = '';
        if($comparebles_property->property_id != $details->property_id){
            $col28 .= !empty($comparebles_property->furnishings_description)?$comparebles_property->furnishings_description:'';
        } else {
            $col28 .= !empty($details->propertyInfoAdditionalDetails->furnishings_description)?$details->propertyInfoAdditionalDetails->furnishings_description:'';
        }
        
        //<!--Financing -->
        $col29 = '';
        if($comparebles_property->property_id != $details->property_id){
            $col29 .= !empty($comparebles_property->financing_considered)?$comparebles_property->financing_considered:'';
        } else {
            $col29 .= !empty($details->propertyInfoAdditionalBrokerageDetails->financing_considered)?$details->propertyInfoAdditionalBrokerageDetails->financing_considered:'';
        }

        $result = array($col1, $col2, $col3, $col4, $col5, $col6, $col7, $col8, $col9, $col10, $col23, $col11, $col12, $col13
        ,$col15, $col24, $col16, $col17, $col25, $col26, $col27, $col28, $col29 , $col18, $col19, $col20, $col21, $col22
        , $col14
        );
        
        return $result;

    }// end makeComparableRowForProperty




    private function makeOwnComparableRow(PropertyInfo $details, $excludedPropertiesIDs=array()){
        $comparebles_property = (object) $details->getAttributes();
        $comparebles_property->status = $details->getStatus();
        $comparebles_property->selfProp = true;
        return $this->makeComparableRowForProperty($comparebles_property, $details, $excludedPropertiesIDs);
    }// end makeOwnComparableRow





    private function actionExcludeProperty($del_id, $session_id) {
        if ($del_id) {
            $criteria = new CDbCriteria();
            $criteria->alias = 't';
            $criteria->select = 'exid';
            $criteria->condition = "session_id = :session_id AND product_id = :del_id";
            $criteria->params = array(':session_id' => $session_id, ':del_id' => $del_id);
            $result_query_delete = ExcludeProperty::model()->find($criteria);

            if ($result_query_delete->exid == '') {
                $row = array();
                $row['mid'] = Yii::app()->user->id;
                $row['session_id'] = $session_id;
                $row['product_id'] = $del_id;
                $model = new ExcludeProperty;
                $model->attributes = $row;
                if ($model->validate()) {
                    $model->save();
                }
            }
        }
    }

    private function actionCompareEstimatedPriceTable($stage, $property_type) {
        $criteria = new CDbCriteria();
        $criteria->alias = 't';
        $criteria->select = '*';
        $criteria->condition = " property_type = :property_type AND stage = :stage ";
        $criteria->params = array(':property_type' => $property_type, ':stage' => $stage);
        return CompareEstimatedPriceTable::model()->find($criteria);
    }

    private function actionComparePropertyLat($property_lat, $distance) {
        $miles_distance = 0.014428 * $distance;
        $lat_from = $property_lat - $miles_distance;
        $lat_to = $property_lat + $miles_distance;
        $compare_property_lat_from = " BETWEEN '" . $lat_from . "' ";
        $compare_property_lat_to = " AND '" . $lat_to . "' ";
        return " AND `property_info`.`getlatitude` {$compare_property_lat_from} {$compare_property_lat_to}";
    }

    private function actionComparePropertyLon($property_lon, $distance) {
        $miles_distance = 0.014428 * $distance;
        ;
        $lon_from = $property_lon + $miles_distance;
        $lon_to = $property_lon - $miles_distance;
        $compare_property_lon_from = " BETWEEN '" . $lon_to . "' ";
        $compare_property_lon_to = " AND '" . $lon_from . "' ";
        return " AND `property_info`.`getlongitude` {$compare_property_lon_from} {$compare_property_lon_to} ";
    }

    private function actionComparePropertyYearBuild($year_biult_id, $year_compare) {
        $year_build_from = $year_biult_id - $year_compare;
        $year_build_to = $year_biult_id + $year_compare;
        $compare_property_year_build_from = " BETWEEN '" . $year_build_from . "' ";
        $compare_property_year_build_to = " AND '" . $year_build_to . "' ";
        return " AND `property_info`.`year_biult_id` {$compare_property_year_build_from} {$compare_property_year_build_to}";
    }

    private function actionCompareLotSqFootage($lot_sq_footage, $lotacre_compare) {
        $percent_lot_footage = $lot_sq_footage * ($lotacre_compare / 100);
        $lot_sq_footage_from = $lot_sq_footage - $percent_lot_footage;
        $lot_sq_footage_to = $lot_sq_footage + $percent_lot_footage;
        $compare_lot_sq_footage_from = " BETWEEN '" . $lot_sq_footage_from . "' ";
        $compare_lot_sq_footage_to = " AND '" . $lot_sq_footage_to . "'";
        return " AND `property_info`.`lot_acreage` {$compare_lot_sq_footage_from} {$compare_lot_sq_footage_to}";
    }

    private function actionCompareHouseSqFootage($house_sq_footage, $house_compare) {
        $percent_house_footage = $house_sq_footage * ($house_compare / 100);
        $house_sq_footage_from = $house_sq_footage - $percent_house_footage;
        $house_sq_footage_to = $house_sq_footage + $percent_house_footage;
        $compare_house_sq_footage_from = " BETWEEN '" . $house_sq_footage_from . "'";
        $compare_house_sq_footage_to = " AND '" . $house_sq_footage_to . "'";
        return " AND `property_info`.`house_square_footage` {$compare_house_sq_footage_from} {$compare_house_sq_footage_to}";
    }

    private function actionGetExcludeProperty($session_id) {
        $criteria = new CDbCriteria();
        $criteria->alias = 't';
        $criteria->select = 'exid';
        $criteria->condition = 'session_id = :session_id';
        $criteria->params = array(':session_id' => $session_id);
        return ExcludeProperty::model()->find($criteria);
    }

    private function actionComparePropertyInfo($property_id, $session_id, $gettoday_date, $comp_time, $compare_property_type, $compare_property_zipcode, $compare_property_year_build, $compare_lot_sq_footage, $compare_house_sq_footage, $compare_property_lon, $compare_property_lat,
                    $compare_bedrooms, $compare_bathrooms, $compare_subdivision, $compare_house_views, $compare_sub_type) {
        $excluded_statuses_str = $this->getExcludedStatusesStr($property_id);

        //was AND UPPER(`property_info_additional_brokerage_details`.`status`) NOT IN ('HISTORY','EXPIRED','AUCTION')
        //AND UPPER(`property_info_additional_brokerage_details`.`status`) NOT IN (". $excluded_statuses_str .")

        $result_query = Yii::app()->db->createCommand($sql="SELECT
                    count(`property_info`.`property_id`) AS count_property,

                    AVG( IF ( `property_info`.`house_square_footage`, `property_info`.`property_price` / `property_info`.`house_square_footage`, 0 ) ) AS comps_house_footage_average,
                    MAX(`property_info`.`house_square_footage`) AS house_footage_max,
                    MIN(`property_info`.`house_square_footage`) AS house_footage_min,
                    AVG(`property_info`.`property_price`) AS average_price, 
                    AVG(`property_info`.`house_square_footage`) AS house_square_footage_average, 
                    AVG(`property_info`.`bedrooms`) AS bedrooms_average, 
                    AVG(`property_info`.`bathrooms`) AS bathrooms_average, 
                    AVG(`property_info`.`garages`) AS garages_average,
                    AVG(`property_info`.`lot_acreage`) AS house_lot_acerage_average, 
                    AVG(`property_info`.`year_biult_id`) AS year_built_average, 
                    AVG(`property_info`.`pool`) AS pool_average, 
                    AVG(`property_info_details`.`spa`) AS spa_average, 
                    AVG( IF (`property_info`.`lot_acreage`, `property_info`.`property_price` / `property_info`.`lot_acreage`, 0 ) ) AS comps_lot_footage_average, 
                    MIN(`property_info`.`property_price`) AS min_price, 
                    VARIANCE(`property_info`.`property_price`) AS var_price, 
                    MAX(`property_info`.`property_price`) AS max_price,
                    MIN(`property_info`.`property_uploaded_date`) AS min_uploaded_date,
                    MAX(`property_info`.`property_uploaded_date`) AS max_uploaded_date,
                    MIN(`property_info`.`property_updated_date`) AS min_date, 
                    MAX(`property_info`.`property_updated_date`) AS max_date,
                    MIN(`property_info`.`house_square_footage`) AS min_sqft, 
                    MAX(`property_info`.`house_square_footage`) AS max_sqft,
                    MIN(`property_info`.`percentage_depreciation_value`) AS min_percent, 
                    MAX(`property_info`.`percentage_depreciation_value`) AS max_percent,
                    MIN( IF (`property_info`.`house_square_footage`, `property_info`.`property_price` / `property_info`.`house_square_footage`, 0 ) ) AS min_ppsqft, 		
                    MAX( IF (`property_info`.`house_square_footage`, `property_info`.`property_price` / `property_info`.`house_square_footage`, 0 ) ) AS max_ppsqft,
                    MAX(`property_info`.`lot_acreage`) AS max_lot, 
                    MIN(`property_info`.`lot_acreage`) AS min_lot,	
                    STDDEV( IF ( `property_info`.`house_square_footage`, `property_info`.`property_price` / `property_info`.`house_square_footage`, 0 ) ) AS square_footage_stddev, 
                    STDDEV( IF ( `property_info`.`lot_acreage`, `property_info`.`property_price` / `property_info`.`lot_acreage`, 0 ) ) AS lot_footage_stddev,
        AVG(IF (property_info.bathrooms, property_info.property_price/(property_info.bathrooms), 0 )) as bathrooms_amenity_average, 
        STDDEV(IF (property_info.bathrooms, property_info.property_price/(property_info.bathrooms), 0 )) as bathrooms_amenity_stddev, 
        AVG(IF (property_info.bedrooms, property_info.property_price/(property_info.bedrooms), 0 )) as bedrooms_amenity_average, 
        STDDEV(IF (property_info.bedrooms, property_info.property_price/(property_info.bedrooms), 0 )) as bedrooms_amenity_stddev, 
        AVG(IF (property_info.garages, property_info.property_price/(property_info.garages), NULL )) as garages_amenity_average,
        STDDEV(IF (property_info.garages, property_info.property_price/(property_info.garages), NULL )) as garages_amenity_stddev,
        AVG(IF (property_info.pool, property_info.property_price/(property_info.pool), NULL )) as pool_amenity_average,
        STDDEV(IF (property_info.pool, property_info.property_price/(property_info.pool), NULL )) as pool_amenity_stddev
                    
                    FROM `property_info` 
                    LEFT JOIN ( `property_info_details`, `property_info_additional_brokerage_details` )  
                    ON ( `property_info_details`.`property_id` = `property_info`.`property_id` AND `property_info_additional_brokerage_details`.`property_id`=`property_info`.`property_id` )
                    WHERE NOT EXISTS ( SELECT `exclude_property`.`product_id` 
                                       FROM `exclude_property`
                                       WHERE `exclude_property`.`product_id` = `property_info`.`property_id` 
                                       AND `exclude_property`.`session_id` = '" . $session_id . "' ) 
                          AND UPPER(`property_info`.`property_status`)='ACTIVE' 
                          AND UPPER(`property_info_additional_brokerage_details`.`status`) NOT IN (". $excluded_statuses_str .")
                          AND `property_info`.`property_price` > 0 
                          AND `property_info`.`property_expire_date` >='" . $gettoday_date . "' 
                          AND `property_info`.`property_updated_date` >='" . $comp_time . "' 
                          {$compare_property_type}
                          {$compare_property_zipcode}
                          {$compare_property_year_build} 
                          {$compare_lot_sq_footage} 
                          {$compare_house_sq_footage} 
                          {$compare_property_lon} 
                          {$compare_property_lat}
                          {$compare_bedrooms} {$compare_bathrooms}
                          {$compare_subdivision}
                          {$compare_house_views}
                          {$compare_sub_type}
                          AND `property_info`.`property_id` != '" . $property_id . "'")->queryRow();
//Yii::log('actionComparePropertyInfo sql: ' . $sql ,'ERROR');

        return (object) $result_query;
    }

    private function actionTtable2Tail($count_property) {
        $df_count = $count_property - 1;
        return TTable2Tail::model()->findByAttributes(array('df' => $df_count));
    }

    public function actionTable2Tail() {
        if (Yii::app()->request->isAjaxRequest) {
            $result = array();
            $result['status'] = 'error';
            $result['row'] = '';
            $count_property = isset($_POST['count_property']) ? $_POST['count_property'] : 0;
            if ($count_property > 1) {
                $df_count = $count_property - 1;
                $result['status'] = 'success';
                $result['row'] = TTable2Tail::model()->findByAttributes(array('df' => $df_count));
            }
            echo CJSON::encode($result);
            Yii::app()->end();
        } else {
            $this->redirect('/');
        }
    }

    private function actionComparePropertyInfoAllRows($property_id, $session_id, $gettoday_date, $comp_time, $compare_property_type, $compare_property_zipcode, $compare_property_year_build, $compare_lot_sq_footage, $compare_house_sq_footage, $compare_property_lon, $compare_property_lat,
                    $compare_bedrooms, $compare_bathrooms, $compare_subdivision, $compare_house_views, $compare_sub_type) {

        $excluded_statuses_str = $this->getExcludedStatusesStr($property_id);

        //was AND UPPER(`property_info_additional_brokerage_details`.`status`) NOT IN ('HISTORY','EXPIRED','AUCTION')
        //AND UPPER(`property_info_additional_brokerage_details`.`status`) NOT IN (". $excluded_statuses_str .")

        $result_query = Yii::app()->db->createCommand("SELECT
            `property_info`.*,
            `property_info_details`.*,
            `property_info_additional_brokerage_details`.*,
            `property_info_additional_details`.*,
            {{property_info_slug}}.slug
                    FROM `property_info` 
                    LEFT JOIN ( `property_info_details`, `property_info_additional_brokerage_details`, `property_info_additional_details`, {{property_info_slug}} )  
                    ON ( `property_info_details`.`property_id` = `property_info`.`property_id` AND `property_info_additional_brokerage_details`.`property_id`=`property_info`.`property_id`
                     AND `property_info_additional_details`.`property_id`=`property_info`.`property_id` AND {{property_info_slug}}.property_id = `property_info`.`property_id` )
                    
                          WHERE UPPER(`property_info`.`property_status`)='ACTIVE'
                          AND UPPER(`property_info_additional_brokerage_details`.`status`) NOT IN (". $excluded_statuses_str .")
                          AND `property_info`.`property_price` > 0
                          AND `property_info`.`property_expire_date` >='" . $gettoday_date . "' 
                          AND `property_info`.`property_updated_date` >='" . $comp_time . "' 
                          {$compare_property_type}
                          {$compare_property_zipcode}
                          {$compare_property_year_build} 
                          {$compare_lot_sq_footage} 
                          {$compare_house_sq_footage} 
                          {$compare_property_lon} 
                          {$compare_property_lat}
                          {$compare_bedrooms} {$compare_bathrooms}
                          {$compare_subdivision}
                          {$compare_house_views}
                          {$compare_sub_type}
                          AND `property_info`.`property_id` != '" . $property_id . "'")->queryAll();
        return (object) $result_query;
        /*WHERE NOT EXISTS ( SELECT `exclude_property`.`product_id` 
                                       FROM `exclude_property`
                                       WHERE `exclude_property`.`product_id` = `property_info`.`property_id` 
                                       AND `exclude_property`.`session_id` = '" . $session_id . "' ) */
    }

    private function getExcludedStatusesStr($property_id) {
        $session = Yii::app()->session;
        $excluded_status_types_for_prop = $this->default_excluded_status_types;
        $excluded_statuses_for_prop = array();
        $default_excluded_statuses = array('DEFAULT');
        if (isset($session['excluded_statuses'])) {
            $excluded_statuses = $session['excluded_statuses'];

            if (array_key_exists($property_id, $excluded_statuses)) {
                $excluded_status_types_for_prop = $excluded_statuses[$property_id];
            }
        }

        $excluded_statuses_for_prop = $default_excluded_statuses;

        if (is_array($excluded_status_types_for_prop)) {
            foreach($excluded_status_types_for_prop as $status_type) {
                if (array_key_exists($status_type, $this->status_types)) {
                    $excluded_statuses_for_prop = array_merge($excluded_statuses_for_prop, $this->status_types[$status_type]);
                }
            }
        }

        $excluded_statuses_str = '';

        for ($i = 0, $l = count($excluded_statuses_for_prop); $i < $l; $i++) {
            if ($i === 0) {
                $excluded_statuses_str .= "'" . $excluded_statuses_for_prop[$i] . "'";
            } else {
                $excluded_statuses_str .= ",'" . $excluded_statuses_for_prop[$i] . "'";
            }
        }

        return $excluded_statuses_str;
    }
    
    private function getExcludeProperty($session_id) {
        $result_query = Yii::app()->db->createCommand("SELECT `exclude_property`.`product_id` 
                                       FROM `exclude_property`
                                       WHERE `exclude_property`.`session_id` = '" . $session_id . "'")->queryAll();
        return (object) $result_query;
    }

    public function actionUpdatePropsByShape() {
        if (Yii::app()->request->isAjaxRequest) {
            if (!Yii::app()->session->sessionID) {
                Yii::app()->session->open();
            }

            $session_id = Yii::app()->session->sessionID;
            $property_id = isset($_POST['property_id']) ? $_POST['property_id'] : 0;
            $properties_for_excluding = isset($_POST['propertiesForExcluding']) ? $_POST['propertiesForExcluding'] : '';
            $properties_for_excluding = json_decode($properties_for_excluding, true);
            $properties_for_including = isset($_POST['propertiesForIncluding']) ? $_POST['propertiesForIncluding'] : '';
            $properties_for_including = json_decode($properties_for_including, true);
            $shape = isset($_POST['shape']) ? $_POST['shape'] : '';


            $mapShape = DetailsMapShape::model()->findByAttributes(array(
                'session_id' => $session_id,
                'prop_id' => $property_id
            ));
            $excluded_by_shape = array();

            if ($mapShape) {
                $isNew = false;

                if (!is_null($mapShape->excluded_props_by_shape)) {
                    $excluded_by_shape = json_decode($mapShape->excluded_props_by_shape, true);
                }

                $mapShape->shape = $shape;
            } else {
                $mapShape = new DetailsMapShape();
                $isNew = true;
                $mapShape->session_id = $session_id;
                $mapShape->prop_id = $property_id;
                $mapShape->shape = $shape;
            }

            // exclude props
            foreach ($properties_for_excluding as $prop_id => $prop) {
                $model = new ExcludeProperty;
                $row = array();
                $row['mid'] = Yii::app()->user->isGuest ? 0 : Yii::app()->user->id;
                $row['session_id'] = $session_id;
                $row['product_id'] = $prop_id;
                $model->attributes = $row;
                if ($model->validate()) {
                    $model->save();
                    $excluded_by_shape[$prop_id] = $prop;
                }
            }

            // include props
            foreach ($properties_for_including as $prop_id => $prop) {
                $criteria = new CDbCriteria();
                $criteria->condition = "product_id = :product_id AND session_id = :sesion_id";
                $criteria->params = array(':product_id' => $prop_id, ':sesion_id' => $session_id);
                if (ExcludeProperty::model()->deleteAll($criteria) > 0) {
                    unset($excluded_by_shape[$prop_id]);
                }
            }

            // save data
            $mapShape->excluded_props_by_shape =  json_encode($excluded_by_shape);
            if ($isNew === true) {
                if ($mapShape->validate()) {
                    $mapShape->save();
                }
            } else {
                $mapShape->update();
            }

            echo $mapShape->excluded_props_by_shape;
            exit();
        }
    }

    public function actionAddExcludeProperty() {
        if (Yii::app()->request->isAjaxRequest) {
            $result = 'error';
            if (!Yii::app()->session->sessionID) {
                Yii::app()->session->open();
            }
            $property_id = isset($_POST['property_id']) ? $_POST['property_id'] : 0;
            if ($property_id) {
                $model = new ExcludeProperty;
                $row = array();
                $row['mid'] = Yii::app()->user->isGuest ? 0 : Yii::app()->user->id;
                $row['session_id'] = Yii::app()->session->sessionID;
                $row['product_id'] = $property_id;
                $model->attributes = $row;
                if ($model->validate()) {
                    $model->save();
                    $result = 'success';
                }
                echo json_encode($result);
                exit();
            }
        } else {
            $this->redirect(Yii::app()->createUrl("/"));
        }
    }

    public function actionDeleteExcludeProperty() {
        if (Yii::app()->request->isAjaxRequest) {
            $result = 'error';
            if (!Yii::app()->session->sessionID) {
                Yii::app()->session->open();
            }
            $property_id = isset($_POST['property_id']) ? $_POST['property_id'] : 0;
            if ($property_id) {
                $criteria = new CDbCriteria();
                $criteria->condition = "product_id = :product_id AND session_id = :sesion_id";
                $criteria->params = array(':product_id' => $property_id, ':sesion_id' => Yii::app()->session->sessionID);
                ExcludeProperty::model()->deleteAll($criteria);
                $result = 'success';
            }
            echo json_encode($result);
            exit();
        } else {

            $this->redirect(Yii::app()->createUrl("/"));
        }
    }

    private function getTail($result_t_score) {
        if(!$this->tail){
           $res =  $result_t_score ? $result_t_score->tail_90 : 0;
        } else {
           $res =  $result_t_score ? $result_t_score->{$this->tail} : 0;
        }
        return $res;
    }

    private function getComparePropertyInfo($del_id,
                                            $property_id,
                                            $property_type,
                                            $property_zipcode,
                                            $property_lat,
                                            $property_lon,
                                            $year_biult_id,
                                            $lot_sq_footage,
                                            $house_sq_footage,
                                            $bathrooms,
                                            $garages,
                                            $pool,
                                            $percentage_depreciation_value,
                                            $estimated_price,
                                            $bedrooms,
                                            $subdivision,
                                            $fundamentals_factor,
                                            $conditional_factor,
                                            $house_views,
                                            $sub_type
            )
    {
        
        $result = array();
//        $curStage = 1;
        $curStage = $this->getMinStage($property_id);
        $gettoday_date = date('Y-m-d');
        $comp_time = date('Y-m-d', time() - 200 * 24 * 60 * 60);
        if (!Yii::app()->session->sessionID) {
            Yii::app()->session->open();
        }
        $session_id = Yii::app()->session->sessionID;
        if ($del_id) {
            $this->actionExcludeProperty($del_id, $session_id);
        }
        if (($property_type != '') && ($property_type != 0) && ($property_type != 4) && ($property_type != 5)) 
        {
        } else {
            return $result;
        }

        $house_views_list_full = MarketTrendTable::houseViewsList();
        $house_views_list = $this->getHouseViewsArr($house_views_list_full, $house_views);
        
        $this->calculateEstimatedPriceStage(
                    $curStage,
                    $result, $gettoday_date, $comp_time, $session_id,
                                            $property_id,
                                            $property_type,
                                            $property_zipcode,
                                            $property_lat,
                                            $property_lon,
                                            $year_biult_id,
                                            $lot_sq_footage,
                                            $house_sq_footage,
                                            $bathrooms,
                                            $garages,
                                            $pool,
                                            $percentage_depreciation_value,
                                            $estimated_price,
                                            $bedrooms,
                                            $subdivision,
                                            $fundamentals_factor,
                                            $conditional_factor,
                                            $house_views_list,
                                            $sub_type
                );
        $result['estimated_value_subject_property_stage'] = $result['estimated_value_subject_property'];
//Yii::log('estimated_value_subject_property 0-0: ' . print_r(array($result['estimated_value_subject_property'],$result['percentage_depreciation_value'],$result['current_stage'],$result['estimated_price_dollar']),1) ,'ERROR');
            if (($result['estimated_value_subject_property'] < 500)
                    ||  ($result['percentage_depreciation_value'] < -1000) 
                    ||  ($result['percentage_depreciation_value'] > 95)) {
//                $estimated_price = 0;
//                $estimated_price_dollar = 0;
//                $estimated_value_subject_property = 0;
//                $percentage_depreciation_value = 0;
                $result['estimated_price'] = 0;
                $result['estimated_price_dollar'] = 0;
                $result['estimated_value_subject_property'] = 0;
                $result['percentage_depreciation_value'] = 0;
            }

        
//        $result['estimated_price'] = $estimated_price;
//        $result['estimated_price_dollar'] = $estimated_price_dollar;
//        $result['estimated_value_subject_property'] = $estimated_value_subject_property;
//        $result['percentage_depreciation_value'] = $percentage_depreciation_value;
//        $result['low_range'] = $low_range;
//        $result['high_range'] = $high_range;
//        $result['result_query'] = $result_query;
//        $result['result_queryAllRows'] = $result_queryAllRows;
        $result['exclude'] = $this->getExcludeProperty($session_id);

        $result['comparable_price_sparkline'] = array();
        if(!empty($result['result_queryAllRows'])){
            foreach ($result['result_queryAllRows'] as $resultQueryRow) {
                $result['comparable_price_sparkline'][] = $resultQueryRow['property_price'];
            }
            natsort($result['comparable_price_sparkline']);
            
        }
//Yii::log('estimated_value_subject_property 0-1: ' . $result['estimated_value_subject_property'] ,'ERROR');
        return $result;
    }

    private function getCompMin($stage) {
        $comp_min = 0;

        if ($stage <= Yii::app()->params['maxCalcStages']) {
            $comps_min = Yii::app()->db->createCommand()
                ->select('min_comp')
                ->from('compare_estimated_price_table')
                ->where('stage=:stage', array(':stage'=>$stage))
                ->queryAll();

            $comps_min = end($comps_min);
            $comp_min = $comps_min['min_comp'];
        }

        return $comp_min;
    }

    private function calculateEstimatedPriceStage(
                    $curStage,
                    &$result, $gettoday_date, $comp_time, $session_id,
                                            $property_id,
                                            $property_type,
                                            $property_zipcode,
                                            $property_lat,
                                            $property_lon,
                                            $year_biult_id,
                                            $lot_sq_footage,
                                            $house_sq_footage,
                                            $bathrooms,
                                            $garages,
                                            $pool,
                                            $percentage_depreciation_value,
                                            $estimated_price,
                                            $bedrooms,
                                            $subdivision,
                                            $fundamentals_factor,
                                            $conditional_factor,
                                            $house_views_list,
                                            $sub_type
            )
    {
//        $compare_property_type = '';
        $compare_property_type = "AND property_info.property_type = {$property_type}";
        $select_estimated_price_result = $this->actionCompareEstimatedPriceTable($curStage, $property_type);
        
        if(empty($select_estimated_price_result)) {
                $result['estimated_price'] = $estimated_price;
                $result['estimated_price_dollar'] = 0;
                $result['estimated_value_subject_property'] = 0;
                $result['current_stage'] = $curStage;
                $result['comp_min'] = $this->getCompMin($result['current_stage']);
            return ;
        }
        
        $compare_property_zipcode = '';
        if ($property_zipcode != '') {
            $compare_property_zipcode = "AND property_info.property_zipcode = {$property_zipcode}";
        }
       
        $compare_property_lat = '';
        if (($property_lat != '0.000000') && ($property_lat != '')) {           
            $compare_property_lat = $this->actionComparePropertyLat($property_lat, $select_estimated_price_result->distance);
        }
        $compare_property_lon = '';
        if (($property_lon != '0.000000') && ($property_lon != '')) {
            $compare_property_lon = $this->actionComparePropertyLon($property_lon, $select_estimated_price_result->distance);
        }
        $compare_property_year_build = '';
        if ($year_biult_id != '') {
            $compare_property_year_build = $this->actionComparePropertyYearBuild($year_biult_id, $select_estimated_price_result->year_estimated);
        }
        $compare_lot_sq_footage = '';
        if ($lot_sq_footage != '') {
            $compare_lot_sq_footage = $this->actionCompareLotSqFootage($lot_sq_footage, $select_estimated_price_result->lot_estimated);
        }
        $compare_house_sq_footage = '';
        if ($house_sq_footage != '') {
            $compare_house_sq_footage = $this->actionCompareHouseSqFootage($house_sq_footage, $select_estimated_price_result->house_estimated);
        }
        $compare_bathrooms = '';
        if(!empty($select_estimated_price_result->baths_estimated) &&  !empty($bathrooms)){
            $percent_bathrooms = $bathrooms*($select_estimated_price_result->baths_estimated/100);
            $compare_bathrooms = "AND property_info.bathrooms BETWEEN " . ($bathrooms-$percent_bathrooms) . "  AND " . ($bathrooms+$percent_bathrooms);
        }
        $compare_bedrooms = '';
        if(!empty($select_estimated_price_result->beds_estimated) &&  !empty($bedrooms)){
            $percent_bedrooms = $bedrooms*($select_estimated_price_result->beds_estimated/100);
            $compare_bedrooms = "AND property_info.bedrooms BETWEEN " . ($bedrooms-$percent_bedrooms) . "  AND " . ($bedrooms+$percent_bedrooms);
        }
        $compare_subdivision = '';
        if (!empty($select_estimated_price_result->subdivision_comp) && !empty($subdivision)) {
            $compare_subdivision = "AND property_info.subdivision = " . Yii::app()->db->quoteValue($subdivision);
        }
        $compare_house_views ='';
        if(!empty($select_estimated_price_result->house_views_comp) && !empty($house_views_list)){
            $compare_house_views_part = '';
            foreach ($house_views_list as $value) {
                if(!empty($compare_house_views_part)) { $compare_house_views_part .= " AND "; }
                $compare_house_views_part .= " property_info_details.house_views NOT LIKE '%" . addslashes($value) ."%' ";
            }
            if(!empty($compare_house_views_part)) {
                $compare_house_views = " AND ( ($compare_house_views_part) OR property_info_details.house_views IS NULL )";
            }
        }
        $compare_sub_type = '';
        if (!empty($select_estimated_price_result->sub_type_comp) && !empty($sub_type)) {
            $compare_sub_type = "AND property_info.sub_type = " . Yii::app()->db->quoteValue($sub_type);
        }
            
        $estimated_value_subject_property = 0;

//        $select_query_delete_1 = $this->actionGetExcludeProperty($session_id);

        if (($compare_lot_sq_footage != '') ||
                ($compare_house_sq_footage != '') ||
                ($compare_property_year_build != '') ||
                ($compare_property_zipcode != '') ||
                ($compare_property_type != '')
                || ($compare_bathrooms != '')
                || ($compare_bedrooms != '')
                || ($compare_subdivision != '')
                || ($compare_house_views != '')
                || ($compare_sub_type != '')
            ) {

            $result_queryAllRows = $this->actionComparePropertyInfoAllRows(
                    $property_id, $session_id, $gettoday_date, $comp_time, $compare_property_type, $compare_property_zipcode, $compare_property_year_build, $compare_lot_sq_footage, $compare_house_sq_footage, $compare_property_lon, $compare_property_lat,
                    $compare_bedrooms, $compare_bathrooms, $compare_subdivision, $compare_house_views, $compare_sub_type);

            $result_query = $this->actionComparePropertyInfo(
                    $property_id, $session_id, $gettoday_date, $comp_time, $compare_property_type, $compare_property_zipcode, $compare_property_year_build, $compare_lot_sq_footage, $compare_house_sq_footage, $compare_property_lon, $compare_property_lat,
                    $compare_bedrooms, $compare_bathrooms, $compare_subdivision, $compare_house_views, $compare_sub_type);

            $total_count = $result_query->count_property ? $result_query->count_property : 0;
            $result_t_score = $this->actionTtable2Tail($total_count);
            $t_score = $this->getTail($result_t_score);
            $low_sd = $t_score;
            $high_sd = $t_score;
            $low_range = 0;
            $high_range = 0;

            if ($total_count >= $select_estimated_price_result->min_comp) {

                $result['house_weighted'] = $select_estimated_price_result->house_weighted;
                $result['lot_weighted'] = $select_estimated_price_result->lot_weighted;
                $result['amenties_weighted'] = $select_estimated_price_result->bathrooms_weighted + $select_estimated_price_result->bedrooms_weighted + $select_estimated_price_result->garages_weighted + $select_estimated_price_result->pool_weighted;
                $result['low_sd'] = $low_sd;
                $result['high_sd'] = $high_sd;
                
                $result['result_query'] = $result_query;
                $result['result_queryAllRows'] = $result_queryAllRows;

                $house_square_footage_gravity = ((float)$house_sq_footage!=0.0)?sqrt($result_query->house_square_footage_average / $house_sq_footage ):0.0;
                $lot_footage_gravity = ((float)$lot_sq_footage!=0.0)?sqrt( $result_query->house_lot_acerage_average / $lot_sq_footage ):0.0;
                
                $result_query->house_footage_average = $result_query->comps_house_footage_average * $house_square_footage_gravity;
                $result_query->lot_footage_average = $result_query->comps_lot_footage_average * $lot_footage_gravity;

                $qualifying_value_square_footage = ((1+$fundamentals_factor)*$result_query->house_footage_average) + ($conditional_factor * ($result_query->square_footage_stddev  / (sqrt($total_count))));
                $qualifying_value_lot_footage = ((1+$fundamentals_factor)*$result_query->lot_footage_average) + ($conditional_factor * ($result_query->lot_footage_stddev  / (sqrt($total_count))));
                $qualifying_value_bathrooms_amenties = ((1+$fundamentals_factor)*$result_query->bathrooms_amenity_average)+($conditional_factor*($result_query->bathrooms_amenity_stddev/(sqrt($total_count))));
                $qualifying_value_bedrooms_amenties = ((1+$fundamentals_factor)*$result_query->bedrooms_amenity_average)+($conditional_factor*($result_query->bedrooms_amenity_stddev/(sqrt($total_count))));
                $qualifying_value_garages_amenties = ((1+$fundamentals_factor)*$result_query->garages_amenity_average)+($conditional_factor*($result_query->garages_amenity_stddev/(sqrt($total_count))));
                $qualifying_value_pool_amenties = ((1+$fundamentals_factor)*$result_query->pool_amenity_average)+($conditional_factor*($result_query->pool_amenity_stddev/(sqrt($total_count))));
  
                $weighted100 = EstimatedPrice::getSumWeighted(
                    array(
                        'house_weighted' => $select_estimated_price_result->house_weighted,
                        'lot_weighted' => $select_estimated_price_result->lot_weighted,
                        'bathrooms_weighted' => $select_estimated_price_result->bathrooms_weighted,
                        'bedrooms_weighted' => $select_estimated_price_result->bedrooms_weighted,
                        'garages_weighted' => $select_estimated_price_result->garages_weighted,
                        'pool_weighted' => $select_estimated_price_result->pool_weighted,
                    ),
                    $qualifying_value_square_footage, $qualifying_value_lot_footage, $qualifying_value_bathrooms_amenties,
                    $qualifying_value_garages_amenties, $qualifying_value_pool_amenties, $qualifying_value_bedrooms_amenties
                );

                //calculate the low range value               
                $low_qualifying_value_square_footage = ((1+$fundamentals_factor)*$result_query->house_footage_average) - ($low_sd * ($result_query->square_footage_stddev / (sqrt($total_count))));
                $low_qualifying_value_lot_footage = ((1+$fundamentals_factor)*$result_query->lot_footage_average) - ($low_sd * ($result_query->lot_footage_stddev / (sqrt($total_count))));
                $low_qualifying_value_bathrooms_amenties = ((1+$fundamentals_factor)*$result_query->bathrooms_amenity_average)-($low_sd*($result_query->bathrooms_amenity_stddev/(sqrt($total_count))));
                $low_qualifying_value_bedrooms_amenties = ((1+$fundamentals_factor)*$result_query->bedrooms_amenity_average)-($low_sd*($result_query->bedrooms_amenity_stddev/(sqrt($total_count))));
                $low_qualifying_value_garages_amenties = ((1+$fundamentals_factor)*$result_query->garages_amenity_average)-($low_sd*($result_query->garages_amenity_stddev/(sqrt($total_count))));
                $low_qualifying_value_pool_amenties = ((1+$fundamentals_factor)*$result_query->pool_amenity_average)-($low_sd*($result_query->pool_amenity_stddev/(sqrt($total_count))));

                $low_estimated_value_square_footage = $low_qualifying_value_square_footage * $house_sq_footage;
                $low_estimated_value_lot_footage = $low_qualifying_value_lot_footage * $lot_sq_footage;
                $low_estimated_value_bathrooms_amenties = $low_qualifying_value_bathrooms_amenties*($bathrooms);
                $low_estimated_value_bedrooms_amenties = $low_qualifying_value_bedrooms_amenties*($bedrooms);
                $low_estimated_value_garages_amenties = $low_qualifying_value_garages_amenties*($garages);
                $low_estimated_value_pool_amenties = $low_qualifying_value_pool_amenties*($pool);

                $low_weighted_value_square_footage = $low_estimated_value_square_footage * ($select_estimated_price_result->house_weighted / $weighted100);
                $low_weighted_value_lot_footage = $low_estimated_value_lot_footage * ($select_estimated_price_result->lot_weighted / $weighted100);
                $low_weighted_value_bathrooms_amenties = $low_estimated_value_bathrooms_amenties*($select_estimated_price_result->bathrooms_weighted/$weighted100);
                $low_weighted_value_bedrooms_amenties = $low_estimated_value_bedrooms_amenties*($select_estimated_price_result->bedrooms_weighted/$weighted100);
                $low_weighted_value_garages_amenties = $low_estimated_value_garages_amenties*($select_estimated_price_result->garages_weighted/$weighted100);
                $low_weighted_value_pool_amenties = $low_estimated_value_pool_amenties*($select_estimated_price_result->pool_weighted/$weighted100);

                $low_range = $low_weighted_value_square_footage+$low_weighted_value_lot_footage+$low_weighted_value_bathrooms_amenties+$low_weighted_value_bedrooms_amenties +$low_weighted_value_garages_amenties +$low_weighted_value_pool_amenties;

                //calculate the high range value                                 
                $high_qualifying_value_square_footage = ((1+$fundamentals_factor)*$result_query->house_footage_average) + ($high_sd * ($result_query->square_footage_stddev / (sqrt($total_count))));
                $high_qualifying_value_lot_footage = ((1+$fundamentals_factor)*$result_query->lot_footage_average) + ($high_sd * ($result_query->lot_footage_stddev / (sqrt($total_count))));
                $high_qualifying_value_bathrooms_amenties = ((1+$fundamentals_factor)*$result_query->bathrooms_amenity_average)+($high_sd*($result_query->bathrooms_amenity_stddev/(sqrt($total_count))));
                $high_qualifying_value_bedrooms_amenties = ((1+$fundamentals_factor)*$result_query->bedrooms_amenity_average)+($high_sd*($result_query->bedrooms_amenity_stddev/(sqrt($total_count))));
                $high_qualifying_value_garages_amenties = ((1+$fundamentals_factor)*$result_query->garages_amenity_average)+($high_sd*($result_query->garages_amenity_stddev/(sqrt($total_count))));
                $high_qualifying_value_pool_amenties = ((1+$fundamentals_factor)*$result_query->pool_amenity_average)+($high_sd*($result_query->pool_amenity_stddev/(sqrt($total_count))));

                $high_estimated_value_square_footage = $high_qualifying_value_square_footage * $house_sq_footage;
                $high_estimated_value_lot_footage = $high_qualifying_value_lot_footage * $lot_sq_footage;
                $high_estimated_value_bathrooms_amenties = $high_qualifying_value_bathrooms_amenties*($bathrooms);
                $high_estimated_value_bedrooms_amenties = $high_qualifying_value_bedrooms_amenties*($bedrooms);
                $high_estimated_value_garages_amenties = $high_qualifying_value_garages_amenties*($garages);
                $high_estimated_value_pool_amenties = $high_qualifying_value_pool_amenties*($pool);

                $high_weighted_value_square_footage = $high_estimated_value_square_footage * ($select_estimated_price_result->house_weighted / $weighted100);
                $high_weighted_value_lot_footage = $high_estimated_value_lot_footage * ($select_estimated_price_result->lot_weighted / $weighted100);
                $high_weighted_value_bathrooms_amenties = $high_estimated_value_bathrooms_amenties*($select_estimated_price_result->bathrooms_weighted/$weighted100);
                $high_weighted_value_bedrooms_amenties = $high_estimated_value_bedrooms_amenties*($select_estimated_price_result->bedrooms_weighted/$weighted100);
                $high_weighted_value_garages_amenties = $high_estimated_value_garages_amenties*($select_estimated_price_result->garages_weighted/$weighted100);
                $high_weighted_value_pool_amenties = $high_estimated_value_pool_amenties*($select_estimated_price_result->pool_weighted/$weighted100);

                $high_range = $high_weighted_value_square_footage+$high_weighted_value_lot_footage+$high_weighted_value_bathrooms_amenties+$high_weighted_value_bedrooms_amenties +$high_weighted_value_garages_amenties +$high_weighted_value_pool_amenties;

                    if(
                        $house_sq_footage > $result_query->house_footage_max
                        || $house_sq_footage < $result_query->house_footage_min
                        || $house_sq_footage > 0 && $qualifying_value_square_footage <= 0 

                        || $lot_sq_footage > 0 && $qualifying_value_lot_footage <= 0 
                        || $bathrooms > 0 && $qualifying_value_bathrooms_amenties <= 0 
                        || $bedrooms > 0 && $qualifying_value_bedrooms_amenties <= 0 
                        || $garages > 0 && $qualifying_value_garages_amenties <= 0 
                        || $pool > 0 && $qualifying_value_pool_amenties <= 0 
                    ) {
                        $curStage++;
                        if($curStage <= Yii::app()->params['maxCalcStages']) {
                            unset($result_query);
                            unset($result_queryAllRows);
                            unset($result['result_query']);
                            unset($result['result_queryAllRows']);
                            $this->calculateEstimatedPriceStage(
                                $curStage,
                                $result, $gettoday_date, $comp_time, $session_id,
                                                        $property_id,
                                                        $property_type,
                                                        $property_zipcode,
                                                        $property_lat,
                                                        $property_lon,
                                                        $year_biult_id,
                                                        $lot_sq_footage,
                                                        $house_sq_footage,
                                                        $bathrooms,
                                                        $garages,
                                                        $pool,
                                                        $percentage_depreciation_value,
                                                        $estimated_price,
                                                        $bedrooms,
                                                        $subdivision,
                                                        $fundamentals_factor,
                                                        $conditional_factor,
                                                        $house_views_list,
                                                        $sub_type
                            );
                            return ;
                        } else {
                        $result['house_weighted'] = $select_estimated_price_result->house_weighted;
                        $result['lot_weighted'] = $select_estimated_price_result->lot_weighted;
                        $result['amenties_weighted'] = $select_estimated_price_result->bathrooms_weighted + $select_estimated_price_result->bedrooms_weighted + $select_estimated_price_result->garages_weighted + $select_estimated_price_result->pool_weighted;
                        $result['low_sd'] = $low_sd;
                        $result['high_sd'] = $high_sd;

                        $result['result_query'] = $result_query;
                        $result['result_queryAllRows'] = $result_queryAllRows;
                        $result['estimated_price'] = $estimated_price;
                        $result['estimated_price_dollar'] = 0;
                        $result['estimated_value_subject_property'] = 0;
                        $result['percentage_depreciation_value'] = $percentage_depreciation_value;
                        $result['current_stage'] = $curStage-1;
                        $result['comp_min'] = $this->getCompMin($result['current_stage']);

                            return ;
                        }
                    } else {

                $estimated_value_square_footage = $qualifying_value_square_footage * $house_sq_footage;
                $estimated_value_lot_footage = $qualifying_value_lot_footage * $lot_sq_footage;
                $estimated_value_bathrooms_amenties = $qualifying_value_bathrooms_amenties*($bathrooms);
                $estimated_value_bedrooms_amenties = $qualifying_value_bedrooms_amenties*($bedrooms);
                $estimated_value_garages_amenties = $qualifying_value_garages_amenties*($garages);
                $estimated_value_pool_amenties = $qualifying_value_pool_amenties*($pool);
                
                $weighted_value_square_footage = $estimated_value_square_footage * ($select_estimated_price_result->house_weighted / $weighted100);
                $weighted_value_lot_footage = $estimated_value_lot_footage * ($select_estimated_price_result->lot_weighted / $weighted100);
                $weighted_value_bathrooms_amenties = $estimated_value_bathrooms_amenties*($select_estimated_price_result->bathrooms_weighted/$weighted100);
                $weighted_value_bedrooms_amenties = $estimated_value_bedrooms_amenties*($select_estimated_price_result->bedrooms_weighted/$weighted100);
                $weighted_value_garages_amenties = $estimated_value_garages_amenties*($select_estimated_price_result->garages_weighted/$weighted100);
                $weighted_value_pool_amenties = $estimated_value_pool_amenties*($select_estimated_price_result->pool_weighted/$weighted100);

                $estimated_value_subject_property = $weighted_value_square_footage+$weighted_value_lot_footage+$weighted_value_bathrooms_amenties +$weighted_value_bedrooms_amenties +$weighted_value_garages_amenties +$weighted_value_pool_amenties;
                $estimated_price_dollar = $estimated_value_subject_property;
//Yii::log(print_r(array($weighted_value_square_footage, $weighted_value_lot_footage, $weighted_value_amenties,$estimated_value_subject_property),1) ,'ERROR');
                $result['estimated_price'] = $estimated_price;
                $result['estimated_price_dollar'] = $estimated_price_dollar;
                $result['estimated_value_subject_property'] = $estimated_value_subject_property;
                $result['percentage_depreciation_value'] = $percentage_depreciation_value;
                $result['low_range'] = $low_range;
                $result['high_range'] = $high_range;
                $result['current_stage'] = $curStage;
                $result['comp_min'] = $this->getCompMin($result['current_stage']);
                return  ;
                    }
            } else {
                $curStage++;
                if($curStage <= Yii::app()->params['maxCalcStages']) {
                    unset($result_query);
                    unset($result_queryAllRows);  
                    $this->calculateEstimatedPriceStage(
                        $curStage,
                        $result, $gettoday_date, $comp_time, $session_id,
                                                $property_id,
                                                $property_type,
                                                $property_zipcode,
                                                $property_lat,
                                                $property_lon,
                                                $year_biult_id,
                                                $lot_sq_footage,
                                                $house_sq_footage,
                                                $bathrooms,
                                                $garages,
                                                $pool,
                                                $percentage_depreciation_value,
                                                $estimated_price,
                                                $bedrooms,
                                                $subdivision,
                                                $fundamentals_factor,
                                                $conditional_factor,
                                                $house_views_list,
                                                $sub_type
                    );
                    return ;
                } else {
                $result['house_weighted'] = $select_estimated_price_result->house_weighted;
                $result['lot_weighted'] = $select_estimated_price_result->lot_weighted;
                $result['amenties_weighted'] = $select_estimated_price_result->bathrooms_weighted + $select_estimated_price_result->bedrooms_weighted + $select_estimated_price_result->garages_weighted + $select_estimated_price_result->pool_weighted;
                $result['low_sd'] = $low_sd;
                $result['high_sd'] = $high_sd;
                
                $result['result_query'] = $result_query;
                $result['result_queryAllRows'] = $result_queryAllRows;
                $result['estimated_price'] = $estimated_price;
                $result['estimated_price_dollar'] = 0;
                $result['estimated_value_subject_property'] = 0;
                $result['percentage_depreciation_value'] = $percentage_depreciation_value;
                $result['current_stage'] = $curStage-1;
                $result['comp_min'] = $this->getCompMin($result['current_stage']);
                
                    return ;
                }
            }
        }
    }

    public function actionFavorites() {
        $response = array();
        if (Yii::app()->user->isGuest) {
            $response[] = "This option is available only for authed.";
        } else {
            $property_id = isset($_POST['propery_id']) ? $_POST['propery_id'] : '';
            if ($property_id) {
                $row = array();
                $row['SavedListing'] = array();
                $row['SavedListing']['property_id'] = $property_id;
                $row['SavedListing']['mid'] = Yii::app()->user->id;
                $row['SavedListing']['save_date'] = date('Y-m-d');
                $model = new SavedListing;
                $model->attributes = $row['SavedListing'];
                if ($model->validate()) {
                    $model->save();
                }
                $response[] = "The property was saved.";
            }
        }
        echo json_encode($response);
        exit();
    }

    //  USE INDEX (year_biult_id)
    private function getSimilarHomesForSale($id,$zipcode, $price, $square, $property_type, $year_biult) {
        $sql = "SELECT * FROM `property_info` AS t1
                    LEFT JOIN `property_info_additional_brokerage_details` AS t2 
                    ON `t2`.`property_id` = `t1`.`property_id`  
                    AND  (`t2`.`status` NOT IN ('HISTORY', 'LEASED','SOLD', 'CLOSED', '', 'EXPIRED', 'Contingent Offer', 'Pending Offer' ) 
                            AND `t2`.`status` IS NOT NULL )
                    LEFT JOIN ( `zipcode` AS t3, 
                                `city` AS t4, 
                                `county` AS t5, 
                                `state` AS t6, 
                                `tbl_property_info_slug` AS t7 ) 
                    ON (`t3`.`zip_id` = `t1`.`property_zipcode` 
                        AND `t4`.`cityid` = `t1`.`property_city_id` 
                        AND `t5`.`county_id` = `t1`.`property_county_id` 
                        AND `t6`.`stid` = `t1`.`property_state_id` 
                        AND `t7`.`property_id` = `t1`.`property_id` )
                    WHERE `t1`.`property_zipcode` = '{$zipcode}'
                    AND ( `t1`.`property_price` BETWEEN {$price}-{$price}*20/100 AND {$price}+{$price}*20/100 )
                    AND ( `t1`.`house_square_footage` BETWEEN {$square}-{$square}*30/100 AND {$square}+{$square}*30/100 )
                    AND ( `t1`.`property_type` = {$property_type})
                    AND ( `t1`.`year_biult_id` BETWEEN {$year_biult}-10 AND {$year_biult}+10)
                    AND ( `t1`.`property_id` != {$id})";
        $command = Yii::app()->db->createCommand($sql);
        return $command->queryAll(); //cache(1000, null)->
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return PropertyInfo the loaded model
     * @throws CHttpException
     */
    public function loadModel($id) {
        $model = PropertyInfo::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param PropertyInfo $model the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'property-info-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    /*
     * @TODO : need refactoring
     */
    private function setAnalitics($property_id) {
//$time_start1 = microtime(TRUE);
//$time_start = $time_start1;

        $model = PropertyInfo::model()->findByPk($property_id);
//$time_end = microtime(TRUE);
//$time = $time_end - $time_start;
//$time_start = $time_end;
//Yii::log('Step 0-0: ' . $time ,'ERROR'); // 3.5838282108307
        if(!empty($model)) {
            $model->views = $model->views + 1;
            $model->update();
        }
//$time_end = microtime(TRUE);
//$time = $time_end - $time_start;
//$time_start = $time_end;
//Yii::log('Step 0-1: ' . $time ,'ERROR'); // 4.5241408348083
    }

    public function actionSaveAgent() {
        if (Yii::app()->request->isAjaxRequest) {
            if (!Yii::app()->user->isGuest) {
                $agent_id = isset($_POST['agent_id']) ? $_POST['agent_id'] : 0;
                if ($agent_id !== 0) {
                    $model = SavedAgent::model()->findByAttributes(array('agent_id' => $agent_id, 'mid' => Yii::app()->user->id));
                    if (!$model) {
                        $row = array();
                        $row['saved_id'] = null;
                        $row['agent_id'] = $agent_id;
                        $row['mid'] = Yii::app()->user->id;
                        $row['saved_timestamp'] = $_SERVER['REQUEST_TIME'];
                        $model = new SavedAgent;
                        $model->attributes = $row;
                        if ($model->validate()) {
                            $model->save();
                        } else {
                            header("Content-type: application/json");
                            echo CJSON::encode($model->getErrors());
                            Yii::app()->end();
                        }
                    } else {
                        echo 'already saved';
                        Yii::app()->end();
                    }
                }
            }
        } else {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
    }

    public function actionDetachAgent() {
        if (Yii::app()->request->isAjaxRequest) {
            $result = array();
            $result['status'] = 'error';
            if (!Yii::app()->user->isGuest) {
                $agent_id = isset($_POST['agent_id']) ? $_POST['agent_id'] : 0;
                if ($agent_id !== 0) {
                    $model = SavedAgent::model()->deleteAllByAttributes(array('agent_id' => $agent_id, 'mid' => Yii::app()->user->id));
                    if ($model) {
                        $result['status'] = 'success';
                    }
                }
            }
            header("Content-type: application/json");
            echo CJSON::encode($result);
            Yii::app()->end();
        } else {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
    }

    private function getSearchResultSmallQuery($search_query, $user_id = null) {
        $search = $this->setSearchObj();

        $result = array();
        $query = array();
        $result['count_result'] = 0;
        $result['result'] = array();
        $result['status'] = 'failed';
        $query="";
        $sub_query = '';
        $sub_query .=!empty($search_query['city_searchfld']) ? ' @city_name "' . $search_query['city_searchfld'] . '"' : '';
        $sub_query .=!empty($search_query['state_searchfld']) ? ' @state_code "' . $search_query['state_searchfld'] . '"' : '';
        $sub_query .=!empty($search_query['zipcode_searchfld']) ? ' @zip_code "' . $search_query['zipcode_searchfld'] . '"' : '';
        $sub_query .=!empty($search_query['subdivision_searchfld']) ? ' @subdivision "' . $search_query['subdivision_searchfld'] . '"' : '';

        $street_number = !empty($search_query['street_number_searchfld']) ? $search_query['street_number_searchfld'] : '';

        if(trim($street_number)){
            $sub_query .= !empty($search_query['street_address_searchfld']) ? ' @property_street "' . $street_number. ' ' . $search_query['street_address_searchfld'] . '"' : '';
        } else {
            $sub_query .= !empty($search_query['street_address_searchfld']) ? ' @property_street "' . $search_query['street_address_searchfld'] . '"' : '';
        }


        if ($sub_query) {
            $search->setSortMode(SPH_SORT_RELEVANCE);
            $search->SetFieldWeights(array('property_street' => 50, 'city_name' => 30, 'state_code' => 15));
            $query .= ' ( ' . $sub_query . ' ) ';
        }
        $search_query['searchfld'] = strpos($search_query['searchfld'], ',') ?
                trim(str_replace(',', '', $search_query['searchfld'])) : $search_query['searchfld'];

        if (!empty($search_query['country_searchfld'])) {
            $search_query['searchfld'] = str_replace($search_query['country_searchfld'], '', $search_query['searchfld']);
        }
        if (!empty($search_query['zipcode_searchfld'])) {
            $search_query['searchfld'] = str_replace($search_query['zipcode_searchfld'], '', $search_query['searchfld']);
        }
        if (!empty($search_query['state_searchfld'])) {
            $search_query['searchfld'] = str_replace($search_query['state_searchfld'], '', $search_query['searchfld']);
        }
        if (!empty($search_query['city_searchfld'])) {
            $search_query['searchfld'] = str_replace($search_query['city_searchfld'], '', $search_query['searchfld']);
        }
//        if (!empty($search_query['street_address_searchfld'])) {
//            $search_query['searchfld'] = str_replace($search_query['street_address_searchfld'], '', $search_query['searchfld']);
//        }
//        if (!empty($search_query['street_number_searchfld'])) {
//            $search_query['searchfld'] = str_replace($search_query['street_number_searchfld'], '', $search_query['searchfld']);
//        }
        if (trim($search_query['searchfld'])) {
            $search->setSortMode(SPH_SORT_RELEVANCE);
            $search->SetFieldWeights(array('property_street' => 60,'zip_code'=>20, 'city_name' => 25, 'state_code' => 15));
            $search_query['searchfld'] = strpos($search_query['searchfld'], '-') ?
                    trim(str_replace("-", "", $search_query['searchfld'])) : $search_query['searchfld'];

            $pos_arr = array();
            $pos_arr = explode(" ", $search_query['searchfld']);
            $address_arr = array();
            for ($i = 0; $i < count($pos_arr); $i++) {
                $len = strlen(trim($pos_arr[$i]));
                if ($len == 0) {
                    continue;
                }
                $address_arr[] = trim($pos_arr[$i]);

            }
            $search_query['searchfld'] = implode(' ', $address_arr);
            if (!empty($query)) {
                $query .= ' | ';
            }
            if (trim($search_query['searchfld'])) {
                if(strpos($search_query['searchfld'], ' ') > 2){
                    $query .= '( @* "' . str_replace(' ', '" | "', trim($search_query['searchfld'])) . '" )  ';
                } else {
                    $query .= '( @* "' . $search_query['searchfld'] . '" )';
                }

            }
        }

        $flag = 0;
//        if (!empty($search_query['zipcode_searchfld'])) {
//            $search->setFilter("zip_code", array($search_query['zipcode_searchfld']));
//            $flag = 1;
//        }

        $query .= $search_query['sale_type_searchfld'] == 'ALL Sale Types' ? ' ( @status "Active" | "Active-Exclusive Right" | "Auction" | Closed | "Contingent Offer" | "Exclusive Agency" | "For Sale" | History | Leased | "Pending Offer" | Sold | "Temporarily Off the Market" ) ( @property_status  Active )' : '';
        $query .= $search_query['sale_type_searchfld'] == 'For Sale' ? ' ( @status "Active" | "For Sale" | Active | Auction | "Active-Exclusive Right" | "Exclusive Agency" ) ( @property_status  Active )' : '';
        $query .= $search_query['sale_type_searchfld'] == 'Under Value' ? ' ( @status "Active" | "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) ( @property_status  Active )' : '';
        $query .= $search_query['sale_type_searchfld'] == 'Equity Deals' ? ' ( @status "Active" | "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) ( @property_status  Active )' : '';
        $query .= $search_query['sale_type_searchfld'] == 'Foreclosures' ? ' ( ( @status "Active" | "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) ( @foreclosure "yes"  ) ) ( @property_status  Active )' : '';
        $query .= $search_query['sale_type_searchfld'] == 'Shortsales' ? ' ( ( @status "Active" | "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) (  @short_sale "yes" ) ) ( @property_status  Active )' : '';
        $query .= $search_query['sale_type_searchfld'] == 'Auction' ? ' (  @status "Auction" ) ( @property_status  Active )' : '';
        $query .= $search_query['sale_type_searchfld'] == 'All Property Records' ? ' ( @property_status  Active ) ' : '';
        $query .= $search_query['sale_type_searchfld'] == 'For Rent' ? ' (( @status "Active" | "Active-Exclusive Right" | "Contingent Offer" | "Exclusive Agency" )) ( @property_status  Active )' : '';
        $query .= $search_query['sale_type_searchfld'] == 'Owner Will Carry' ? ' ( @status "Active" | "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) (@financing_considered "OWC") ( @property_status  Active )' : '';
        $query .= $search_query['sale_type_searchfld'] == 'AITD Opportunities' ? ' ( @status "Active" | "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) (@financing_considered "AITD") ( @property_status  Active )' : '';
        $query .= $search_query['sale_type_searchfld'] == 'Mid Cap Rental Potential' ? ' ( @status "Active" | "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) ( @property_status  Active )' : '';
        $query .= $search_query['sale_type_searchfld'] == 'High Cap Rental Potential' ? ' ( @status "Active" | "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) ( @property_status  Active )' : '';
        $query .= $search_query['sale_type_searchfld'] == 'Rental Properties With Equity' ? ' ( @status "Active" | "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) ( @property_status  Active )' : '';
        $query .= $search_query['sale_type_searchfld'] == 'High Cap And High Equity Opportunities' ? ' ( @status "Active" | "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) ( @property_status  Active )' : '';

        //Exclude property_type = 9 (that is rental) from sale type search
        if($search_query['sale_type_searchfld'] == "For Rent"){
            $search->setFilter("property_type", array(9));
            $flag = 1;
        }

        if($search_query['sale_type_searchfld'] == "For Sale"){
            $search->setFilter("property_type", array(1,2,3,4,5,6,7,8,16));
            $flag = 1;
        }

        $resArray = array();
        if ($query != '') {
            $resArray = $search->query($query, '*');
        } else {
            if ($flag == 1) {
                $resArray = $search->query('', '*');
            }
        }
        if (!empty($resArray)) {
            if (count($resArray['matches']) > 0) {
                $list_pk = array();
                $list_weight = array();
                foreach ($resArray['matches'] as $matches) {
                    $list_pk[] = $matches['id'];
                    $list_weight[$matches['id']] = $matches['weight'];
                }
                $alt_search = PropertyInfo::model()->with(
                        'city', 
                        'county', 
                        'state', 
                        'zipcode', 
                        'propertyInfoAdditionalBrokerageDetails', 
                        'brokerage_join',
                        'slug',
                        'propertyInfoPhoto'
                        )->findAllByAttributes(
                                array('property_id' => $list_pk)
                        );
            }
            if (!empty($alt_search)) {
                $result['count_result'] = count($alt_search);
                $result['result'] = $this->actionGetSearchResult($alt_search, $list_weight, $user_id);
                $result['res_map_layout'] = SiteHelper::getSearchMapResult($alt_search, $list_weight, $user_id);
                $result['status'] = 'success';
                $result['latlon'] = SiteHelper::getLatLonResult($alt_search);
                $result['top_search'] = $search_query;

            } else {
                $result['status'] = 'nothing';
            }
        }
        return $result;
    }

    private function setSearchObj() {
        $search = Yii::App()->search;
        $search->setSelect('*');
        $search->setArrayResult(true);
        $search->setMatchMode(SPH_MATCH_EXTENDED2);
        $search->SetLimits(0, 1000, 1000);// 1000, 1000); // 100, 100);
        $search->setSortMode(SPH_SORT_ATTR_DESC, 'property_updated_date');
        $search->SetFieldWeights(array('property_updated_date' => 100));

        return $search;
    }
    
    private function setSearchQuery($searchForm,$user_id = null) {
//$time_start1 = microtime(TRUE);
//$time_start = $time_start1;
        $search = $this->setSearchObj();
        $results = array();
        $result = array();
        $result['count_result'] = 0;
        $result['result'] = array();
        $result['status'] = 'failed';
        $query = '';
        $sub_query = '';
        $sub_query .=!empty($searchForm['city']) ? ' @city_name "' . $searchForm['city'] . '"' : '';
        $sub_query .=!empty($searchForm['state']) ? ' @state_code "' . $searchForm['state'] . '"' : '';
        $sub_query .=!empty($searchForm['zipcode']) ? ' @zip_code "' . $searchForm['zipcode'] . '"' : '';
        $sub_query .=!empty($searchForm['subdivision']) ? ' @subdivision "' . $searchForm['subdivision'] . '"' : '';
        $street_number = !empty($searchForm['street_number']) ? $searchForm['street_number'] : '';

        if(trim($street_number)){
            $sub_query .= !empty($searchForm['street_address']) ? ' @property_street "' . $street_number. ' ' . $searchForm['street_address'] . '" /1' : '';

        } else {
            $sub_query .= !empty($searchForm['street_address']) ? ' @property_street "' . $searchForm['street_address'] . '" /1' : '';

        }


        if ($sub_query) {
            $search->setSortMode(SPH_SORT_RELEVANCE);
            $search->SetFieldWeights(array('property_street' => 50, 'city_name' => 30, 'state_code' => 15));
            $query .= ' ( ' . $sub_query . ' ) ';
        }
        $searchForm['address'] = strpos($searchForm['address'], ',') ?
                trim(str_replace(',', '', $searchForm['address'])) : $searchForm['address'];
//        if (!empty($searchForm['street_number'])) {
//            $searchForm['address'] = str_replace($searchForm['street_number'], '', $searchForm['address']);
//        }
        if (!empty($searchForm['country'])) {
            $searchForm['address'] = str_replace($searchForm['country'], '', $searchForm['address']);
            $searchForm['address'] = str_replace('USA', '', $searchForm['address']);
        }
        if (!empty($searchForm['zipcode'])) {
            $searchForm['address'] = str_replace($searchForm['zipcode'], '', $searchForm['address']);
        }
        if (!empty($searchForm['state'])) {
            $searchForm['address'] = str_replace($searchForm['state'], '', $searchForm['address']);
        }
        if (!empty($searchForm['city'])) {
            $searchForm['address'] = str_replace($searchForm['city'], '', $searchForm['address']);
        }
//        if (!empty($searchForm['street_address'])) {
//            $searchForm['address'] = str_replace($searchForm['street_address'], '', $searchForm['address']);
//        }

        if (trim($searchForm['address'])) {
            $search->setSortMode(SPH_SORT_RELEVANCE);
            $search->SetFieldWeights(array('property_street' => 40,'zip_code'=>20, 'city_name' => 25, 'state_code' => 15));
            $searchForm['address'] = strpos($searchForm['address'], '-') ?
                    trim(str_replace("-", "", $searchForm['address'])) : $searchForm['address'];

            $pos_arr = array();
            $pos_arr = explode(" ", $searchForm['address']);
            $address_arr = array();
            for ($i = 0; $i < count($pos_arr); $i++) {
                $len = strlen(trim($pos_arr[$i]));
                if ($len == 0) {
                    continue;
                }
                $address_arr[] = trim($pos_arr[$i]);

            }

            $searchForm['address'] = implode(' ', $address_arr);

            if (!empty($query)) {
                $query .= ' | ';
            }

            if (trim($searchForm['address'])) {
//                if(strpos($searchForm['address'], ' ') > 2){
//                    $query .= '( @* "' . str_replace(' ', '" | "', trim($searchForm['address'])) . '" )  ';
//                } else {
                    $query .= '( @* "' . $searchForm['address'] . '" )';
//                }
            }

        }
//Yii::log($query,'ERROR');
        $keywords = '';
        if ($searchForm['keywords']) {
            $keywords = strpos($searchForm['keywords'], ',') ?
                    explode(',', $searchForm['keywords']) : $searchForm['keywords'];
            $keywords_str = '';
            if (is_array($keywords)) {
                foreach ($keywords as $keyword) {
                    $delim = '';
                    if ($keywords_str) {
                        $delim = ' | ';
                    }
                    $keywords_str .= $delim . "\"" . trim($keyword) . "\"";
                }
            } else {
                $keywords_str = "\"" . trim($keywords) . "\"";
            }


            // @TODO specify fields in wich perform keywords search
            // all-field search operator: @* hello - http://sphinxsearch.com/docs/current/extended-syntax.html
            // operator OR: hello | world
            $query .= ' ( @* ' . $keywords_str . ' ) ';
        }

        $flag = 0;

        $checkMembership = SiteHelper::forFullPaidMembersOnly(true);

        $query .= $searchForm['sale_type'] == 'ALL Sale Types' ? ' ( @status "Active" | "Active-Exclusive Right" | "Auction" | Closed | "Contingent Offer" | "Exclusive Agency" | "For Sale" | History | Leased | "Pending Offer" | Sold | "Temporarily Off the Market" ) ( @property_status  Active )' : '';
        $query .= $searchForm['sale_type'] == 'For Sale' ? ' ( @status "For Sale" | Active | "Auction" | "Active-Exclusive Right" | "Exclusive Agency" ) ( @property_status  Active )' : '';
        $query .= $searchForm['sale_type'] == 'All Property Records' ? ' ( @property_status  Active ) ' : '';
        $query .= $searchForm['sale_type'] == 'For Rent' ? ' ( @status "Active" | "Active-Exclusive Right" | "Contingent Offer" | "Exclusive Agency" ) ( @property_status  Active )' : '';

        if($checkMembership === true) {
            $query .= $searchForm['sale_type'] == 'Under Value' ? ' ( @status "Active" | "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) ( @property_status  Active )' : '';
            $query .= $searchForm['sale_type'] == 'Equity Deals' ? ' ( @status "Active" | "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) ( @property_status  Active )' : '';
            $query .= $searchForm['sale_type'] == 'Foreclosures' ? ' ( ( @status "Active" | "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) ( @foreclosure "yes" ) ) ( @property_status  Active )' : '';
            $query .= $searchForm['sale_type'] == 'Shortsales' ? ' ( ( @status"Active" |  "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) ( @short_sale "yes" ) ) ( @property_status  Active )' : '';
            $query .= $searchForm['sale_type'] == 'Auction' ? ' (  @status "Auction" ) ( @property_status  Active )' : '';
            $query .= $searchForm['sale_type'] == 'Owner Will Carry' ? ' ( @status "Active" | "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) (@financing_considered "OWC") ( @property_status  Active )' : '';
            $query .= $searchForm['sale_type'] == 'AITD Opportunities' ? ' ( @status "Active" | "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) (@financing_considered "AITD") ( @property_status  Active )' : '';
            $query .= $searchForm['sale_type'] == 'Mid Cap Rental Potential' ? ' ( @status "Active" | "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) ( @property_status  Active )' : '';
            $query .= $searchForm['sale_type'] == 'High Cap Rental Potential' ? ' ( @status "Active" | "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) ( @property_status  Active )' : '';
            $query .= $searchForm['sale_type'] == 'Rental Properties With Equity' ? ' ( @status "Active" | "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) ( @property_status  Active )' : '';
            $query .= $searchForm['sale_type'] == 'High Cap And High Equity Opportunities' ? ' ( @status "Active" | "Active-Exclusive Right" | "Auction" | "Exclusive Agency" | "For Sale" ) ( @property_status  Active )' : '';
        }
        //Exclude property_type = 9 (that is rental) from sale type search
        if($searchForm['sale_type'] == "For Rent"){
            $search->setFilter("property_type", array(9));
            $flag = 1;
        }

        if($searchForm['sale_type'] == "For Sale"){
            $search->setFilter("property_type", array(0,1,2,3,4,5,6,7,8,16));
            $flag = 1;
        }

        if (!empty($searchForm['min_price']) && !empty($searchForm['max_price'])) {
            $search->setFilterRange("property_price", intval($searchForm['min_price']), intval($searchForm['max_price']));
            $flag = 1;
        }
        if (!empty($searchForm['min_price']) && empty($searchForm['max_price'])) {
            $search->setFilterRange("property_price", intval($searchForm['min_price']), 9999999999999);
            $flag = 1;
        }
        if (empty($searchForm['min_price']) && !empty($searchForm['max_price'])) {
            $search->setFilterRange("property_price", 0, intval($searchForm['max_price']));
            $flag = 1;
        }

        // set $ / square foot filter
        if (!empty($searchForm['min_price_sqft']) || !empty($searchForm['max_price_sqft'])) {
            !empty($searchForm['min_price_sqft']) ? $min_price_sqft = $searchForm['min_price_sqft'] : $min_price_sqft = 1;
            !empty($searchForm['max_price_sqft']) ? $max_price_sqft = $searchForm['max_price_sqft'] : $max_price_sqft = 9999999999999;
            $search->setFilterRange("sqft_wcents", intval($min_price_sqft), intval($max_price_sqft));
            $flag = 1;
        }

        if (!empty($searchForm['min_sqft']) && !empty($searchForm['max_sqft'])) {
            $search->setFilterRange("house_square_footage", intval($searchForm['min_sqft']), intval($searchForm['max_sqft']));
            $flag = 1;
        }
        if (!empty($searchForm['min_sqft']) && empty($searchForm['max_sqft'])) {
            $search->setFilterRange("house_square_footage", intval($searchForm['min_sqft']), 9999999999999);
            $flag = 1;
        }
        if (empty($searchForm['min_sqft']) && !empty($searchForm['max_sqft'])) {
            $search->setFilterRange("house_square_footage", 0, intval($searchForm['max_sqft']));
            $flag = 1;
        }
        if ((int) $searchForm['bed'] > 0) {
            $search->setFilterRange("bedrooms", intval($searchForm['bed']), 9999999999999);
            $flag = 1;
        }
        if ((int) $searchForm['bath'] > 0) {
            $search->setFilterRange("bathrooms", intval($searchForm['bath']), 9999999999999);
            $flag = 1;
        }
        if (!empty($searchForm['pool'])) {
            if ($searchForm['pool'] == 1) {
                //$search->setFilterRange("pool", intval($searchForm['pool']), 9999999999999);
                $search->setFilter("pool", array(1));
            } else {
                $search->setFilter("pool", array(0));
            }
            $flag = 1;
        }
        if (!empty($searchForm['bmarket'])) {
            if ($searchForm['bmarket']) {
                //$search->setFilterRange("spa", intval($searchForm['spa']), 9999999999999);
                $search->setFilterRange("percentage_depreciation_value", intval($searchForm['bmarket']), intval(9999999999999));
            }
            $flag = 1;
        }
        if (!empty($searchForm['stories'])) {
          //  $search->setFilter("stories", $searchForm['stories']);
            $query .= ( '@stories ' . implode(' | ', $searchForm['stories']) );
        }
        if (!empty($searchForm['garage'])) {
            $search->setFilter("garages", $searchForm['garage']);
        }
        if (!empty($searchForm['min_year_built']) && !empty($searchForm['max_year_built'])) {
            $search->setFilterRange("year_biult_id", intval($searchForm['min_year_built']), intval($searchForm['max_year_built']));
            $flag = 1;
        }
        if (!empty($searchForm['min_year_built']) && empty($searchForm['max_year_built'])) {
            $search->setFilterRange("year_biult_id", intval($searchForm['min_year_built']), 9999999999999);
            $flag = 1;
        }
        if (empty($searchForm['min_year_built']) && !empty($searchForm['max_year_built'])) {
            $search->setFilterRange("year_biult_id", 0, intval($searchForm['max_year_built']));
            $flag = 1;
        }
        if (!empty($searchForm['min_lot_size']) && !empty($searchForm['max_lot_size'])) {
            $search->SetFilterFloatRange("lot_acreage", floatval($searchForm['min_lot_size']), floatval($searchForm['max_lot_size']));
            $flag = 1;
        }
        if (!empty($searchForm['min_lot_size']) && empty($searchForm['max_lot_size'])) {
            $search->SetFilterFloatRange("lot_acreage", floatval($searchForm['min_lot_size']), 9999999999999.99);
            $flag = 1;
        }
        if (empty($searchForm['min_lot_size']) && !empty($searchForm['max_lot_size'])) {
            $search->SetFilterFloatRange("lot_acreage", 0.00, floatval($searchForm['max_lot_size']));
            $flag = 1;
        }

        if (!empty($searchForm['sale_type']) && $searchForm['sale_type'] == 'Under Value' && $checkMembership === true) {
            $search->SetFilterRange("persentage", 5, 14.999999);
            $search->setFilter("property_type", array(0,1,2,3,4,5,6,7,8,16));
            $flag = 1;
        }

        if (!empty($searchForm['sale_type']) && $searchForm['sale_type'] == 'Equity Deals' && $checkMembership === true) {
            $search->SetFilterRange("persentage", 15, 9999999999999);
            $search->setFilter("property_type", array(0,1,2,3,4,5,6,7,8,16));
            $flag = 1;
        }

        if (!empty($searchForm['sale_type']) && $searchForm['sale_type'] == 'Mid Cap Rental Potential' && $checkMembership === true) {
            $search->setFilter("mid_cap", array(1));
            $flag = 1;
        }

        if (!empty($searchForm['sale_type']) && $searchForm['sale_type'] == 'High Cap Rental Potential' && $checkMembership === true) {
            $search->setFilter("high_cap", array(1));
            $flag = 1;
        }

        if (!empty($searchForm['sale_type']) && $searchForm['sale_type'] == 'Rental Properties With Equity' && $checkMembership === true) {
            $search->SetFilterRange("persentage", 6.001, 9999999999999);
            $search->setFilter("rent_equity", array(1));
            $flag = 1;
        }

        if (!empty($searchForm['sale_type']) && $searchForm['sale_type'] == 'High Cap And High Equity Opportunities' && $checkMembership === true) {
            $search->SetFilterRange("persentage", 10.001, 9999999999999);
            $search->setFilter("high_rent_equity", array(1));
            $flag = 1;
        }

        if (!empty($searchForm['sale_type']) && $searchForm['sale_type'] == 'All Property Records') {
            $search->setFilter("visible", array(1));
            $flag = 1;
        }
        if (!empty($searchForm['sale_type']) && ($searchForm['sale_type'] == 'For Rent' || $searchForm['sale_type'] == 'For Rent')) {
            $search->setFilter("property_type", array(9));
            $flag = 1;
        }
        if (!empty($searchForm['sale_type']) && $searchForm['sale_type'] == 'ALL Sale Types') {
            $search->setFilter("visible", array(1));
            $flag = 1;
        }

        $geodistance_circle = isset($_POST['geodistance_circle']) ? $_POST['geodistance_circle'] : 0;
        if ($geodistance_circle) {
            $latitude = isset($_POST['latitude']) ? (float) deg2rad($_POST['latitude']) : 0.00;
            $longitude = isset($_POST['longitude']) ? (float) deg2rad($_POST['longitude']) : 0.00;
            $radius = isset($_POST['radius']) ? (float) (round($_POST['radius'], 2)/* * 1.61*/) : 0.00;
            if (($latitude != 0.00) && ($longitude != 0.00) && ($radius != 0.00)) {
                $search->SetGeoAnchor('latitude', 'longitude', $latitude, $longitude);
                $search->SetFilterFloatRange('@geodist', 0.0, $radius);
                $search->SetSortMode(SPH_SORT_EXTENDED, '@geodist ASC');
                $flag = 1;
            }
        }
        $geodistance_rectangle = isset($_POST['geodistance_rectangle']) ? $_POST['geodistance_rectangle'] : 0;
        if ($geodistance_rectangle) {
            $lat1 = isset($_POST['latitude1']) ? (float) deg2rad($_POST['latitude1']) : 0.00;
            $lon1 = isset($_POST['longitude1']) ? (float) deg2rad($_POST['longitude1']) : 0.00;
            $lat2 = isset($_POST['latitude2']) ? (float) deg2rad($_POST['latitude2']) : 0.00;
            $lon2 = isset($_POST['longitude2']) ? (float) deg2rad($_POST['longitude2']) : 0.00;

            if (( $lat1 != 0.00) && ( $lon1 != 0.00 ) && ( $lat2 != 0.00 ) && ( $lon2 != 0.00 )) {
                $min_lat = $lat1 < $lat2 ? $lat1 : $lat2;
                $max_lat = $lat1 < $lat2 ? $lat2 : $lat1;
                $min_lon = $lon1 < $lon2 ? $lon1 : $lon2;
                $max_lon = $lon1 < $lon2 ? $lon2 : $lon1;

                $search->SetFilterFloatRange('latitude', (float) $min_lat, (float) $max_lat);
                $search->SetFilterFloatRange('longitude', (float) $min_lon, (float) $max_lon);
                $flag = 1;
            }
        }
        $geodistance_polygon = isset($_POST['geodistance_polygon']) ? $_POST['geodistance_polygon'] : 0;
        if ($geodistance_polygon) {

            if (isset($_POST['latitude'])) {
                if (is_array($_POST['latitude'])) {
                    $latitudes = $_POST['latitude'];
                    $latitude = array();
                    foreach ($latitudes as $value) {
                        $latitude[] = $value;
                    }
                }
            }
            if (isset($_POST['longitude'])) {
                if (is_array($_POST['longitude'])) {
                    $longitudes = $_POST['longitude'];
                    $longitude = array();
                    foreach ($longitudes as $value) {
                        $longitude[] = $value;
                    }
                }
            }
            $coordinates_arr = array();
            for ($i = 0; $i < count($latitude); $i++) {
                $coordinates_arr[] = deg2rad($latitude[$i]);
                $coordinates_arr[] = deg2rad($longitude[$i]);
            }
            $coordinates_str = implode(',', $coordinates_arr);
            $search->SetSelect("CONTAINS(GEOPOLY2D({$coordinates_str}),latitude,longitude) as is_inside");
            $search->setFilter('is_inside', array(1));
            $flag = 1;
        }

        if (!empty($searchForm['property_type'])) {
            $query_property_type_arr = array();
            $subquerty_property_type = array();
            foreach ($searchForm['property_type'] as $property_type) {
                switch ($property_type) {
                    case 'AK':
                        $query_property_type_arr[] = 1;
                        $subquerty_property_type[] = "Attached";
                        break;
                    case 'HI':
                        $query_property_type_arr[] = 1;
                        $subquerty_property_type[] = "Detached";
                        break;
                    //case 'CA':
                    case 'CA1':
                        $query_property_type_arr[] = 2;
                        break;
                    //case 'NV':
                    case 'OR':
                        $query_property_type_arr[] = 16;
                        break;
                    case 'TH':
                        $query_property_type_arr[] = 3;
                        break;
                    case 'DP':
                        $query_property_type_arr[] = 4;
                        $subquerty_property_type[] = "Duplex";
                        break;
                    case 'TP':
                        $query_property_type_arr[] = 4;
                        $subquerty_property_type[]  = "Triplex";
                        break;
                    case 'FP':
                        $query_property_type_arr[] = 4;
                        $subquerty_property_type[] = "Fourplex";
                        break;
                    case 'AZ':
                        $query_property_type_arr[] = 6;
                        break;
                    case 'CO':
                        $query_property_type_arr[] = 7;
                        break;
                    case 'AL':
                        $query_property_type_arr[] = 5;
                        break;
                }
            }
            if($subquerty_property_type){
                $query .= ( '@sub_type ' . implode(' | ', $subquerty_property_type) );
                $flag = 1;
            }
             
            if ($query_property_type_arr) {
                $search->setFilter("property_type", $query_property_type_arr);
                $flag = 1;
            }
        }

//Yii::log('Property Search ' . $query ,'ERROR'); // 0.25762701034546

        $resArray = array();
        if ($query != '') {
            $resArray = $search->query($query, '*');
        } else {
            if ($flag == 1) {
                $resArray = $search->query('', '*');
            }
        }
//$time_end = microtime(TRUE);
//$time = $time_end - $time_start;
//$time_start = $time_end;
//Yii::log('Step 1: ' . $time ,'ERROR'); // 0.25762701034546
        if (!empty($resArray)) {
            if (count($resArray['matches']) > 0) {
//Yii::log('resArray: ' . print_r($resArray['matches'][0],1) ,'ERROR');
                $list_pk = array();
                $list_weight = array();
                foreach ($resArray['matches'] as $matches) {
                    $list_pk[] = $matches['id'];
                    $list_weight[$matches['id']] = $matches['weight'];
                }
//$time_end = microtime(TRUE);
//$time = $time_end - $time_start;
//$time_start = $time_end;
//Yii::log('Step 2: ' . $time ,'ERROR'); // 0.00050592422485352

                $results = PropertyInfo::model()->cache(1000, null)->with('city', 'county', 'state', 'zipcode'
                        , 'propertyInfoAdditionalBrokerageDetails', 'brokerage_join', 'slug',
                        'propertyInfoPhoto')->findAllByAttributes(array('property_id' => $list_pk));
//$time_end = microtime(TRUE);
//$time = $time_end - $time_start;
//$time_start = $time_end;
//Yii::log('Step 3: ' . $time ,'ERROR'); // 0.76244592666626

                if (!empty($results)) {
//Yii::log('results: ' . print_r($results[0],1) ,'ERROR');
                    $result['count_result'] = count($results);
                    $result['result'] = $this->actionGetSearchResult($results, $list_weight, $user_id);
//$time_end = microtime(TRUE);
//$time = $time_end - $time_start;
//$time_start = $time_end;
//Yii::log('Step 4: ' . $time ,'ERROR'); // 0.68614220619202

                    $result['res_map_layout'] = SiteHelper::getSearchMapResult($results, $list_weight, $user_id);
                    $result['status'] = 'success';
                    $result['latlon'] = SiteHelper::getLatLonResult($results);
//$time_end = microtime(TRUE);
//$time = $time_end - $time_start;
//$time_start = $time_end;
//Yii::log('Step 6: ' . $time ,'ERROR'); // 0.47263383865356

                }
            }
        }
//$time_end = microtime(TRUE);
//$time = $time_end - $time_start1;
//Yii::log('Step All: ' . $time ,'ERROR'); // 2.1794710159302
        return $result;
    }

    public function actionSearch() {
        $expire_user = $this->getExpireUser();
        $user_id = null;
        if (!Yii::app()->user->isGuest) {
            $dependency = new CDbCacheDependency('SELECT lastvisit_at FROM tbl_users WHERE id='.Yii::app()->user->id);
            $model = User::model()->cache(1000,$dependency, 1)->with('profile', 'profession')->findByPk(Yii::app()->user->id);
            if (!is_object($model)) {
                $this->redirect(Yii::app()->createUrl('/user/login'));
            }
            $profile = $model->profile;
            $user_id = $model->id;
        }
            $results = array();
            $result = array();
            $result['count_result'] = 0;
            $result['result'] = array();
            $result['status'] = 'failed';
            if (Yii::app()->request->isAjaxRequest) {

                // save search criteria
                $doSaveSearch = (isset($_POST['save_search_checkbox']) && intval($_POST['save_search_checkbox']) == 1) ? true : false;
                if($doSaveSearch == true && !Yii::app()->user->isGuest){

                    $db_datetime_format = 'Y-m-d H:i:s';
                    $now = new DateTime('now');

                    $savedSearchData = array(
                        'name' => (isset($_POST['save_search_title']) && trim($_POST['save_search_title']) !='') ? trim($_POST['save_search_title']) : $now->format($db_datetime_format),
                        'user_id' => Yii::app()->user->getId(),
                    );

                    $criteria=new CDbCriteria;
                    $criteria->condition = 'name=:name AND user_id=:user_id';
                    $criteria->params = array(':name'=>$savedSearchData['name'], ':user_id'=>$savedSearchData['user_id']);
                    $savedSearchModel = SavedSearch::model()->find($criteria);

                    if(!$savedSearchModel){
                        $savedSearchModel = new SavedSearch('create');

                        $expiry = clone $now;
                        $expiry->add(new DateInterval( "P1Y" ));

                        $savedSearchModel->setAttributes(array(
                            'name' => $savedSearchData['name'],
                            'user_id' => $savedSearchData['user_id'],
                            'email_alert_freq' => SavedSearch::EMAIL_FREQ_NEVER,
                            'expiry_date' => $expiry->format($db_datetime_format),
                        ));

                        if($savedSearchModel->save()){
                            $savedSearchEmail = new SavedSearchEmail();
                            $savedSearchEmail->setAttributes(array(
                                'saved_search_id' => $savedSearchModel->id,
                                'email' => Yii::app()->user->username
                            ));
                            $savedSearchEmail->save();
                            $savedSearchModel->refresh();
                        }
                    }


                    $savedSearchCriteria = SavedSearchCriteria::model()->findAllByAttributes(array('saved_search_id' => $savedSearchModel->id));


                    if(!empty($savedSearchCriteria)){
                        SavedSearchCriteria::model()->deleteAll('saved_search_id = ?' , array($savedSearchModel->id));
                    }

                    $savedSearchModel->saveRelatedSearchCriteria($savedSearchModel, $_POST);
                }



                $searchFormModel = new SearchForm();
                $searchForm = array();
                
                $searchForm['address'] = isset($_POST['address']) ? trim($_POST['address']) : '';
                $searchForm['street_number'] = isset($_POST['street_number']) ? $_POST['street_number'] : '';
                $searchForm['street_address'] = isset($_POST['street_address']) ? $_POST['street_address'] : '';
                $searchForm['city'] = isset($_POST['city']) ? $_POST['city'] : '';
                $searchForm['state'] = isset($_POST['state']) ? $_POST['state'] : '';
                $searchForm['zipcode'] = isset($_POST['zipcode']) ? $_POST['zipcode'] : '';
                $searchForm['country'] = isset($_POST['country']) ? $_POST['country'] : '';
                $searchForm['sale_type'] = isset($_POST['sale_type']) ? $_POST['sale_type'] : '';
                $searchForm['min_price'] = isset($_POST['min_price']) ? trim($_POST['min_price']) : '';
                $searchForm['max_price'] = isset($_POST['max_price']) ? trim($_POST['max_price']) : '';
                $searchForm['pool'] = isset($_POST['pool']) ? trim($_POST['pool']) : '';
                $searchForm['bmarket'] = isset($_POST['bmarket']) ? trim($_POST['bmarket']) : '';
                $searchForm['property_type'] = Array();                                           //need clarify
                if (isset($_POST['property_type'])) {
                    if (is_array($_POST['property_type'])) {
                        $property_type = $_POST['property_type'];
                        foreach ($property_type as $value) {
                            $searchForm['property_type'][] = $value;
                        }
                    }
                } else {
                    $searchForm['property_type'] = '';
                }
                $searchForm['stories'] = Array();                                           //need clarify
                if (isset($_POST['stories'])) {
                    if (is_array($_POST['stories'])) {
                        $stories = $_POST['stories'];
                        foreach ($stories as $value) {
                            $searchForm['stories'][] = $value;
                        }
                    }
                } else {
                    $searchForm['stories'] = '';
                }
                $searchForm['garage'] = Array();                                           //need clarify
                if (isset($_POST['garage'])) {
                    if (is_array($_POST['garage'])) {
                        $garage = $_POST['garage'];
                        foreach ($garage as $value) {
                            $searchForm['garage'][] = $value;
                        }
                    }
                } else {
                    $searchForm['garage'] = '';
                }
                $searchForm['min_price_sqft'] = isset($_POST['min_price_sqft']) ? trim($_POST['min_price_sqft']) : '';
                $searchForm['max_price_sqft'] = isset($_POST['max_price_sqft']) ? trim($_POST['max_price_sqft']) : '';
                $searchForm['min_sqft'] = isset($_POST['min_sqft']) ? trim($_POST['min_sqft']) : '';
                $searchForm['max_sqft'] = isset($_POST['max_sqft']) ? trim($_POST['max_sqft']) : '';
                $searchForm['bed'] = isset($_POST['bed']) ? $_POST['bed'] : '';
                $searchForm['bath'] = isset($_POST['bath']) ? $_POST['bath'] : '';
                $searchForm['keywords'] = isset($_POST['keywords']) ? trim($_POST['keywords']) : '';

                if (isset($_POST['min_year_built']) && !empty($_POST['min_year_built'])) {
                    $min_year_built = array();
                    $min_year_built = explode(" ", trim($_POST['min_year_built']));
                    $searchForm['min_year_built'] = $min_year_built[1];
                } else {
                    $searchForm['min_year_built'] = '';
                }
                if (!empty($_POST['max_year_built'])) {
                    $max_year_built = array();
                    $max_year_built = explode(" ", trim($_POST['max_year_built']));
                    $searchForm['max_year_built'] = $max_year_built[1];
                } else {
                    $searchForm['max_year_built'] = '';
                }
                if (!empty($_POST['min_lot_size'])) {
                    $min_lot_size = array();
                    $min_lot_size = explode(" ", trim($_POST['min_lot_size']));
                    $searchForm['min_lot_size'] = $min_lot_size[0];
                } else {
                    $searchForm['min_lot_size'] = '';
                }
                if (!empty($_POST['max_lot_size'])) {
                    $max_lot_size = array();
                    $max_lot_size = explode(" ", trim($_POST['max_lot_size']));
                    $searchForm['max_lot_size'] = $max_lot_size[0];
                } else {
                    $searchForm['max_lot_size'] = '';
                }

                $searchFormModel->attributes = $searchForm;
                $result = '';
                if ($searchFormModel->validate()) {

                    $result = $this->setSearchQuery($searchForm, $user_id);
                } else {
                    $result = $searchFormModel->getErrors();
                }
                echo CJSON::encode($result);
                Yii::app()->end();
            }
            $search_fields = array();
            if(isset($_POST['top-search-submit'])){

                $search_fields['searchfld'] = isset($_POST['searchfld']) ? strip_tags($_POST['searchfld']) : '';
                $search_fields['street_number_searchfld'] = isset($_POST['street_number_searchfld']) ? strip_tags($_POST['street_number_searchfld']) : '';
                $search_fields['street_address_searchfld'] = isset($_POST['street_address_searchfld']) ? strip_tags($_POST['street_address_searchfld']) : '';
                $search_fields['city_searchfld'] = isset($_POST['city_searchfld']) ? strip_tags($_POST['city_searchfld']) : '';
                $search_fields['state_searchfld'] = isset($_POST['state_searchfld']) ? strip_tags($_POST['state_searchfld']) : '';
                $search_fields['zipcode_searchfld'] = isset($_POST['zipcode_searchfld']) ? strip_tags($_POST['zipcode_searchfld']) : '';
                $search_fields['country_searchfld'] = isset($_POST['country_searchfld']) ? strip_tags($_POST['country_searchfld']) : '';
                $search_fields['sale_type_searchfld'] = isset($_POST['sale_type_searchfld']) ? strip_tags($_POST['sale_type_searchfld']) : '';
                $search_fields['subdivision_searchfld'] = isset($_POST['subdivision_searchfld']) ? strip_tags($_POST['subdivision_searchfld']) : '';


                if ($search_fields) {
                    $result = $this->getSearchResultSmallQuery($search_fields, $user_id);
                }

            }





            $general_search_fields = array();

            if(Yii::app()->request->isPostRequest){
                $general_search_fields = $this->fillSearchFields(Yii::app()->request);

                if(isset($general_search_fields['geodistance_rectangle']) ||
                    isset($general_search_fields['geodistance_circle']) ||
                    isset($general_search_fields['geodistance_polygon'])){

                    $factory = new MapBoundaryFactory();
                    $general_search_fields['map_boundary'] = $factory->create($general_search_fields);
                }

                $general_search_fields['searchOnLoad'] = Yii::app()->request->getParam('searchOnLoad');
                $general_search_fields['save_search_title'] = Yii::app()->request->getParam('save_search_title');
            }







            $this->render('search', array(
                    'profile' => Yii::app()->user->isGuest ? '' : $profile,
                    'search_results' => $result,
                    'expire_user' => $expire_user, 
                    'top_search' => $search_fields,
                    'general_search_fields' => $general_search_fields,
            ));
//        } else { 
//            $this->redirect('/user/login');
//        }
    }


    private function fillSearchFields(CHttpRequest $request){

        $arr = array();

        foreach(SavedSearch::$allowedAttrs as $attr_name)
        {
            if($request->getParam($attr_name)){
                $arr[$attr_name] = $request->getParam($attr_name);
            }
        }

        return $arr;
    }


    private function actionGetSearchResult($search_results, $list_weight = array(),$user_id = null) {
        $result = array();
        $this->_lat = 0;
        $this->_lon = 0;

        foreach ($search_results as $search_result) {
            $discont = 0;
            if (($search_result['percentage_depreciation_value'] >= Yii::app()->params['underValueDeals'])) {
                $discont = $search_result['percentage_depreciation_value'];
            }
            if ($discont == 0) {
                if (( ($search_result['estimated_price'] > 0) &&
                    (100 - ($search_result['property_price'] * 100 / $search_result['estimated_price'])) > 0)) {
                    $discont = 100 - ($search_result['property_price'] * 100 / $search_result['estimated_price']);
                }
            }

            $stat = $search_result->propertyInfoAdditionalBrokerageDetails->status;
            $colorScheme = SiteHelper::getColorScheme($stat,$discont);
            if (isset($search_result->propertyInfoAdditionalBrokerageDetails->status)) {
                $prop_stat_caps = strtoupper($search_result->propertyInfoAdditionalBrokerageDetails->status);
                $property_sale_date = date('m/d/Y', strtotime($search_result->property_updated_date));
                $property_price = number_format($search_result->property_price);
                $status = '<span class="label '.$colorScheme['label-color'].'">' . $prop_stat_caps . '</span>';
            }
            if (( ($search_result->getlatitude != 0.000000) && ($search_result->getlatitude != '') ) && ( ($search_result->getlongitude != 0.000000) && ($search_result->getlongitude != '') )) {
                if (($this->_lat == 0) && ($this->_lon == 0)) {
                    $this->_lat = $search_result->getlatitude;
                    $this->_lon = $search_result->getlongitude;
                }
            }
            $col0 = '';
            if(!empty($list_weight[$search_result->property_id])) {
                $col0 .= $list_weight[$search_result->property_id]. '-' . strtotime($search_result->property_updated_date);
            }

            $col1 = "<a href=" . Yii::app()->createUrl('property/details' , array( 'slug'=>$search_result->slug->slug)) ." class='exclude-reinclude' data-property_id='". $search_result->property_id ."' >";
            $col1 .= CPathCDN::checkPhoto($search_result, "thumb-img-140", 0 );
            $col1 .= '</a>';

            $discont = $search_result->getDiscontValue();

            if ($discont >= Yii::app()->params['underValueDeals'] && ($prop_stat_caps != 'HISTORY' && $prop_stat_caps != 'EXPIRED')) {
                $col1 .= '<br><span class="label bg-color-greenDark">' . round($discont) . '% Below TMV</span>';
            }

            $col2 = '';
            $col2 .= $search_result->property_street.'<br>';
            $col2 .= $search_result->city ? $search_result->city->city_name . ', ' : '';
            $col2 .= $search_result->state ? $search_result->state->state_code . ' ' : '';
            $col2 .= $search_result->zipcode ? $search_result->zipcode->zip_code : '';
            $col2 .= '<br>';
            $community = $search_result->community_name ? $search_result->community_name : '';
            if ($community == '' || $community == 'None') {
                $community = $search_result->subdivision ? $search_result->subdivision : '';
            }
            if ($community == '' || $community == 'None') {
                $community = $search_result->area ? $search_result->area : '';
            }

            $col2 .= $community ? ucwords(strtolower($community)) . '<br>' : '';
            if ($search_result->brokerage_join) {
                $brokerage_name = $search_result->brokerage_join->brokerage_name ? $search_result->brokerage_join->brokerage_name : '';
                if ($brokerage_name) {
                    $col2 .= ucwords(strtolower($brokerage_name));
                }
            }
            $col2 = "<a class=\"property-title\" href=" . Yii::app()->createUrl('property/details' , array( 'slug'=>$search_result->slug->slug)) . " >".$col2."</a>";

            $col3 = '';
            $col3 .= $status;
            if($user_id != null){
                $user_property_info = SiteHelper::getUserPropertyStatus($user_id,$search_result->mls_sysid,$search_result->mls_name);
                $user_status = '';
                if(!isset($user_property_info)){
                    $user_status = 'New';
                } else {
                    $user_status = $user_property_info->user_property_status;
                    if(strtotime($search_result->property_uploaded_date) > strtotime($user_property_info->last_viewed_date)){
                        $user_property_info->user_property_status = 'Updated';
                    }
                }
                $label_of_user_status = SiteHelper::getColorSchemeOfUserPropertyStatus($user_status);
                $col3 .= '<br><br><span class="label-user-property-status '.$label_of_user_status.'">'.$user_status.'</span>';
            }
            if($search_result->property_type == '9'){
                $trueMarket = 'TMR';
                $estimated = 'Estimated Spread';
            } else {
                $trueMarket = 'TMV';
                $estimated = 'Estimated Equity';
            }
            $col4 = '';
            setlocale(LC_MONETARY, 'en_US');
             $col4 .= $search_result->property_price ? '$ '.number_format($search_result->property_price,0,'.',',').'<br>' : '';
//            $col4 .= $search_result->property_price ? '$ '.money_format('%i', $search_result->property_price).'<br>':'';
            if ($search_result->estimated_price > 0) {
                $col4 .= $trueMarket . ' $' . number_format($search_result->estimated_price,0,'.',',');
//                $col4 .= $trueMarket . ' $' . money_format('%i', $search_result->estimated_price);
                if ($discont >= Yii::app()->params['underValueDeals']) {
                    $estimatedEquity = $search_result->getEstimatedEquity($search_result->estimated_price, $search_result->property_price);
                     $estimatedEquity = number_format($estimatedEquity,0,'.',',');
//                    $estimatedEquity = money_format('%i', $estimatedEquity);
                    $col4 .= '<br/>'. $estimated .' $' . $estimatedEquity;
                }
            }

            $col5 = '';
            $col5 .= $search_result->house_square_footage ? $search_result->house_square_footage . ' Sq Ft<br>' : '';
            $col5 .= $search_result->lot_acreage ? $search_result->lot_acreage . ' Acre<br>' : '';
            $col5 .= PropertyInfo::getPropertyType($search_result->property_type);

            $col6 = '';
            $col6 .= $search_result->bedrooms ? $search_result->bedrooms . " Beds/<br>" : '';
            $col6 .= $search_result->bathrooms ? $search_result->bathrooms . ' Baths/<br>' : '';
            $col6 .= $search_result->garages ? $search_result->garages . ' Car Gar' : '';

            $updatedDate = $search_result->getUpdatedDateViaStatus();
            $col7 = '';
            $col7 .= str_replace("-", "/", $updatedDate);
            $col7 .= '<br>';

            $datetime_now = new DateTime();
            $datetime_exp = new DateTime($updatedDate);
            $interval = $datetime_now->diff($datetime_exp);
            $quantity = $interval->days;
            $status_p = $colorScheme['status'];
            $col7 .= $quantity ? $quantity . ' DOM' : '';
            $col7 .= '<br>';
            $col7 .= '<div class="btn-group custom-block-btn-group">'
                        //<button type="button" class="btn btn-success btn-sm favorite" data-property_id="' . $search_result->property_id . '"><i class="fa fa-heart"></i></button>
                        .'<a class="property_info_row" data-lat="' . $search_result->getlatitude
                        . '" data-lon="' . $search_result->getlongitude
                        . '" data-status="' . $status_p
                        . '" data-address= "' . $search_result->property_street
                        . '" data-property_id="' . $search_result->property_id
                        . '" href="' . Yii::app()->createUrl('property/details', array( 'slug'=>$search_result->slug->slug))
                        . '" >
                        <button type="button" class="btn btn-primary btn-sm"> <i class="fa fa-search-plus"></i> </button> </a>'
                        .'<button onclick="showinmap(this)" property_id="'.$search_result->property_id.'" type="button" class="show-in-map btn btn-success btn-sm"> <i class="fa fa-map-marker"></i> </button>'
                        //<button type="button" class="btn btn-danger btn-sm delete-property" data-property_id="' . $search_result->property_id . '"><i class="fa fa-times"></i></button>
                    .'</div>';
            $col8 = '';
            if($search_result->public_remarks != null){
                $col8 .= '<div class="public-remarks ellipsed"">';
                $col8 .= '<p>'.$search_result->public_remarks.'</p>';
                if (strlen($search_result->public_remarks) > 180)
                    $col8 .= '<span onclick="toggleEllapse(this)">(Read more)</span>';
                $col8 .= '</div>';
            } else {
                $col8 .= '-';
            }
            $result[] = array($col0, $col1, $col2, $col3, $col4, $col5, $col6, $col8, $col7);
        }

        return $result;
    }

    public function actionAddFavorites() {
        if (Yii::app()->request->isAjaxRequest) {
            $property_id = isset($_POST['property']) ? $_POST['property'] : 0;
            $result = "This option is available only for authed.";
            if (!Yii::app()->user->isGuest) {
                if ($property_id > 0) {
                    $model = new SavedListing;
                    $earlier_saved = $model->countByAttributes(array('property_id' => $property_id, 'mid' => Yii::app()->user->id));
                    if (!$earlier_saved) {
                        $row = array();
                        $row['save_id'] = null;
                        $row['property_id'] = $property_id;
                        $row['mid'] = Yii::app()->user->id;
                        $row['save_date'] = date("Y-m-d");
                        $model->attributes = $row;
                        if ($model->validate()) {
                            $model->save();
                            $result = "The property was saved.";
                        }
                    } else {
                        $result = "The property was saved earlier.";
                    }
                }
            }
            echo CJSON::encode($result);
            Yii::app()->end();
        }
    }

    public function actionDeleteFavorites() {
        if (Yii::app()->request->isAjaxRequest) {
            $property_id = isset($_POST['property']) ? $_POST['property'] : 0;
            $result = array();
            if (!Yii::app()->user->isGuest) {
                if ($property_id > 0) {
                    $model = SavedListing::model()->deleteAllByAttributes(array('property_id' => $property_id, 'mid' => Yii::app()->user->id));
                    if ($model) {
                        $result[] = "The property was deleted from favorites.";
                    }
                }
            }

            echo CJSON::encode($result);
            Yii::app()->end();
        }
    }
    
 
    public function getPhotoArr($modelArr) {
        if(isset($modelArr->propertyInfoPhoto[0])) {
            $model = $modelArr->propertyInfoPhoto[0];
        } else {
            return array();
        }
        $photoArr = array();
            for ($j = 2; $j <=40 ; $j++) {
                    $photo_key = 'photo'.$j;
                    $caption_key = 'caption'.$j;
                    if(!empty($model->$photo_key)){
                        $caption = property_exists($model, $caption_key) ? $model->$caption_key : '';
                        $photoArr[]=(object)array(
                                'property_id'=>$model->property_id,
                                'caption'=>$caption,
                                'photo1'=>$model->$photo_key,
                                'fullAddress'=>$modelArr->getFullAddress() .' '. $photo_key
                        );
                    }  else {
                        continue;
                    }
            }
        if (isset($photoArr[0]) && strtolower(substr($photoArr[0]->photo1, 0, 4)) === 'http') {
            $file_headers=!empty(Yii::app()->cache)?Yii::app()->cache->get($photoArr[0]->photo1):false;
            if($file_headers===false)
            {
    //            $file_headers = @get_headers($photoArr[0]->photo1);
                $file_headers = CPathCDN::checkS3Photo($photoArr[0]->photo1);
                if(!empty(Yii::app()->cache)) {
                    Yii::app()->cache->set($photoArr[0]->photo1,$file_headers, 1000);
                }
            }
            if($file_headers[0] != 'HTTP/1.1 404 Not Found') {
            } else {
                return array();
            }
        }
        return $photoArr;
    }
    
    private function getHouseViewsArr($house_views_list, $house_views) {
        $house_views_ret = array();
        $house_views_lower = strtolower($house_views);
        foreach ($house_views_list as $value) {
            if(strpos($house_views_lower, $value) === false) {
                $house_views_ret[] = $value;
            }
        }
        return $house_views_ret ;
    }

    private function countPropertiesExclude($comparebles_properties) {
        $excludedPropertiesIDs = array();

        if(property_exists($comparebles_properties, 'exclude')){
            foreach ($comparebles_properties->exclude as $exclude) {
                $excludedPropertiesIDs[$exclude['product_id']] = $exclude['product_id'];
            }
        }

        $countExcludeProperties = 0;
        if(property_exists($comparebles_properties, 'result_queryAllRows') && !empty($excludedPropertiesIDs)){
            foreach ($comparebles_properties->result_queryAllRows as $comparebles_property) {
                $comparebles_property = (object) $comparebles_property;
                if(!empty($excludedPropertiesIDs[$comparebles_property->property_id])){
                    $countExcludeProperties++;
                }

            }
        }
        
        return $countExcludeProperties;
    }



/**
 * used for similare homes
 * 
 * @param type $property
 * @return type 
 */
    public function getFullAddress( $property = false ) {
        if(!$property) {
//            $property = PropertyInfo::model()->cache(1000, null)->with('city', 'county', 'state', 'zipcode')->findByAttributes(array('property_id' => $id));
            return '';
        }
        $address = $property->property_street;
        $address .= !empty($address)?' ':'';
        if(empty($property->city_name)) {
            $address .= !empty($property->city->city_name)?$property->city->city_name:'';
        } else {
            $address .= $property->city_name;
        }
        $address = ucwords(strtolower($address));
        
        $address .= !empty($address)?', ':'';
        if(empty($property->state_code)) {
            $address .= !empty($property->state->state_code)?strtoupper($property->state->state_code):'';
        } else {
            $address .= $property->state_code;
        }
        
        $address .= !empty($address)?' ':'';
        if(empty($property->zip_code)) {
            $address .= !empty($property->zipcode->zip_code)?strtoupper($property->zipcode->zip_code):'';
        } else {
            $address .= $property->zip_code;
        }
        
        return $address;
    }

    /**
     * Show property info history page. This method has specific url, defined in main.php
     *
     * @param $id
     */
    public function actionHistory($id){
        $profile = '';
        if(!Yii::app()->user->isGuest) {
            $userModel = User::model()->with('profile', 'profession')->findByPk(Yii::app()->user->id);
            $profile = $userModel->profile;
        }

        $property = PropertyInfoHistory::model()
            ->with(array(
                'propertyInfoDetails',
                'propertyInfoAdditionalDetails',
                'propertyInfoAdditionalBrokerageDetails',
                'city',
                'county',
                'state',
                'zipcode'))
            ->find(array("condition"=>"t.property_id = $id"));

        $actualInfo = PropertyInfo::model()
            ->with(array(
                'propertyInfoAdditionalBrokerageDetails',
                'propertyInfoAdditionalDetails',
                'propertyInfoDetails',
                'propertyInfoPhoto',
                'user',
                'city',
                'county',
                'state',
                'zipcode'))
            ->find(array("condition"=>"mls_sysid = $property->mls_sysid"));

        if($actualInfo === null){
            $actualInfo = new ActualInfoIsNull();
        }

        ($property->propertyInfoDetails === null) ? $property->propertyInfoDetails = new ActualInfoIsNull() : $property->propertyInfoDetails;
        ($property->propertyInfoAdditionalDetails === null) ? $property->propertyInfoAdditionalDetails = new ActualInfoIsNull() : $property->propertyInfoAdditionalDetails;
        ($property->propertyInfoAdditionalBrokerageDetails === null) ? $property->propertyInfoAdditionalBrokerageDetails = new ActualInfoIsNull() : $property->propertyInfoAdditionalBrokerageDetails;

        $this->render('propertyHistoryDetailsView', array(
            'property' => $property,
            'actualInfo' => $actualInfo,
            'profile' => Yii::app()->user->isGuest ? '' : $profile,
        ));
    }
    
}
