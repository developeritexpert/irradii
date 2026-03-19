<?php
namespace app\models;
use yii\db\ActiveRecord;
class TblUserPropertyInfo extends ActiveRecord {
    public static function tableName() { return 'tbl_user_property_info'; }
    public static function model($className = __CLASS__) { return new static(); }
    public static function findByPk($pk) { return static::findOne($pk); }
    public static function findByAttributes($attributes) { return static::findOne($attributes); }
}
