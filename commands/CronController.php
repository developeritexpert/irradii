<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\models\PropertyInfo;
use app\components\EstimatedPrice;
use app\models\TblCronMarketInfoSubdivision;
use app\models\TblCronMarketInfoArea;
use app\models\TblCronMarketInfoZipcode;
use app\models\TblCronMarketInfoCity;
use app\models\TblCronMarketInfoCounty;
use app\models\TblCronMarketInfoState;

/**
 * CronController handles various cron tasks migrated from Yii 1.
 */
class CronController extends Controller
{
    /**
     * Updates market info aggregation tables.
     * Migrated from CronMarketInfoCommand.php
     */
    public function actionMarketInfo()
    {
        $this->stdout("Starting Market Info Calculation at " . date('Y-m-d H:i:s') . "\n");

        $tables = [
            'subdivision' => [
                'table' => 'tbl_cron_market_info_subdivision',
                'group_by' => 'subdivision',
                'select_field' => 'subdivision',
            ],
            'area' => [
                'table' => 'tbl_cron_market_info_area',
                'group_by' => 'area',
                'select_field' => 'area',
            ],
            'zipcode' => [
                'table' => 'tbl_cron_market_info_zipcode',
                'group_by' => 'property_zipcode',
                'select_field' => 'property_zipcode',
                'id_field' => 'zipcode_id',
            ],
            'city' => [
                'table' => 'tbl_cron_market_info_city',
                'group_by' => 'property_city_id',
                'select_field' => 'property_city_id',
                'id_field' => 'city_id',
            ],
            'county' => [
                'table' => 'tbl_cron_market_info_county',
                'group_by' => 'property_county_id',
                'select_field' => 'property_county_id',
                'id_field' => 'county_id',
            ],
            'state' => [
                'table' => 'tbl_cron_market_info_state',
                'group_by' => 'property_state_id',
                'select_field' => 'property_state_id',
                'id_field' => 'state_id',
            ],
        ];

        foreach ($tables as $key => $config) {
            $this->stdout("Processing table: {$config['table']}... ");
            $start = time();
            $table = $config['table'];
            $groupBy = $config['group_by'];
            $idField = $config['id_field'] ?? $key;
            $isNumeric = isset($config['id_field']);
            if ($isNumeric) {
                $where = "`tbl_p`.`{$groupBy}` IS NOT NULL AND `tbl_p`.`{$groupBy}` != 0";
            } else {
                $where = "`tbl_p`.`{$groupBy}` IS NOT NULL AND `tbl_p`.`{$groupBy}` != ''";
            }

            $this->stdout("Processing table: {$table}...\n");

            try {
                // Fetch distinct IDs first to batch the heavy aggregation
                $distinctIds = (new \yii\db\Query())
                    ->select([$groupBy])
                    ->from('property_info tbl_p')
                    ->where($where)
                    ->groupBy([$groupBy])
                    ->column();

                $totalIds = count($distinctIds);
                $this->stdout("Found {$totalIds} unique groups to process.\n");

                $batchSize = 50; 
                for ($i = 0; $i < $totalIds; $i += $batchSize) {
                    $batchIds = array_slice($distinctIds, $i, $batchSize);
                    $idList = "'" . implode("','", array_map(function($id) { return addslashes($id); }, $batchIds)) . "'";
                    
                    $batchWhere = "{$where} AND `tbl_p`.`{$groupBy}` IN ({$idList})";

                    $sqlRaw = "SELECT 
                        `tbl_p`.`{$groupBy}`, 
                        NOW(),
                        COUNT(*),
                        COUNT(IF(UPPER(`tbS`.`status`) = 'FOR SALE', 1, NULL)),
                        COUNT(IF(UPPER(`tbS`.`status`) = 'SOLD', 1, NULL)),
                        COUNT(IF(UPPER(`tbS`.`status`) = 'FORECLOSURE', 1, NULL)),
                        COUNT(IF(UPPER(`tbS`.`status`) = 'SHORT_SALES', 1, NULL)),
                        AVG(`tbl_p`.`property_price`),
                        MAX(IF(`tbl_p`.`house_square_footage` > 0, `tbl_p`.`property_price` / `tbl_p`.`house_square_footage`, 0)),
                        MIN(IF(`tbl_p`.`house_square_footage` > 0, `tbl_p`.`property_price` / `tbl_p`.`house_square_footage`, 0)),
                        AVG(IF(`tbl_p`.`house_square_footage` > 0, `tbl_p`.`property_price` / `tbl_p`.`house_square_footage`, 0))
                    FROM `property_info` tbl_p 
                    LEFT JOIN `property_info_additional_brokerage_details` tbS ON `tbS`.`property_id` = `tbl_p`.`property_id`
                    WHERE {$batchWhere}
                    GROUP BY `tbl_p`.`{$groupBy}`";

                    $results = Yii::$app->db->createCommand($sqlRaw)->queryAll(\PDO::FETCH_NUM);
                    
                    if (!empty($results)) {
                        // Add NULL for the auto-increment ID column at the beginning of each result row
                        foreach ($results as &$row) {
                            array_unshift($row, null);
                        }
                        
                        Yii::$app->db->createCommand()->batchInsert($table, [
                            'id', $idField, 'date', 'total', 'sale', 'sold', 'foreclosure', 'short_sales', 'avg_price', 'high_ppsf', 'low_ppsf', 'avg_ppsf'
                        ], $results)->execute();
                    }

                    $processedCount = min($i + $batchSize, $totalIds);
                    $this->stdout("Processed {$processedCount} of {$totalIds} groups for {$table}...\n");
                }

                $timeTaken = time() - $start;
                $this->stdout("Finished table {$table} in {$timeTaken} seconds.\n\n");
            } catch (\Exception $e) {
                $this->stdout("Error updating table {$table}: " . $e->getMessage() . "\n");
                if (isset($sql)) $this->stdout("The SQL being executed was: {$sql}\n");
            }
        }

        $this->stdout("Finished Market Info Calculation at " . date('Y-m-d H:i:s') . "\n");
        return ExitCode::OK;
    }

    /**
     * Batch updates estimated prices for all properties.
     * Migrated from CronEstimatedPriceCommand.php
     */
    public function actionSetEstimatedPrice()
    {
        $this->stdout("Starting Estimated Price update at " . date('Y-m-d H:i:s') . "\n");

        $query = PropertyInfo::find();
        $count = $query->count();
        $i = 0;

        $estimator = new EstimatedPrice();

        foreach ($query->batch(100) as $properties) {
            foreach ($properties as $property) {
                $i++;
                
                // Get additional details for estimation
                $details = $property->propertyInfoDetails;
                
                $result = $estimator->getComparePropertyInfo(
                    null, // del_id
                    $property->property_id,
                    $property->property_type,
                    $property->property_zipcode,
                    $property->getlatitude ?? '0.000000', 
                    $property->getlongitude ?? '0.000000',
                    $property->year_biult_id,
                    $property->lot_acreage,
                    $property->house_square_footage,
                    $property->bathrooms,
                    $property->garages,
                    $property->pool,
                    $property->percentage_depreciation_value,
                    $property->estimated_price,
                    $property->bedrooms,
                    $property->subdivision,
                    0, // fundamentals_factor (default)
                    0, // conditional_factor (default)
                    $details->house_views ?? '',
                    $property->sub_type
                );

                if (isset($result['estimated_value_subject_property'])) {
                    $property->estimated_price = (int)$result['estimated_value_subject_property'];
                    $property->save(false);
                }

                if ($i % 100 === 0) {
                    $this->stdout("Processed {$i} of {$count} properties.\n");
                }
            }
        }

        $this->stdout("Finished Estimated Price update at " . date('Y-m-d H:i:s') . "\n");
        return ExitCode::OK;
    }

    /**
     * Loads property photos from RETS and uploads to S3.
     * Migrated from CronLoadPhotosCommand.php
     */
    public function actionLoadPhotos($limit = 100)
    {
        $this->stdout("Starting Load Photos at " . date('Y-m-d H:i:s') . "\n");

        // We need a table to track photo loading queue, similar to legacy tbl_property_info_cron_load_photo
        // If it doesn't exist, we might need to create it or use a different tracking mechanism.
        // For now, let's assume the table exists or we'll process properties without photos.
        
        // Use intval to ensure limit is numeric and avoid quoting issues in SQL
        $limit = (int)$limit;
        $sql = "SELECT * FROM `tbl_property_info_cron_load_photo` 
                WHERE `process` is NULL OR (`process` is NOT NULL AND 60 <= TIMESTAMPDIFF(MINUTE, process_at, NOW()))
                LIMIT {$limit}";
        
        $items = Yii::$app->db->createCommand($sql)->queryAll();
        
        if (empty($items)) {
            $this->stdout("No photos in queue to load.\n");
            return ExitCode::OK;
        }

        $rets_login_url = Yii::$app->params['rets_login_url'];
        $rets_username = Yii::$app->params['rets_username'];
        $rets_password = Yii::$app->params['rets_password'];

        $rets = new \app\components\phRETS();
        if (!$rets->Connect($rets_login_url, $rets_username, $rets_password)) {
            $this->stderr("Failed to connect to RETS server.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $awsConfig = Yii::$app->params['awsKeys'];
        $s3 = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region'  => $awsConfig['region'],
            'credentials' => [
                'key'    => $awsConfig['key'],
                'secret' => $awsConfig['secret'],
            ],
        ]);

        foreach ($items as $item) {
            $id = $item['mls_sysid'];
            $this->stdout("Processing MLS ID: {$id}\n");

            // Mark as processing
            Yii::$app->db->createCommand()->update('tbl_property_info_cron_load_photo', [
                'process' => 1,
                'process_at' => new \yii\db\Expression('NOW()'),
            ], ['id' => $item['id']])->execute();

            $photos = $rets->GetObject("Property", "LargePhoto", $id, '*');
            if (!empty($photos) && is_array($photos)) {
                $temp = [];
                $number = 0;
                foreach ($photos as $photoIndex => $photo) {
                    if ($photo['Success'] == true && !empty($photo['Data'])) {
                        $number++;
                        if ($number > 25) break;

                        $objId = $photo['Object-ID'] ?? ($photo['Content-ID'] ?? $photoIndex);
                        $key = "photo/{$id}/image-{$id}-{$objId}.jpg";
                        try {
                            $s3->putObject([
                                'Body' => $photo['Data'],
                                'Bucket' => $awsConfig['bucket'],
                                'Key' => $key,
                                'ContentType' => 'image/jpeg',
                            ]);

                            if ($number > 1) {
                                $temp["photo{$number}"] = "//img1.ippraisall.com/" . $key;
                            } else {
                                // Update main photo1 in property_info if first photo
                                Yii::$app->db->createCommand()->update('property_info', ['photo1' => "//img1.ippraisall.com/" . $key], ['mls_sysid' => $id])->execute();
                            }
                        } catch (\Exception $e) {
                            $this->stderr("S3 Upload failed for {$id}: " . $e->getMessage() . "\n");
                        }
                    }
                }

                // Update property_info_photo
                if (!empty($temp)) {
                    // Check if record exists
                    $exists = Yii::$app->db->createCommand("SELECT count(*) FROM `property_info_photo` WHERE `property_id` = :id", [':id' => $id])->queryScalar();
                    if ($exists) {
                        Yii::$app->db->createCommand()->update('property_info_photo', $temp, ['property_id' => $id])->execute();
                    } else {
                        $temp['property_id'] = $id;
                        Yii::$app->db->createCommand()->insert('property_info_photo', $temp)->execute();
                    }
                }

                // Remove from queue
                Yii::$app->db->createCommand()->delete('tbl_property_info_cron_load_photo', ['id' => $item['id']])->execute();
            } else {
                $this->stderr("No photos found for MLS ID: {$id}\n");
                // Reset processing status so it can be retried later
                Yii::$app->db->createCommand()->update('tbl_property_info_cron_load_photo', [
                    'process' => null,
                ], ['id' => $item['id']])->execute();
            }
        }

        $rets->Disconnect();
        $this->stdout("Finished Load Photos at " . date('Y-m-d H:i:s') . "\n");
        return ExitCode::OK;
    }

    /**
     * Generates sitemap.xml.
     * Simplified version of CronSitemapCommand.php
     */
    public function actionSitemap()
    {
        $this->stdout("Starting Sitemap generation at " . date('Y-m-d H:i:s') . "\n");

        $base_url = "https://ippraisall.com/property/details/";
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

        // Add static URLs
        $xml .= "  <url><loc>https://ippraisall.com/</loc><changefreq>daily</changefreq><priority>1.0</priority></url>\n";
        $xml .= "  <url><loc>https://ippraisall.com/property/search</loc><changefreq>daily</changefreq><priority>0.8</priority></url>\n";

        // Add property details URLs via Slug model
        $query = \app\models\PropertyInfoSlug::find();
        $count = $query->count();
        $this->stdout("Processing {$count} slugs... ");
        
        foreach ($query->batch(1000) as $slugs) {
            foreach ($slugs as $slug) {
                $xml .= "  <url><loc>{$base_url}" . urlencode($slug->slug) . "</loc><changefreq>weekly</changefreq><priority>0.6</priority></url>\n";
            }
        }

        $xml .= "</urlset>";

        $filePath = Yii::getAlias('@app/web/sitemap.xml');

        if (file_put_contents($filePath, $xml)) {
            $this->stdout("Sitemap generated successfully at {$filePath}\n");
        } else {
            $this->stderr("Failed to write sitemap to {$filePath}\n");
        }

        $this->stdout("Finished Sitemap generation at " . date('Y-m-d H:i:s') . "\n");
        return ExitCode::OK;
    }
}
