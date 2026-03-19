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
}
