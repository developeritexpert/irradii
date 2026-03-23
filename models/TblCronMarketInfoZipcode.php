<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "cron_market_info_zipcode".
 *
 * @property integer $id
 * @property integer $zipcode_id
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
class TblCronMarketInfoZipcode extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_cron_market_info_zipcode';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['zipcode_id', 'date', 'total', 'sale', 'sold', 'foreclosure', 'short_sales', 'avg_price', 'high_ppsf', 'low_ppsf', 'avg_ppsf'], 'required'],
            [['zipcode_id', 'total', 'sale', 'sold', 'foreclosure', 'short_sales'], 'integer'],
            [['avg_price', 'high_ppsf', 'low_ppsf', 'avg_ppsf'], 'number'],
            [['date'], 'safe'],
        ];
    }
}
