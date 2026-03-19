<?php

namespace app\helpers;

class SiteHelper {

    public static function isValueEmpty($value){
        if(is_array($value)){
            if(empty($value))
                return true;
        }else{
            $value = trim($value);

            if($value == '')
                return true;
        }

        return false;
    }

    public static function toString($value){
        if(is_array($value)){
            return implode(', ', $value);
        }else{
            return $value;
        }
    }

    public static function timeElapsed($secs){
        $bit = array(
            'y' => $secs / 31556926 % 12,
            'w' => $secs / 604800 % 52,
            'd' => $secs / 86400 % 7,
            'h' => $secs / 3600 % 24,
            'm' => $secs / 60 % 60,
            's' => $secs % 60
            );
        $ret = array();  
        foreach($bit as $k => $v) {
            if($v > 0){
                $ret[] = $v . $k;
            }
        }
        if(empty($ret)) {
            $ret[] = '0s';
        }
        return join(' ', $ret);
    }

    /**
     * Send to user mail
     */
    public static function sendMail($email,$subject,$message) {
//        $adminEmail = Yii::app()->params['adminEmail'];
        
        $mail = new YiiMailer();
        $mail->clearLayout();//if layout is already set in config
        $mail->setFrom('noreply@ippraisall.com', 'ippraisall.com');
        $mail->setTo($email);
        $mail->setSubject($subject);
        $mail->setBody($message);
//                if($mail->send()) {
//                    Yii::log('Email Alert: Sended to ' . $email ,'ERROR'); 
//                } else {
//                    Yii::log('Email Alert: NOT Sended to ' . $email ,'ERROR'); 
//                }
        return $mail->send();
    }
    
    public static function isAdmin() {
        $roles=Rights::getAssignedRoles(Yii::app()->user->Id); 
        if(in_array('Admin', array_keys($roles))) {
            return true;
        }
        return false;
    }
    
    public static function getLatLonResult($search_results) {
        $_lat = 0;
        $_lon = 0;
        foreach ($search_results as $search_result) {
            if (( ($search_result->getlatitude != 0.000000) && ($search_result->getlatitude != '') ) && ( ($search_result->getlongitude != 0.000000) && ($search_result->getlongitude != '') )) {
                $_lat = $search_result->getlatitude;
                $_lon = $search_result->getlongitude;
                return array($_lat, $_lon);
            }
        }        
        return array($_lat, $_lon);
    }

    public static function getSearchMapResult($search_results, $list_weight = array()) {
        
        $result = array();
        foreach ($search_results as $search_result) {
            $discont = 0;

            if (($search_result->percentage_depreciation_value >= Yii::app()->params['underValueDeals'])) {
                $discont = $search_result->percentage_depreciation_value;
            }
            if ($discont == 0) {
                if (( ($search_result->estimated_price > 0) &&
                    (100 - ($search_result->property_price * 100 / $search_result->estimated_price)) > 0)) {
                    $discont = 100 - ($search_result->property_price * 100 / $search_result->estimated_price);
                }
            }

            $is_discont = $discont >= Yii::app()->params['underValueDeals'];
            if (isset($search_result->propertyInfoAdditionalBrokerageDetails->status)) {
                $status = strtoupper($search_result->propertyInfoAdditionalBrokerageDetails->status);
                $colorScheme = self::getColorScheme($status,$is_discont);
                $property_sale_date = date('m/d/Y', strtotime($search_result->property_updated_date));
                $property_price = number_format($search_result->property_price);
                $status_label = '<span class="label '.$colorScheme['label-color'].'">' . $status . '</span>';
            }

            /*User property status*/
            $user_id = Yii::app()->user->getId();
            $user_status_label = '';
            if($user_id != null){
                $user_property_info = SiteHelper::getUserPropertyStatus($user_id,$search_result->mls_sysid,$search_result->mls_name);
                if(!isset($user_property_info)){
                    $user_status = 'New';
                } else {
                    $user_status = $user_property_info->user_property_status;
                    if(strtotime($search_result->property_uploaded_date) > strtotime($user_property_info->last_viewed_date)){
                        $user_property_info->user_property_status = 'Updated';
                    }
                }
                $label_of_user_status = SiteHelper::getColorSchemeOfUserPropertyStatus($user_status);
                $user_status_label = '<br><br><span class="label-user-property-status '.$label_of_user_status.'">'.$user_status.'</span>';
            }

            //if (($search_result->percentage_depreciation_value >= 10)) {`
            $col0 = '';
            if(!empty($list_weight[$search_result->property_id])) {
                $col0 .= $list_weight[$search_result->property_id] . '-' . $search_result->property_updated_date;
            } else {
                $col0 .= $search_result->property_id ;
            }
            $dat_status = $colorScheme['status'];
            $col1 = '';
            $col1 .= '<h6 class=""><a href="'.Yii::app()->createUrl('property/details', array( 'slug'=>$search_result->slug->slug)).'"> ' . $search_result->fullAddress . '</a><span class="status-label pull-right">'.$user_status_label . $status_label .'</span></h6>' ;
            $col1 .= '<div style="position: relative;">';
            $col1 .= "<a class='property_info_row_map exclude-reinclude' data-lat='"
                        . $search_result->getlatitude . "' data-lon='" 
                        . $search_result->getlongitude . "' data-status='"
                        . $dat_status . "' data-discont='"
                        . $discont ."' data-address= '"
                        . $search_result->property_street . "' data-property_id='"
                        . $search_result->property_id . "' href=" . Yii::app()->createUrl('property/details', array( 'slug'=>$search_result->slug->slug)) . " > " 
                        . CPathCDN::checkPhoto($search_result, "img-responsive" ) . "</a>";


            if ($is_discont) {
                $col1 .= '<span class="air air-top-left label bg-color-greenDark">' . round($discont) . '% Below TMV</span>';
            }
            
            $col1 .= '<span class="air air-bottom-left badge bg-color-blue"> ' . $search_result->countPhoto() . ' photos </span>';
            $col1 .= '</div>';

            $col2 = '<br/>';
            $col2 .= '<div class="row row-eq-height row-wrapper"><div class="col-xs-4"><strong>';
            $col2 .= $search_result->property_price ? 'List Price: <br> <span class="price">$'.number_format($search_result->property_price,0,'.',',') : '';
            $col2 .= '</span></strong></div>';

            if ($search_result->estimated_price > 0) {
                $col2 .= '<div class="col-xs-4 "><strong>';
                $col2 .= 'True Market Value:<br>$ ' . number_format($search_result->estimated_price,0,'.',',');
                $col2 .= '</strong></div>';
                if($search_result->estimated_price > $search_result->property_price){
                    $estimatedEquity = $search_result->estimated_price - $search_result->property_price;
                    $estimated_equity = number_format($estimatedEquity, 0, '.', ',');
                    $col2 .= '<div class="col-xs-4"><strong>';
                    $col2 .= 'Estimated Equity:<br>$ ' . $estimated_equity;
                    $col2 .= '</strong></div>';
                }
            }
            $col2 .= '</div>';
            $col2 .= '<div class="row"><div class="col-xs-12">';
            $col2 .= '<p style="padding:10px 0">';
            $col2 .= $search_result->house_square_footage ? $search_result->house_square_footage.' Square Feet, ' : '';
            $col2 .= $search_result->property_type ? PropertyInfo::getPropertyType($search_result->property_type).', ' : '';
            $col2a = $search_result->bedrooms ? $search_result->bedrooms.' Beds' : '';
            $col2a .= ($search_result->bedrooms && $search_result->bathrooms)?'/ ' : '';
            $col2a .= $search_result->bathrooms ? $search_result->bathrooms.' Baths ' : '';
            $col2 .= !empty($col2a) ? $col2a .', ' : '';
            
            $col2b = $search_result->garages ? $search_result->garages.' Car Gar, ' : '';
            $col2b .= ($search_result->garages && !empty($search_result->pool))?'/ ' : '';
            $col2b .= !empty($search_result->pool) ? ' Pool, ' : '';
            $col2 .= !empty($col2b) ? $col2b : '';
            
            $col2 .= $search_result->lot_acreage ? $search_result->lot_acreage.' Lot Acreage <br>' : '';

            $col2 .= 'Updated Date: '.$search_result->property_updated_date;
            $col2 .= '</p>';
            if($search_result->public_remarks){
                $col2 .= '<p>'.$search_result->public_remarks.'</p>';
            }
            $col2 .= '</div>';
            $col2 .= '</div>';
            $col1 .= $col2;
            $updatedDate = $search_result->getUpdatedDateViaStatus();
            $col3 = '';
            $col3 .= str_replace("-", "/", $updatedDate);
            $col4 = $search_result->property_price;
            $col5 = $search_result->estimated_price > 0 ? $search_result->estimated_price : '';
            $col6 = $discont ? $discont : '';
            $col7 = $search_result->estimated_price > $search_result->property_price ? $estimated_equity : '';
            $col8 = $search_result->fullAddress;
            $col9 = $search_result->property_updated_date;
            $col10 = isset($user_status) ? $user_status : '';

            $result[] = array($col0, $col1, $col3, $col4, $col5, $col6, $col7, $col8, $col9, $col10);
        }
        return $result;
    }

    public static function forMembersOnly($value) {
        if (Yii::app()->user->isGuest) {
            return '<a data-dismiss="modal" data-toggle="modal" href="/user/login" data-target="#modal_login" >Members Only</a>';
        } else {
            return $value;
        }
    }
    public static function isMember() {
        if (!Yii::app()->user->isGuest) {
            $user_id = Yii::app()->user->id ? Yii::app()->user->id : null;
            if ($user_id == null) {
                return false;
            }
            $results = Subscriptions::model()->findAll(
                'user_id=:user_id AND status=:status',
                array(
                    ':user_id' => $user_id,
                    ':status' => 'active'
                )
            );
            $count = count ( $results );
            return $count != 0 ? true : false ;
        }
    }

    public static function forFullPaidMembersOnly($value) {
        if (Yii::app()->user->isGuest) {
            return '<a data-dismiss="modal" data-toggle="modal" href="/user/login" data-target="#modal_login" >Members Only</a>';
        } else {
            if ( self::isAdmin() || Yii::app()->controller->getExpireUser() > 0) {
                return $value;
            } else {
                $subscriptions_left = 31 - Subscriptions::model()->count('subscription_id = :subscription_id', array(':subscription_id'=>'1'));
                return '<a href="'.Yii::app()->params['linkToBuyingSubscr'].'" rel="popover-hover" data-placement="top" data-original-title="For only $99.00 a month, get FULL ACCESS MEMBERSHIP gives you the competitive advantage with our EXCLUSIVE search filters, library of analytics tools, time saving deal finding automation features, and full access to the complete live database of property listings. ACT NOW only '.$subscriptions_left.' memberships left." >Full Paid Members Only</a>';
            }
        }
    }

    public static function  buildRows($array_1, $array_2){
        foreach($array_2 as $key => $val){
            //Check do we have empty key in array_1 that has been created from PropertyInfoHistory instance. It could be empty if we call undefined method or property at PropertyInfoHistory.
            if (!array_key_exists($key, $array_1)) {
                $array_1[$key] = '';
            }
            if (!array_key_exists($key, $array_2)) {
                $array_1[$key] = '';
            }
            if(
                ($array_1[$key] == '' && $array_2[$key] == '')
                or ($array_1[$key] == 0 && $array_2[$key] == '')
                or ($array_1[$key] == '' && $array_2[$key] == 0)
                or ($array_1[$key] == 0 && $array_2[$key] == 0)
            ){}
            else{
                ?>
                <tr>
                    <td><?php echo $key?></td>
                    <td><?php echo $array_1[$key]?></td>
                    <td><?php echo $array_2[$key]?></td>
                </tr>
            <?php }
        }
    }

    /**
     * Build Payment Form for subscriptions.
     *
     * @param string $submitButton - html code for submit button
     * @param int $planId - id of SubscriptionPlans
     */
    public static function buildPaymentForm($submitButton = '<input type="submit">', $planId = 1){
        $subscr_form_data = PayPalIpn::getSubscriptionFormData($planId);
        ?>
        <form id="createSubscription" action="<?php echo $subscr_form_data['payNowButtonUrl']; ?>" method="post" target="_top">
            <input type="hidden" name="cmd" value="_xclick-subscriptions">
            <input type="hidden" name="business" value="<?php echo $subscr_form_data['receiverEmail']; ?>">
            <input type="hidden" name="lc" value="GB">

            <input type="hidden" name="item_name" value="<?php echo $subscr_form_data['serviceName']; ?>">
            <input type="hidden" name="no_note" value="1">
            <input type="hidden" name="no_shipping" value="1">

            <input type="hidden" name="return" value="<?php echo $subscr_form_data['returnUrl']; ?>">

            <input type="hidden" name="src" value="1">
            <input type="hidden" name="a3" value="<?php echo $subscr_form_data['amount']; ?>">

            <input type="hidden" name="p3" value="1">
            <input type="hidden" name="t3" value="M">

            <input id="customData" type="hidden" name="custom" value='<?php echo $subscr_form_data['jsonrow']; ?>'>
            <input type="hidden" name="currency_code" value="USD">

            <?php echo $submitButton?>

        </form>
        <?php
    }
    public static function buildPaymentStandardForm($formConf){
        $subscr_form_data = PayPalIpn::getSubscriptionFormData(1);

        $form = '<form id="'.$formConf['id'].'" action="' . $subscr_form_data['payNowButtonUrl'] . '" method="post" target="_top">';
        $form .= '<input type="hidden" name="cmd" value="_xclick-subscriptions">';
        $form .= '<input type="hidden" name="business" value="' . $subscr_form_data['receiverEmail'] . '">';
        $form .= '<input type="hidden" name="lc" value="GB">';
        $form .= '<input type="hidden" name="item_name" value="' . $subscr_form_data['serviceName'] . '">';
        $form .= '<input type="hidden" name="no_note" value="1">';
        $form .= '<input type="hidden" name="no_shipping" value="1">';
        $form .= '<input type="hidden" name="return" value="' . $subscr_form_data['returnUrl'] . '">';
        $form .= '<input type="hidden" name="src" value="1">';
        $form .= '<input type="hidden" name="a3" value="' . $subscr_form_data['amount'] . '">';
        $form .= '<input type="hidden" name="p3" value="1">';
        $form .= '<input type="hidden" name="t3" value="M">';
        $form .= '<input id="customData" type="hidden" name="custom" value=\'' . $subscr_form_data['jsonrow'] . '\'>';
        $form .= '<input type="hidden" name="currency_code" value="USD">';
        $form .= '</form>';

        return $form;
    }
    public static function buildPaymentFormFreeTrial($formConf){
        $subscr_form_data = PayPalIpn::getSubscriptionFormData(1);
        $user_id =  Yii::app()->user->id;
        $current_plan = 1;
        $custom_data = array('user_id' => $user_id, 'service_id' => $current_plan);
        $form = '
        <form id="'.$formConf['id'].'" action="'. $subscr_form_data['payNowButtonUrl'] .'" method="post" target="_top">
            <input type="hidden" name="cmd" value="_s-xclick">
            <input type="hidden" name="hosted_button_id" value="'.$formConf['paypalFormId'].'">
            <input type="hidden" name="return" value="http://irradii.com/membership/membership/index/status/success">
            <input id="customData" type="hidden" name="custom" value=\''.json_encode($custom_data).'\'>
        </form>';

        return $form;
    }

    public static function dateDiff($dformat, $endDate, $beginDate){
        $date_parts1=explode($dformat, $beginDate);
        $date_parts2=explode($dformat, $endDate);
        $start_date=gregoriantojd($date_parts1[1], $date_parts1[2], $date_parts1[0]);
        $end_date=gregoriantojd($date_parts2[1], $date_parts2[2], $date_parts2[0]);
        return $end_date - $start_date;
    }

    public static function getColorIfUnderValueOrEquityDeals($details)
    {
        $discont = $details->getDiscontValue();
        $condition = $discont >= Yii::app()->params['underValueDeals'];
        $colorScheme = $condition ? 'scheme-text-success' : '';

        return $colorScheme;
    }

    public static function defineColorScheme($details)
    {
        $discont = $details->getDiscontValue();
        $status = $details->propertyInfoAdditionalBrokerageDetails->status;
        $condition = $discont >= Yii::app()->params['underValueDeals'];
        $colorScheme = SiteHelper::getColorScheme($status,$condition);

        return $colorScheme;
    }
    public static function getColorSchemeOfUserPropertyStatus ($status = null){
        $backgroundColor='';
        switch ($status){
            default:
            case 'New':
            case 'Updated':
                $backgroundColor = 'scheme-bg-primary';
                break;
            case 'Viewed':
                $backgroundColor = 'scheme-bg-muted';
                break;
            case 'Saved':
                $backgroundColor = 'scheme-bg-success';
                break;
            case 'Dismissed':
                $backgroundColor = 'scheme-bg-darken';
                break;
            case 'Offered':
                $backgroundColor = 'scheme-bg-warning';
                break;
            case 'Purchased':
                $backgroundColor = 'scheme-bg-success';
                break;
            case 'Rejected':
                $backgroundColor = 'scheme-bg-danger';
                break;
        }
        return $backgroundColor;
    }

    public static function getColorScheme($status,$condition=null){
        if(isset($status)){
            $status = strtoupper($status);
            if(
                preg_match("/^HISTORY$/", $status) ||
                preg_match("/^TEMPORARILY OFF THE MARKET$/", $status) ||
                preg_match("/^WITHDRAWN$/", $status)
            ){
                $scheme = array(
                    'color_m'=>'background-color:#999999',
                    'status' => 'archive',
                    'color' => 'scheme-text-muted',
                    'label-color' => 'scheme-bg-muted',
                    'icon' => 'fa-archive',
                    'icon_map_lg' => 'fa-home',
                    'icon_map_sm' => 'fa-circle'
                );
                return $scheme;
            } elseif (
                preg_match("/^RESTNTLY SOLD$/", $status) ||
                preg_match("/^CLOSED$/", $status) ||
                preg_match("/^LEASED$/", $status) ||
                preg_match("/^SOLD$/", $status)
            ){
                $scheme = array(
                    'color_m'=>'background-color:#404040',
                    'status' => 'closed',
                    'color' => 'scheme-text-darken',
                    'label-color' => 'scheme-bg-darken',
                    'icon' => 'fa-lock',
                    'icon_map_lg' => 'fa-home',
                    'icon_map_sm' => 'fa-circle'
                );
                return $scheme;
            } elseif (
                preg_match("/^FORECLOSURE$/", $status) ||
                preg_match("/^SHORT SALE$/", $status) ||
                preg_match("/^AUCTION$/", $status)
            ){
                $scheme = array(
                    'color_m'=>'background-color:#A90329',
                    'status' => 'alert',
                    'color' => 'scheme-text-danger',
                    'label-color' => 'scheme-bg-danger',
                    'icon' => 'fa-gavel',
                    'icon_map_lg' => 'fa-home',
                    'icon_map_sm' => 'fa-circle'
                );
                return $scheme;
            } elseif (
                preg_match("/^CONTINGENT OFFER$/", $status) ||
                preg_match("/^PENDING OFFER$/", $status)
            ){
                $scheme = array(
                    'color_m'=>'background-color:#C79121',
                    'status' => 'warning',
                    'color' => 'scheme-text-warning',
                    'label-color' => 'scheme-bg-warning',
                    'icon' => 'fa-clock-o',
                    'icon_map_lg' => 'fa-home',
                    'icon_map_sm' => 'fa-circle'
                );
                return $scheme;
            } elseif ( (
                preg_match("/^FOR RENT$/", $status) ||
                preg_match("/^FOR SALE$/", $status) ||
                preg_match("/^ACTIVE$/", $status) ||
                preg_match("/^EXCLUSIVE AGENCY$/", $status) ||
                preg_match("/^ACTIVE-EXCLUSIVE RIGHT$/", $status) ) &&
                $condition
            ){
                $scheme = array(
                    'color_m'=>'background-color:#739E73',
                    'status' => 'action',
                    'color' => 'scheme-text-success',
                    'label-color' => 'scheme-bg-success',
                    'icon' => 'fa-search-plus',
                    'icon_map_lg' => 'fa-home',
                    'icon_map_sm' => 'fa-circle'
                );
                return $scheme;
            } elseif (
                preg_match("/^FOR RENT$/", $status) ||
                preg_match("/^FOR SALE$/", $status) ||
                preg_match("/^ACTIVE$/", $status) ||
                preg_match("/^EXCLUSIVE AGENCY$/", $status) ||
                preg_match("/^ACTIVE-EXCLUSIVE RIGHT$/", $status)
            ){
                $scheme = array(
                    'color_m'=>'background-color:#3276B1',
                    'status' => 'active',
                    'color' => 'scheme-text-primary',
                    'label-color' => 'scheme-bg-primary',
                    'icon' => 'fa-flag',
                    'icon_map_lg' => 'fa-home',
                    'icon_map_sm' => 'fa-circle'
                );
                return $scheme;
            } else {
                $scheme = array(
                    'color_m'=>'background-color:#000000',
                    'status' => 'default',
                    'color' => 'scheme-text-default',
                    'label-color' => 'scheme-bg-default',
                    'icon' => 'fa-archive',
                    'icon_map_lg' => 'fa-home',
                    'icon_map_sm' => 'fa-circle'
                );
                return $scheme;
            }
        }
    }
    
    public static function getUserPropertyStatus($user_id,$mls_sysid,$mls_name){
        $user_property_info = TblUserPropertyInfo::model()->findByAttributes(array(
            'user_id'=>$user_id,'mls_sysid'=>$mls_sysid, 'mls_name'=>$mls_name
        ));
        return $user_property_info;
    }

    public static function getUserProfile(){
        $profile = '';
        if(!Yii::app()->user->isGuest) {
            $userModel = User::model()->with('profile', 'profession')->findByPk(Yii::app()->user->id);
            $profile = $userModel->profile;
        }
        return $profile;
    }
} 