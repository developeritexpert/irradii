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

    public static function forFullPaidMembersOnly($value)
    {
        if (Yii::$app->user->isGuest) {
            return 0; // Returning 0 to indicate guest
        }
        
        if (self::isAdmin()) {
            return $value;
        }
        
        // expireUser logic from Yii1 snippet
        if (method_exists(Yii::$app->controller, 'getExpireUser') && Yii::$app->controller->getExpireUser() > 0) {
            return $value;
        }

        // Default or complex subscription logic
        return 0; 
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
        $discont   = method_exists($details, 'getDiscontValue') ? $details->getDiscontValue() : 0;
        $condition = $discont >= (Yii::$app->params['underValueDeals'] ?? 5);
        return $condition ? 'scheme-text-success' : '';
    }

    public static function defineColorScheme($details)
    {
        $discont   = method_exists($details, 'getDiscontValue') ? $details->getDiscontValue() : 0;
        $status    = isset($details->propertyInfoAdditionalBrokerageDetails->status)
            ? $details->propertyInfoAdditionalBrokerageDetails->status : '';
        $condition = $discont >= (Yii::$app->params['underValueDeals'] ?? 5);
        return self::getColorScheme($status, $condition);
    }

    public static function getColorScheme($status, $condition = null)
    {
        if (!isset($status)) {
            return [];
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
}
