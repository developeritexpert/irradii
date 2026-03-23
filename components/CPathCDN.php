<?php

namespace app\components;



use Yii;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\Credentials\Credentials;

class CPathCDN {

    public static function baseurl($type = 'cdn') {

        // Safe fallback when no theme is configured
        $themeBase = (Yii::$app->view->theme !== null)
            ? Yii::$app->view->theme->baseUrl
            : Yii::$app->request->baseUrl;

        switch ($type) {

            case 'css':
                if (!empty(Yii::$app->params['cdnCss'])) {
                    return Yii::$app->params['cdnCss'];
                }
                return $themeBase;

            case 'js':
                if (!empty(Yii::$app->params['cdnJs'])) {
                    return Yii::$app->params['cdnJs'];
                }
                return $themeBase;

            case 'img':
                if (!empty(Yii::$app->params['cdnImg'])) {
                    return Yii::$app->params['cdnImg'];
                }
                return $themeBase;

            case 'cdn':
                if (!empty(Yii::$app->params['cdnCdn'])) {
                    return Yii::$app->params['cdnCdn'] . $themeBase;
                }
                return $themeBase;


            case 'images':
                $cdnImages = Yii::$app->params['cdnImages'];
                if (!empty($cdnImages)) {
                    if (is_array($cdnImages)) {
                        return $cdnImages[0];
                    } else {
                        return $cdnImages;
                    }
                }
                return Yii::$app->request->baseUrl;

            case 'photo':
                $cdnPhotos = Yii::$app->params['cdnPhotos'];
                if (!empty($cdnPhotos)) {
                    if (is_array($cdnPhotos)) {
                        return $cdnPhotos[0];
                    } else {
                        return $cdnPhotos;
                    }
                }
                return Yii::$app->request->baseUrl;

            default:
                break;
        }

        return Yii::$app->request->baseUrl;
    }


    public static function checkPhoto($param_photo, $class = '', $check = 0, $width = null) {

        if ($width !== null) {
            $width = ' width="'.$width.'" ';
        }

        $photo1 = $param_photo->photo1;
        // Global normalization
        if (strpos($photo1, 'irradii') !== false) {
            $photo1 = str_replace('irradii', 'ippraisall', $photo1);
        }

        $param_alt = (!empty($param_photo->fullAddress)) ? $param_photo->fullAddress : '';

        if (strtolower(substr($photo1, 0, 4)) === 'http') {

            $cdnPhotos = Yii::$app->params['cdnPhotos'];

            if (!empty($cdnPhotos)) {
                $photo1 = str_replace(
                    'http://www.propertyhookup.com/admin/photos/',
                    CPathCDN::baseurl('photo') . '/photo/',
                    $photo1
                );
            }

            if (!$check) {
                return '<img '.$width.' class="'.$class.'" src="'.$photo1.'" alt="'.$param_alt.'">';
            } else {
                $file_headers = Yii::$app->cache->get($photo1);

                if ($file_headers === false) {
                    $file_headers = CPathCDN::checkS3Photo($photo1);
                    Yii::$app->cache->set($photo1, $file_headers, 1000);
                }

                if ($file_headers[0] != 'HTTP/1.1 404 Not Found') {
                    return '<img '.$width.' class="'.$class.'" src="'.$photo1.'" alt="'.$param_alt.'">';
                } else {
                    return '<img class="'.$class.'" src="'.CPathCDN::baseurl('images').'/image_absent.jpg" alt="'.$param_alt.'">';
                }
            }

        } else {
            $fullUrl = CPathCDN::baseurl('images') . '/images/property_image/' . $photo1;
            $photo1_file = Yii::$app->basePath . "/../images/property_image/" . $photo1;

            if (is_readable($photo1_file)) {
                return '<img '.$width.' class="'.$class.'" src="'.$fullUrl.'" alt="'.$param_alt.'">';
            } else {
                return '<img class="'.$class.'" src="'.CPathCDN::baseurl('images').'/image_absent.jpg" alt="'.$param_alt.'">';
            }
        }
    }


    public static function checkS3Photo($photo) {

        $aws = Yii::$app->params['awsKeys'];
        $credentials = new Credentials($aws['key'], $aws['secret']);

        $s3 = new S3Client([
            'version' => 'latest',
            'region' => $aws['region'],
            'credentials' => $credentials,
        ]);

        $photo = str_replace(
            [
                'http://www.propertyhookup.com/admin/photos/',
                'http:' . CPathCDN::baseurl('photo') . '/',
                CPathCDN::baseurl('photo') . '/'
            ],
            [
                'photo/',
                '',
                ''
            ],
            $photo
        );

        try {

            $resp = $s3->headObject([
                'Bucket' => $aws['bucket'],
                'Key' => str_replace('http://img1.irradii.com/', '', $photo)
            ]);

        } catch (S3Exception $exception) {

            return ['HTTP/1.1 404 Not Found'];
        }

        return $resp ? ['HTTP/1.1 200 Found'] : ['HTTP/1.1 404 Not Found'];
    }


    public static function uploadS3Images($upload, $type, $filename) {

        $aws = Yii::$app->params['awsKeys'];
        $credentials = new Credentials($aws['key'], $aws['secret']);

        $s3 = new S3Client([
            'version' => 'latest',
            'region' => $aws['region'],
            'credentials' => $credentials,
        ]);

        $s3->putObject([
            'Bucket' => $aws['bucket'],
            'Key' => 'images/'.$type.'/'.$filename,
            'SourceFile' => $upload->getTempName(),
        ]);

        $image = Yii::$app->image->load($upload->getTempName());

        $image
            ->resize(180, 180)
            ->crop(180, 180)
            ->save(CPathCDN::pathImage($type, '50_50_'.$filename));

        $s3->putObject([
            'Bucket' => $aws['bucket'],
            'Key' => 'images/'.$type.'/50_50_'.$filename,
            'SourceFile' => CPathCDN::pathImage($type, '50_50_'.$filename),
        ]);

        unlink(CPathCDN::pathImage($type, '50_50_'.$filename));
    }


    public static function uploadS3Files($upload, $type, $filename) {

        $aws = Yii::$app->params['awsKeys'];
        $credentials = new Credentials($aws['key'], $aws['secret']);

        $s3 = new S3Client([
            'version' => 'latest',
            'region' => $aws['region'],
            'credentials' => $credentials,
        ]);

        $s3->putObject([
            'Bucket' => $aws['bucket'],
            'Key' => 'images/'.$type.'/'.$filename,
            'SourceFile' => $upload->getTempName(),
        ]);
    }


    public static function pathImage($image_category, $upload_photo) {

        return Yii::$app->basePath .
            "/../images/" .
            $image_category .
            "/" .
            $upload_photo;
    }


    public static function publish($asset) {

        return Yii::$app->assetManager->publish($asset);
    }


    public static function gzipPublish($asset, $contentType) {

        $string = file_get_contents($asset);

        $compressedString = gzencode($string, 9);

        return Yii::$app->assetManager->publish($asset);
    }

}