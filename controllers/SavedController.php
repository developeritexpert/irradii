<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\db\Expression;
use app\models\User;
use app\components\SiteHelper;
use app\components\CPathCDN;
use yii\db\Query;
use yii\caching\DbDependency;

class SavedController extends Controller
{
    public $layout = 'irradii_main';

    public $status_types = [
        'Viewed',
        'Dismissed',
        'Saved',
        'Offered',
        'Purchased',
        'Rejected'
    ];

    public $default_excluded_status_types = ['Viewed'];

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['properties', 'get-saved-properties'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionProperties()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/user/login']);
        } else {
            $model = User::find()
                ->with(['profile'])
                ->where(['id' => Yii::$app->user->id])
                ->cache(1000)
                ->one();
            
            if (!$model) {
                return $this->redirect(['/user/login']);
            }
            
            $profile = $model->profile;
            $user_id = $model->id;
        }

        $breadcrumbs = [
            [
                'label' => 'Saved Properties',
                'url' => ['saved/properties'],
            ]
        ];

        return $this->render('saved', [
            'model' => $model,
            'profile' => $profile,
            'breadcrumbs' => $breadcrumbs,
            'user_id' => $user_id,
        ]);
    }

    public function actionGetSavedProperties()
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => 'error', 'message' => 'Unauthorized'];
        }

        $user_id = Yii::$app->user->id;
        $dependency = new DbDependency([
            'sql' => 'SELECT lastvisit_at FROM tbl_users WHERE id=' . $user_id
        ]);

        $model = User::find()
            ->with(['profile'])
            ->where(['id' => $user_id])
            ->cache(1000, $dependency)
            ->one();

        if (!$model) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['status' => 'error', 'message' => 'User not found'];
        }

        if (Yii::$app->request->isAjax) {
            $obj = Yii::$app->request->post();
            $statuses = $this->status_types;
            if (isset($obj['excluded_statuses'])) {
                $statuses = array_diff($this->status_types, $obj['excluded_statuses']);
            }

            $properties = (new Query())
                ->select('*')
                ->from('property_info p')
                ->join('INNER JOIN', 'tbl_user_property_info u', 'p.mls_sysid=u.mls_sysid')
                ->join('INNER JOIN', 'property_info_details pd', 'pd.property_id=p.property_id')
                ->join('INNER JOIN', 'property_info_additional_brokerage_details ab', 'ab.property_id=p.property_id')
                ->join('INNER JOIN', 'tbl_property_info_slug s', 's.property_id=p.property_id')
                ->join('INNER JOIN', 'zipcode z', 'z.zip_id=p.property_zipcode')
                ->join('INNER JOIN', 'city ci', 'ci.cityid=p.property_city_id')
                ->join('INNER JOIN', 'county co', 'co.county_id=p.property_county_id')
                ->join('INNER JOIN', 'state st', 'st.stid=p.property_state_id')
                ->where(['u.user_id' => $user_id])
                ->andWhere(['in', 'u.user_property_status', $statuses])
                ->all();

            $result = [];
            // In the original JS, ths.forEach fills newArr, which is then sent as 'fields'.
            // If fields is present, it calls makeDataTable.
            if (isset($obj['fields'])) {
                foreach ($properties as $property) {
                    $result[] = $this->makeDataTable((object)$property);
                }
            }

            Yii::$app->response->format = Response::FORMAT_JSON;
            return $result;
        }
    }

    private function makeDataTable($property)
    {
        $widget__property_status = '';
        $discont = 0;
        
        $underValueDeals = Yii::$app->params['underValueDeals'] ?? 10;

        if ($property->percentage_depreciation_value >= $underValueDeals) {
            $discont = $property->percentage_depreciation_value;
        }
        if ($discont == 0) {
            if ($property->estimated_price > 0 && (100 - ($property->property_price * 100 / $property->estimated_price)) > 0) {
                $discont = 100 - ($property->property_price * 100 / $property->estimated_price);
            }
        }

        if (isset($property->status)) {
            $comp_stat = strtoupper($property->status);
            $conditon = $discont >= $underValueDeals;
            $colorScheme = SiteHelper::getColorScheme($comp_stat, $conditon);
            $widget__property_status = '<span class="label ' . ($colorScheme['label-color'] ?? '') . ' ">';
        }

        $status_p = !empty($colorScheme['status']) ? $colorScheme['status'] : '';

        // Col 0: Image
        $col0 = '<a class="property_info_row"
                  data-lat="' . $property->getlatitude . '"
                  data-lon="' . $property->getlongitude . '"
                  data-status="' . $status_p . '"
                  data-address= "' . $property->property_street . '"
                  data-property_id= "' . $property->property_id . '"
                  data-property="' . Yii::$app->urlManager->createUrl(['property/details', 'slug' => $property->slug]) . '"    
                  href="' . Yii::$app->urlManager->createUrl(['property/details', 'slug' => $property->slug]) . '">';
        if ($property->photo1) {
            $col0 .= CPathCDN::checkPhoto($property, "thumb-img-140", 0);
        }
        $col0 .= '</a>';

        // Col 1: Address
        $col1 = $property->property_street . '<br>';
        $col1 .= $property->city_name ? $property->city_name . ', ' : '';
        $col1 .= $property->state_code ? $property->state_code . ' ' : '';
        $col1 .= $property->zip_code ? $property->zip_code : '';
        $col1 .= '<br>';
        $community = $property->community_name ?: ($property->subdivision ?: ($property->area ?: ''));
        $col1 .= $community ? ucwords(strtolower($community)) . '<br>' : '';
        $col1 = "<a href=" . Yii::$app->urlManager->createUrl(['property/details', 'slug' => $property->slug]) . " >" . $col1 . "</a>";

        // Col 2: Status
        $col2 = $widget__property_status;
        if (isset($property->status)) {
            $col2 .= $property->status . '</span>';
        }
        if ($property->user_id != null) {
            $user_status = $property->user_property_status;
            if (strtotime($property->property_uploaded_date) > strtotime($property->last_viewed_date)) {
                $user_status = 'Updated';
            }
            $label_of_user_status = SiteHelper::getColorSchemeOfUserPropertyStatus($user_status);
            $col2 .= '<br><br><span class="label-user-property-status ' . $label_of_user_status . '">' . $user_status . '</span>';
        }

        // Col 3: List Price
        $col3 = '';
        $historyStatuses = ['HISTORY', 'RECENTLY SOLD', 'CLOSED', 'SOLD', 'TEMPOFF', 'NOT FOR SALE', 'TEMPORARILY OFF THE MARKET'];
        if (empty($property->selfProp) && isset($property->status) && in_array(strtoupper($property->status), $historyStatuses)) {
            if (in_array(strtoupper($property->status), ['HISTORY', 'SOLD', 'CLOSED'])) {
                $col3 = '$' . number_format($property->list_price);
            } else {
                $col3 = '-';
            }
        } else {
            $col3 = '$' . number_format($property->property_price);
        }

        // Col 4: Sale Price
        $col4 = (isset($property->status) && in_array(strtoupper($property->status), ['HISTORY', 'SOLD', 'LEASED', 'CLOSED']))
            ? '$' . number_format($property->property_price) : '-';

        // Col 5: TMV (Estimated Price)
        $col5 = ($property->estimated_price > 0) ? '$' . number_format($property->estimated_price) : '-';

        // Col 6: Date
        if (isset($property->status) && strtoupper($property->status) == 'HISTORY') {
            $col6 = (new \DateTime($property->property_updated_date))->modify('-1 year')->format('Y-m-d');
        } else {
            $col6 = $property->property_updated_date;
        }

        // Col 7: $/SqFt
        $col7 = '$' . (($property->house_square_footage != 0) ? number_format(($property->property_price / $property->house_square_footage), 2) : '0');

        // Col 8: Sq Ft
        $col8 = round($property->house_square_footage);

        // Col 9: Bed
        $col9 = $property->bedrooms;

        // Col 10: Bath
        $col10 = $property->bathrooms;

        // Col 11: Garage (Original logic col23)
        $col23 = $property->garages;

        // Col 12: Lot (Original logic col11)
        $col11 = sprintf("%01.2f", round($property->lot_acreage, 2));

        // Col 13: Yr Blt (Original logic col12)
        $col12 = $property->year_biult_id;

        // Col 14: Stories (Original logic col15)
        $col15 = $property->stories ?: '';

        // Col 15: Pool (Original logic col24)
        $col24 = $property->pool ?: '';

        // Col 16: Spa
        $col16 = $property->spa ?: '';

        // Col 17: Condition
        $col17 = $property->over_all_property ?: '';

        // Col 18: House Faces (Original logic col25)
        $col25 = $property->house_faces ?: '';

        // Col 19: House Views (Original logic col26)
        $col26 = $property->house_views ?: '';

        // Col 20: Flooring (Original logic col27)
        $col27 = $property->flooring_description ?: '';

        // Col 21: Furnishings (Original logic col28)
        $col28 = $property->furnishings_description ?: '';

        // Col 22: Financing (Original logic col29)
        $col29 = $property->financing_considered ?: '';

        // Col 23: Foreclosure (Original logic col18)
        $col18 = $property->foreclosure ?: '';

        // Col 24: Short Sale (Original logic col19)
        $col19 = $property->short_sale ?: '';

        // Col 25: Bank Owned (Original logic col20)
        $col20 = $property->reporeo ?: '';

        // Col 26: Original Price
        $col21 = $property->original_list_price ? '$' . number_format($property->original_list_price) : '';

        // Col 27: Days on Market
        $propertyDate = $property->entry_date ?: $property->property_uploaded_date;
        if ($propertyDate) {
            $now = new \DateTime();
            $exp = new \DateTime($propertyDate);
            $col22 = $now->diff($exp)->days;
        } else {
            $col22 = '';
        }

        return [
            $col0, $col1, $col2, $col3, $col4, $col5, $col6, $col7, $col8, $col9, $col10, $col23, $col11, $col12,
            $col15, $col24, $col16, $col17, $col25, $col26, $col27, $col28, $col29, $col18, $col19, $col20, $col21, $col22
        ];
    }

    public function getFullAddress($property = false)
    {
        if (!$property) return '';
        $property = (object)$property;
        $address = $property->property_street;
        $city = $property->city_name ?: ($property->city->city_name ?? '');
        $address .= ($address && $city ? ' ' : '') . $city;
        $address = ucwords(strtolower($address));
        $state = $property->state_code ?: ($property->state->state_code ?? '');
        $address .= ($address && $state ? ', ' : '') . strtoupper($state);
        $zip = $property->zip_code ?: ($property->zipcode->zip_code ?? '');
        $address .= ($address && $zip ? ' ' : '') . strtoupper($zip);
        return $address;
    }
}