<?php

namespace app\controllers;

use Yii;
use yii\web\Controller as BaseController;
use app\models\TblUsersProfiles;

class Controller extends BaseController
{
    public $layout = 'column1';

    public $menu = [];
    public $breadcrumbs = [];
    public $signin;
    public $body_ID;
    public $body_onload;
    public $title;

    public function formatMoney($number, $fractional = false)
    {
        if ($fractional) {
            $number = sprintf('%.2f', $number);
        }

        while (true) {
            $replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number);

            if ($replaced != $number) {
                $number = $replaced;
            } else {
                break;
            }
        }

        return $number;
    }

    public function getDistance($latitude1, $longitude1, $latitude2, $longitude2)
    {
        $theta = $longitude1 - $longitude2;

        $miles = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) +
                 (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));

        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;

        return $miles;
    }

    public function showDump($arr)
    {
        echo '<pre>', print_r($arr, true), '</pre>';
    }

    public function getExpireUser()
    {
        $expire_user = 0;

        if (!Yii::$app->user->isGuest) {

            $model = TblUsersProfiles::find()
                ->where(['mid' => Yii::$app->user->id])
                ->cache(3)
                ->one();

            if ($model && $model->membership_expire_date) {

                $datetime_now = new \DateTime();
                $datetime_exp = new \DateTime($model->membership_expire_date);

                $interval = $datetime_now->diff($datetime_exp);

                if ($interval->days > 0 || $interval->h > 0 || $interval->i > 0 || $interval->s > 0) {
                    $expire_user = $model->payment_type;
                }
            }
        }

        return $expire_user;
    }
}