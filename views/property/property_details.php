<?php
/**
 * @var $this yii\web\View
 * @var $details app\models\PropertyInfo
 * @var $model app\models\User|null
 * @var $profile app\models\Profile|null
 * @var $user_property_info app\models\TblUserPropertyInfo|null
 * @var $market_info object
 * @var $similar_homes array
 * @var $s_homes array
 * @var $comparebles_properties object
 * @var $c_properties array|string
 * @var $countExcludeProperties int
 * @var $shape string
 * @var $excluded_by_shape string
 */

use yii\helpers\Url;
use yii\helpers\Html;
use app\components\SiteHelper;
use app\components\CPathCDN;

$property_type_array = [
    '0' => 'Unknown', '1' => 'Single Family Home', '2' => 'Condo', '3' => 'Townhouse',
    '4' => 'Multi Family', '5' => 'Land', '6' => 'Mobile Home', '7' => 'Manufactured Home',
    '8' => 'Time Share', '9' => 'Rental', '16' => 'High Rise',
];
$pool_array = ['0' => 'No', '1' => 'Yes'];
$amenities_stove_id_array = ['0' => 'No', '1' => 'Stove/Oven', '2' => 'Stove Top Only'];
$amenities_washer_id_array = ['0' => 'No', '1' => 'Washer/Dryer', '2' => 'Dryer Only', '3' => 'Shared Washer/Dryer'];
$amenities_fireplace_id_array = ['0' => 'No', '1' => 'Wood Burning', '2' => 'Natural Gas'];
$amenities_parking_id_array = [
    '0' => 'Not Listed', '1' => 'Street Parking', '2' => 'Driveway Parking', 
    '3' => 'Reserved Parking', '4' => 'Carport', '5' => '1 Car Garage', 
    '6' => '2 Car Garage', '7' => '3 Car Garage', '8' => '4 Car Garage', 
    '9' => '5+ Car Garage', '10' => 'Permit Parking Only'
];
$over_all_property_array = ['1' => 'Is ready to move in', '2' => 'Needs a little TLC', '3' => 'Is a moderate fixer upper', '4' => 'Needs major repair'];
$pool_array = ['0' => 'No', '1' => 'Yes'];

$discont = method_exists($details, 'getDiscontValue') ? $details->getDiscontValue() : 0;

$postfix_after_rounded = ($details->property_type == 9) ? '' : 'K';
$round_value           = ($details->property_type == 9) ? 1 : 1000;

$p_type = isset($details->property_type) ? ' - ' . ($property_type_array[$details->property_type] ?? '') : '';

$this->title = $details->getFullAddress() . $p_type;

// Color scheme helpers
$colorScheme          = SiteHelper::defineColorScheme($details);
$text_color_if_discont = SiteHelper::getColorIfUnderValueOrEquityDeals($details);

$listOrSold     = ($details->property_type == 9) ? 'RENT PRICE' : 'LIST PRICE';
$icon           = '<i class="fa ' . $colorScheme['icon'] . ' fa-fw ' . $colorScheme['color'] . '"></i>';
$icon_map_lg    = '<i class="fa ' . $colorScheme['icon_map_lg'] . ' fa-fw ' . $colorScheme['color'] . '"></i>';
$status_str     = '<span class="' . $text_color_if_discont . '"> $' . number_format($details->property_price) . '</span>';
$status_str2    = 'Last Updated: ' . date('m/d/Y', strtotime($details->property_updated_date));

// Brokerage status logic
$prop_stat_caps = '';
if (isset($details->propertyInfoAdditionalBrokerageDetails->status)) {
    $brok_status    = $details->propertyInfoAdditionalBrokerageDetails->status;
    $prop_stat_caps = '<h5 class="' . $colorScheme['color'] . '">' . strtoupper($brok_status) . '</h5>';
    $property_price = number_format($details->property_price);
    $status_str2    = 'Last Updated: ' . date('m/d/Y', strtotime($details->property_updated_date));

    $sold_statuses = ['RECENTLY SOLD', 'CLOSED', 'SOLD', 'LEASED', 'TEMPOFF', 'NOT FOR SALE', 'TEMPORARILY OFF THE MARKET'];
    if (in_array(strtoupper($brok_status), $sold_statuses)) {
        $listOrSold = ($details->property_type == 9) ? 'LEASED PRICE' : 'SOLD PRICE';
    }
    $status_str = $prop_stat_caps . '<span class="' . $text_color_if_discont . '"> $' . $property_price . '</span>';
}

$underValueDeals = Yii::$app->params['underValueDeals'] ?? 5;
$estimatedEquity = 0;
if ($discont >= $underValueDeals && method_exists($details, 'getEstimatedEquity')) {
    $estimatedEquity = $details->getEstimatedEquity($details->estimated_price, $details->property_price);
    $estimatedEquity = number_format($estimatedEquity, 0, '.', ',');
}

$status_str2 .= '<span class="views-for-equity"> Page Views <span><i class="fa fa-eye"></i>&nbsp;<span id="jarviswidget-ctrls">' . ($details->views ?? 0) . '</span></span></span>';

// Sparkline placeholders for structure parity
$sparks_true_marker_value = '<div class="sparkline ' . ($text_color_if_discont ?? '') . ' hidden-mobile hidden-md hidden-sm">1000, 1200, 931, 1071, 930, 1031</div>';
$sparks_mo_mo_chg = '<div class="sparkline ' . ($text_color_if_discont ?? '') . ' hidden-mobile hidden-md hidden-sm">1000, 1200, 931, 1071, 930, 1031</div>';
$sparks_true_marker = '<div class="sparkline ' . ($text_color_if_discont ?? '') . ' hidden-mobile hidden-md hidden-sm">1000, 1200, 931, 1071, 930, 1031</div>';
$sparks_page_views = '<div class="sparkline ' . ($text_color_if_discont ?? '') . ' hidden-mobile hidden-md hidden-sm">1000, 1200, 931, 1071, 930, 1031</div>';

// Photo slider
$slider_arr = [];
$photoArr   = $this->context->getPhotoArr($details);
foreach ($photoArr as $i => $propertyInfoPhoto) {
    if (empty($propertyInfoPhoto->photo1)) continue;
    $photocaption = $propertyInfoPhoto->caption ? "<p>{$propertyInfoPhoto->caption}</p>" : '';
    $active_cls = ($i == 0) ? ' active' : '';
    $slider_arr[] = '<div class="item' . $active_cls . '">' . CPathCDN::checkPhoto($propertyInfoPhoto, 'img-responsive', 0) . $photocaption . '</div>';
}

// Session tracking for recent pages
$session    = Yii::$app->session;
$uri_page   = '@' . Yii::$app->request->url;
$recent_pg  = $details->getFullAddress() . $p_type . $uri_page;
$sess_pages = (array)$session->get('recent_pages', []);
$sess_pages[] = $recent_pg;
$sess_pages = array_unique($sess_pages);
while (count($sess_pages) > 10) { array_shift($sess_pages); }
$session->set('recent_pages', $sess_pages);

// Property slug for in-page links
$propSlug = $details->slug ? $details->slug->slug : $details->property_id;
$propUrl  = Url::to(['property/details', 'slug' => $propSlug]);

$isGuest = Yii::$app->user->isGuest;
?>

<style>
    .label_status_sold { background-color: #333 !important; color: #fff !important; }
    .label_status_active { background-color: #3276b1 !important; color: #fff !important; }
    .success { background-color: #dff0d8 !important; }
    #stage-slider { padding: 10px 0; margin: 0 15px; display: inline-block; vertical-align: middle; min-width: 150px; }
    .status_filter { display: inline-block; vertical-align: middle; margin-right: 15px; }
    .widget-body-toolbar { padding: 8px 10px; background: #fafafa; border-bottom: 1px solid #ccc; display: flex; align-items: center; justify-content: flex-start; flex-wrap: wrap; }
    .dt-toolbar { padding: 10px; background: #eee; border-bottom: 1px solid #ccc; display: flex; align-items: center; justify-content: space-between; }
    .datatable_col_reorder_wrapper .dt-toolbar:first-child { display: none; } /* Hide DT internal toolbar if we use custom one */
    .label_status { font-size: 85%; padding: .2em .6em .3em; display: inline-block; border-radius: .25em; font-weight: 700; }
    #myCarousel { border: 1px solid #ccc; background: #000; margin-bottom: 20px; min-height: 350px; height: auto; }
    #myCarousel .item { transition: opacity 0.6s ease-in-out; height: auto; }
    #myCarousel .item img { margin: 0 auto; width: 100%; height: auto; display: block; }
    .carousel-indicators { bottom: 10px; left: 50%; z-index: 15; width: 60%; padding-left: 0; margin-left: -30%; text-align: center; list-style: none; }
    .carousel-indicators li { display: inline-block; width: 10px; height: 10px; margin: 1px; text-indent: -999px; cursor: pointer; background-color: #000 \9; background-color: rgba(255,255,255,0.5); border: 1px solid #fff; border-radius: 10px; }
    .carousel-indicators .active { width: 12px; height: 12px; margin: 0; background-color: #fff; }
    #wid-id-2dt-c > div { height: auto !important; }
    .carousel-inner { height: auto !important; }
    .thumb-img { width: 180px; position: absolute; display: none; }
    #total_comp_prop a:hover .thumb-img {
        display: block;
        z-index: 99999;
        left: 20px;
        top: auto;
        bottom: 20px;
        border: none;
        box-shadow: 0 2px 10px 0 rgba(0, 0, 0, 0.5);
    }
    #total_comp_prop tr:nth-child(1) a:hover .thumb-img,
    #total_comp_prop tr:nth-child(2) a:hover .thumb-img,
    #total_comp_prop tr:nth-child(3) a:hover .thumb-img {
        top: 20px; bottom: auto;
    }
</style>

<?php 
if (!$isGuest) {
    echo $this->render('/layouts/aside', ['profile' => $profile]);
}
?>
<div id="main" role="main" class="<?= $isGuest ? 'guest-variant' : '' ?>">

    <!-- RIBBON -->
    <div id="ribbon" class="<?= $isGuest ? 'ribbon-guest-variant' : '' ?>">
        <span class="ribbon-button-alignment">
            <span id="refresh" class="btn btn-ribbon" data-title="refresh" rel="tooltip" data-placement="bottom"
                  data-original-title="<i class='text-warning fa fa-warning'></i> Warning! This will reset your widget settings."
                  data-html="true"><i class="fa fa-refresh"></i></span>
        </span>

        <ol class="breadcrumb">
            <li>Home</li>
            <li>Real Estate for Sale</li>
            <li><?= isset($details->state->state_code) ? '<a>' . Html::encode($details->state->state_code) . '</a>' : '' ?></li>
            <li><?= isset($details->city->city_name) ? '<a>' . Html::encode($details->city->city_name) . '</a>' : '' ?></li>
            <li><?= ($details->property_zipcode && $details->property_zipcode != 0 && $details->zipcode)
                ? '<a>' . Html::encode($details->zipcode->zip_code) . '</a>' : '' ?></li>
        </ol>
    </div>
    <!-- END RIBBON -->

    <!-- MAIN CONTENT -->
    <div id="content">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-5">
                <h1 class="page-title txt-color-blueDark">
                    <?= $icon_map_lg ?>
                    <?= Html::encode($details->property_street) ?>
                    <span>
                        <?php if (isset($details->city->city_name)): ?>
                            <a class="search-field-query"
                               data-subdivision=""
                               data-zipcode=""
                               data-city="<?= Html::encode($details->city->city_name) ?>"
                               data-state="<?= Html::encode($details->state->state_code ?? '') ?>">
                                <?= Html::encode($details->city->city_name) ?>,
                            </a>
                        <?php endif; ?>
                        <?php if (isset($details->state->state_code)): ?>
                            <a class="search-field-query"
                               data-subdivision=""
                               data-zipcode=""
                               data-city=""
                               data-state="<?= Html::encode($details->state->state_code) ?>">
                                <?= Html::encode($details->state->state_code) ?>
                            </a>
                        <?php endif; ?>
                        <?php if ($details->property_zipcode && $details->property_zipcode != 0 && $details->zipcode): ?>
                            <a class="search-field-query"
                               data-subdivision=""
                               data-city="<?= Html::encode($details->city->city_name ?? '') ?>"
                               data-state="<?= Html::encode($details->state->state_code ?? '') ?>"
                               data-zipcode="<?= Html::encode($details->zipcode->zip_code) ?>">
                                <?= Html::encode($details->zipcode->zip_code) ?>
                            </a>
                        <?php endif; ?>
                    </span>
                </h1>
            </div>

            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-7">
                <ul id="sparks" class="header-prices">
                    <li class="sparks-info <?= $text_color_if_discont ?>">
                        <h5><?= $status_str ?></h5>
                    </li>
                    <li class="sparks-info">
                        <?php if (isset($comparebles_properties->current_stage)): ?>
                            <script>var current_stage = <?= (int)$comparebles_properties->current_stage ?>;</script>
                        <?php endif; ?>
                        <h5>
                            <?= $details->property_type == 9 ? 'True Market Rent' : 'True Market Value' ?>
                            <?php
                            if (isset($comparebles_properties->estimated_price_dollar)) {
                                if ($comparebles_properties->estimated_price_dollar != 0) {
                                    $new_discont = 100 - ($details->property_price * 100 / $comparebles_properties->estimated_price_dollar);
                                    echo '<span class="">$' . number_format(round($comparebles_properties->estimated_price_dollar)) . '</span>';
                                } else {
                                    echo '<span title="Not Enough Data" class="">-</span>';
                                }
                            } else {
                                echo '<span title="Not Enough Data" class="">-</span>';
                            }
                            ?>
                            <a id="goToComparables" href="javascript:void(0);">View Comparables</a>
                        </h5>
                        <?= $sparks_true_marker_value; ?>
                    </li>

                    <?php if (isset($comparebles_properties->low_range) && isset($comparebles_properties->high_range)): ?>
                        <li class="sparks-info">
                            <h5>Value Range
                                <span class="<?= $text_color_if_discont ?>">
                                    $<span class="low_value_b">
                                        <?= number_format(round($comparebles_properties->low_range / $round_value), 0, '.', ',') . $postfix_after_rounded ?>
                                    </span>-$<span class="high_value_b">
                                        <?= number_format(round($comparebles_properties->high_range / $round_value), 0, '.', ',') . $postfix_after_rounded ?>
                                    </span>
                                </span>
                            </h5>
                            <?= $sparks_mo_mo_chg; ?>
                        </li>
                    <?php endif; ?>

                    <?php if (isset($new_discont) && $new_discont >= $underValueDeals): ?>
                        <li class="sparks-info">
                            <h5>
                                <?= $details->property_type == 9 ? 'Estimated Spread' : 'Estimated Equity' ?>
                                <span class="<?= $text_color_if_discont ?>">
                                    $<?php
                                    if (isset($comparebles_properties->estimated_price_dollar)
                                        && $comparebles_properties->estimated_price_dollar != 0) {
                                        if (method_exists($details, 'getEstimatedEquity')) {
                                            $eq = $details->getEstimatedEquity($comparebles_properties->estimated_price_dollar, $details->property_price);
                                            echo number_format($eq, 0, '.', ',');
                                        } else {
                                             echo number_format($comparebles_properties->estimated_price_dollar - $details->property_price, 0, '.', ',');
                                        }
                                    } else {
                                        echo $estimatedEquity;
                                    }
                                    ?>
                                </span>
                            </h5>
                        </li>
                        <li class="sparks-info">
                            <h5> Below <?= $details->property_type == 9 ? 'TMR' : 'TMV' ?> <span class="<?= $text_color_if_discont ?>"><?= round($new_discont) ?>%</span></h5>
                            <?= $sparks_page_views; ?>
                        </li>
                    <?php endif; ?>

                </ul>
            </div>
        </div><!-- /.row -->

        <!-- widget grid -->
        <section id="widget-grid" class="">
            <!-- row -->
            <div class="row">
                <article class="col-sm-12 col-md-12 col-lg-9">
                    <!-- new widget -->
                    <div class="jarviswidget jarviswidget-sortable" id="wid-id-2dt-c" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-deletebutton="false">
                        <header>
                            <?php if($user_property_info != null) :?>
                                <?php $bgColorOfStatuses = SiteHelper::getColorSchemeOfUserPropertyStatus($user_property_info->user_property_status);?>
                                <div class="user-property-status" style="float:left;display: inline-block">
                                    <div class="btn-group hidden-phone pull-left">
                                        <a id="status-button" class="btn dropdown-toggle btn-sm <?php echo $bgColorOfStatuses;?>" data-toggle="dropdown">
                                            <span><?php echo $user_property_info->user_property_status; ?></span> <span class="caret"> </span>
                                        </a>
                                        <ul class="dropdown-menu pull-left">
                                            <li><a href="javascript:void(0);" data-status="Saved">Save</a></li>
                                            <li><a href="javascript:void(0);" data-status="Dismissed">Dismiss</a></li>
                                            <li><a href="javascript:void(0);" data-status="Offered">Offer</a></li>
                                            <li><a href="javascript:void(0);" data-status="Purchased">Purchase</a></li>
                                            <li><a href="javascript:void(0);" data-status="Rejected">Reject</a></li>
                                        </ul>
                                    </div>

                                    <span><?php echo date('m/d/Y',strtotime($user_property_info->last_viewed_date)) ;?></span>
                                </div>
                            <?php endif;?>

                            <span class="widget-icon"> <?php echo $icon; ?> </span>
                            <h3 class="header-status-and-views" style="">
                                <?php echo $status_str2; ?>
                            </h3>

                            <?php if (count($slider_arr) > 0): ?>
                                <div class="widget-toolbar hidden-mobile">
                                    <span class="onoffswitch-title">Slideshow</span>
                                    <span class="onoffswitch">
                                        <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" checked="checked" id="myonoffswitch">
                                        <label class="onoffswitch-label" for="myonoffswitch">
                                            <span class="onoffswitch-inner" data-swchon-text="YES" data-swchoff-text="NO"></span>
                                            <span class="onoffswitch-switch"></span>
                                        </label>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </header>

                        <div>
                            <div class="jarviswidget-editbox"></div>

                            <!-- photo slide fade widget content -->
                            <div class="col-sm-9 col-md-9 col-lg-9" id="parentCarouselBlock">
                                <?php if (!empty($details->photo1) || count($slider_arr) > 0): ?>
<?php
    $final_slides = [];
    $main_photo_url = $details->photo1;
    $main_caption = !empty($details->caption1) ? "<p>{$details->caption1}</p>" : "";
    
    // Always start with the primary photo
    if (!empty($main_photo_url)) {
        $final_slides[] = '<div class="item active">' . CPathCDN::checkPhoto($details, 'img-responsive', 0) . $main_caption . '</div>';
    }

    // Add others from photoArr
    if (!empty($photoArr)) {
        foreach ($photoArr as $photoObj) {
            $url = $photoObj->photo1 ?? '';
            if (!empty($url) && $url != $main_photo_url) {
                $caption = !empty($photoObj->caption) ? "<p>{$photoObj->caption}</p>" : '';
                $final_slides[] = '<div class="item">' . CPathCDN::checkPhoto($photoObj, 'img-responsive', 0) . $caption . '</div>';
                // Limit to prevent overflow if database has duplicates
                if (count($final_slides) >= 41) break; 
            }
        }
    }
?>
                                        <div id="myCarousel" class="carousel fade">
                                            <?php if (count($final_slides) > 0): ?>
                                                <ol class="carousel-indicators">
                                                    <?php foreach ($final_slides as $i => $s): ?>
                                                        <li data-target="#myCarousel" data-slide-to="<?= $i ?>" class="<?= $i == 0 ? 'active' : '' ?>"></li>
                                                    <?php endforeach; ?>
                                                </ol>
                                                <div class="carousel-inner">
                                                    <?php foreach ($final_slides as $slide_html) { echo $slide_html; } ?>
                                                </div>
                                            <?php endif; ?>
                                        <a class="left carousel-control" href="#myCarousel" data-slide="prev">
                                            <span class="glyphicon glyphicon-chevron-left"></span>
                                        </a>
                                        <a class="right carousel-control" href="#myCarousel" data-slide="next">
                                            <span class="glyphicon glyphicon-chevron-right"></span>
                                        </a>
                                    </div>
                                <?php else : ?>
                                    <?php
                                    if ($details->mls_name == 'CSV-imported') {
                                        echo '<img src="' . Html::encode($details->photo1) . '" alt="' . Html::encode($details->getFullAddress()) . '" >';
                                    } else {
                                        ?>
                                        <div id="map-canvas45" class="col-md-12 map45" style="height: 370px;"></div>
                                        <script type="text/javascript">
                                            var map45;
                                            var lat = "<?php echo $details->getlatitude; ?>";
                                            var lng = "<?php echo $details->getlongitude; ?>";
                                            function initialize45() {
                                                var pos = new google.maps.LatLng(lat, lng);
                                                var mapOptions = {
                                                    center: pos,
                                                    zoom: 19,
                                                    mapTypeId: google.maps.MapTypeId.SATELLITE
                                                };
                                                map45 = new google.maps.Map(document.getElementById('map-canvas45'), mapOptions);
                                                var a_path = getPathImages();
                                                var status = '<?php echo !empty($details->propertyInfoAdditionalBrokerageDetails->status) ? strtolower($details->propertyInfoAdditionalBrokerageDetails->status) : 'for sale'; ?>';
                                                switch (status){
                                                    default:
                                                    case 'active':
                                                        var image = new google.maps.MarkerImage(a_path + '/images/map-icons/blue.png', null, null, new google.maps.Point(0, 34),new google.maps.Size(35, 40));
                                                        break;
                                                    case 'archive':
                                                        var image = new google.maps.MarkerImage(a_path + '/images/map-icons/gray.png', null, null, new google.maps.Point(0, 34),new google.maps.Size(35, 40));
                                                        break;
                                                    case 'action':
                                                        var image = new google.maps.MarkerImage(a_path + '/images/map-icons/green.png', null, null, new google.maps.Point(0, 34),new google.maps.Size(35, 40));
                                                        break;
                                                    case 'alert':
                                                        var image = new google.maps.MarkerImage(a_path + '/images/map-icons/red.png', null, null, new google.maps.Point(0, 34),new google.maps.Size(35, 40));
                                                        break;
                                                    case 'warning':
                                                        var image = new google.maps.MarkerImage(a_path + '/images/map-icons/yellow.png', null, null, new google.maps.Point(0, 34),new google.maps.Size(35, 40));
                                                        break;
                                                    case 'closed':
                                                        var image = new google.maps.MarkerImage(a_path + '/images/map-icons/black.png', null, null, new google.maps.Point(0, 34),new google.maps.Size(35, 40));
                                                        break;
                                                }
                                                var marker = new google.maps.Marker({
                                                    position: pos,
                                                    map: map45,
                                                    icon: image,
                                                    title: '123124'
                                                });
                                                map45.setTilt(45);
                                            }
                                            google.maps.event.addDomListener(window, 'load', initialize45);
                                        </script>
                                        <?php
                                    }
                                    ?>
                                <?php endif; ?>

                                <?php if (strtolower($details->public_remarks ?? '') !== 'null') :?>
                                    <div id="public-remarks">
                                        <p><?= $details->public_remarks; ?></p>
                                    </div>
                                <?php endif ;?>
                            </div>

                            <!-- tabl -->
                            <div class="col-sm-3">
                                <table class="table table-bordered table-striped table-condensed">
                                    <tbody>
                                        <tr>
                                            <th colspan="2"><?php echo $details->house_square_footage; ?> Square Feet</th>
                                        </tr>
                                        <tr>
                                            <?php $in_str = $details->subdivision ? 'in' : ''; ?>
                                            <td colspan="2">
                                                <?php echo array_key_exists($details->property_type, $property_type_array) ? $property_type_array[$details->property_type] : ''; ?>
                                                &nbsp;<?php echo $in_str; ?>
                                                <a rel="popover-hover" class="search-field-query"
                                                   data-city="<?php echo $details->city_name ?? ''; ?>"
                                                   data-state="<?php echo $details->state_code ?? ''; ?>"
                                                   data-zipcode="<?php echo $details->zip_code ?? ''; ?>"
                                                   data-subdivision="<?php echo $details->subdivision; ?>">
                                                    <?php echo $details->subdivision; ?>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Bedrooms:</th>
                                            <td><?php echo $details->bedrooms; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Bathrooms:</th>
                                            <td><?php echo $details->bathrooms; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Year Built:</th>
                                            <td><?php echo $details->year_biult_id; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Garage:</th>
                                            <?php echo !empty($details->garages) ? '<td>' . $details->garages . '</td>' : '<td class="text-muted">N/A</td>'; ?>
                                        </tr>
                                        <tr>
                                            <th>Lot Acreage:</th>
                                            <?php echo $details->lot_acreage != 0 ? '<td>' . $details->lot_acreage . '</td>' : '<td class="text-muted">N/A</td>'; ?>
                                        </tr>
                                        <tr>
                                            <th>Pool:</th>
                                            <?php 
                                            // Some properties might have 'pool' on main table, others in details. checking both.
                                            $poolVal = !empty($details->propertyInfoDetails->amenities_pool_id) ? $details->propertyInfoDetails->amenities_pool_id : ($details->pool ?? 0);
                                            echo $poolVal != 0 ? '<td>' . $poolVal . '</td>' : '<td class="text-muted">N/A</td>'; 
                                            ?>
                                        </tr>
                                        <tr>
                                            <th>Spa:</th>
                                            <?php 
                                            $spaVal = !empty($details->propertyInfoDetails->spa) ? $details->propertyInfoDetails->spa : ($details->spa ?? 0);
                                            echo $spaVal != 0 ? '<td>' . $spaVal . '</td>' : '<td class="text-muted">N/A</td>'; 
                                            ?>
                                        </tr>
                                        <tr>
                                            <th>Gated:</th>
                                            <?php 
                                            $gatedVal = !empty($details->propertyInfoDetails->amenities_gated_community) ? $details->propertyInfoDetails->amenities_gated_community : ($details->amenities_gated_community ?? 0);
                                            echo $gatedVal != 0 ? '<td>' . $gatedVal . '</td>' : '<td class="text-muted">N/A</td>'; 
                                            ?>
                                        </tr>
                                    </tbody>
                                </table>

                                <?php if($user_property_info != null) :?>
                                    <form id="user_property_note">
                                        <textarea name="" id="" cols="30" rows="10" placeholder="My Notes..."><?php echo $user_property_info->user_property_note; ?></textarea>
                                    </form>
                                <?php endif;?>
                            </div>

                            <!-- tabl -->
                            <?= $this->render('/property/_property_description', ['details' => $details]) ?>
                            <!-- end widget content -->
                        </div>
                        <!-- end widget div -->
                    </div>
                    <!-- end widget -->
                </article>

                <article class="col-sm-12 col-md-6 col-lg-3">
                    <!-- chat widget (UI parity with Yii1) -->
                    <div class="chatWidget js-chat-widget jarviswidget jarviswidget-color-blue"
                         id="wid-id-1"
                         data-widget-editbutton="false"
                         data-widget-colorbutton="false"
                         data-widget-deletebutton="false"
                         data-delay="10000"
                         data-maxmsglength="100"
                         data-property_id="<?= (int)$details->property_id ?>"
                         data-owner_mid="<?= (int)($details->mid ?? 0) ?>"
                         data-property_zipcode="<?= (int)($details->property_zipcode ?? 0) ?>"
                         data-property_status="<?= !empty($details->propertyInfoAdditionalBrokerageDetails->status) ? strtoupper($details->propertyInfoAdditionalBrokerageDetails->status) : '' ?>"
                         data-property_street="<?= Html::encode($details->property_street ?? '') ?>"
                         data-property_type="<?= (int)($details->property_type ?? 0) ?>"
                    >
                        <header>
                            <span class="widget-icon"> <i class="fa fa-comments txt-color-white"></i> </span>
                            <h2> Contact the Agent </h2>
                            <div class="widget-toolbar">
                                <div class="btn-group">
                                    <button class="btn dropdown-toggle btn-xs btn-success" data-toggle="dropdown">
                                        Connect <i class="fa fa-caret-down"></i>
                                    </button>
                                    <ul class="dropdown-menu pull-right js-status-update">
                                        <li id="chat_button_li"><a href="javascript:void(0);"><i class="fa fa-circle txt-color-green"></i> Chat</a></li>
                                        <li id="message_button_li"><a href="javascript:void(0);"><i class="fa fa-circle txt-color-blue"></i> Message</a></li>
                                        <li class="divider"></li>
                                        <li id="block_button_li"><a href="javascript:void(0);"><i class="fa fa-power-off"></i> Block</a></li>
                                    </ul>
                                </div>
                            </div>
                        </header>

                        <div>
                            <div class="widget-body widget-hide-overflow no-padding">
                                <div id="chat-container">
                                    <span class="chat-list-open-close"><i class="fa fa-user"></i><b id="agent_count_flag">0</b></span>
                                    <div class="chat-list-body custom-scroll">
                                        <ul id="chat-users">
                                            <!-- Populated by JS -->
                                        </ul>
                                    </div>
                                    <div class="chat-list-footer">
                                        <div class="control-group">
                                            <form class="smart-form">
                                                <section>
                                                    <label class="input">
                                                        <input type="text" id="filter-chat-list" placeholder="Filter">
                                                    </label>
                                                </section>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div id="chat-body" class="chat-body custom-scroll">
                                    <ul class='current-agent-chat-title' id="current-agent-chat" data-current_agent_mid="">
                                        <!-- Populated by JS -->
                                    </ul>
                                    <ul id="js-chat-messages"></ul>
                                </div>

                                <div class="chat-footer">
                                    <div class="textarea-div">
                                        <div class="typearea">
                                            <textarea placeholder="I would like more information on homes for sale in the area that are similar to <?= Html::encode($details->property_street ?? '') ?>"
                                                      id="textarea-expand"
                                                      class="custom-scroll"
                                                <?= $isGuest ? 'disabled="disabled"' : '' ?>
                                            ></textarea>
                                        </div>
                                    </div>
                                    <span class="textarea-controls">
                                        <button class="btn btn-sm btn-primary pull-right <?= $isGuest ? 'disabled' : '' ?>" <?= $isGuest ? 'disabled="disabled"' : '' ?>>Send</button>
                                        <span class="pull-right smart-form" style="margin-top: 3px; margin-right: 10px;">
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="unAuthModal" role="dialog" tabindex="-1" class="modal fade">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button data-dismiss="modal" class="close closeModal" type="button">×</button>
                                    <h1>Hello!</h1>
                                </div>
                                <div class="modal-body">
                                    <p>You've got to sign up or log in to use this feature. Joining is free!</p>
                                    <div class="modal-footer">
                                        <a class="btn btn-primary" href="<?= \yii\helpers\Url::to(['/user/registration']) ?>">Sign up</a>
                                        &nbsp;
                                        <a class="btn btn-primary" href="<?= \yii\helpers\Url::to(['/user/login']) ?>">Log in</a>
                                        &nbsp;
                                        <button data-dismiss="modal" class="btn btn-default closeModal" type="button">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

                <div class="clearfix"></div>
            </div><!-- /.row -->

        <!-- row: Map + Property Comparison -->
        <div class="row">

            <article class="col-sm-12 col-md-12
            <?php
            if(is_object($comparebles_properties) && property_exists($comparebles_properties, 'estimated_value_subject_property') && is_object($comparebles_properties->result_query ?? null))
                echo 'col-lg-6'
            ?>
            ">

                <!-- Map Widget -->
                <div class="jarviswidget" id="wid-id-2map" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-deletebutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-map-marker"></i> </span>
                        <h2>Map</h2>

                        <div class="widget-toolbar">
                            <span class="onoffswitch-title"><i class="fa fa-location-arrow"></i> Show Comps</span>
                            <span class="onoffswitch">
                                <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" checked="checked" id="myonoffswitch2">
                                <label class="onoffswitch-label" for="myonoffswitch2"> <span class="onoffswitch-inner" data-swchon-text="YES" data-swchoff-text="NO"></span> <span class="onoffswitch-switch"></span> </label> </span>
                        </div>

                        <div class="widget-toolbar hidden-mobile">
                            <div class="btn-group">
                                <button class="btn dropdown-toggle btn-xs btn-primary" data-toggle="dropdown">
                                    Draw Boundaries <i class="icon-caret-down"></i>
                                </button>
                                <ul class="dropdown-menu pull-right">
                                    <li><a href="javascript:void(0);" class="rectangle"><i class="icon-circle txt-color-green"></i> Rectangle</a></li>
                                    <li><a href="javascript:void(0);" class="radius"><i class="icon-circle txt-color-red"></i> Radius</a></li>
                                    <li><a href="javascript:void(0);" class="freehand"><i class="icon-delete"></i> Free Hand</a></li>
                                    <li class="divider"></li>
                                    <li><a href="javascript:void(0);" class="delete-button"><i class="icon-circle txt-color-red"></i> Delete Shape</a></li>
                                </ul>
                            </div>
                        </div>
                    </header>

                    <div>
                        <div class="jarviswidget-editbox">
                            <div><label>Title:</label><input type="text" /></div>
                        </div>
                        <div class="widget-body no-padding mobile-wrapper">
                            <div id="map-wrap">
                                <div id="map-canvas"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end Map Widget -->
            </article>

            <?php if (is_object($comparebles_properties) && property_exists($comparebles_properties, 'estimated_value_subject_property')): ?>
            <article class="col-sm-12 col-md-12 col-lg-6 sortable-grid ui-sortable">

                <!-- Property Comparison Widget -->
                <div class="jarviswidget jarviswidget-sortable" id="wid-id-00" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" role="widget">
                    <header role="heading">
                        <div class="jarviswidget-ctrls" role="menu">
                            <a href="#" class="button-icon jarviswidget-toggle-btn" rel="tooltip" title="" data-placement="bottom" data-original-title="Collapse"><i class="fa fa-minus "></i></a>
                            <a href="javascript:void(0);" class="button-icon jarviswidget-fullscreen-btn" rel="tooltip" title="" data-placement="bottom" data-original-title="Fullscreen"><i class="fa fa-resize-full "></i></a>
                        </div>
                        <span class="widget-icon"> <i class="fa fa-bar-chart-o text-color-green"></i> </span>
                        <h2 class="h2-fake"><?= Html::encode($details->property_street) ?> - Property Comparison</h2>
                        <span class="jarviswidget-loader"><i class="fa fa-refresh fa-spin"></i></span>
                    </header>

                    <div class="no-padding" role="content">
                        <div class="jarviswidget-editbox">test</div>

                        <div class="widget-body">
                            <div id="myTabContent" class="tab-content">
                                <div class="tab-pane fade active in padding-10 no-padding-bottom" id="s1">
                                    <div class="row no-space">
                                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 show-stats">

                                            <div class="row">
                                                <!-- SQ FT -->
                                                <div class="col-xs-12 col-sm-6 col-md-12 col-lg-12">
                                                    <span class="text">
                                                        <?= number_format($details->house_square_footage) ?> SQ FT
                                                        <span class="pull-right">
                                                            <span id="min_sqft"><?php if (is_object($comparebles_properties->result_query ?? null)) { echo number_format($comparebles_properties->result_query->min_sqft); } ?></span> -
                                                            <span id="max_sqft"><?php if (is_object($comparebles_properties->result_query ?? null)) { echo number_format($comparebles_properties->result_query->max_sqft); } ?></span>
                                                        </span>
                                                    </span>
                                                    <?php
                                                    $progress_bar_class = 'bg-color-green';
                                                    if (is_object($comparebles_properties->result_query ?? null)) {
                                                        $delta_min_max_sqft = $comparebles_properties->result_query->max_sqft - $comparebles_properties->result_query->min_sqft;
                                                        $delta_house_square_footage = $details->house_square_footage - $comparebles_properties->result_query->min_sqft;
                                                        $delta_min_max_sqft_value = $delta_min_max_sqft != 0 ? round($delta_house_square_footage * 100 / $delta_min_max_sqft) : 0;
                                                        if($delta_min_max_sqft_value != 0 && round($delta_house_square_footage * 100 / $delta_min_max_sqft) < 50){
                                                            $progress_bar_class = 'bg-color-blue';
                                                        }
                                                    }
                                                    ?>
                                                    <div class="progress">
                                                        <div class="progress-bar <?= $progress_bar_class ?>" id="min_sqft_max_sqft_progress" style="width:<?php if (isset($delta_min_max_sqft_value)) { echo min(100, max(0, (int)$delta_min_max_sqft_value)); } ?>%;"></div>
                                                    </div>
                                                </div>

                                                <!-- $/SQ FT -->
                                                <div class="col-xs-12 col-sm-6 col-md-12 col-lg-12">
                                                    <span class="text">
                                                        <span id="price_per_sq"><?php if (($details->property_price != 0) && ($details->house_square_footage != 0)) { echo '$'.round($details->property_price / $details->house_square_footage, 2); } ?></span> per SQ FT
                                                        <span class="pull-right">
                                                            <span id="lowppsqft_1"><?= is_object($comparebles_properties->result_query ?? null) ? '$'.number_format($comparebles_properties->result_query->min_ppsqft,2,'.',',') : '' ?></span> -
                                                            <span id="highppsqft_1"><?= is_object($comparebles_properties->result_query ?? null) ? '$'.number_format($comparebles_properties->result_query->max_ppsqft,2,'.',',') : '' ?></span>
                                                        </span>
                                                    </span>
                                                    <div class="progress">
                                                        <?php if (isset($comparebles_properties->result_query->max_ppsqft) && isset($comparebles_properties->result_query->min_ppsqft)) {
                                                            $delta_min_max_ppsqft = round($comparebles_properties->result_query->max_ppsqft,2) - round($comparebles_properties->result_query->min_ppsqft, 2);
                                                            $delta_property_price_ppsq = ($details->house_square_footage!=0)?round($details->property_price / $details->house_square_footage, 2) - round($comparebles_properties->result_query->min_ppsqft, 2):0;
                                                            $delta_property_pric_val = $delta_property_price_ppsq > 0 && $delta_min_max_ppsqft != 0 ? $delta_property_price_ppsq * 100 / $delta_min_max_ppsqft : 0;
                                                        }?>
                                                        <div class="progress-bar <?= (isset($delta_property_pric_val) && $delta_property_pric_val < 50)? 'bg-color-green' : 'bg-color-blue' ?>" id="price_per_sq_progress" style="width:<?php if (isset($delta_property_pric_val)) { echo min(100, max(0, (int)$delta_property_pric_val)); } ?>%;"></div>
                                                    </div>
                                                </div>

                                                <!-- ACRE LOT -->
                                                <div class="col-xs-12 col-sm-6 col-md-12 col-lg-12">
                                                    <span class="text">
                                                        <span id="lot_size"><?php if ($details->lot_acreage) { echo number_format(round($details->lot_acreage, 3),3,'.',','); } ?></span> ACRE LOT
                                                        <span class="pull-right">
                                                            <span id="lowlot_1"><?= is_object($comparebles_properties->result_query ?? null) ? number_format($comparebles_properties->result_query->min_lot,3,'.',',') : '' ?></span> -
                                                            <span id="highlot_1"><?= is_object($comparebles_properties->result_query ?? null) ? number_format($comparebles_properties->result_query->max_lot,3,'.',',') : '' ?></span>
                                                        </span>
                                                    </span>
                                                    <div class="progress">
                                                        <?php if (isset($comparebles_properties->result_query->max_lot) && isset($comparebles_properties->result_query->min_lot)) {
                                                            $delta_min_max_lot = round($comparebles_properties->result_query->max_lot - $comparebles_properties->result_query->min_lot, 3);
                                                            $delta_lot = round($details->lot_acreage - $comparebles_properties->result_query->min_lot, 3);
                                                            $delta_lot_val = $delta_lot > 0 && $delta_min_max_lot != 0 ? round($delta_lot * 100 / $delta_min_max_lot) : 0;
                                                        } ?>
                                                        <div class="progress-bar <?= (isset($delta_lot_val) && $delta_lot_val < 50)? 'bg-color-blue' : 'bg-color-green' ?>" id="lot_size_progress" style="width:<?php if (isset($delta_lot_val)) { echo min(100, max(0, (int)$delta_lot_val)); } ?>%;"></div>
                                                    </div>
                                                </div>

                                                <!-- LIST PRICE -->
                                                <div class="col-xs-12 col-sm-6 col-md-12 col-lg-12">
                                                    <span class="text">
                                                        <span id="listprice"><?= '$'.number_format($details->property_price, 0, '.', ',') ?></span>
                                                        <?= $listOrSold ?>
                                                        <span class="pull-right">COMPS:
                                                             <span id="min_price"><?php if (isset($comparebles_properties->result_query->min_price)) { echo '$'.round($comparebles_properties->result_query->min_price / $round_value ).$postfix_after_rounded; } ?></span> -
                                                             <span id="max_price"><?php if (isset($comparebles_properties->result_query->max_price)) { echo '$'.round($comparebles_properties->result_query->max_price / $round_value ).$postfix_after_rounded; } ?></span>
                                                        </span>
                                                    </span>
                                                    <div class="progress">
                                                        <?php if (isset($comparebles_properties->result_query->max_price) && isset($comparebles_properties->result_query->min_price)) {
                                                            $delta_min_max_comp = round($comparebles_properties->result_query->max_price - $comparebles_properties->result_query->min_price);
                                                            $delta_comp = round($details->property_price - $comparebles_properties->result_query->min_price);
                                                            $delta_comp_val = $delta_comp > 0 && $delta_min_max_comp != 0 ? round($delta_comp * 100 / $delta_min_max_comp) : 0;
                                                        } ?>
                                                        <div class="progress-bar <?= (isset($delta_comp_val) && $delta_comp_val < 50)? 'bg-color-green' : 'bg-color-blue' ?>" id="min_price_max_price_progress" style="width:<?php if (isset($delta_comp_val)) { echo min(100, max(0, $delta_comp_val)); } ?>%;"></div>
                                                    </div>
                                                </div>

                                                <!-- TMV RANGE -->
                                                <div class="col-xs-12 col-sm-6 col-md-12 col-lg-12">
                                                    <span class="text">
                                                        <span id="tmvalue"><?php if (isset($comparebles_properties->estimated_value_subject_property)) { echo '$'.number_format($comparebles_properties->estimated_value_subject_property,0,'.',','); } ?></span>
                                                        <?= $details->property_type == 9 ? 'TMR' : 'TMV' ?>
                                                        <span class="pull-right">RANGE:
                                                             <span id="low_value"><?php if (isset($comparebles_properties->low_range)) { echo '$'.number_format(round($comparebles_properties->low_range / $round_value ),0,'.',',') . $postfix_after_rounded; } ?></span> -
                                                             <span id="high_value"><?php if (isset($comparebles_properties->high_range)) { echo '$'.number_format(round($comparebles_properties->high_range / $round_value),0,'.',',') . $postfix_after_rounded; } ?></span>
                                                        </span>
                                                    </span>
                                                    <div class="progress">
                                                        <?php if (isset($comparebles_properties->high_range) && isset($comparebles_properties->low_range)) {
                                                            $delta_max_min_value = round($comparebles_properties->high_range - $comparebles_properties->low_range);
                                                            $dleta_value = round(($comparebles_properties->estimated_value_subject_property ?? 0) - $comparebles_properties->low_range);
                                                            $dleta_value_value = $dleta_value > 0 && $delta_max_min_value != 0 ? $dleta_value * 100 / $delta_max_min_value : 0;
                                                        } ?>
                                                        <div class="progress-bar <?= (isset($dleta_value_value) && $dleta_value_value < 50)? 'bg-color-blue' : 'bg-color-green' ?>" id="low_value_high_value_progress" style="width:<?php if (isset($dleta_value_value)) { echo $dleta_value_value; } ?>%;"></div>
                                                    </div>
                                                </div>

                                            </div><!-- /.row -->
                                        </div>
                                    </div>
                                </div><!-- end s1 tab pane -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end Property Comparison Widget -->

                <!-- True Market Value Widget -->
                <div class="jarviswidget jarviswidget-sortable" id="wid-id-0" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" role="widget">
                    <header role="heading">
                        <span class="widget-icon"> <i class="fa fa-usd text-color-green"></i> </span>
                        <h2 class="h2-fake">True Market Value - <?= Html::encode($details->property_street) ?></h2>
                    </header>
                    <div class="no-padding" role="content">
                        <div class="widget-body">
                            <div class="row no-space">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 show-stats">
                                    <div class="row">
                                        <!-- TMV -->
                                        <div class="col-xs-6 col-sm-3 col-md-3 col-lg-3">
                                            <span class="text-muted small"><?= $details->property_type == 9 ? 'TRUE MARKET RENT' : 'TRUE MARKET VALUE' ?></span><br>
                                            <span id="tmv" class="h4"><?php
                                                if (isset($comparebles_properties->estimated_value_subject_property)) {
                                                    echo $comparebles_properties->estimated_value_subject_property != 0 ? '<span class="'.$text_color_if_discont.'">$' . number_format(round($comparebles_properties->estimated_value_subject_property)) . '</span>' : '<span title="Not Enough Data" class="'.$text_color_if_discont.'">-</span>';
                                                } else {
                                                    echo '<span title="Not Enough Data" class="'.$text_color_if_discont.'">-</span>';
                                                }
                                                ?></span>
                                        </div>
                                        <!-- Equity -->
                                        <div class="col-xs-6 col-sm-3 col-md-3 col-lg-3">
                                            <span class="text-muted small"><?= $details->property_type == 9 ? 'ESTIMATED SPREAD' : 'ESTIMATED EQUITY' ?></span><br>
                                            <span id="dynamicEstimatedEquity" class="h4 <?php echo $text_color_if_discont ?>"><?php echo (isset($comparebles_properties->estimated_value_subject_property) && $comparebles_properties->estimated_value_subject_property != 0) ? '$'.number_format($comparebles_properties->estimated_value_subject_property - $details->property_price, 0, '.', ',') : '-'; ?></span>
                                            <?php if (isset($comparebles_properties->estimated_price_dollar) && $comparebles_properties->estimated_price_dollar > 0) :?>
                                                <?php $percentage = (($details->property_price - $comparebles_properties->estimated_price_dollar) / $comparebles_properties->estimated_price_dollar) * 100 ;?>
                                                <span class="label bg-color-green display-inline-block" style="vertical-align: top; margin-left: 5px;" title="Asking Price <?= round($percentage) ?>% Below TMV"><i class="fa fa-caret-down"></i><?= round($percentage) ?>%</span>
                                            <?php endif; ?>
                                        </div>
                                        <!-- Value Range -->
                                        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                                            <span class="text-muted small">VALUE RANGE</span><br>
                                            <span class="h4 <?= $text_color_if_discont ?>">$<span class="low_value_b">
                                            <?php if (isset($comparebles_properties->low_range)) { echo number_format(round($comparebles_properties->low_range / $round_value ),0,'.',',') . $postfix_after_rounded; } ?></span>-$<span class="high_value_b">
                                            <?php if (isset($comparebles_properties->high_range)) { echo number_format(round($comparebles_properties->high_range / $round_value),0,'.',',') . $postfix_after_rounded; } ?></span></span>
                                        </div>
                                    </div>

                                    <div class="row no-space margin-top-10">
                                        <!-- Confidence Gauge -->
                                        <div class="overflow-visible col-xs-12 col-sm-6 col-md-6 col-lg-6">
                                            <div class="col-xs-8 col-sm-7 col-md-7 col-lg-8">
                                                <div id="confidence_chart" class="easy-pie-chart txt-color-green" data-pie-percent="90" data-percent="90" data-pie-size="50" data-size="50">
                                                    <span id="confidence_chart_text" class="percent percent-sign">90</span>
                                                </div>
                                                <span class="easy-pie-title"> CONFIDENCE </span>
                                            </div>
                                            <div class="margin-top-10 col-xs-4 col-sm-5 col-md-5 col-lg-4">
                                                <div id="confidence_slider">
                                                    <input type="text" class="slider slider-primary" id="g1" value=""
                                                           data-slider-max="98"
                                                           data-slider-min="50"
                                                           data-slider-value="90"
                                                           data-slider-selection="before"
                                                           data-slider-handle="round">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Days on Market Gauge -->
                                        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                                            <?php
                                            $dtz = new DateTimeZone(Yii::$app->timeZone ?? "UTC");
                                            $datetime_now = new DateTime('now', $dtz);
                                            $propertyDate = !empty($details->propertyInfoAdditionalBrokerageDetails->entry_date)
                                                ? $details->propertyInfoAdditionalBrokerageDetails->entry_date : $details->property_uploaded_date;
                                            if ($propertyDate) {
                                                $datetime_exp = new DateTime($propertyDate, $dtz);
                                                $interval = $datetime_now->diff($datetime_exp);
                                                $quantity = $interval->days;
                                                $quantity_percent = $quantity;
                                                if($quantity_percent > 100) $quantity_percent = 100;
                                            } else {
                                                $quantity = 0;
                                                $quantity_percent = 0;
                                            }

                                            // Gauge color logic from legacy
                                            $chart_class = 'txt-color-green';
                                            if ($quantity >= 31 && $quantity <= 90) {
                                                $chart_class = 'txt-color-orange';
                                            } elseif ($quantity >= 91) {
                                                $chart_class = 'txt-color-red';
                                            }
                                            ?>
                                            <div id="days_on_market_chart" class="easy-pie-chart <?= $chart_class ?>" data-pie-percent="<?= $quantity_percent ?>" data-percent="<?= $quantity_percent ?>" data-pie-size="50" data-size="50">
                                                <span class="percent percent-sign"><?= $quantity ?></span>
                                            </div>
                                            <span class="easy-pie-title"> DAYSONMARKET </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end True Market Value Widget -->
            </article>
            <?php endif; ?>

        </div><!-- end row: Map + Property Comparison -->

        <!-- row: Comparable Properties Table (Full Width) -->
        <?php
        $countProp = 0;
        if (is_object($comparebles_properties) && isset($comparebles_properties->result_query)) {
            if (is_object($comparebles_properties->result_query) && isset($comparebles_properties->result_query->count_property)) {
                $countProp = (int)$comparebles_properties->result_query->count_property;
            } elseif (is_array($comparebles_properties->result_query) && isset($comparebles_properties->result_query['count_property'])) {
                $countProp = (int)$comparebles_properties->result_query['count_property'];
            }
        }
        $displayCount = max(0, (int)$countProp - (int)$countExcludeProperties);
        ?>
        <?php if ($countProp > 0): ?>
        <div class="row">
            <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <!-- Comparable Properties Table -->
                    <div class="jarviswidget jarviswidget-sortable jarviswidget-color-blue" id="wid-id-2proptbl" data-widget-editbutton="false" data-widget-deletebutton="false" data-widget-colorbutton="false">
                        <header>
                            <span class="widget-icon"> <i class="fa fa-table"></i> </span>
                            <?php
                            $curr_stage = 1;
                            if (property_exists($comparebles_properties, 'current_stage')) {
                                $curr_stage = $comparebles_properties->current_stage;
                            }?>
                            <h2 id="total_comp_prop" title="<?= $curr_stage ?>"><?= $displayCount ?> Comparable Properties</h2>
                        </header>

                        <div>
                            <div class="jarviswidget-editbox"></div>

                            <div class="widget-body no-padding mobile-wrapper">
                                <div class="widget-body-toolbar">
                                        <form action="#" class="status_filter">
                                            <select multiple class="select2" name="status_type">
                                                <?php
                                                $all_status_types = [
                                                    'For Sale' => 'For Sale', 'Pending' => 'Pending', 'Sold' => 'Sold',
                                                    'In Escrow' => 'In Escrow', 'Active' => 'Active', 'Cancelled' => 'Cancelled',
                                                    'For Rent' => 'For Rent', 'Leased' => 'Leased', 'Archive' => 'Archive'
                                                ];
                                                $session_data = Yii::$app->session;
                                                $excluded_statuses = [];
                                                $excluded_statuses_for_prop = ['Archive'];

                                                if ($session_data->has('excluded_statuses')) {
                                                    $excluded_statuses = $session_data->get('excluded_statuses');
                                                    if (is_array($excluded_statuses) && array_key_exists($details->property_id, $excluded_statuses)) {
                                                        $excluded_statuses_for_prop = $excluded_statuses[$details->property_id];
                                                    }
                                                }

                                                foreach ($all_status_types as $key => $name) {
                                                    if ($details->property_type == 9 && ($key == 'For Sale' || $key == 'Sold')) continue;
                                                    if ($details->property_type != 9 && ($key == 'For Rent' || $key == 'Leased')) continue;

                                                    $selected = in_array($key, $excluded_statuses_for_prop) ? '' : 'selected';
                                                    echo '<option value="' . Html::encode($key) . '" ' . $selected . '>' . Html::encode($key) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </form>

                                        <div id="stage-slider">
                                            <input type="text" class="slider slider-primary" value=""
                                                   data-slider-max="100"
                                                   data-slider-min="1"
                                                   data-slider-value="<?= property_exists($comparebles_properties, 'current_stage') ? $comparebles_properties->current_stage : 1 ?>"
                                                   data-slider-selection="before"
                                                   data-slider-handle="round">
                                        </div>
                                        <div style="clear:both"></div>
                                    </div>
                                <table class="table table-striped table-hover datatable_col_reorder">
                                    <thead>
                                        <tr>
                                            <th style="display:none"></th>
                                            <th>Address</th>
                                            <th>Status</th>
                                            <th><?= $details->property_type == 9 ? 'Rent Price' : 'List Price' ?></th>
                                            <th><?= $details->property_type == 9 ? 'Leased Price' : 'Sale Price' ?></th>
                                            <th><?= $details->property_type == 9 ? 'TMR' : 'TMV' ?></th>
                                            <th>Date</th>
                                            <th>$/SqFt</th>
                                            <th>Sq Ft</th>
                                            <th>Bed</th>
                                            <th>Bath</th>
                                            <th>Garage</th>
                                            <th>Lot</th>
                                            <th>Yr Blt</th>
                                            <th>Dist.</th>
                                            <th>Stories</th>
                                            <th>Pool</th>
                                            <th>Spa</th>
                                            <th>Condition</th>
                                            <th>House Faces</th>
                                            <th>House Views</th>
                                            <th>Flooring</th>
                                            <th>Furnishings</th>
                                            <th>Financing</th>
                                            <th>Foreclosure</th>
                                            <th>Short Sale</th>
                                            <th>Bank Owned</th>
                                            <th>Original Price</th>
                                            <th>Days on Market</th>
                                            <th>Tool Options</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <!-- end Comparable Properties Table -->
            </article>
        </div><!-- end row: Comparable Properties Table -->
        <?php endif; // countProp > 0 ?>

        </div><!-- end widget-grid section -->

        <!-- Market Info Row -->
        <?php if (false): ?>
        <div class="row">
            <div class="col-xs-12">
                <div class="jarviswidget jarviswidget-color-blue" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false">
                    <header>
                        <span class="widget-icon"><i class="fa fa-bar-chart"></i></span>
                        <h2>Market Information</h2>
                    </header>
                    <div>
                        <div class="widget-body">
                            <ul class="nav nav-tabs" id="marketInfoTabs">
                                <?php if ($market_info->subdivision): ?>
                                    <li class="active"><a data-toggle="tab" href="#marketSubdivision">
                                        <?= Html::encode($details->subdivision ?: $details->area) ?>
                                    </a></li>
                                <?php endif; ?>
                                <?php if ($market_info->zipcode): ?>
                                    <li<?= !$market_info->subdivision ? ' class="active"' : '' ?>>
                                        <a data-toggle="tab" href="#marketZipcode">
                                            Zipcode <?= Html::encode($details->zipcode->zip_code ?? '') ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if ($market_info->city): ?>
                                    <li><a data-toggle="tab" href="#marketCity"><?= Html::encode($details->city->city_name ?? '') ?></a></li>
                                <?php endif; ?>
                                <?php if ($market_info->county): ?>
                                    <li><a data-toggle="tab" href="#marketCounty"><?= Html::encode($details->county->county_name ?? '') ?></a></li>
                                <?php endif; ?>
                                <?php if ($market_info->state): ?>
                                    <li><a data-toggle="tab" href="#marketState"><?= Html::encode($details->state->state_code ?? '') ?></a></li>
                                <?php endif; ?>
                            </ul>
                            <div class="tab-content">
                                <?php if ($market_info->subdivision): ?>
                                <div id="marketSubdivision" class="tab-pane active">
                                    <table class="table table-striped table-condensed">
                                        <tbody>
                                            <tr><th>Median Price</th><td><?= $market_info->subdivision->median_price ? '$' . number_format($market_info->subdivision->median_price) : '-' ?></td></tr>
                                            <tr><th>Avg. Days on Market</th><td><?= $market_info->subdivision->avg_dom ?? '-' ?></td></tr>
                                            <tr><th>Total Active</th><td><?= $market_info->subdivision->total_active ?? '-' ?></td></tr>
                                            <tr><th>Total Sold</th><td><?= $market_info->subdivision->total_sold ?? '-' ?></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                                <?php if ($market_info->zipcode): ?>
                                <div id="marketZipcode" class="tab-pane<?= !$market_info->subdivision ? ' active' : '' ?>">
                                    <table class="table table-striped table-condensed">
                                        <tbody>
                                            <tr><th>Median Price</th><td><?= $market_info->zipcode->median_price ? '$' . number_format($market_info->zipcode->median_price) : '-' ?></td></tr>
                                            <tr><th>Avg. Days on Market</th><td><?= $market_info->zipcode->avg_dom ?? '-' ?></td></tr>
                                            <tr><th>Total Active</th><td><?= $market_info->zipcode->total_active ?? '-' ?></td></tr>
                                            <tr><th>Total Sold</th><td><?= $market_info->zipcode->total_sold ?? '-' ?></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                                <?php if ($market_info->city): ?>
                                <div id="marketCity" class="tab-pane">
                                    <table class="table table-striped table-condensed">
                                        <tbody>
                                            <tr><th>Median Price</th><td><?= $market_info->city->median_price ? '$' . number_format($market_info->city->median_price) : '-' ?></td></tr>
                                            <tr><th>Avg. Days on Market</th><td><?= $market_info->city->avg_dom ?? '-' ?></td></tr>
                                            <tr><th>Total Active</th><td><?= $market_info->city->total_active ?? '-' ?></td></tr>
                                            <tr><th>Total Sold</th><td><?= $market_info->city->total_sold ?? '-' ?></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                                <?php if ($market_info->county): ?>
                                <div id="marketCounty" class="tab-pane">
                                    <table class="table table-striped table-condensed">
                                        <tbody>
                                            <tr><th>Median Price</th><td><?= $market_info->county->median_price ? '$' . number_format($market_info->county->median_price) : '-' ?></td></tr>
                                            <tr><th>Avg. Days on Market</th><td><?= $market_info->county->avg_dom ?? '-' ?></td></tr>
                                            <tr><th>Total Active</th><td><?= $market_info->county->total_active ?? '-' ?></td></tr>
                                            <tr><th>Total Sold</th><td><?= $market_info->county->total_sold ?? '-' ?></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                                <?php if ($market_info->state): ?>
                                <div id="marketState" class="tab-pane">
                                    <table class="table table-striped table-condensed">
                                        <tbody>
                                            <tr><th>Median Price</th><td><?= $market_info->state->median_price ? '$' . number_format($market_info->state->median_price) : '-' ?></td></tr>
                                            <tr><th>Avg. Days on Market</th><td><?= $market_info->state->avg_dom ?? '-' ?></td></tr>
                                            <tr><th>Total Active</th><td><?= $market_info->state->total_active ?? '-' ?></td></tr>
                                            <tr><th>Total Sold</th><td><?= $market_info->state->total_sold ?? '-' ?></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.row market info -->
        <?php endif; ?>

        <!-- Similar Homes For Sale -->
        <?php if (count($similar_homes ?? []) > 0): ?>
        <div class="row">
            <article class="col-sm-12 col-md-12 col-lg-6">
                <div class="jarviswidget jarviswidget-sortable jarviswidget-color-green" id="wid-id-shfs" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-deletebutton="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-table"></i> </span>
                        <h2><?= count($similar_homes) ?> Similar Homes for <?= $details->property_type == 9 ? 'Rent' : 'Sale' ?></h2>
                    </header>

                    <div>
                        <div class="jarviswidget-editbox"></div>

                        <div class="widget-body no-padding mobile-wrapper" style="height:440px; overflow-y: scroll; overflow-x: hidden;">
                            <div class="widget-body-toolbar"></div>
                            <table class="table table-striped table-bordered table-hover dt_basic">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Price</th>
                                        <th>Address</th>
                                        <th>Desc.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </article>
        </div>
        <?php endif; ?>

        <!-- end Comparable Properties -->

        <!-- Property Details Extended - Description, Brokerage Info, etc. -->
        <?php if (false): ?>
        <div class="row">
            <div class="col-xs-12">
                <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false">
                    <header>
                        <span class="widget-icon"><i class="fa fa-info-circle"></i></span>
                        <h2>Brokerage Details</h2>
                    </header>
                    <div>
                        <div class="widget-body">
                            <table class="table table-striped table-condensed">
                                <tbody>
                                    <?php if ($details->public_remarks): ?>
                                    <tr>
                                        <th>Description</th>
                                        <td><?= nl2br(Html::encode($details->public_remarks)) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($details->propertyInfoAdditionalBrokerageDetails->pagent_name): ?>
                                    <tr>
                                        <th>Listing Agent</th>
                                        <td><?= Html::encode($details->propertyInfoAdditionalBrokerageDetails->pagent_name) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($details->brokerageJoin && $details->brokerageJoin->brokerage_name): ?>
                                    <tr>
                                        <th>Listing Office</th>
                                        <td><?= Html::encode($details->brokerageJoin->brokerage_name) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($details->propertyInfoAdditionalBrokerageDetails->financing_considered): ?>
                                    <tr>
                                        <th>Financing</th>
                                        <td><?= Html::encode($details->propertyInfoAdditionalBrokerageDetails->financing_considered) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Property Details -->
        <?php if (false): ?>
        <div class="row">
            <div class="col-xs-12">
                <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false">
                    <header>
                        <span class="widget-icon"><i class="fa fa-list-alt"></i></span>
                        <h2>Additional Details</h2>
                    </header>
                    <div>
                        <div class="widget-body">
                            <table class="table table-striped table-condensed">
                                <tbody>
                                    <?php if ($details->propertyInfoDetails->stories): ?>
                                    <tr><th>Stories</th><td><?= Html::encode($details->propertyInfoDetails->stories) ?></td></tr>
                                    <?php endif; ?>
                                    <?php if ($details->propertyInfoDetails->spa): ?>
                                    <tr><th>Spa</th><td><?= $details->propertyInfoDetails->spa ? 'Yes' : 'No' ?></td></tr>
                                    <?php endif; ?>
                                    <?php if ($details->propertyInfoDetails->interior_features): ?>
                                    <tr><th>Interior Features</th><td><?= Html::encode($details->propertyInfoDetails->interior_features) ?></td></tr>
                                    <?php endif; ?>
                                    <?php if ($details->propertyInfoDetails->exterior_features): ?>
                                    <tr><th>Exterior Features</th><td><?= Html::encode($details->propertyInfoDetails->exterior_features) ?></td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </section><!-- end widget grid -->
    </div><!-- /#content -->
</div><!-- /#main -->




<a href="#" class="close"></a><h4 class="alert-heading">Warning!</h4><p>The property lies outside the drawn area.</p><p>Please remove or expand the area</p></div>

<!-- Detail Popup for Map markers -->
<div class="comparables-properties-response"></div>

<div class="alert alert-block alert-warning alert-remove-exclude">
    <a class="close" data-dismiss="alert" href="#">×</a>
    <h4 class="alert-heading">Warning!</h4>
    <div class="msg"></div>
</div>

<div class="detail-pop-up">
    <button class="close" type="button">×</button>
    <a class="show-in-table" href="#">Show in table</a>
    <div class="row">
        <div class="col-sm-6">
            <a class="img-container">
                <div id="detail-pop-up-carousel" class="carousel fade">
                    <div class="carousel-inner" role="listbox">
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-6">
            <a class="link-container">
                <div class="street"></div>
                <div class="city"></div>
                <div class="subdivision"></div>
                <div class="type"></div>
                <div class="metrics"></div>
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 bottom-line-popup">
            <div class="row">
                <div class="col-xs-5 col-sm-4 label-container"></div>
                <div class="col-xs-7 col-sm-8 price"></div>
            </div>
            <div class="row">
                <div class="col-xs-5 col-sm-4 popup-tmv"></div>
                <div class="col-xs-7 col-sm-8 popup-tmv-price"></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="exclude-btn"></div>
        </div>
    </div>
</div>

<?php
$property_exists_flag = is_object($comparebles_properties) && property_exists($comparebles_properties, 'result_queryAllRows') ? 1 : 0;
$estimated_value_subject_property = is_object($comparebles_properties) && property_exists($comparebles_properties, 'estimated_value_subject_property') ? $comparebles_properties->estimated_value_subject_property : 0;

// Register the main property details script
$cleanTitle = str_replace(["\r","\n"], '', $details->property_street);
$this->registerJs("
    c_properties = " . json_encode($c_properties) . ";
    details_property = " . json_encode($details) . ";
    details_latitude = " . (float)$details->getlatitude . ";
    details_longitude = " . (float)$details->getlongitude . ";
    title = " . json_encode($cleanTitle) . ";
    map = '';
    markers = [];
    position = '';
    property_exists_flag = {$property_exists_flag};
    estimated_value_subject_property = {$estimated_value_subject_property};
    if(property_exists_flag == 1){
        arrRows = " . json_encode(property_exists($comparebles_properties, 'result_queryAllRows') ? $comparebles_properties->result_queryAllRows : 0) . ";
    }
    comparables_properties = " . json_encode(property_exists($comparebles_properties, 'result_queryAllRows') ? $comparebles_properties->result_queryAllRows : 0) . ";
    comparables_properties_full = " . json_encode($comparebles_properties) . ";
    details_property_id = " . (int)$details->property_id . ";
    house_sq_footage = " . (float)$details->house_square_footage . ";
    lot_sq_footage = " . (float)$details->lot_acreage . ";
    bathrooms = " . (float)$details->bathrooms . ";
    garages = " . (float)$details->garages . ";
    pool = " . (int)$details->pool . ";
    house_square_footage = " . (float)$details->house_square_footage . ";
    var propertyPrice = property_price = " . (float)$details->property_price . ";
    lot_acreage = " . (float)$details->lot_acreage . ";
    s_homes = " . json_encode($s_homes) . ";
    source_shape = {$shape};
    excluded_by_shape = {$excluded_by_shape};
    marker_on_popup = null;
    comp_min = " . json_encode(property_exists($comparebles_properties, 'comp_min') ? $comparebles_properties->comp_min : 0) . ";
    comp_min = parseInt(comp_min);
    var property_type = " . (int)$details->property_type . ";
    if(property_type == 9){
        var roundValue = 1;
        var postfixAfterRounding = '';
        var true_market = 'TMR';
    } else {
        var roundValue = 1000;
        var postfixAfterRounding = 'K';
        var true_market = 'TMV';
    }

    var DTComparablesPropertie;
    var low_sd, high_sd, table2Tail_arr;
    confidence_value = 'tail_90';

    // Similar Homes DataTable
    var dTable = $('.dt_basic').dataTable({
        'iDisplayLength':100,
        'sPaginationType' : 'bootstrap_full',
        'aaData': s_homes,
        'bDeferRender': true,
        'aoColumns': [
            null,
            { 'sType': 'currency' },
            null,
            { 'sType': 'natural' }
        ]
    });

    // Comparable Properties DataTable
    function setDataTableComparablesPropertie(){
        if(DTComparablesPropertie){ DTComparablesPropertie = $('.datatable_col_reorder').dataTable().fnDestroy(); }
        DTComparablesPropertie = $('.datatable_col_reorder').dataTable({
            'sPaginationType' : 'bootstrap',
            'aaData': c_properties,
            'bDeferRender': true,
            'iDisplayLength' : 25,
            'bAutoWidth': false,
            'stateSave': false,
            'aoColumns': [
                /* 0  indicator   */ { 'sType': 'num-html', 'bSortable': false },
                /* 1  address     */ null,
                /* 2  status      */ null,
                /* 3  list price  */ { 'sType': 'currency' },
                /* 4  sale price  */ { 'sType': 'currency' },
                /* 5  tmv         */ { 'sType': 'currency' },
                /* 6  date        */ null,
                /* 7  $/sqft      */ { 'sType': 'currency' },
                /* 8  sqft        */ { 'sType': 'natural' },
                /* 9  bed         */ null,
                /* 10 bath        */ null,
                /* 11 garage      */ { 'bVisible': false },
                /* 12 lot         */ null,
                /* 13 yr blt      */ null,
                /* 14 dist        */ null,
                /* 15 stories     */ { 'bVisible': false },
                /* 16 pool        */ { 'bVisible': false },
                /* 17 spa         */ { 'bVisible': false },
                /* 18 condition   */ { 'bVisible': false },
                /* 19 faces       */ { 'bVisible': false },
                /* 20 views       */ { 'bVisible': false },
                /* 21 flooring    */ { 'bVisible': false },
                /* 22 furnishings */ { 'bVisible': false },
                /* 23 financing   */ { 'bVisible': false },
                /* 24 forecl      */ { 'bVisible': false },
                /* 25 short sale  */ { 'bVisible': false },
                /* 26 bank owned  */ { 'bVisible': false },
                /* 27 orig price  */ { 'bVisible': false, 'sType': 'currency' },
                /* 28 dom         */ { 'bVisible': false },
                /* 29 tools       */ { 'sType': 'num-html', 'bSortable': false }
            ],
            'sDom': \"<'dt-top-row'Clf>r<'dt-wrapper't><'dt-row dt-bottom-row'<'row'<'col-sm-6'i><'col-sm-6 text-right'p>>\",
            'fnDrawCallback': function () {
                setAllMap(null);
                highlightDetailInComparableTable($(this));
                getDataCurrentPage($(this));
            },
            'fnStateLoad': function (oSettings) {
                return JSON.parse(localStorage.getItem('DataTables_' + this.fnSettings().sTableId));
            },
            'fnInitComplete' : function(oSettings, json) {
                $('.ColVis_Button').addClass('btn btn-default btn-sm').html('Columns <i class=\"icon-arrow-down\"></i>');
            }
        });
        countActiveCompProperties();
    }

    // Status filter change
    $('.status_filter .select2').on('change', function() {
        var optionNotSelected = $(this).find('option').not(':selected'),
            excludedStatuses = [], data;
        optionNotSelected.each(function(index, element) { excludedStatuses.push($(element).val()); });
        data = { property_id: details_property_id, excluded_statuses: excludedStatuses };
        $.ajax({ url: '/property/updateexcludedstatuses', data: data, type: 'POST', dataType: 'json', cache: false, success: function(){ getNewComparaplesProperties(); } });
    });

    // Stage slider
    $('#stage-slider input.slider').on('slideStop', function(slideEvt) {
        var sliderValue = slideEvt.value;
        if (sliderValue <= 0) { sliderValue = 1; }
        setStageSliderValue(sliderValue);
        $.ajax({ url: '/property/updateminstage', data: { property_id: details_property_id, min_stage: sliderValue }, type: 'POST', dataType: 'json', cache: false, success: function(){ getNewComparaplesProperties(); } });
    });

    function setStageSliderValue(value) {
        var slider = $('#stage-slider input.slider');
        slider.attr('data-slider-value', value);
        slider.slider('setValue', value);
        $('#stage-slider .tooltip-inner').text(value);
    }

    // Map functions
    function setAllMap(map) {
        for (var i = 0; i < markers.length; i++) {
            typeof map !== 'undefined' ? markers[i].setMap(map) : console.log('map is undefined');
            window.markers = markers;
        }
    }

    function setMarkersArray(latLon_arr){
        var a_path = window.location.origin, marker;
        if(typeof markers !== 'undefined'){ markers = []; }
        for(var key = 0; key < latLon_arr.length; key++){
            position = '';
            if(((latLon_arr[key].lat === '0.000000') || (latLon_arr[key].lat === '')) && ((latLon_arr[key].lon === '0.000000') || (latLon_arr[key].lon === ''))) continue;
            position = new google.maps.LatLng(latLon_arr[key].lat, latLon_arr[key].lon);
            var status = latLon_arr[key].status.toLowerCase();
            var image;
            switch (status){
                default: case 'active': image = new google.maps.MarkerImage(a_path + '/images/map-icons/blue.png', null, null, new google.maps.Point(0, 34), new google.maps.Size(35, 40)); break;
                case 'archive': image = new google.maps.MarkerImage(a_path + '/images/map-icons/gray.png', null, null, new google.maps.Point(0, 34), new google.maps.Size(35, 40)); break;
                case 'action': image = new google.maps.MarkerImage(a_path + '/images/map-icons/green.png', null, null, new google.maps.Point(0, 34), new google.maps.Size(35, 40)); break;
                case 'alert': image = new google.maps.MarkerImage(a_path + '/images/map-icons/red.png', null, null, new google.maps.Point(0, 34), new google.maps.Size(35, 40)); break;
                case 'warning': image = new google.maps.MarkerImage(a_path + '/images/map-icons/yellow.png', null, null, new google.maps.Point(0, 34), new google.maps.Size(35, 40)); break;
                case 'closed': image = new google.maps.MarkerImage(a_path + '/images/map-icons/black.png', null, null, new google.maps.Point(0, 34), new google.maps.Size(35, 40)); break;
            }
            marker = new google.maps.Marker({ position: position, icon:image, animation: google.maps.Animation.DROP, title: latLon_arr[key].address, property_id: latLon_arr[key].property_id, prop_id: latLon_arr[key].prop_id });
            marker.addListener('click', toggleDetailPopup);
            markers.push(marker);
            bounds.extend(position);
        }
        if(!bounds.isEmpty()) { map.fitBounds(bounds); }
        setAllMap(map);
    }

    function getDataCurrentPage(dataTableObj){
        pseudoMarkers = {};
        latLon_arr = [];
        dataTableObj.find('.property_info_row').each(function(){
            if(parseInt($(this).data('excluded')) == 1) return true;
            if($(this).data('self')){ $(this).parent().parent().css('font-weight','bold'); }
            var arr = {};
            arr.lat = $(this).data('lat'); arr.lon = $(this).data('lon'); arr.status = $(this).data('status');
            arr.address = $(this).data('address'); arr.property_id = $(this).data('property_id');
            arr.prop_id = $(this).closest('tr').find('.exclude_reinclude').data('property_id');
            if (arr.prop_id !== undefined) {
                latLon_arr.push(arr);
                pseudoMarkers[arr.prop_id] = { lat: parseFloat(arr.lat), lng: parseFloat(arr.lon) };
            }
            delete arr;
        });
        setMarkersArray(latLon_arr);
    }

    function highlightDetailInComparableTable(dataTableObj){
        dataTableObj.find('a[data-property_id='+details_property_id+']').each(function(){ $(this).closest('tr').addClass('success'); });
    }

    function countActiveCompProperties(current_stage){
        if(typeof current_stage !== 'undefined'){ $('#total_comp_prop').attr('title', 'Comp Stage = '+current_stage); }
        var total_comp_prop_str = $('#total_comp_prop').text();
        var total_comp_prop_num = DTComparablesPropertie.fnGetData().length - 1;
        var total_comp_prop_substr = total_comp_prop_str.substr(-22);
        var exclude_count = $('.fa-reply').length;
        if (exclude_count > 0) { total_comp_prop_num -= exclude_count; }
        $('#total_comp_prop').empty().text(total_comp_prop_num + total_comp_prop_substr);
    }

    // Map initialization
    function initialize() {
        var pos = new google.maps.LatLng(details_latitude, details_longitude);
        var mapOptions = { zoom: 12, minZoom: 2, center: pos };
        map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
        var a_path = window.location.origin;
        colorScheme = " . json_encode(SiteHelper::defineColorScheme($details)) . ";
        status = colorScheme.status.toLowerCase();
        var image;
        switch (status){
            default: case 'active': image = new google.maps.MarkerImage(a_path + '/images/map-icons/blue.png', null, null, new google.maps.Point(0, 34), new google.maps.Size(35, 40)); break;
            case 'archive': image = new google.maps.MarkerImage(a_path + '/images/map-icons/gray.png', null, null, new google.maps.Point(0, 34), new google.maps.Size(35, 40)); break;
            case 'action': image = new google.maps.MarkerImage(a_path + '/images/map-icons/green.png', null, null, new google.maps.Point(0, 34), new google.maps.Size(35, 40)); break;
            case 'alert': image = new google.maps.MarkerImage(a_path + '/images/map-icons/red.png', null, null, new google.maps.Point(0, 34), new google.maps.Size(35, 40)); break;
            case 'warning': image = new google.maps.MarkerImage(a_path + '/images/map-icons/yellow.png', null, null, new google.maps.Point(0, 34), new google.maps.Size(35, 40)); break;
            case 'closed': image = new google.maps.MarkerImage(a_path + '/images/map-icons/black.png', null, null, new google.maps.Point(0, 34), new google.maps.Size(35, 40)); break;
        }
        bounds = new google.maps.LatLngBounds();
        bounds.extend(pos);
        var marker = new google.maps.Marker({ position: pos, animation: google.maps.Animation.DROP, map: map, icon: image, title: title });
        marker.addListener('click', toggleDetailPopup);
        if(c_properties){ setDataTableComparablesPropertie(); }
        setupShape();
    }
    if(typeof google !== 'undefined' && google.maps) {
        google.maps.event.addDomListener(window, 'load', initialize);
    }

    // Detail popup functions
    function toggleDetailPopup() {
        var marker = this;
        if (marker === marker_on_popup) { hideDetailPopup(); return; }
        hideDetailPopup(); showDetailPopup(marker); updatePopupPosition(marker);
    }
    function updatePopupPosition(marker) {
        var popup$ = $('.detail-pop-up'), popupHeight = popup$.height(), popupWidth = popup$.width(),
            topRight = map.getProjection().fromLatLngToPoint(map.getBounds().getNorthEast()),
            bottomLeft = map.getProjection().fromLatLngToPoint(map.getBounds().getSouthWest()),
            scale = Math.pow(2, map.getZoom()),
            worldPoint = map.getProjection().fromLatLngToPoint(marker.getPosition()),
            markerCoords = new google.maps.Point((worldPoint.x - bottomLeft.x) * scale, (worldPoint.y - topRight.y) * scale),
            top = (markerCoords['y'] < popupHeight + 40) ? 0 : (markerCoords['y'] - popupHeight - 40),
            left = (markerCoords['x'] < popupWidth) ? 0 : (markerCoords['x'] - popupWidth);
        popup$.css({ top: top, left: left });
    }
    function showDetailPopup(marker) {
        var prop_id = marker.prop_id,
            rows$ = $('.datatable_col_reorder').find('tbody tr'),
            is_main_prop = false,
            row$, img$, label$, price, street, exclude_btn = '';

        marker_on_popup = marker;

        if (typeof prop_id === 'undefined' || prop_id == details_property_id) {
            is_main_prop = true;
            rows$.each(function(index, element) {
                if ($(element).find('.exclude_reinclude').length === 0) { row$ = $(element); }
            });
            prop_id = details_property_id;
        } else {
            rows$.each(function(index, element) {
                if ($(element).find('.exclude_reinclude').data('property_id') == prop_id) { row$ = $(element); }
            });
        }

        if (row$) {
            var img$ = row$.find('img.thumb-img');
            var imgSrc = img$.length ? img$.attr('src') : '';
            var firstPhoto$ = imgSrc ? $('<div class=\"item active\"><img src=\"' + imgSrc + '\" style=\"width:100%\"></div>') : '';
            
            label$ = row$.find('.label').clone();
            price = row$.find('td').eq(3).text(); 
            street = row$.find('.property_info_row').data('address');

            if (is_main_prop !== true) {
                exclude_btn = row$.find('.exclude_reinclude').clone();
                exclude_btn.on('click', function() {
                    hideDetailPopup();
                    row$.find('.exclude_reinclude').trigger('click');
                });
                $('.detail-pop-up').find('.exclude-btn').empty().append('<span>Exclude from Comps&nbsp;</span>').append(exclude_btn);
            } else {
                $('.detail-pop-up').find('.exclude-btn').empty();
            }

            $('.detail-pop-up').find('.carousel-inner').empty().append(firstPhoto$);
            $('.detail-pop-up').find('.label-container').empty().append(label$);
            $('.detail-pop-up').find('.price').text(price);
            $('.detail-pop-up').find('.street').text(street);
            
            $('.detail-pop-up').find('.show-in-table').off('click').on('click', function(e){
                e.preventDefault();
                $('html, body').animate({ scrollTop: row$.offset().top - 100 }, 500);
                row$.addClass('highlight-row');
                setTimeout(function(){ row$.removeClass('highlight-row'); }, 2000);
            });
        }

        $('.detail-pop-up').fadeIn().appendTo('#map-canvas');

        $.ajax({
            url: '/property/getcomppropertydetails',
            data: {property_id: prop_id},
            type: 'POST',
            dataType: 'json',
            cache: false,
            success: function(data){
                $('#detail-pop-up-carousel').carousel('pause');
                var popup$ = $('.detail-pop-up');
                popup$.find('.city').text(data.city);
                popup$.find('.subdivision').text(data.subdivision);
                popup$.find('.type').text(data.type);
                popup$.find('.metrics').html(data.metrics);
                popup$.find('.img-container').attr('href', data.url);
                popup$.find('.link-container').attr('href', data.url);
                if (data.carousel) { popup$.find('.carousel-inner').append(data.carousel); }
                if (data.tmv) {
                    popup$.find('.popup-tmv').text('TMV');
                    popup$.find('.popup-tmv-price').text(data.tmv);
                }
            }
        });
    }

    function hideDetailPopup() {
        marker_on_popup = null;
        var popup$ = $('.detail-pop-up');
        popup$.find('.carousel-inner').empty();
        popup$.find('.price').text(''); popup$.find('.label-container').empty();
        popup$.find('.street').text(''); popup$.find('.exclude-btn').empty();
        popup$.find('.city').text(''); popup$.find('.subdivision').text('');
        popup$.find('.type').text(''); popup$.find('.popup-tmv-price').text('');
        popup$.find('.popup-tmv').text(''); popup$.find('.metrics').empty();
        popup$.hide();
    }

    $('.detail-pop-up .close').on('click', function() { hideDetailPopup(); });

    // Show Comps toggle
    $('#wid-id-2map .onoffswitch-label').click(function(){
        if($('#myonoffswitch2').prop('checked') === true) { setAllMap(null); } else { setAllMap(map); }
    });

    // Go to comparables
    $('#goToComparables').click(function() { $('html, body').animate({ scrollTop: $('#total_comp_prop').offset().top }, 2000); });

    // Drawing boundaries
    var pseudoMarkers = {}, excludedProps = {}, drawingManager, selectedShape;

    function setupShape() { drawShapeFromJSON(source_shape); if (!$.isArray(excluded_by_shape)) { excludedProps = excluded_by_shape; } }

    function drawShapeFromJSON(json) {
        if (Object.keys(json).length === 0) return;
        if (json.type === 'rectangle') { selectedShape = new google.maps.Rectangle({ editable: true, strokeWeight: 0, map: map, bounds: json.bounds }); google.maps.event.addListener(selectedShape, 'bounds_changed', function() { updateComparableProperties(); }); }
        else if (json.type === 'circle') { selectedShape = new google.maps.Circle({ strokeWeight: 0, editable: true, map: map, center: json.center, radius: json.radius }); google.maps.event.addListener(selectedShape, 'center_changed', function() { updateComparableProperties(); }); google.maps.event.addListener(selectedShape, 'radius_changed', function() { updateComparableProperties(); }); }
        else if (json.type === 'polygon') { selectedShape = new google.maps.Polygon({ strokeWeight: 0, editable: true, map: map, paths: json.path }); google.maps.event.addListener(selectedShape.getPath(), 'set_at', function() { updateComparableProperties(); }); google.maps.event.addListener(selectedShape.getPath(), 'insert_at', function() { updateComparableProperties(); }); }
    }

    $('.rectangle').click(function() { deleteSelectedShape(); drawingManager = new google.maps.drawing.DrawingManager({ drawingMode: google.maps.drawing.OverlayType.RECTANGLE, drawingControl: false, rectangleOptions: { editable: true, strokeWeight: 0 } }); drawingManager.setMap(map); google.maps.event.addListener(drawingManager, 'rectanglecomplete', function(rect) { drawingManager.setDrawingMode(null); setSelection(rect); updateComparableProperties(); google.maps.event.addListener(rect, 'bounds_changed', function() { updateComparableProperties(); }); }); });
    $('.radius').click(function() { deleteSelectedShape(); drawingManager = new google.maps.drawing.DrawingManager({ drawingMode: google.maps.drawing.OverlayType.CIRCLE, drawingControl: false, circleOptions: { strokeWeight: 0, editable: true } }); drawingManager.setMap(map); google.maps.event.addListener(drawingManager, 'circlecomplete', function(circle) { drawingManager.setDrawingMode(null); setSelection(circle); updateComparableProperties(); google.maps.event.addListener(circle, 'center_changed', function(){ updateComparableProperties(); }); google.maps.event.addListener(circle, 'radius_changed', function(){ updateComparableProperties(); }); }); });
    $('.freehand').click(function() { deleteSelectedShape(); drawingManager = new google.maps.drawing.DrawingManager({ drawingMode: google.maps.drawing.OverlayType.POLYGON, drawingControl: false, polygonOptions: { strokeWeight: 0, editable: true } }); drawingManager.setMap(map); google.maps.event.addListener(drawingManager, 'polygoncomplete', function(polygon) { drawingManager.setDrawingMode(null); setSelection(polygon); updateComparableProperties(); google.maps.event.addListener(polygon.getPath(), 'set_at', function() { updateComparableProperties(); }); google.maps.event.addListener(polygon.getPath(), 'insert_at', function() { updateComparableProperties(); }); }); });
    $('.delete-button').click(function() { deleteSelectedShape(); });

    function deleteSelectedShape() { if (selectedShape) { selectedShape.setMap(null); } selectedShape = null; updateComparableProperties(); }
    function setSelection(shape) { selectedShape = shape; shape.setEditable(true); }
    function getJsonShape() {
        var json = {};
        if (!selectedShape) return json;
        if (selectedShape.constructor === google.maps.Rectangle) { json.type = 'rectangle'; json.bounds = selectedShape.getBounds().toJSON(); }
        else if (selectedShape.constructor === google.maps.Circle) { json.type = 'circle'; json.center = selectedShape.getCenter().toJSON(); json.radius = selectedShape.radius; }
        else if (selectedShape.constructor === google.maps.Polygon) { json.type = 'polygon'; json.path = []; var path = selectedShape.getPath().getArray(); for (var i = 0; i < path.length; i++) { json.path.push(path[i].toJSON()); } }
        return json;
    }
    function getPropsForExcluding() {
        var coordinates, propertiesForExcluding = {}, position = {};
        if (!selectedShape) return propertiesForExcluding;
        if (selectedShape.constructor === google.maps.Polygon) { for(var prop in pseudoMarkers){ position = new google.maps.LatLng(pseudoMarkers[prop]); if (!google.maps.geometry.poly.containsLocation(position, selectedShape)) { propertiesForExcluding[prop] = pseudoMarkers[prop]; } } return propertiesForExcluding; }
        coordinates = selectedShape.getBounds();
        for(var prop in pseudoMarkers) { position = new google.maps.LatLng(pseudoMarkers[prop]); if (!coordinates.contains(position)) { propertiesForExcluding[prop] = pseudoMarkers[prop]; } }
        return propertiesForExcluding;
    }
    function getPropsForIncluding() {
        var propertiesForIncluding = {}, position = {};
        if (!selectedShape) return excludedProps;
        if (selectedShape.constructor === google.maps.Polygon) { for(var prop in excludedProps){ position = new google.maps.LatLng(excludedProps[prop]); if (google.maps.geometry.poly.containsLocation(position, selectedShape)) { propertiesForIncluding[prop] = excludedProps[prop]; } } return propertiesForIncluding; }
        var coordinates = selectedShape.getBounds();
        for(var prop in excludedProps){ position = new google.maps.LatLng(excludedProps[prop]); if (coordinates.contains(position)) { propertiesForIncluding[prop] = excludedProps[prop]; } }
        return propertiesForIncluding;
    }
    function updateComparableProperties(propsForExcluding){
        var propertiesForExcluding = propsForExcluding || getPropsForExcluding(),
            propertiesForIncluding = getPropsForIncluding(),
            shape = getJsonShape();
        $.when(
            $('#wid-id-2map').find('.jarviswidget-loader').eq(0).css('display', 'block'),
            $.ajax({ url: '/property/updatepropsbyshape', data: { property_id: details_property_id, shape: JSON.stringify(shape), propertiesForExcluding: JSON.stringify(propertiesForExcluding), propertiesForIncluding: JSON.stringify(propertiesForIncluding) }, type: 'POST', dataType: 'json', cache: false, success: function(data){ excludedProps = data; } })
        ).then(getNewPropsSet);
    }
    function getNewPropsSet() {
        $.ajax({ url: '/property/getmoreconfidenceinfo', data: { property_id: details_property_id, tail: confidence_value }, type: 'POST', dataType: 'json', cache: false,
            success: function(data){
                if (data.status.toLowerCase() === 'success') {
                    current_stage = data.comparebles['current_stage']; setStageSliderValue(current_stage);
                    comp_min = parseInt(data.comparebles['comp_min']);
                    c_properties = data.c_properties; setDataTableComparablesPropertie(); countActiveCompProperties(current_stage);
                    resetTMV(data.comparebles);
                    $('#wid-id-2map').find('.jarviswidget-loader').eq(0).css('display', 'none');
                }
            }
        });
    }
    function getNewComparaplesProperties() {
        $.ajax({ url: '/property/getmoreconfidenceinfo', data: { property_id: details_property_id, tail: confidence_value }, type: 'POST', dataType: 'json', cache: false,
            success: function(data){
                if(data.status.toLowerCase() === 'success'){
                    current_stage = data.comparebles['current_stage']; setStageSliderValue(current_stage);
                    comp_min = parseInt(data.comparebles['comp_min']);
                    c_properties = data.c_properties; setDataTableComparablesPropertie(); countActiveCompProperties(current_stage);
                    resetTMV(data.comparebles);
                }
            }
        });
    }
    function resetTMV(obj){
        if(!obj || !obj.result_query) return;
        var data = obj.result_query;
        $('#min_sqft').html(parseInt(data.min_sqft).toLocaleString()); $('#max_sqft').html(parseInt(data.max_sqft).toLocaleString());
        var d_sqft = parseFloat(data.max_sqft) - parseFloat(data.min_sqft);
        var d_detail_sqft = parseFloat(house_square_footage) - parseFloat(data.min_sqft);
        var sqft_prog = d_detail_sqft > 0 && d_sqft != 0 ? d_detail_sqft * 100 / d_sqft : 0;
        $('#min_sqft_max_sqft_progress').css('width', Math.round(sqft_prog)+'%');

        var d_ppsqft = parseFloat(data.max_ppsqft) - parseFloat(data.min_ppsqft);
        var d_pps = parseFloat(property_price / house_square_footage) - parseFloat(data.min_ppsqft);
        var pps_prog = d_pps > 0 && d_ppsqft != 0 ? d_pps * 100 / d_ppsqft : 0;
        $('#price_per_sq_progress').css('width', Math.round(pps_prog)+'%');

        var d_lot = parseFloat(data.max_lot) - parseFloat(data.min_lot);
        var d_lot_s = parseFloat(lot_acreage) - parseFloat(data.min_lot);
        var lot_prog = d_lot_s > 0 && d_lot != 0 ? d_lot_s * 100 / d_lot : 0;
        $('#lot_size_progress').css('width', Math.round(lot_prog)+'%');

        var d_range_price = parseInt(data.max_price) - parseInt(data.min_price);
        var d_price = parseInt(property_price) - parseInt(data.min_price);
        var price_prog = d_price > 0 && d_range_price != 0 ? d_price * 100 / d_range_price : 0;
        $('#min_price_max_price_progress').css('width', Math.round(price_prog)+'%');

        if (obj.estimated_value_subject_property) {
            var est_str = '$' + parseInt(obj.estimated_value_subject_property).toLocaleString();
            $('#tmvalue').html(est_str);
        }
        if (obj.low_range) { $('#low_value').html('$' + Math.round(obj.low_range / roundValue).toLocaleString() + postfixAfterRounding); }
        if (obj.high_range) { $('#high_value').html('$' + Math.round(obj.high_range / roundValue).toLocaleString() + postfixAfterRounding); }
    }

    // Exclude/include button handler
    function sendRequest(property_id, url){
        $.ajax({
            url: url,
            data: {
                property_id: property_id,
                '" . \Yii::$app->request->csrfParam . "': '" . \Yii::$app->request->csrfToken . "'
            },
            type: 'POST',
            dataType: 'json',
            cache: false
        });
    }
    $('.datatable_col_reorder').on('click', 'button.exclude_reinclude', function(){
        property_id = $(this).data('property_id');
        $(this).toggleClass('fa-times'); $(this).toggleClass('fa-reply');
        if($(this).hasClass('fa-reply')){
            var row = $(this).closest('tr');
            $(row).find('td').each(function(){ $(this).addClass('row-disable'); });
            sendRequest(property_id, '/property/addexcludeproperty');
            getNewComparaplesProperties();
            return false;
        }
        if($(this).hasClass('fa-times')){
            var row = $(this).closest('tr');
            $(row).find('td').each(function(){ $(this).removeClass('row-disable'); });
            sendRequest(property_id, '/property/deleteexcludeproperty');
            getNewComparaplesProperties();
            return false;
        }
    });

    // parentCarouselBlockSetting parity
    function setImgageToParent(){
        var widthParent = $('#parentCarouselBlock').width();
        $('#parentCarouselBlock img').css('width', widthParent + 'px');
    }
    setImgageToParent();
    $(window).resize(function(){ setImgageToParent(); });

    // Carousel autoplay
    $('.carousel').carousel({interval: 5000});
    $('#wid-id-2dt-c .onoffswitch-label').click(function(){
        if($('#myonoffswitch').prop('checked') === true ){
                $('.carousel').carousel('pause');
        } else {
                $('.carousel').carousel({interval: 5000}); 
        }  
     });

    // Search field query
    $('.search-field-query').click(function(){
        var city = $(this).data('city'), state = $(this).data('state'), zipcode = $(this).data('zipcode'),
            subdivision = $(this).data('subdivision'), searchfld = city+' '+state+' '+zipcode;
        $.form('/property/search', { city_searchfld: city, state_searchfld: state, zipcode_searchfld: zipcode, subdivision_searchfld: subdivision, sale_type_searchfld:'For Sale', searchfld: searchfld, 'top-search-submit': 1 }, 'POST').submit();
    });

    // Agent Chat Integration
    var Chat = {
        _this : '',
        delay : '',
        maxMsgLength : '',
        property_id :  '',
        owner_mid : '',
        property_zipcode : '',
        current_agent_mid : '',
        property_status : '',
        property_street : '',
        property_agents_info : '',
        interval : '',
        intervalNewCustomers : '',
        current_user_id : '" . (!Yii::$app->user->id ? 0 : Yii::$app->user->id) . "',                       
        current_agent : '',
        user_type : '',
        chat_users : '',
        room_counter : '',
        message_created : '',
        
        start: function (){
            Chat._this = $(document).find('.js-chat-widget');
            Chat.delay = $(Chat._this).data('delay') || 10000;
            Chat.maxMsgLength = $(Chat._this).data('maxmsglength') || 150;
            Chat.property_id =  $(Chat._this).data('property_id') || 0;
            Chat.owner_mid = $(Chat._this).data('owner_mid') || 0;
            Chat.property_zipcode = $(Chat._this).data('property_zipcode') || 0;
            Chat.current_agent_mid = $(Chat._this).data('current_agent_mid') || 0;
            Chat.property_status = $(Chat._this).data('property_status') || 0;
            Chat.property_street = $(Chat._this).data('property_street') || 0;
            Chat.property_type = $(Chat._this).data('property_type') || null;
            Chat.loadMessagesFistTime();
        },
        
        loadMessagesFistTime : function(){
            $.ajax({
                url: '/property/chat',
                data: {action: 'get',
                       owner_mid: Chat.owner_mid,
                       property_zipcode: Chat.property_zipcode
                   },
                type: 'POST',
                dataType: 'json',
                cache: false,
                success: function(data){Chat.loadSuccessFirstTime(data)},
                error: Chat.loadError
            });
        },
        
        loadSuccessFirstTime : function(data){
            Chat.user_type = data.user_type;
            Chat.property_agents_info = data; 
            Chat.chat_users = data.chat_users;
            Chat.clearChatMessages();
            Chat.clearTextareaExpand();
            
            if(Chat.user_type === 'owner'){                                                             // OWNER
                Chat.clearChatUsers();
                if(data.collocutor_list && data.collocutor_list.length > 0){
                    Chat.loadCollocutorlist(data);
                    Chat.stopInterval();
                    var _this_collocutor = $('#chat-users li a.active').data('mid');
                    Chat.startInterval(Chat.current_user_id, _this_collocutor);
                    Chat.startIntervalNewCustomers();
                } else {
                    Chat.loadListChatRooms(data); 
                    Chat.startIntervalNewCustomers();
                }   
            }
            
            if(Chat.user_type === 'user'){                                                              // USER
                Chat.loadListChatRooms(data);
                Chat.stopInterval();
                var _this_chat_owner = $('#chat-users li a.active').data('mid');
                if(Chat.current_user_id != ''){
                    Chat.loadMessages(_this_chat_owner, Chat.current_user_id);
                }
            }
            Chat.scrollBottom();
        },
        
        saveAgent : function(agent_id){
            $.ajax({
                url: '/property/saveagent',
                data:{ agent_id: agent_id },
                type: 'POST',
                dataType: 'json',
                cache: false,
                success: function(data){ 
                    $('#chat-users').find('a[data-mid=\"'+agent_id+'\"]').attr('data-saved','yes'); 
                },
                error: Chat.loadError()
            });
        },
        
        deleteSavedAgent : function(agent_id){
            $.ajax({
                url: '/property/detachagent',
                data:{agent_id: agent_id},
                type: 'post',
                dataType: 'json',
                cache: false,
                success: function(data){ 
                    $('#chat-users').find('a[data-mid=\"'+agent_id+'\"][data-kind=\"saved_agents\"]').parent().detach();
                    if($('#chat-users li').length > 0){
                        var li_arr = [];
                        $('#chat-users li').each(function(){
                            if($(this).find('a').attr('data-is_online')=='yes'){
                                li_arr.push($(this).index());
                            } 
                        });
                        if(li_arr.length > 0){
                            $('#chat-users li:eq('+li_arr[0]+') a').trigger('click'); 
                        } else {
                            $('#chat_button_li').css('display','none');
                        }
                    } else {
                        $('#current-agent-chat').empty();
                        $('#js-chat-messages').empty();
                    }
                },
                error: Chat.loadError()
            });
        },
                
        clearChatMessages : function(){
            $('#agent_count_flag').empty();
        },
        
        clearChatUsers : function(){
            $('#chat-users').empty();
        },
        
        loadCollocutorlist : function(data){
            var flag_count = 0;
            if(data.collocutor_list && data.collocutor_list.length > 0){
                for (var j=0; j < data.collocutor_list.length; j++){
                    var collocutor_list = data.collocutor_list[j];
                    var collocutor_listt_photo = collocutor_list.profile.upload_photo ? collocutor_list.profile.upload_photo : 'male.png';
                    var online = collocutor_list.user == 'yes' ? '<span class=\"label label-info\">On Line</span>' : '<span class=\"label label-info\">Off Line</span>'
                    $('#chat-users').append('<li>'+
                            '<a href=\"javascript:void(0);\"'+ 
                                ' data-mid=\"'+collocutor_list.profile.mid+'\"'+ 
                                ' data-kind=\"collocutor_list\"'+
                                ' data-is_online=\"'+collocutor_list.user+'\"'+
                                ' data-index=\"'+j+'\">'+
                                '<img src=\"/images/avatars/'+collocutor_listt_photo+'\" alt=\"\">'+
                                collocutor_list.profile.first_name+'&nbsp;'+collocutor_list.profile.last_name+
                                online+'</a></li>');
                    if(j==0){
                        $('#chat-users li:eq(0) a').addClass('active');
                        Chat.current_agent = collocutor_list;
                        Chat.insertCurrentChatTitle(collocutor_list);
                        Chat.loadMessages(Chat.current_user_id, parseInt(collocutor_list.profile.mid)); 
                    }
                }
                flag_count += j;
                $('#agent_count_flag').empty().html(flag_count);
            }
            if($('#chat-users li').length > 0){
                var li_arr = [];
                $('#chat-users li').each(function(){
                    if($(this).find('a').attr('data-is_online')=='yes'){
                        li_arr.push($(this).index());
                    } 
                });
                if(li_arr.length > 0){
                    $('#chat-users li:eq('+li_arr[0]+') a').trigger('click'); 
                } else {
                    $('#chat_button_li').css('display','none');
                }
            }       
        },
        
        startIntervalNewCustomers : function(){
            Chat.intervalNewCustomers = setInterval(function(){
                Chat.getNewCustomers();
            }, 60000);
        },
        
        stopIntervalNewCustomers : function(){
            clearInterval(Chat.intervalNewCustomers);
        },
        
        getNewCustomers : function(){                                               
            $.ajax({
                url: '/property/chat',
                data: { user_type: Chat.user_type },
                type: 'POST',
                dataType: 'json',
                cache: false,
                success: function(data){
                    for ( var key in data.chat_users ){
                        if (Chat.chat_users[key]){
                            var old_row_online = data.chat_users[key].user == 'yes' ? 'On Line' : 'Off Line';
                            $('#chat-users li a[data-mid='+key+']').find('span.label').empty().html(old_row_online);
                            if(old_row_online == 'On Line'){
                                if($('#chat-users li a[data-mid='+key+']').hasClass('active')){
                                    $('#current-agent-chat li:first img').addClass('online');
                                }
                            } else {
                                if($('#chat-users li a[data-mid='+key+']').hasClass('active')){
                                    $('#current-agent-chat li:first img').removeClass('online');
                                }
                            }
                            delete data.chat_users[key];
                        }
                    }
                    var flag_count = 0;
                    if(Object.keys(data.chat_users).length > 0){
                        for (var z in data.chat_users){
                            flag_count += 1;
                            Chat.chat_users[z] = data.chat_users[z];
                            if (!Chat.property_agents_info.collocutor_list) Chat.property_agents_info.collocutor_list = [];
                            Chat.property_agents_info.collocutor_list.push(data.chat_users[z]);
                            var collocutor_list = data.chat_users[z];
                            var collocutor_list_photo = collocutor_list.profile.upload_photo ? collocutor_list.profile.upload_photo : 'male.png';

                            var online = collocutor_list.user == 'yes' ? 
                                '<span class=\"label label-info\">On Line</span>' :
                                '<span class=\"label label-info\">Off Line</span>';
                            var online_border = collocutor_list.user == 'yes' ? 'online' : '';
                            $('#chat-users').append('<li>'+
                                    '<a href=\"javascript:void(0);\"'+ 
                                    ' data-mid=\"'+collocutor_list.profile.mid+'\"'+
                                    ' data-is_online=\"'+collocutor_list.user+'\"'+
                                    ' data-kind=\"collocutor_list\">'+
                                        '<img class=\"'+online_border+'\" src=\"/images/avatars/'+collocutor_list_photo+'\" alt=\"\">'+
                                        collocutor_list.profile.first_name+'&nbsp;'+collocutor_list.profile.last_name+
                                        online+'</a></li>');
                        }
                    }                              
                    var num = $('#agent_count_flag').text() || 0;
                    $('#agent_count_flag').empty().html(flag_count + parseInt(num));
                }
            });
        },
                    
        loadListChatRooms : function(data){
            Chat.clearChatUsers();
            var flag_count = 0;
            
            if(data.owner && data.owner.length > 0){
                for(var n = 0; n < data.owner.length; n++){
                    var owner = data.owner[n];
                    if(owner.profile.mid == Chat.current_user_id) continue;
                    var owner_online =  owner.user == 'yes' ? 'online' : '';
                    var owner_photo = owner.profile.upload_photo ? owner.profile.upload_photo : 'male.png';
                    $('#chat-users').append('<li><a href=\"javascript:void(0);\"'+
                                                    ' data-mid=\"'+owner.profile.mid+'\"'+
                                                    ' data-kind=\"owner\"'+
                                                    ' data-is_online=\"'+owner.user+'\"'+
                                                    ' data-index=\"'+n+'\"'+
                                                    ' data-saved=\"no\">'+
                                                '<img src=\"/images/avatars/'+owner_photo+'\" class=\"'+owner_online+'\" alt=\"\">'+owner.profile.first_name+'&nbsp;'+
                                                    owner.profile.last_name+'<span class=\"label label-primary pull-right\">Agent</span></a></li>');
                    flag_count++;
                }
            }
            if(data.advertising_agents_list && data.advertising_agents_list.length > 0){
                var limit = Math.min(data.advertising_agents_list.length, 4);
                for(var i = 0; i < limit; i++){
                    var agent = data.advertising_agents_list[i];
                    if(agent.profile.mid == Chat.current_user_id) continue;
                    var advertising_online =  agent.user == 'yes' ? 'online' : '';
                    var agent_photo = agent.profile.upload_photo ? agent.profile.upload_photo : 'male.png';
                    $('#chat-users').append('<li><a href=\"javascript:void(0);\"'+
                                                    ' data-mid=\"'+agent.profile.mid+'\"'+
                                                    ' data-kind=\"advertising_agents_list\"'+
                                                    ' data-is_online=\"'+agent.user+'\"'+
                                                    ' data-index=\"'+i+'\"'+
                                                    ' data-saved=\"no\">'+
                                                    '<img src=\"/images/avatars/'+agent_photo+'\" class=\"'+advertising_online+'\" alt=\"\">'+agent.profile.first_name+'&nbsp;'+
                                                        agent.profile.last_name+'<span class=\"label label-success pull-right\">Advertising</span></a></li>');
                    flag_count++;
                }
            }
            
            $('#agent_count_flag').empty().html(flag_count);
            
            if($('#chat-users li').length > 0){
                var li_arr = [];
                $('#chat-users li').each(function(){
                    if($(this).find('img').hasClass('online')){
                        li_arr.push($(this).index());
                    } 
                });
                if(li_arr.length > 0){
                    $('#chat-users li:eq('+li_arr[0]+') a').trigger('click');
                } else {
                    $('#chat_button_li').hide();
                    var current_agent_el = $('#chat-users li:eq(0) a');
                    if(current_agent_el.length > 0){
                        var kind_agent = current_agent_el.data('kind');
                        var agent_mid = current_agent_el.data('mid');
                        var agent_index = current_agent_el.data('index');
                        Chat.current_agent = data[kind_agent][agent_index];
                        Chat.insertCurrentChatTitle(Chat.current_agent);
                        Chat.loadMessages(agent_mid, Chat.current_user_id );
                        current_agent_el.addClass('active');
                    } 
                }
            } 
        },

        checkZipCode : function(city, stateCode, agentZip){
            var api_key = '" . (Yii::$app->params['zipCodeApiKey'] ?? '') . "';
            if(api_key && stateCode && agentZip && city){
                $.ajax({
                    url: 'https://www.zipcodeapi.com/rest/'+api_key+'/city-zips.json/'+city+'/'+stateCode,
                    type: 'GET',
                    success: function(data){
                        if(data.zip_codes && data.zip_codes.indexOf(agentZip) == -1){
                            $('citystatezip').html('');
                        }
                    }
                });
            }
        },

        insertCurrentChatTitle : function(current_title){
            var photo = current_title.profile.upload_photo ? current_title.profile.upload_photo : 'male.png';
            var current_date = new Date();
            var city = (current_title.city && current_title.city != 0) ? current_title.city+',&nbsp;' : '';
            var state = (current_title.state && current_title.state != 0) ? current_title.state+'&nbsp;' : '';
            var zipcode = (current_title.zip && current_title.zip != 0) ? current_title.zip : '';
            var phone = current_title.profile.phone_office ? '<abbr title=\"Phone\">P:&nbsp;</abbr>'+current_title.profile.phone_office : '';

            this.checkZipCode(current_title.city, current_title.state_code, zipcode);

            var office = current_title.profile.office || '';
            var website_url = '';
            if(current_title.profile.website_url){
                var url = current_title.profile.website_url.toLowerCase().indexOf('http') == 0 ? current_title.profile.website_url : 'http://'+current_title.profile.website_url;
                website_url = '<a href=\"'+url+'\" target=\"_blank\">'+office+' website</a>';
            }
            
            var hi_name = current_title.profile.first_name ? 'Hi '+current_title.profile.first_name+',' : 'Hi,';
            var typeText = Chat.property_type == 9 ? 'rent' : 'sale'; 
            var message = '', placeholder = '';

            switch (Chat.property_status){
                case 'HISTORY': case 'SOLD': case 'NOT FOR SALE': case 'PENDING':
                    message = '\"Thank you for checking out '+this.property_street+'. Contact me for information on homes for ' + typeText + ' in the area!\"';
                    placeholder = hi_name+' I would like more information on homes for ' + typeText + ' in the area that are similar to '+this.property_street+'…';
                    break;
                default:
                    message = '\"Thank you for checking out '+this.property_street+'. Please contact me if you have any questions!\"';
                    placeholder = hi_name+' I am interested in '+this.property_street+' and would like more information on the listing';
            }

            var online_class = current_title.user == 'yes' ? 'online' : '';
            if(online_class == 'online'){
                $('#chat_button_li').show();
                $('#chat_button_li a').trigger('click');
            } else { 
                $('#message_button_li a').trigger('click'); 
            }

            $('.current-agent-chat-title').attr('data-current_agent_mid', current_title.profile.mid);
            $('.current-agent-chat-title').html('<li class=\"message agent\">'+
                        '<img src=\"/images/avatars/'+photo+'\" class=\"'+online_class+'\" alt=\"\" width=\"50\">'+
                        '<div class=\"message-text\">'+
                            '<time>'+current_date.getFullYear()+'-'+(current_date.getMonth()+1)+'-'+current_date.getDate()+'</time>'+
                            '<a href=\"javascript:void(0);\" class=\"username\">'+current_title.profile.first_name+' '+current_title.profile.last_name+'</a>'+
                            '<address>'+
                                '<strong>'+office+'</strong><br>'+(current_title.profile.street_address || '')+
                                '<br><citystatezip>'+city+state+zipcode+'</citystatezip><br>'+
                                phone+
                            '</address>'+
                        '</div>'+
                    '</li>'+
                    '<li class=\"message broker\">'+
                            message+
                    '</li>');
            $('#textarea-expand').attr('placeholder', placeholder);
        },
        
        scrollBottom : function(){
            var el = $('#chat-body').get(0);
            if (el) $('#chat-body').scrollTop(el.scrollHeight);
        },

        sendMessage : function(text_message, owner_room, collocutor, m_type){
            $.ajax({
                url: '/property/messages',
                data: {
                        message : text_message,
                        owner_room: owner_room, 
                        collocutor: collocutor,
                        m_type: m_type
                },
                type: 'POST',
                dataType: 'json',
                success: function(data){ Chat.loadSuccess(data); },
                error: function(){ Chat.loadError(); }
            });
        },

        loadMessages : function(room_owner_id, collocutor_id){
            $.ajax({
                url: '/property/messages',
                data: {
                        owner_room: room_owner_id, 
                        collocutor: collocutor_id 
                },
                type: 'POST',
                dataType: 'json',
                success: function(data){ Chat.loadSuccess(data); },
                error: function(){ Chat.loadError(); }
            });
        },
        
        loadSuccess : function(data){
            Chat.renderMessages(data);
            Chat.scrollBottom();
        },
        
        renderMessages : function(data){
            if(Chat.current_user_id != 0 && data.messages){
                var html = '';
                for(var i = 0; i < data.messages.length; i++){
                    var msg = data.messages[i];
                    var author_id = msg.message.author_id;
                    var user_profile = (author_id == Chat.current_user_id) ? 
                        Chat.property_agents_info.current_user.profile : 
                        (Chat.chat_users[author_id] ? Chat.chat_users[author_id].profile : {first_name:'User', last_name:'', upload_photo:'male.png'});
                    
                    var avatar = user_profile.upload_photo || 'male.png';
                    html += '<li class=\"message\">'+
                                '<img class=\"online\" src=\"/images/avatars/'+avatar+'\" alt=\"\" width=\"50\">'+
                                '<div class=\"message-text\">'+ 
                                    '<a>'+user_profile.first_name+' '+user_profile.last_name+'</a>'+
                                '</div>'+
                                    '<br>'+msg.message.chat_message+
                            '</li>';
                }
                $('#js-chat-messages').html(html);
            }
        },
        
        clearTextareaExpand : function(){
            $('#textarea-expand').val('');
        },
        
        loadError : function (){
            Chat.proceedError('Server respond with error. Please, try again later.');
        },
        
        proceedError : function(message){
            var errorEl = $('.js-chat-error'); // Add this element if needed
            if (errorEl.length) {
                errorEl.text(message || 'Something went wrong!').slideDown();
                setTimeout(function(){errorEl.slideUp();}, 2000);
            } else {
                console.error(message);
            }
        },
        
        startInterval : function(room_owner_id, collocutor_id){
            Chat.interval = setInterval(function(){
                Chat.loadMessages(room_owner_id, collocutor_id);
            }, Chat.delay);
        },
        
        stopInterval : function(){
            if (Chat.interval) clearInterval(Chat.interval);
        },
    };

    // Chat widget event handlers
    $(document).on('click', '.chat-list-body #chat-users li a', function(e) {
        e.preventDefault(); 
        var _this = $(this);
        var kind = _this.data('kind');
        var current_mid = _this.data('mid');
        var index = _this.data('index');
        var saved = _this.attr('data-saved');

        $('#current-agent-chat').attr('data-current_agent_mid', current_mid); 
        $('#chat-users li a').removeClass('active');
        _this.addClass('active');
        _this.parent().prependTo('#chat-users');

        Chat.stopInterval();
        if(Chat.user_type === 'user'){
            Chat.insertCurrentChatTitle(Chat.property_agents_info[kind][index]);
            Chat.loadMessages(current_mid, Chat.current_user_id);
        } else if(Chat.user_type === 'owner'){
            Chat.insertCurrentChatTitle(Chat.property_agents_info[kind][index]);
            Chat.loadMessages(Chat.current_user_id, parseInt(current_mid));
            Chat.startInterval(Chat.current_user_id, parseInt(current_mid));
        }
        return false;
    });

    $(document).on('click', '.textarea-controls button', function(){
        var current_agent_mid = $('#current-agent-chat').attr('data-current_agent_mid');    
        var text_message = $('#textarea-expand').val();
        var isOnline = $('#chat-users li a.active').attr('data-is_online');
        
        if(text_message != ''){
            Chat.stopInterval();
            if(Chat.user_type === 'owner'){
                var kind = $('#chat-users li a.active').attr('data-kind'); 
                Chat.sendMessage(text_message, (kind == 'collocutor_list' ? Chat.current_user_id : current_agent_mid), (kind == 'collocutor_list' ? current_agent_mid : Chat.current_user_id), isOnline);
                Chat.startInterval(Chat.current_user_id, current_agent_mid);
            } else {
                Chat.sendMessage(text_message, current_agent_mid, Chat.current_user_id, isOnline);
                Chat.startInterval(current_agent_mid, Chat.current_user_id);
            }
            Chat.clearTextareaExpand();
        }
    });

    $('.chat-footer .textarea-div').on('click', function(){
        if($(this).find('textarea').prop('disabled')){
            $('#unAuthModal').modal('show');
        }
    });

    Chat.start();
"); ?>

