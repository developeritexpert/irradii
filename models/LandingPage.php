<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "tbl_landing_page".
 *
 * @property int $id
 * @property string $title
 * @property int $status
 * @property int $search_id
 * @property int $post_top_id
 * @property int $post_bottom_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property SavedSearch $search
 * @property Post $postTop
 * @property Post $postBottom
 * @property MembershipOptions $membershipOptions
 */
class LandingPage extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tbl_landing_page';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'status'], 'required'],
            [['status', 'search_id', 'post_top_id', 'post_bottom_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'status' => 'Status',
            'search_id' => 'Search ID',
            'post_top_id' => 'Post Top ID',
            'post_bottom_id' => 'Post Bottom ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSearch()
    {
        return $this->hasOne(SavedSearch::class, ['id' => 'search_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPostTop()
    {
        return $this->hasOne(Post::class, ['id' => 'post_top_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPostBottom()
    {
        return $this->hasOne(Post::class, ['id' => 'post_bottom_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMembershipOptions()
    {
        return $this->hasOne(MembershipOptions::class, ['landing_id' => 'id']);
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return \yii\data\ActiveDataProvider
     */
    public function search($params = [])
    {
        $query = self::find()->with(['search', 'postTop', 'postBottom']);

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['id' => $this->id])
            ->andFilterWhere(['status' => $this->status])
            ->andFilterWhere(['search_id' => $this->search_id])
            ->andFilterWhere(['post_top_id' => $this->post_top_id])
            ->andFilterWhere(['post_bottom_id' => $this->post_bottom_id]);

        $query->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }

    /**
     * Helper to get relative URL as expected in old views
     * @return string
     */
    public function getUrl()
    {
        return \yii\helpers\Url::to(['/landing/landing', 'id' => $this->id]);
    }
}
