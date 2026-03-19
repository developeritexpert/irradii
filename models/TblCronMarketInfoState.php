<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "cron_market_info_state".
 *
 * @property integer $id
 * @property integer $state_id
 * @property string $date
 * @property integer $total
 * @property integer $sale
 * @property integer $sold
 * @property integer $foreclosure
 * @property integer $short_sales
 * @property double $avg_price
 * @property double $high_ppsf
 * @property double $low_ppsf
 * @property double $avg_ppsf
 */
class TblCronMarketInfoState extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cron_market_info_state';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['state_id', 'date', 'total', 'sale', 'sold', 'foreclosure', 'short_sales', 'avg_price', 'high_ppsf', 'low_ppsf', 'avg_ppsf'], 'required'],
            [['state_id', 'total', 'sale', 'sold', 'foreclosure', 'short_sales'], 'integer'],
            [['avg_price', 'high_ppsf', 'low_ppsf', 'avg_ppsf'], 'number'],
            [['date'], 'safe'],
        ];
    }
}
