<?php

namespace app\components;

use Yii;
use app\models\User;
use app\models\Subscriptions; // Assuming this model will exist or exists
use yii\helpers\Html;
use yii\helpers\Url;

class SiteHelper
{
    public static function isValueEmpty($value)
    {
        if (is_array($value)) {
            return empty($value);
        }
        $value = trim((string)$value);
        return $value === '';
    }

    public static function isAdmin()
    {
        // Simple check if user is guest
        if (Yii::$app->user->isGuest) {
            return false;
        }
        
        // In Yii2, check for 'admin' role if using Rbac, or check superuser field
        $user = Yii::$app->user->identity;
        if ($user && isset($user->superuser)) {
            return (int)$user->superuser === 1;
        }
        
        return false;
    }

    public static function forMembersOnly($value)
    {
        if (Yii::$app->user->isGuest) {
            return '<a data-bs-dismiss="modal" data-bs-toggle="modal" href="' . Url::to(['/user/login']) . '" data-bs-target="#modal_login">Members Only</a>';
        }
        return $value;
    }

    public static function isMember()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        
        $userId = Yii::$app->user->id;
        // This assumes a Subscriptions model exists. If not, this might need adjustment.
        // For now, let's keep it safe.
        if (class_exists('app\models\Subscriptions')) {
            return \app\models\Subscriptions::find()
                ->where(['user_id' => $userId, 'status' => 'active'])
                ->exists();
        }
        
        return false;
    }

    public static function getColorSchemeOfUserPropertyStatus($status = null)
    {
        $backgroundColor = '';
        switch ($status) {
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

    public static function getColorIfUnderValueOrEquityDeals($details)
    {
        $discont = is_object($details) && method_exists($details, 'getDiscontValue') ? $details->getDiscontValue() : 0;
        $underValueDeals = Yii::$app->params['underValueDeals'] ?? 10;
        return $discont >= $underValueDeals ? 'scheme-text-success' : '';
    }

    public static function defineColorScheme($details)
    {
        $discont = is_object($details) && method_exists($details, 'getDiscontValue') ? $details->getDiscontValue() : 0;
        $status = '';
        if (is_object($details)) {
            if (isset($details->propertyInfoAdditionalBrokerageDetails->status)) {
                $status = $details->propertyInfoAdditionalBrokerageDetails->status;
            } elseif (isset($details->status)) {
                $status = $details->status;
            }
        }
        $underValueDeals = Yii::$app->params['underValueDeals'] ?? 10;
        return self::getColorScheme($status, $discont >= $underValueDeals);
    }

    public static function getColorScheme($status, $condition = null)
    {
        if (!isset($status)) {
            return [
                'color_m'    => 'background-color:#000000',
                'status'     => 'default',
                'color'      => 'scheme-text-default',
                'label-color'=> 'scheme-bg-default',
                'icon'       => 'fa-archive',
                'icon_map_lg'=> 'fa-home',
                'icon_map_sm'=> 'fa-circle',
            ];
        }
        $status = strtoupper($status);

        if (preg_match('/^HISTORY$/', $status) ||
            preg_match('/^TEMPORARILY OFF THE MARKET$/', $status) ||
            preg_match('/^WITHDRAWN$/', $status)
        ) {
            return [
                'color_m'    => 'background-color:#999999',
                'status'     => 'archive',
                'color'      => 'scheme-text-muted',
                'label-color'=> 'scheme-bg-muted',
                'icon'       => 'fa-archive',
                'icon_map_lg'=> 'fa-home',
                'icon_map_sm'=> 'fa-circle',
            ];
        } elseif (preg_match('/^RECENTLY SOLD$/', $status) ||
                  preg_match('/^CLOSED$/', $status) ||
                  preg_match('/^LEASED$/', $status) ||
                  preg_match('/^SOLD$/', $status)
        ) {
            return [
                'color_m'    => 'background-color:#404040',
                'status'     => 'closed',
                'color'      => 'scheme-text-darken',
                'label-color'=> 'scheme-bg-darken',
                'icon'       => 'fa-lock',
                'icon_map_lg'=> 'fa-home',
                'icon_map_sm'=> 'fa-circle',
            ];
        } elseif (preg_match('/^FORECLOSURE$/', $status) ||
                  preg_match('/^SHORT SALE$/', $status) ||
                  preg_match('/^AUCTION$/', $status)
        ) {
            return [
                'color_m'    => 'background-color:#A90329',
                'status'     => 'alert',
                'color'      => 'scheme-text-danger',
                'label-color'=> 'scheme-bg-danger',
                'icon'       => 'fa-gavel',
                'icon_map_lg'=> 'fa-home',
                'icon_map_sm'=> 'fa-circle',
            ];
        } elseif (preg_match('/^CONTINGENT OFFER$/', $status) ||
                  preg_match('/^PENDING OFFER$/', $status)
        ) {
            return [
                'color_m'    => 'background-color:#C79121',
                'status'     => 'warning',
                'color'      => 'scheme-text-warning',
                'label-color'=> 'scheme-bg-warning',
                'icon'       => 'fa-clock-o',
                'icon_map_lg'=> 'fa-home',
                'icon_map_sm'=> 'fa-circle',
            ];
        } elseif ((preg_match('/^FOR RENT$/', $status) ||
                   preg_match('/^FOR SALE$/', $status) ||
                   preg_match('/^ACTIVE$/', $status) ||
                   preg_match('/^EXCLUSIVE AGENCY$/', $status) ||
                   preg_match('/^ACTIVE-EXCLUSIVE RIGHT$/', $status)) && $condition
        ) {
            return [
                'color_m'    => 'background-color:#739E73',
                'status'     => 'action',
                'color'      => 'scheme-text-success',
                'label-color'=> 'scheme-bg-success',
                'icon'       => 'fa-search-plus',
                'icon_map_lg'=> 'fa-home',
                'icon_map_sm'=> 'fa-circle',
            ];
        } elseif (preg_match('/^FOR RENT$/', $status) ||
                  preg_match('/^FOR SALE$/', $status) ||
                  preg_match('/^ACTIVE$/', $status) ||
                  preg_match('/^EXCLUSIVE AGENCY$/', $status) ||
                  preg_match('/^ACTIVE-EXCLUSIVE RIGHT$/', $status)
        ) {
            return [
                'color_m'    => 'background-color:#3276B1',
                'status'     => 'active',
                'color'      => 'scheme-text-primary',
                'label-color'=> 'scheme-bg-primary',
                'icon'       => 'fa-flag',
                'icon_map_lg'=> 'fa-home',
                'icon_map_sm'=> 'fa-circle',
            ];
        } else {
            return [
                'color_m'    => 'background-color:#000000',
                'status'     => 'default',
                'color'      => 'scheme-text-default',
                'label-color'=> 'scheme-bg-default',
                'icon'       => 'fa-archive',
                'icon_map_lg'=> 'fa-home',
                'icon_map_sm'=> 'fa-circle',
            ];
        }
    }

    public static function getUserPropertyStatus($user_id, $mls_sysid, $mls_name)
    {
        return \app\models\TblUserPropertyInfo::findOne([
            'user_id'   => $user_id,
            'mls_sysid' => $mls_sysid,
            'mls_name'  => $mls_name,
        ]);
    }

    public static function getUserProfile()
    {
        if (!Yii::$app->user->isGuest) {
            $userModel = User::find()->with(['profile'])->where(['id' => Yii::$app->user->id])->one();
            return $userModel ? $userModel->profile : null;
        }
        return null;
    }

    public static function toString($value)
    {
        return is_array($value) ? implode(', ', $value) : (string)$value;
    }

    public static function timeElapsed($secs)
    {
        $bit = [
            'y' => $secs / 31556926 % 12,
            'w' => $secs / 604800 % 52,
            'd' => $secs / 86400 % 7,
            'h' => $secs / 3600 % 24,
            'm' => $secs / 60 % 60,
            's' => $secs % 60
        ];
        $ret = [];
        foreach ($bit as $k => $v) {
            if ($v > 0) $ret[] = $v . $k;
        }
        return empty($ret) ? '0s' : join(' ', $ret);
    }

    public static function dateDiff($dformat, $endDate, $beginDate)
    {
        $date_parts1 = explode($dformat, $beginDate);
        $date_parts2 = explode($dformat, $endDate);
        if (count($date_parts1) < 3 || count($date_parts2) < 3) return 0;
        $start_date = gregoriantojd($date_parts1[1], $date_parts1[2], $date_parts1[0]);
        $end_date = gregoriantojd($date_parts2[1], $date_parts2[2], $date_parts2[0]);
        return $end_date - $start_date;
    }

    public static function forFullPaidMembersOnly($value)
    {
        if (Yii::$app->user->isGuest) {
            return '<a data-bs-dismiss="modal" data-bs-toggle="modal" href="' . Url::to(['/user/login']) . '" data-bs-target="#modal_login">Members Only</a>';
        }
        
        if (self::isAdmin()) {
            return $value;
        }
        
        // Check for session/controller method for expireUser if needed
        $expireUser = 0;
        if (method_exists(Yii::$app->controller, 'getExpireUser')) {
            $expireUser = Yii::$app->controller->getExpireUser();
        }

        if ($expireUser > 0) {
            return $value;
        }

        $subscriptionsLeft = 31; // Placeholder as in original snippet
        if (class_exists('app\models\Subscriptions')) {
             $subscriptionsLeft = 31 - \app\models\Subscriptions::find()->where(['subscription_id' => '1'])->count();
        }

        return '<a href="' . (Yii::$app->params['linkToBuyingSubscr'] ?? '#') . '" rel="popover-hover" data-bs-placement="top" data-bs-original-title="For only $99.00 a month, get FULL ACCESS MEMBERSHIP gives you the competitive advantage with our EXCLUSIVE search filters, library of analytics tools, time saving deal finding automation features, and full access to the complete live database of property listings. ACT NOW only ' . $subscriptionsLeft . ' memberships left." >Full Paid Members Only</a>';
    }

    public static function getLatLonResult($search_results)
    {
        $_lat = 0;
        $_lon = 0;
        foreach ($search_results as $search_result) {
            if ((($search_result->getlatitude != 0.000000) && ($search_result->getlatitude != '')) && (($search_result->getlongitude != 0.000000) && ($search_result->getlongitude != ''))) {
                $_lat = $search_result->getlatitude;
                $_lon = $search_result->getlongitude;
                return [$_lat, $_lon];
            }
        }
        return [$_lat, $_lon];
    }

    public static function getSearchMapResult($search_results, $list_weight = [])
    {
        $result = [];
        $underValueDeals = Yii::$app->params['underValueDeals'] ?? 10;
        foreach ($search_results as $search_result) {
            $discont = 0;
            if (($search_result->percentage_depreciation_value >= $underValueDeals)) {
                $discont = $search_result->percentage_depreciation_value;
            }
            if ($discont == 0) {
                if (($search_result->estimated_price > 0) &&
                    (100 - ($search_result->property_price * 100 / $search_result->estimated_price)) > 0) {
                    $discont = 100 - ($search_result->property_price * 100 / $search_result->estimated_price);
                }
            }

            $is_discont = $discont >= $underValueDeals;
            $colorScheme = ['label-color' => '', 'status' => ''];
            $status_label = '';
            $status = '';
            if (isset($search_result->propertyInfoAdditionalBrokerageDetails->status)) {
                $status = strtoupper($search_result->propertyInfoAdditionalBrokerageDetails->status);
                $colorScheme = self::getColorScheme($status, $is_discont);
                $status_label = '<span class="label ' . $colorScheme['label-color'] . '">' . $status . '</span>';
            }

            /*User property status*/
            $user_id = Yii::$app->user->id;
            $user_status_label = '';
            $user_status = 'New';
            if ($user_id != null) {
                $user_property_info = SiteHelper::getUserPropertyStatus($user_id, $search_result->mls_sysid, $search_result->mls_name);
                if ($user_property_info) {
                    $user_status = $user_property_info->user_property_status;
                    if (isset($search_result->property_uploaded_date) && isset($user_property_info->last_viewed_date) &&
                        strtotime($search_result->property_uploaded_date) > strtotime($user_property_info->last_viewed_date)) {
                        $user_status = 'Updated';
                    }
                }
                $label_of_user_status = SiteHelper::getColorSchemeOfUserPropertyStatus($user_status);
                $user_status_label = '<br><br><span class="label-user-property-status ' . $label_of_user_status . '">' . $user_status . '</span>';
            }

            $col0 = !empty($list_weight[$search_result->property_id]) 
                    ? $list_weight[$search_result->property_id] . '-' . $search_result->property_updated_date 
                    : $search_result->property_id;

            $dat_status = $colorScheme['status'] ?? '';
            $slug = isset($search_result->slug->slug) ? $search_result->slug->slug : '';
            $detailsUrl = Url::to(['property/details', 'slug' => $slug]);
            
            /* Column 1: Value (Photo) */
            $col_photo = '<div style="position: relative;">';
            $col_photo .= "<a class='property_info_row property_info_row_map exclude-reinclude' data-lat='"
                . $search_result->getlatitude . "' data-lon='"
                . $search_result->getlongitude . "' data-status='"
                . $dat_status . "' data-discont='"
                . $discont . "' data-address= '"
                . $search_result->property_street . "' data-property_id='"
                . $search_result->property_id . "' href=\"" . $detailsUrl . "\" > "
                . \app\components\CPathCDN::checkPhoto($search_result, "img-responsive") . "</a>";
            if ($is_discont) {
                $col_photo .= '<span class="label bg-color-greenDark" style="position:absolute; bottom:2px; left:2px; padding:2px 4px; font-size:9px; color:#fff;">' . round($discont) . '% Below TMV</span>';
            }
            $col_photo .= '</div>';

            /* Column 2: Address */
            $city_st_zip = ($search_result->city->city_name ?? '') . ', ' . ($search_result->state->state_code ?? '') . ' ' . ($search_result->zipcode->zip_code ?? '');
            $col_address = '<div><a href="' . $detailsUrl . '" style="font-weight:bold; color:#005580;">' . ($search_result->property_street ?? '') . '</a></div>';
            $col_address .= '<div style="font-size:11px; color:#666;">' . $city_st_zip . '</div>';
            if (!empty($search_result->subdivision)) {
                $col_address .= '<div style="font-size:10px; color:#999;">' . $search_result->subdivision . '</div>';
            }
            
            /* Column 3: Status */
            $col_status = $status_label;
            if (isset($search_result->property_uploaded_date) && (time() - strtotime($search_result->property_uploaded_date)) < 86400 * 7) {
                $col_status .= '<br><span class="label label-primary" style="font-size:9px; padding:2px 4px; display:inline-block; margin-top:2px;">New</span>';
            }
            if ($user_status_label) {
                $col_status .= $user_status_label;
            }
            
            /* Column 4: List Price */
            $col_price = '<div style="font-weight:bold; color:#333;">$' . number_format($search_result->property_price, 0, '.', ',') . '</div>';
            if ($search_result->estimated_price > 0) {
                $col_price .= '<div style="font-size:11px; color:#666;">TMV <span style="color:#000;">$' . number_format($search_result->estimated_price, 0, '.', ',') . '</span></div>';
                $equity = $search_result->getEstimatedEquity($search_result->estimated_price, $search_result->property_price);
                if ($equity > 0) {
                    $col_price .= '<div style="font-size:10px; color:#008000;">Estimated Equity $' . number_format($equity, 0, '.', ',') . '</div>';
                }
            }

            /* Column 5: Sq. Ft. */
            $col_sqft = '<div style="font-weight:bold;">' . number_format($search_result->house_square_footage, 0, '.', ',') . ' Sq Ft</div>';
            $col_sqft .= '<div style="font-size:11px; color:#666;">' . number_format($search_result->lot_acreage, 3, '.', ',') . ' Acre</div>';
            $col_sqft .= '<div style="font-size:11px; color:#666;">' . $search_result->getPropertyTypeStr() . '</div>';
            
            /* Column 6: Beds/Baths */
            $col_beds_baths = '<div style="font-weight:bold;">' . $search_result->bedrooms . ' Beds / ' . number_format($search_result->bathrooms, 2, '.', ',') . ' Baths</div>';
            if ($search_result->garages) {
                $col_beds_baths .= '<div style="font-size:11px; color:#666;">' . $search_result->garages . ' Car Gar</div>';
            }

            /* Column 7: Public Remarks */
            $col_remarks = !empty($search_result->public_remarks) ? '<div class="remarks-trunc" style="font-size:11px; color:#444;">' . mb_strimwidth($search_result->public_remarks, 0, 160, "...") . ' <a href="'.$detailsUrl.'" style="color:#005580;">(Read more)</a></div>' : '';

            /* Column 8: List Date + Action Buttons */
            $updatedDateRaw = method_exists($search_result, 'getUpdatedDateViaStatus') ? $search_result->getUpdatedDateViaStatus() : $search_result->property_updated_date;
            $updatedDate = date("Y/m/d", strtotime($updatedDateRaw));
            
            $dom = '';
            $list_date = $search_result->propertyInfoAdditionalBrokerageDetails->list_date ?? null;
            if ($list_date) {
                $days = floor((time() - strtotime($list_date)) / 86400);
                $dom = '<div style="font-size:11px; color:#666;">' . ($days > 0 ? $days : 0) . ' DOM</div>';
            }

            $buttons = '<div style="margin-top:5px;">';
            $buttons .= '<a href="'.$detailsUrl.'" class="btn btn-xs btn-primary" title="Details" style="margin-right:2px; padding:2px 5px;"><i class="fa fa-search"></i></a>';
            $buttons .= '<a href="javascript:void(0);" onclick="showinmap(this)" property_id="'.$search_result->property_id.'" class="btn btn-xs btn-success" title="Map" style="padding:2px 5px;"><i class="fa fa-map-marker"></i></a>';
            $buttons .= '</div>';

            $result[] = [
                $col0,             // 0: Weight
                $col_photo,        // 1: Value (Photo)
                $col_address,      // 2: Address
                $col_status,       // 3: Status
                $col_price,        // 4: List Price
                $col_sqft,         // 5: Sq. Ft.
                $col_beds_baths,   // 6: Beds/Baths
                $col_remarks,      // 7: Public Remarks
                '<div style="min-width:70px;">' . $updatedDate . $dom . $buttons . '</div>', // 8: List Date + Buttons
                $updatedDateRaw,   // 9: Raw List Date
                $search_result->property_price, // 10: Raw List Price
                $search_result->estimated_price, // 11: Raw TMV
                $discont,          // 12: Raw % Below Value
                $search_result->getEstimatedEquity($search_result->estimated_price, $search_result->property_price), // 13: Raw Estimated Equity
                $search_result->property_street, // 14: Raw Full Address
                $search_result->property_updated_date, // 15: Raw Last Updated Date
                $user_status       // 16: Raw Viewer Status
            ];
        }
        return $result;
    }

    public static function buildRows($array_1, $array_2)
    {
        foreach ($array_2 as $key => $val) {
            if (!array_key_exists($key, $array_1)) {
                $array_1[$key] = '';
            }
            if (
                ($array_1[$key] == '' && $array_2[$key] == '')
                || ($array_1[$key] == 0 && $array_2[$key] == '')
                || ($array_1[$key] == '' && $array_2[$key] == 0)
                || ($array_1[$key] == 0 && $array_2[$key] == 0)
            ) {
                continue;
            }
            echo "<tr><td>{$key}</td><td>{$array_1[$key]}</td><td>{$array_2[$key]}</td></tr>";
        }
    }

    public static function sendMail($email, $subject, $message)
    {
        try {
            return Yii::$app->mailer->compose()
                ->setFrom(['noreply@ippraisall.com' => 'ippraisall.com'])
                ->setTo($email)
                ->setSubject($subject)
                ->setHtmlBody($message)
                ->send();
        } catch (\Exception $e) {
            Yii::error('Email Alert: NOT Sent to ' . $email . '. Error: ' . $e->getMessage());
            return false;
        }
    }

    public static function buildPaymentForm($submitButton = '<input type="submit">', $planId = 1)
    {
        if (!class_exists('app\models\PayPalIpn')) return 'PayPalIpn not found';
        $subscr_form_data = \app\models\PayPalIpn::getSubscriptionFormData($planId);
        $html = '<form id="createSubscription" action="' . $subscr_form_data['payNowButtonUrl'] . '" method="post" target="_top">';
        $html .= '<input type="hidden" name="cmd" value="_xclick-subscriptions">';
        $html .= '<input type="hidden" name="business" value="' . $subscr_form_data['receiverEmail'] . '">';
        $html .= '<input type="hidden" name="lc" value="GB">';
        $html .= '<input type="hidden" name="item_name" value="' . $subscr_form_data['serviceName'] . '">';
        $html .= '<input type="hidden" name="no_note" value="1">';
        $html .= '<input type="hidden" name="no_shipping" value="1">';
        $html .= '<input type="hidden" name="return" value="' . $subscr_form_data['returnUrl'] . '">';
        $html .= '<input type="hidden" name="src" value="1">';
        $html .= '<input type="hidden" name="a3" value="' . $subscr_form_data['amount'] . '">';
        $html .= '<input type="hidden" name="p3" value="1">';
        $html .= '<input type="hidden" name="t3" value="M">';
        $html .= '<input id="customData" type="hidden" name="custom" value=\'' . $subscr_form_data['jsonrow'] . '\'>';
        $html .= '<input type="hidden" name="currency_code" value="USD">';
        $html .= $submitButton;
        $html .= '</form>';
        return $html;
    }
}
