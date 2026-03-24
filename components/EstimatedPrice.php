<?php

namespace app\components;

use Yii;
use app\models\ExcludeProperty;
use app\models\CompareEstimatedPriceTable;
use app\models\TTable2Tail;
use app\models\MarketTrendTable;

class EstimatedPrice
{
    /**
     * Main entry point for getting comparison info (legacy wrapper)
     */
    public function getComparePropertyInfo(
        $del_id, $property_id, $property_type, $property_zipcode,
        $property_lat, $property_lon, $year_biult_id,
        $lot_sq_footage, $house_sq_footage, $bathrooms, $garages, $pool,
        $percentage_depreciation_value, $estimated_price, $bedrooms,
        $subdivision, $fundamentals_factor, $conditional_factor,
        $house_views, $sub_type
    ) {
        $result = [];
        $curStage = 1;
        $gettoday_date = date('Y-m-d');
        $comp_time = date('Y-m-d', time() - 200 * 24 * 60 * 60);
        $session_id = Yii::$app->session->id;

        if ($del_id) {
            $this->actionExcludeProperty($del_id, $session_id);
        }

        if (($property_type == '') || ($property_type == 0) || ($property_type == 4) || ($property_type == 5)) {
            return $result;
        }

        $house_views_list_full = MarketTrendTable::houseViewsList();
        $house_views_list = $this->getHouseViewsArr($house_views_list_full, $house_views);

        $this->calculateEstimatedPriceStage(
            $curStage,
            $result, $gettoday_date, $comp_time, $session_id,
            $property_id, $property_type, $property_zipcode,
            $property_lat, $property_lon, $year_biult_id,
            $lot_sq_footage, $house_sq_footage, $bathrooms, $garages, $pool,
            $percentage_depreciation_value, $estimated_price, $bedrooms,
            $subdivision, $fundamentals_factor, $conditional_factor,
            $house_views_list, $sub_type
        );

        $result['estimated_value_subject_property_stage'] = $result['estimated_value_subject_property'] ?? 0;

        if ((($result['estimated_value_subject_property'] ?? 0) < 500)
            || (($result['percentage_depreciation_value'] ?? 0) < -1000)
            || (($result['percentage_depreciation_value'] ?? 0) > 95)
        ) {
            $result['estimated_price'] = 0;
            $result['estimated_price_dollar'] = 0;
            $result['estimated_value_subject_property'] = 0;
            $result['percentage_depreciation_value'] = 0;
        }

        $result['comparable_price_sparkline'] = [];

        return $result;
    }

    /**
     * Recursive calculation through stages
     */
    public function calculateEstimatedPriceStage(
        $curStage,
        &$result, $gettoday_date, $comp_time, $session_id,
        $property_id, $property_type, $property_zipcode,
        $property_lat, $property_lon, $year_biult_id,
        $lot_sq_footage, $house_sq_footage, $bathrooms, $garages, $pool,
        $percentage_depreciation_value, $estimated_price, $bedrooms,
        $subdivision, $fundamentals_factor, $conditional_factor,
        $house_views_list, $sub_type
    ) {
        $compare_property_type = "AND property_info.property_type = {$property_type}";
        $select_estimated_price_result = $this->actionCompareEstimatedPriceTable($curStage, $property_type);

        if (empty($select_estimated_price_result)) {
            $result['estimated_price'] = $estimated_price;
            $result['estimated_price_dollar'] = 0;
            $result['estimated_value_subject_property'] = 0;
            $result['current_stage'] = $curStage;
            $result['comps'] = 0;
            $result['low_range'] = 0;
            $result['high_range'] = 0;
            $result['house_weighted'] = 0;
            $result['lot_weighted'] = 0;
            $result['bathrooms_weighted'] = 0;
            $result['bedrooms_weighted'] = 0;
            $result['garages_weighted'] = 0;
            $result['pool_weighted'] = 0;
            return;
        }

        $compare_property_zipcode = '';
        if ($property_zipcode != '') {
            $compare_property_zipcode = "AND property_info.property_zipcode = {$property_zipcode}";
        }

        $distance = (float)($select_estimated_price_result->distance ?? 0);

        $compare_property_lat = '';
        if (($property_lat != '0.000000') && ($property_lat != '') && $distance > 0) {
            $compare_property_lat = $this->actionComparePropertyLat($property_lat, $distance);
        }
        $compare_property_lon = '';
        if (($property_lon != '0.000000') && ($property_lon != '') && $distance > 0) {
            $compare_property_lon = $this->actionComparePropertyLon($property_lon, $distance);
        }
        $compare_property_year_build = '';
        $year_compare = $select_estimated_price_result->year_estimated ?? null;
        if ($year_biult_id != '' && $year_compare !== null && (float)$year_compare > 0) {
            $compare_property_year_build = $this->actionComparePropertyYearBuild($year_biult_id, $year_compare);
        }
        $compare_lot_sq_footage = '';
        $lot_compare = $select_estimated_price_result->lot_estimated ?? null;
        if ($lot_sq_footage != '' && $lot_compare !== null && (float)$lot_compare > 0) {
            $compare_lot_sq_footage = $this->actionCompareLotSqFootage($lot_sq_footage, $lot_compare);
        }
        $compare_house_sq_footage = '';
        $house_compare = $select_estimated_price_result->house_estimated ?? null;
        if ($house_sq_footage != '' && $house_compare !== null && (float)$house_compare > 0) {
            $compare_house_sq_footage = $this->actionCompareHouseSqFootage($house_sq_footage, $house_compare);
        }
        $compare_bathrooms = '';
        if (!empty($select_estimated_price_result->baths_estimated) && !empty($bathrooms)) {
            $percent_bathrooms = $bathrooms * ($select_estimated_price_result->baths_estimated / 100);
            $compare_bathrooms = "AND property_info.bathrooms BETWEEN " . ($bathrooms - $percent_bathrooms) . "  AND " . ($bathrooms + $percent_bathrooms);
        }
        $compare_bedrooms = '';
        if (!empty($select_estimated_price_result->beds_estimated) && !empty($bedrooms)) {
            $percent_bedrooms = $bedrooms * ($select_estimated_price_result->beds_estimated / 100);
            $compare_bedrooms = "AND property_info.bedrooms BETWEEN " . ($bedrooms - $percent_bedrooms) . "  AND " . ($bedrooms + $percent_bedrooms);
        }
        $compare_subdivision = '';
        if (!empty($select_estimated_price_result->subdivision_comp) && !empty($subdivision)) {
            $compare_subdivision = "AND property_info.subdivision = " . Yii::$app->db->quoteValue($subdivision);
        }
        $compare_house_views = '';
        if (!empty($select_estimated_price_result->house_views_comp) && !empty($house_views_list)) {
            $compare_house_views_part = '';
            foreach ($house_views_list as $value) {
                if (!empty($compare_house_views_part)) {
                    $compare_house_views_part .= " AND ";
                }
                $compare_house_views_part .= " property_info_details.house_views NOT LIKE '%" . addslashes($value) . "%' ";
            }
            if (!empty($compare_house_views_part)) {
                $compare_house_views = " AND ( ($compare_house_views_part) OR property_info_details.house_views IS NULL )";
            }
        }
        $compare_sub_type = '';
        if (!empty($select_estimated_price_result->sub_type_comp) && !empty($sub_type)) {
            $compare_sub_type = "AND property_info.sub_type = " . Yii::$app->db->quoteValue($sub_type);
        }

        $estimated_value_subject_property = 0;

        if (
            ($compare_lot_sq_footage != '') || ($compare_house_sq_footage != '') ||
            ($compare_property_year_build != '') || ($compare_property_zipcode != '') ||
            ($compare_property_type != '') || ($compare_bathrooms != '') ||
            ($compare_bedrooms != '') || ($compare_subdivision != '') ||
            ($compare_house_views != '') || ($compare_sub_type != '')
        ) {
            $low_range = 0;
            $high_range = 0;
            $percentage_depreciation_value = 0.0;

            $total_count = $this->countComparePropertyInfo(
                $session_id, $gettoday_date, $comp_time, $compare_property_type, $compare_property_zipcode, $compare_property_year_build, $compare_lot_sq_footage, $compare_house_sq_footage, $compare_property_lon, $compare_property_lat, $property_id,
                $compare_bedrooms, $compare_bathrooms, $compare_subdivision, $compare_house_views, $compare_sub_type
            );

            // Fallback retries: if the full constraint set yields zero comparable rows,
            // progressively relax the most specific filters so we can still render comparables.
            if ((int)$total_count === 0 && (int)$curStage === 1) {
                // Retry 1: remove subdivision/sub-type/house-views constraints.
                $compare_subdivision = '';
                $compare_house_views = '';
                $compare_sub_type = '';
                $total_count = $this->countComparePropertyInfo(
                    $session_id,
                    $gettoday_date,
                    $comp_time,
                    $compare_property_type,
                    $compare_property_zipcode,
                    $compare_property_year_build,
                    $compare_lot_sq_footage,
                    $compare_house_sq_footage,
                    $compare_property_lon,
                    $compare_property_lat,
                    $property_id,
                    $compare_bedrooms,
                    $compare_bathrooms,
                    $compare_subdivision,
                    $compare_house_views,
                    $compare_sub_type
                );

                // Retry 2: if still zero, remove lot acreage constraint.
                if ((int)$total_count === 0) {
                    $compare_lot_sq_footage = '';
                    $total_count = $this->countComparePropertyInfo(
                        $session_id,
                        $gettoday_date,
                        $comp_time,
                        $compare_property_type,
                        $compare_property_zipcode,
                        $compare_property_year_build,
                        $compare_lot_sq_footage,
                        $compare_house_sq_footage,
                        $compare_property_lon,
                        $compare_property_lat,
                        $property_id,
                        $compare_bedrooms,
                        $compare_bathrooms,
                        $compare_subdivision,
                        $compare_house_views,
                        $compare_sub_type
                    );
                }

                // Retry 3: if still zero, remove bed/bath constraints.
                if ((int)$total_count === 0) {
                    $compare_bedrooms = '';
                    $compare_bathrooms = '';
                    $total_count = $this->countComparePropertyInfo(
                        $session_id,
                        $gettoday_date,
                        $comp_time,
                        $compare_property_type,
                        $compare_property_zipcode,
                        $compare_property_year_build,
                        $compare_lot_sq_footage,
                        $compare_house_sq_footage,
                        $compare_property_lon,
                        $compare_property_lat,
                        $property_id,
                        $compare_bedrooms,
                        $compare_bathrooms,
                        $compare_subdivision,
                        $compare_house_views,
                        $compare_sub_type
                    );
                }

                // Retry 4: if still zero, remove lat/lon radius constraints.
                if ((int)$total_count === 0) {
                    $compare_property_lat = '';
                    $compare_property_lon = '';
                    $total_count = $this->countComparePropertyInfo(
                        $session_id,
                        $gettoday_date,
                        $comp_time,
                        $compare_property_type,
                        $compare_property_zipcode,
                        $compare_property_year_build,
                        $compare_lot_sq_footage,
                        $compare_house_sq_footage,
                        $compare_property_lon,
                        $compare_property_lat,
                        $property_id,
                        $compare_bedrooms,
                        $compare_bathrooms,
                        $compare_subdivision,
                        $compare_house_views,
                        $compare_sub_type
                    );
                }
            }

            $result['house_weighted'] = $select_estimated_price_result->house_weighted;
            $result['lot_weighted'] = $select_estimated_price_result->lot_weighted;
            $result['bathrooms_weighted'] = $select_estimated_price_result->bathrooms_weighted;
            $result['bedrooms_weighted'] = $select_estimated_price_result->bedrooms_weighted;
            $result['garages_weighted'] = $select_estimated_price_result->garages_weighted;
            $result['pool_weighted'] = $select_estimated_price_result->pool_weighted;

            // In legacy Yii1 the comparable table is populated as long as at least one matching row exists.
            // In Yii2 we were gating on `min_comp`, which can leave `result_queryAllRows` empty and the table blank.
            // So: only require `> 0` matches here.
            if ((int)$total_count > 0) {
                $result_queryAllRows = $this->actionComparePropertyInfoAllRows(
                    $session_id, $gettoday_date, $comp_time, $compare_property_type, $compare_property_zipcode, $compare_property_year_build, $compare_lot_sq_footage, $compare_house_sq_footage, $compare_property_lon, $compare_property_lat, $property_id,
                    $compare_bedrooms, $compare_bathrooms, $compare_subdivision, $compare_house_views, $compare_sub_type
                );

                $result_query = $this->actionComparePropertyInfo(
                    $session_id, $gettoday_date, $comp_time, $compare_property_type, $compare_property_zipcode, $compare_property_year_build, $compare_lot_sq_footage, $compare_house_sq_footage, $compare_property_lon, $compare_property_lat, $property_id,
                    $compare_bedrooms, $compare_bathrooms, $compare_subdivision, $compare_house_views, $compare_sub_type
                );

                $result_t_score = $this->actionTtable2Tail($total_count);
                $t_score = self::getTail($result_t_score);
                $low_sd = $t_score;
                $high_sd = $t_score;

                $result['low_sd'] = $low_sd;
                $result['high_sd'] = $high_sd;

                $result_query->house_square_footage_gravity = 1.0;
                $result_query->lot_footage_gravity = 1.0;

                $result['result_query'] = $result_query;
                $result['result_queryAllRows'] = $result_queryAllRows;

                $house_square_footage_gravity = ((float)$house_sq_footage != 0.0) ? sqrt($result_query->house_square_footage_average / $house_sq_footage) : 0.0;
                $lot_footage_gravity = ((float)$lot_sq_footage != 0.0) ? sqrt($result_query->house_lot_acerage_average / $lot_sq_footage) : 0.0;

                $result_query->house_square_footage_gravity = $house_square_footage_gravity;
                $result_query->lot_footage_gravity = $lot_footage_gravity;

                $result_query->house_footage_average = $result_query->comps_house_footage_average * $house_square_footage_gravity;
                $result_query->lot_footage_average = $result_query->comps_lot_footage_average * $lot_footage_gravity;

                $qualifying_value_square_footage = ((1 + $fundamentals_factor) * $result_query->house_footage_average) + ($conditional_factor * ($result_query->square_footage_stddev / (sqrt($total_count))));
                $qualifying_value_lot_footage = ((1 + $fundamentals_factor) * $result_query->lot_footage_average) + ($conditional_factor * ($result_query->lot_footage_stddev / (sqrt($total_count))));
                $qualifying_value_bathrooms_amenties = ((1 + $fundamentals_factor) * $result_query->bathrooms_amenity_average) + ($conditional_factor * ($result_query->bathrooms_amenity_stddev / (sqrt($total_count))));
                $qualifying_value_bedrooms_amenties = ((1 + $fundamentals_factor) * $result_query->bedrooms_amenity_average) + ($conditional_factor * ($result_query->bedrooms_amenity_stddev / (sqrt($total_count))));
                $qualifying_value_garages_amenties = ((1 + $fundamentals_factor) * $result_query->garages_amenity_average) + ($conditional_factor * ($result_query->garages_amenity_stddev / (sqrt($total_count))));
                $qualifying_value_pool_amenties = ((1 + $fundamentals_factor) * $result_query->pool_amenity_average) + ($conditional_factor * ($result_query->pool_amenity_stddev / (sqrt($total_count))));

                $result_query->qualifying_value_square_footage = $qualifying_value_square_footage;
                $result_query->qualifying_value_lot_footage = $qualifying_value_lot_footage;

                $weighted100 = self::getSumWeighted(
                    $result,
                    $qualifying_value_square_footage, $qualifying_value_lot_footage, $qualifying_value_bathrooms_amenties,
                    $qualifying_value_garages_amenties, $qualifying_value_pool_amenties, $qualifying_value_bedrooms_amenties
                );

                // Calculate the low range value
                $low_qualifying_value_square_footage = ((1 + $fundamentals_factor) * $result_query->house_footage_average) - ($low_sd * ($result_query->square_footage_stddev / (sqrt($total_count))));
                $low_qualifying_value_lot_footage = ((1 + $fundamentals_factor) * $result_query->lot_footage_average) - ($low_sd * ($result_query->lot_footage_stddev / (sqrt($total_count))));
                $low_qualifying_value_bathrooms_amenties = ((1 + $fundamentals_factor) * $result_query->bathrooms_amenity_average) - ($low_sd * ($result_query->bathrooms_amenity_stddev / (sqrt($total_count))));
                $low_qualifying_value_bedrooms_amenties = ((1 + $fundamentals_factor) * $result_query->bedrooms_amenity_average) - ($low_sd * ($result_query->bedrooms_amenity_stddev / (sqrt($total_count))));
                $low_qualifying_value_garages_amenties = ((1 + $fundamentals_factor) * $result_query->garages_amenity_average) - ($low_sd * ($result_query->garages_amenity_stddev / (sqrt($total_count))));
                $low_qualifying_value_pool_amenties = ((1 + $fundamentals_factor) * $result_query->pool_amenity_average) - ($low_sd * ($result_query->pool_amenity_stddev / (sqrt($total_count))));

                $low_estimated_value_square_footage = $low_qualifying_value_square_footage * $house_sq_footage;
                $low_estimated_value_lot_footage = $low_qualifying_value_lot_footage * $lot_sq_footage;
                $low_estimated_value_bathrooms_amenties = $low_qualifying_value_bathrooms_amenties * ($bathrooms);
                $low_estimated_value_bedrooms_amenties = $low_qualifying_value_bedrooms_amenties * ($bedrooms);
                $low_estimated_value_garages_amenties = $low_qualifying_value_garages_amenties * ($garages);
                $low_estimated_value_pool_amenties = $low_qualifying_value_pool_amenties * ($pool);

                $low_weighted_value_square_footage = $low_estimated_value_square_footage * ($select_estimated_price_result->house_weighted / $weighted100);
                $low_weighted_value_lot_footage = $low_estimated_value_lot_footage * ($select_estimated_price_result->lot_weighted / $weighted100);
                $low_weighted_value_bathrooms_amenties = $low_estimated_value_bathrooms_amenties * ($select_estimated_price_result->bathrooms_weighted / $weighted100);
                $low_weighted_value_bedrooms_amenties = $low_estimated_value_bedrooms_amenties * ($select_estimated_price_result->bedrooms_weighted / $weighted100);
                $low_weighted_value_garages_amenties = $low_estimated_value_garages_amenties * ($select_estimated_price_result->garages_weighted / $weighted100);
                $low_weighted_value_pool_amenties = $low_estimated_value_pool_amenties * ($select_estimated_price_result->pool_weighted / $weighted100);

                $low_range = $low_weighted_value_square_footage + $low_weighted_value_lot_footage + $low_weighted_value_bathrooms_amenties + $low_weighted_value_bedrooms_amenties + $low_weighted_value_garages_amenties + $low_weighted_value_pool_amenties;

                // Calculate the high range value
                $high_qualifying_value_square_footage = ((1 + $fundamentals_factor) * $result_query->house_footage_average) + ($high_sd * ($result_query->square_footage_stddev / (sqrt($total_count))));
                $high_qualifying_value_lot_footage = ((1 + $fundamentals_factor) * $result_query->lot_footage_average) + ($high_sd * ($result_query->lot_footage_stddev / (sqrt($total_count))));
                $high_qualifying_value_bathrooms_amenties = ((1 + $fundamentals_factor) * $result_query->bathrooms_amenity_average) + ($high_sd * ($result_query->bathrooms_amenity_stddev / (sqrt($total_count))));
                $high_qualifying_value_bedrooms_amenties = ((1 + $fundamentals_factor) * $result_query->bedrooms_amenity_average) + ($high_sd * ($result_query->bedrooms_amenity_stddev / (sqrt($total_count))));
                $high_qualifying_value_garages_amenties = ((1 + $fundamentals_factor) * $result_query->garages_amenity_average) + ($high_sd * ($result_query->garages_amenity_stddev / (sqrt($total_count))));
                $high_qualifying_value_pool_amenties = ((1 + $fundamentals_factor) * $result_query->pool_amenity_average) + ($high_sd * ($result_query->pool_amenity_stddev / (sqrt($total_count))));

                $high_estimated_value_square_footage = $high_qualifying_value_square_footage * $house_sq_footage;
                $high_estimated_value_lot_footage = $high_qualifying_value_lot_footage * $lot_sq_footage;
                $high_estimated_value_bathrooms_amenties = $high_qualifying_value_bathrooms_amenties * ($bathrooms);
                $high_estimated_value_bedrooms_amenties = $high_qualifying_value_bedrooms_amenties * ($bedrooms);
                $high_estimated_value_garages_amenties = $high_qualifying_value_garages_amenties * ($garages);
                $high_estimated_value_pool_amenties = $high_qualifying_value_pool_amenties * ($pool);

                $high_weighted_value_square_footage = $high_estimated_value_square_footage * ($select_estimated_price_result->house_weighted / $weighted100);
                $high_weighted_value_lot_footage = $high_estimated_value_lot_footage * ($select_estimated_price_result->lot_weighted / $weighted100);
                $high_weighted_value_bathrooms_amenties = $high_estimated_value_bathrooms_amenties * ($select_estimated_price_result->bathrooms_weighted / $weighted100);
                $high_weighted_value_bedrooms_amenties = $high_estimated_value_bedrooms_amenties * ($select_estimated_price_result->bedrooms_weighted / $weighted100);
                $high_weighted_value_garages_amenties = $high_estimated_value_garages_amenties * ($select_estimated_price_result->garages_weighted / $weighted100);
                $high_weighted_value_pool_amenties = $high_estimated_value_pool_amenties * ($select_estimated_price_result->pool_weighted / $weighted100);

                $high_range = $high_weighted_value_square_footage + $high_weighted_value_lot_footage + $high_weighted_value_bathrooms_amenties + $high_weighted_value_bedrooms_amenties + $high_weighted_value_garages_amenties + $high_weighted_value_pool_amenties;

                if (
                    $house_sq_footage > $result_query->house_footage_max
                    || $house_sq_footage < $result_query->house_footage_min
                    || $house_sq_footage > 0 && $qualifying_value_square_footage <= 0
                    || $lot_sq_footage > 0 && $qualifying_value_lot_footage <= 0
                    || $bathrooms > 0 && $qualifying_value_bathrooms_amenties <= 0
                    || $bedrooms > 0 && $qualifying_value_bedrooms_amenties <= 0
                    || $garages > 0 && $qualifying_value_garages_amenties <= 0
                    || $pool > 0 && $qualifying_value_pool_amenties <= 0
                ) {
                    // Keep the comparable rows even if TMV/confidence math fails for this stage.
                    // This mirrors the UI expectation: Comparable table should still show.
                    $result['house_weighted'] = $select_estimated_price_result->house_weighted;
                    $result['lot_weighted'] = $select_estimated_price_result->lot_weighted;
                    $result['bathrooms_weighted'] = $select_estimated_price_result->bathrooms_weighted;
                    $result['bedrooms_weighted'] = $select_estimated_price_result->bedrooms_weighted;
                    $result['garages_weighted'] = $select_estimated_price_result->garages_weighted;
                    $result['pool_weighted'] = $select_estimated_price_result->pool_weighted;
                    $result['low_sd'] = $low_sd;
                    $result['high_sd'] = $high_sd;

                    $result['result_query'] = $result_query;
                    $result['result_queryAllRows'] = $result_queryAllRows;
                    $result['estimated_price'] = $estimated_price;
                    $result['estimated_price_dollar'] = 0;
                    $result['estimated_value_subject_property'] = 0;
                    $result['low_range'] = $low_range;
                    $result['high_range'] = $high_range;
                    $result['percentage_depreciation_value'] = $percentage_depreciation_value;
                    $result['current_stage'] = $curStage;
                    $result['comps'] = $total_count;
                    return;
                } else {
                    $estimated_value_square_footage = $qualifying_value_square_footage * $house_sq_footage;
                    $estimated_value_lot_footage = $qualifying_value_lot_footage * $lot_sq_footage;
                    $estimated_value_bathrooms_amenties = $qualifying_value_bathrooms_amenties * ($bathrooms);
                    $estimated_value_bedrooms_amenties = $qualifying_value_bedrooms_amenties * ($bedrooms);
                    $estimated_value_garages_amenties = $qualifying_value_garages_amenties * ($garages);
                    $estimated_value_pool_amenties = $qualifying_value_pool_amenties * ($pool);

                    $weighted_value_square_footage = $estimated_value_square_footage * ($select_estimated_price_result->house_weighted / $weighted100);
                    $weighted_value_lot_footage = $estimated_value_lot_footage * ($select_estimated_price_result->lot_weighted / $weighted100);
                    $weighted_value_bathrooms_amenties = $estimated_value_bathrooms_amenties * ($select_estimated_price_result->bathrooms_weighted / $weighted100);
                    $weighted_value_bedrooms_amenties = $estimated_value_bedrooms_amenties * ($select_estimated_price_result->bedrooms_weighted / $weighted100);
                    $weighted_value_garages_amenties = $estimated_value_garages_amenties * ($select_estimated_price_result->garages_weighted / $weighted100);
                    $weighted_value_pool_amenties = $estimated_value_pool_amenties * ($select_estimated_price_result->pool_weighted / $weighted100);

                    $estimated_value_subject_property = $weighted_value_square_footage + $weighted_value_lot_footage + $weighted_value_bathrooms_amenties + $weighted_value_bedrooms_amenties + $weighted_value_garages_amenties + $weighted_value_pool_amenties;
                    $estimated_price_dollar = $estimated_value_subject_property;

                    $result['estimated_price'] = $estimated_price;
                    $result['estimated_price_dollar'] = $estimated_price_dollar;
                    $result['estimated_value_subject_property'] = $estimated_value_subject_property;
                    $result['percentage_depreciation_value'] = $percentage_depreciation_value;
                    $result['low_range'] = $low_range;
                    $result['high_range'] = $high_range;
                    $result['current_stage'] = $curStage;
                    $result['comps'] = $total_count;
                    return;
                }
            } else {
                $curStage++;
                if ($curStage <= (Yii::$app->params['maxCalcStages'] ?? 10)) {
                    unset($result_query);
                    unset($result_queryAllRows);
                    $this->calculateEstimatedPriceStage(
                        $curStage,
                        $result, $gettoday_date, $comp_time, $session_id,
                        $property_id, $property_type, $property_zipcode,
                        $property_lat, $property_lon, $year_biult_id,
                        $lot_sq_footage, $house_sq_footage, $bathrooms, $garages, $pool,
                        $percentage_depreciation_value, $estimated_price, $bedrooms,
                        $subdivision, $fundamentals_factor, $conditional_factor,
                        $house_views_list, $sub_type
                    );
                    return;
                } else {
                    $result['estimated_price'] = $estimated_price;
                    $result['estimated_price_dollar'] = 0;
                    $result['estimated_value_subject_property'] = 0;
                    $result['low_range'] = $low_range;
                    $result['high_range'] = $high_range;
                    $result['percentage_depreciation_value'] = $percentage_depreciation_value;
                    $result['current_stage'] = $curStage - 1;
                    $result['comps'] = $total_count;
                    return;
                }
            }
        }
    }

    private function actionExcludeProperty($del_id, $session_id)
    {
        if ($del_id) {
            $existing = ExcludeProperty::find()
                ->where(['session_id' => $session_id, 'product_id' => $del_id])
                ->one();

            if (!$existing) {
                $model = new ExcludeProperty();
                $model->mid = Yii::$app->user->isGuest ? 0 : Yii::$app->user->id;
                $model->session_id = $session_id;
                $model->product_id = $del_id;
                if ($model->validate()) {
                    $model->save();
                }
            }
        }
    }

    private function actionCompareEstimatedPriceTable($stage, $property_type)
    {
        return CompareEstimatedPriceTable::find()
            ->where(['property_type' => $property_type, 'stage' => $stage])
            ->one();
    }

    private function actionComparePropertyLat($property_lat, $distance)
    {
        $miles_distance = 0.014428 * $distance;
        $lat_from = $property_lat - $miles_distance;
        $lat_to = $property_lat + $miles_distance;
        return " AND `property_info`.`getlatitude` BETWEEN " . (float)$lat_from . " AND " . (float)$lat_to;
    }

    private function actionComparePropertyLon($property_lon, $distance)
    {
        $miles_distance = 0.014428 * $distance;
        $lon_from = $property_lon + $miles_distance;
        $lon_to = $property_lon - $miles_distance;
        return " AND `property_info`.`getlongitude` BETWEEN " . (float)$lon_to . " AND " . (float)$lon_from;
    }

    private function actionComparePropertyYearBuild($year_biult_id, $year_compare)
    {
        $year_build_from = $year_biult_id - $year_compare;
        $year_build_to = $year_biult_id + $year_compare;
        return " AND `property_info`.`year_biult_id` BETWEEN " . (int)$year_build_from . " AND " . (int)$year_build_to;
    }

    private function actionCompareLotSqFootage($lot_sq_footage, $lotacre_compare)
    {
        $percent_lot_footage = $lot_sq_footage * ($lotacre_compare / 100);
        $lot_sq_footage_from = $lot_sq_footage - $percent_lot_footage;
        $lot_sq_footage_to = $lot_sq_footage + $percent_lot_footage;
        return " AND `property_info`.`lot_acreage` BETWEEN " . (float)$lot_sq_footage_from . " AND " . (float)$lot_sq_footage_to;
    }

    private function actionCompareHouseSqFootage($house_sq_footage, $house_compare)
    {
        $percent_house_footage = $house_sq_footage * ($house_compare / 100);
        $house_sq_footage_from = $house_sq_footage - $percent_house_footage;
        $house_sq_footage_to = $house_sq_footage + $percent_house_footage;
        return " AND `property_info`.`house_square_footage` BETWEEN " . (float)$house_sq_footage_from . " AND " . (float)$house_sq_footage_to;
    }

    private function actionGetExcludeProperty($session_id)
    {
        return ExcludeProperty::find()
            ->select('product_id')
            ->where(['session_id' => $session_id])
            ->asArray()
            ->all();
    }

    private function actionComparePropertyInfo(
        $session_id, $gettoday_date, $comp_time, $compare_property_type, $compare_property_zipcode, $compare_property_year_build, $compare_lot_sq_footage, $compare_house_sq_footage, $compare_property_lon, $compare_property_lat, $property_id,
        $compare_bedrooms, $compare_bathrooms, $compare_subdivision, $compare_house_views, $compare_sub_type
    ) {
        $sql = "SELECT 
                    count(`property_info`.`property_id`) AS count_property,
                    AVG( IF ( `property_info`.`house_square_footage`, `property_info`.`property_price` / `property_info`.`house_square_footage`, 0 ) ) AS comps_house_footage_average,
                    MAX(`property_info`.`house_square_footage`) AS house_footage_max,
                    MIN(`property_info`.`house_square_footage`) AS house_footage_min,
                    AVG(`property_info`.`property_price`) AS average_price, 
                    AVG(`property_info`.`house_square_footage`) AS house_square_footage_average, 
                    AVG(`property_info`.`bedrooms`) AS bedrooms_average, 
                    AVG(`property_info`.`bathrooms`) AS bathrooms_average, 
                    AVG(`property_info`.`garages`) AS garages_average,
                    AVG(`property_info`.`lot_acreage`) AS house_lot_acerage_average, 
                    AVG(`property_info`.`year_biult_id`) AS year_built_average, 
                    AVG(`property_info`.`pool`) AS pool_average, 
                    AVG(`property_info_details`.`spa`) AS spa_average, 
                    AVG( IF (`property_info`.`lot_acreage`, `property_info`.`property_price` / `property_info`.`lot_acreage`, 0 ) ) AS comps_lot_footage_average, 
                    MIN(`property_info`.`property_price`) AS min_price, 
                    VARIANCE(`property_info`.`property_price`) AS var_price, 
                    MAX(`property_info`.`property_price`) AS max_price,
                    MIN(`property_info`.`property_uploaded_date`) AS min_uploaded_date,
                    MAX(`property_info`.`property_uploaded_date`) AS max_uploaded_date,
                    MIN(`property_info`.`property_updated_date`) AS min_date, 
                    MAX(`property_info`.`property_updated_date`) AS max_date,
                    MIN(`property_info`.`house_square_footage`) AS min_sqft, 
                    MAX(`property_info`.`house_square_footage`) AS max_sqft,
                    MIN(`property_info`.`percentage_depreciation_value`) AS min_percent, 
                    MAX(`property_info`.`percentage_depreciation_value`) AS max_percent,
                    MIN( IF (`property_info`.`house_square_footage`, `property_info`.`property_price` / `property_info`.`house_square_footage`, 0 ) ) AS min_ppsqft, 		
                    MAX( IF (`property_info`.`house_square_footage`, `property_info`.`property_price` / `property_info`.`house_square_footage`, 0 ) ) AS max_ppsqft,
                    MAX(`property_info`.`lot_acreage`) AS max_lot, 
                    MIN(`property_info`.`lot_acreage`) AS min_lot,	
                    STDDEV( IF ( `property_info`.`house_square_footage`, `property_info`.`property_price` / `property_info`.`house_square_footage`, 0 ) ) AS square_footage_stddev, 
                    STDDEV( IF ( `property_info`.`lot_acreage`, `property_info`.`property_price` / `property_info`.`lot_acreage`, 0 ) ) AS lot_footage_stddev,
                    AVG(IF (property_info.bathrooms, property_info.property_price/(property_info.bathrooms), 0 )) as bathrooms_amenity_average, 
                    STDDEV(IF (property_info.bathrooms, property_info.property_price/(property_info.bathrooms), 0 )) as bathrooms_amenity_stddev, 
                    AVG(IF (property_info.bedrooms, property_info.property_price/(property_info.bedrooms), 0 )) as bedrooms_amenity_average, 
                    STDDEV(IF (property_info.bedrooms, property_info.property_price/(property_info.bedrooms), 0 )) as bedrooms_amenity_stddev, 
                    AVG(IF (property_info.garages, property_info.property_price/(property_info.garages), NULL )) as garages_amenity_average,
                    STDDEV(IF (property_info.garages, property_info.property_price/(property_info.garages), NULL )) as garages_amenity_stddev,
                    AVG(IF (property_info.pool, property_info.property_price/(property_info.pool), NULL )) as pool_amenity_average,
                    STDDEV(IF (property_info.pool, property_info.property_price/(property_info.pool), NULL )) as pool_amenity_stddev
                    FROM `property_info` 
                    LEFT JOIN ( `property_info_details`, `property_info_additional_brokerage_details` )  
                    ON ( `property_info_details`.`property_id` = `property_info`.`property_id` AND `property_info_additional_brokerage_details`.`property_id`=`property_info`.`property_id` )
                    WHERE UPPER(`property_info`.`property_status`)='ACTIVE' 
                          AND UPPER(`property_info_additional_brokerage_details`.`status`) NOT IN (
                              'HISTORY',
                              'TEMPOFF',
                              'INCOMPLETE',
                              'NOT FOR SALE',
                              'TEMPORARILY OFF THE MARKET',
                              'EXPIRED',
                              'WITHDRAWN',
                              'WITHDRAWN UNCONDITIONAL',
                              'WITHDRAWN CONDITIONAL'
                          )
                          AND `property_info`.`property_price` > 0 
                          AND `property_info`.`property_expire_date` >=DATE('" . $gettoday_date . "') 
                          AND `property_info`.`property_updated_date` >=DATE('" . $comp_time . "') 
                          {$compare_property_type}
                          {$compare_property_zipcode}
                          {$compare_property_year_build} 
                          {$compare_lot_sq_footage} 
                          {$compare_house_sq_footage} 
                          {$compare_property_lon} 
                          {$compare_property_lat}
                          {$compare_bedrooms} {$compare_bathrooms}
                          {$compare_subdivision}
                          {$compare_house_views}
                          {$compare_sub_type}
                          AND `property_info`.`property_id` != '" . $property_id . "'";
        $result_query = Yii::$app->db->createCommand($sql)->queryOne();
        return (object)$result_query;
    }

    private function countComparePropertyInfo(
        $session_id, $gettoday_date, $comp_time, $compare_property_type, $compare_property_zipcode, $compare_property_year_build, $compare_lot_sq_footage, $compare_house_sq_footage, $compare_property_lon, $compare_property_lat, $property_id,
        $compare_bedrooms, $compare_bathrooms, $compare_subdivision, $compare_house_views, $compare_sub_type
    ) {
        $sql = "SELECT 
                    count(`property_info`.`property_id`) AS count_property
                    FROM `property_info` 
                    LEFT JOIN ( `property_info_details`, `property_info_additional_brokerage_details` )  
                    ON ( `property_info_details`.`property_id` = `property_info`.`property_id` AND `property_info_additional_brokerage_details`.`property_id`=`property_info`.`property_id` )
                    WHERE UPPER(`property_info`.`property_status`)='ACTIVE' 
                          AND UPPER(`property_info_additional_brokerage_details`.`status`) NOT IN (
                              'HISTORY',
                              'TEMPOFF',
                              'INCOMPLETE',
                              'NOT FOR SALE',
                              'TEMPORARILY OFF THE MARKET',
                              'EXPIRED',
                              'WITHDRAWN',
                              'WITHDRAWN UNCONDITIONAL',
                              'WITHDRAWN CONDITIONAL'
                          )
                          AND `property_info`.`property_price` > 0 
                          AND `property_info`.`property_expire_date` >=DATE('" . $gettoday_date . "') 
                          AND `property_info`.`property_updated_date` >=DATE('" . $comp_time . "') 
                          {$compare_property_type}
                          {$compare_property_zipcode}
                          {$compare_property_year_build} 
                          {$compare_lot_sq_footage} 
                          {$compare_house_sq_footage} 
                          {$compare_property_lon} 
                          {$compare_property_lat}
                          {$compare_bedrooms} {$compare_bathrooms}
                          {$compare_subdivision}
                          {$compare_house_views}
                          {$compare_sub_type}
                          AND `property_info`.`property_id` != '" . $property_id . "'";
        return Yii::$app->db->createCommand($sql)->queryScalar();
    }

    private function getHouseViewsArr($house_views_list, $house_views)
    {
        $house_views_ret = [];
        $house_views_lower = strtolower($house_views);
        foreach ($house_views_list as $value) {
            if (strpos($house_views_lower, $value) === false) {
                $house_views_ret[] = $value;
            }
        }
        return $house_views_ret;
    }

    private function actionComparePropertyInfoAllRows(
        $session_id, $gettoday_date, $comp_time, $compare_property_type, $compare_property_zipcode, $compare_property_year_build, $compare_lot_sq_footage, $compare_house_sq_footage, $compare_property_lon, $compare_property_lat, $property_id,
        $compare_bedrooms, $compare_bathrooms, $compare_subdivision, $compare_house_views, $compare_sub_type
    ) {
        $sql = "SELECT *
                    FROM `property_info` 
                    LEFT JOIN ( `property_info_details`, `property_info_additional_brokerage_details` )  
                    ON ( `property_info_details`.`property_id` = `property_info`.`property_id` AND `property_info_additional_brokerage_details`.`property_id`=`property_info`.`property_id` )
                    WHERE UPPER(`property_info`.`property_status`)='ACTIVE' 
                          AND UPPER(`property_info_additional_brokerage_details`.`status`) NOT IN (
                              'HISTORY',
                              'TEMPOFF',
                              'INCOMPLETE',
                              'NOT FOR SALE',
                              'TEMPORARILY OFF THE MARKET',
                              'EXPIRED',
                              'WITHDRAWN',
                              'WITHDRAWN UNCONDITIONAL',
                              'WITHDRAWN CONDITIONAL'
                          )
                          AND `property_info`.`property_price` > 0 
                          AND `property_info`.`property_expire_date` >='" . $gettoday_date . "' 
                          AND `property_info`.`property_updated_date` >='" . $comp_time . "' 
                          {$compare_property_type}
                          {$compare_property_zipcode}
                          {$compare_property_year_build} 
                          {$compare_lot_sq_footage} 
                          {$compare_house_sq_footage} 
                          {$compare_property_lon} 
                          {$compare_property_lat}
                          {$compare_bedrooms} {$compare_bathrooms}
                          {$compare_subdivision}
                          {$compare_house_views}
                          {$compare_sub_type}
                          AND `property_info`.`property_id` != '" . $property_id . "'";
        return Yii::$app->db->createCommand($sql)->queryAll();
    }

    private function actionTtable2Tail($count_property)
    {
        $df_count = $count_property - 1;
        return TTable2Tail::find()->where(['df' => $df_count])->one();
    }

    public static function getTail($result_t_score, $tail = '')
    {
        if (empty($tail)) {
            $res =  $result_t_score ? $result_t_score->tail_90 : 0;
        } else {
            $res =  $result_t_score ? $result_t_score->{$tail} : 0;
        }
        return $res;
    }

    private static function getSumWeighted($result, $qual_sq, $qual_lot, $qual_bath, $qual_garage, $qual_pool, $qual_bed)
    {
        $weighted100 = 0;
        if ((float)$qual_sq != 0) {
            $weighted100 += (float)($result['house_weighted'] ?? 0);
        }
        if ((float)$qual_lot != 0) {
            $weighted100 += (float)($result['lot_weighted'] ?? 0);
        }
        if ((float)$qual_bath != 0) {
            $weighted100 += (float)($result['bathrooms_weighted'] ?? 0);
        }
        if ((float)$qual_bed != 0) {
            $weighted100 += (float)($result['bedrooms_weighted'] ?? 0);
        }
        if ((float)$qual_garage != 0) {
            $weighted100 += (float)($result['garages_weighted'] ?? 0);
        }
        if ((float)$qual_pool != 0) {
            $weighted100 += (float)($result['pool_weighted'] ?? 0);
        }
        return $weighted100 > 0 ? $weighted100 : 1;
    }
}
