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

// Photo slider
$slider_arr = [];
$photoArr   = $this->context->getPhotoArr($details);
foreach ($photoArr as $propertyInfoPhoto) {
    $photocaption = $propertyInfoPhoto->caption ? "<p>{$propertyInfoPhoto->caption}</p>" : '';
    // normalize legacy domain references
    if (strpos($propertyInfoPhoto->photo1, 'irradii')) {
        $propertyInfoPhoto->photo1 = str_replace('irradii', 'ippraisall', $propertyInfoPhoto->photo1);
    }
    $slider_arr[] = '<div class="item">' . CPathCDN::checkPhoto($propertyInfoPhoto, '', 0) . $photocaption . '</div>';
}

// Session tracking for recent pages
$session    = Yii::$app->session;
$uri_page   = '@' . Yii::$app->request->url;
$recent_pg  = $details->getFullAddress() . $p_type . $uri_page;
$sess_pages = $session->get('recent_pages', []);
$sess_pages[] = $recent_pg;
$session->set('recent_pages', $sess_pages);

// Property slug for in-page links
$propSlug = $details->slug ? $details->slug->slug : $details->property_id;
$propUrl  = Url::to(['property/details', 'slug' => $propSlug]);

$isGuest = Yii::$app->user->isGuest;
?>

<?php if (!$isGuest): ?>
<?= $this->render('/layouts/aside', ['profile' => $profile]) ?>
<?php endif; ?>

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
                        <h6><?= $listOrSold ?></h6>
                    </li>
                    <li class="sparks-info">
                        <?php if (property_exists($comparebles_properties, 'current_stage')): ?>
                            <script>var current_stage = <?= (int)$comparebles_properties->current_stage ?>;</script>
                        <?php endif; ?>
                        <h5>
                            <?= $details->property_type == 9 ? 'True Market Rent' : 'True Market Value' ?>
                            <?php
                            if (property_exists($comparebles_properties, 'estimated_price_dollar')) {
                                if ($comparebles_properties->estimated_price_dollar != 0) {
                                    $discont = 100 - ($details->property_price * 100 / $comparebles_properties->estimated_price_dollar);
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
                    </li>

                    <?php if (property_exists($comparebles_properties, 'low_range') && property_exists($comparebles_properties, 'high_range')): ?>
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
                        </li>
                    <?php endif; ?>

                    <?php if ($discont >= $underValueDeals): ?>
                        <li class="sparks-info">
                            <h5>
                                <?= $details->property_type == 9 ? 'Estimated Spread' : 'Estimated Equity' ?>
                                <span class="<?= $text_color_if_discont ?>">
                                    $<?php
                                    if (isset($comparebles_properties->estimated_price_dollar)
                                        && $comparebles_properties->estimated_price_dollar != 0
                                        && $discont >= $underValueDeals
                                        && method_exists($details, 'getEstimatedEquity')) {
                                        $eq = $details->getEstimatedEquity($comparebles_properties->estimated_price_dollar, $details->property_price);
                                        echo number_format($eq, 0, '.', ',');
                                    } else {
                                        echo $estimatedEquity;
                                    }
                                    ?>
                                </span>
                            </h5>
                        </li>
                    <?php endif; ?>

                    <li class="sparks-info">
                        <h5><?= $status_str2 ?></h5>
                    </li>
                </ul>
            </div>
        </div><!-- /.row -->

        <!-- Property Image + Main Details Row -->
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-7 col-lg-7">
                <!-- Photo Carousel -->
                <?php if (count($slider_arr) > 0): ?>
                    <div id="myCarousel" class="carousel fade">
                        <ol class="carousel-indicators">
                            <?php for ($i = 0; $i <= count($slider_arr); $i++): ?>
                                <li data-target="#myCarousel" data-slide-to="<?= $i ?>" class="<?= $i == 0 ? 'active' : '' ?>"></li>
                            <?php endfor; ?>
                        </ol>
                        <div class="carousel-inner">
                            <!-- Slide 1 -->
                            <div class="item active">
                                <?php
                                if (strtolower(substr($details->photo1, 0, 4)) === 'http') {
                                    echo CPathCDN::checkPhoto($details, "", 0);
                                } else {
                                    $photo1 = CPathCDN::baseurl('images') . '/images/property_image/' . $details->photo1;
                                    echo '<img src="' . Html::encode($photo1) . '" alt="' . Html::encode($details->getFullAddress()) . '">';
                                }
                                ?>
                                <?= $details->caption1 ? "<p>" . Html::encode($details->caption1) . "</p>" : '' ?>
                            </div>

                            <!-- Remaining Slides -->
                            <?php foreach ($slider_arr as $slider_arr_value): ?>
                                <?= $slider_arr_value ?>
                            <?php endforeach; ?>
                        </div>
                        <a class="left carousel-control" href="#myCarousel" data-slide="prev"> <span class="glyphicon glyphicon-chevron-left"></span> </a>
                        <a class="right carousel-control" href="#myCarousel" data-slide="next"> <span class="glyphicon glyphicon-chevron-right"></span> </a>
                    </div>
                <?php else: ?>
                    <?php
                    if ($details->mls_name == 'CSV-imported') {
                        echo '<img class="img-responsive" src="' . Html::encode($details->photo1) . '" alt="' . Html::encode($details->getFullAddress()) . '" >';
                    } elseif (strtolower(substr($details->photo1, 0, 4)) === 'http') {
                        echo CPathCDN::checkPhoto($details, "", 0);
                    } else {
                        $photo1 = CPathCDN::baseurl('images') . '/images/property_image/' . $details->photo1;
                        echo '<img class="img-responsive" src="' . Html::encode($photo1) . '" alt="' . Html::encode($details->getFullAddress()) . '">';
                    }
                    ?>
                <?php endif; ?>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-5 col-lg-5">
                <!-- Property quick facts -->
                <div class="jarviswidget jarviswidget-color-blueDark" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false">
                    <header>
                        <span class="widget-icon"><i class="fa fa-home"></i></span>
                        <h2>Property Details</h2>
                    </header>
                    <div>
                        <div class="widget-body">
                            <?= $this->render('_property_address_block', ['property_model' => $details]) ?>
                            <?= $this->render('_property_price_block', ['property_model' => $details, 'user_property_info' => $user_property_info]) ?>
                            <?= $this->render('_property_status_mark', ['property_model' => $details]) ?>
                            <?= $this->render('_property_bedbath_block', ['property_model' => $details]) ?>
                            <?= $this->render('_property_sqft_block', ['property_model' => $details]) ?>
                            <?= $this->render('_property_date_block', ['property_model' => $details]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.row -->

        <!-- Market Info Row -->
        <?php if ($market_info->subdivision || $market_info->zipcode || $market_info->city || $market_info->county || $market_info->state): ?>
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
        <?php if (!empty($s_homes)): ?>
        <div class="row" id="similarHomes">
            <div class="col-xs-12">
                <div class="jarviswidget jarviswidget-color-green" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false">
                    <header>
                        <span class="widget-icon"><i class="fa fa-th-list"></i></span>
                        <h2>Similar Homes For Sale</h2>
                    </header>
                    <div>
                        <div class="widget-body">
                            <table class="table table-bordered table-hover table-condensed" id="similar-homes-table">
                                <thead>
                                    <tr>
                                        <th>Photo</th>
                                        <th>Price / Status</th>
                                        <th>Address</th>
                                        <th>Size / Beds / Baths</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($s_homes as $sh_row): ?>
                                    <tr>
                                        <?php foreach ($sh_row as $sh_col): ?>
                                        <td><?= $sh_col ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.row similar homes -->
        <?php endif; ?>

        <!-- Comparables Section -->
        <?php if (!empty($c_properties)): ?>
        <div class="row" id="comparables">
            <div class="col-xs-12">
                <div class="jarviswidget jarviswidget-color-orange" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false">
                    <header>
                        <span class="widget-icon"><i class="fa fa-exchange"></i></span>
                        <h2>Comparable Properties</h2>
                        <?php if ($countExcludeProperties > 0): ?>
                            <span class="badge badge-danger"><?= $countExcludeProperties ?> excluded</span>
                        <?php endif; ?>
                    </header>
                    <div>
                        <div class="widget-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-condensed table-sortable" id="comps-table">
                                    <thead>
                                        <tr>
                                            <th>Excl.</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Address</th>
                                            <th>Beds</th>
                                            <th>Baths</th>
                                            <th>Lot</th>
                                            <th>Sq Ft</th>
                                            <th>$/SqFt</th>
                                            <th>Yr Built</th>
                                            <th>Garage</th>
                                            <th>TMV</th>
                                            <th>% Below</th>
                                            <th>Subdivision</th>
                                            <th></th>
                                            <th>Pool</th>
                                            <th></th>
                                            <th></th>
                                            <th>H. Faces</th>
                                            <th>H. Views</th>
                                            <th>Flooring</th>
                                            <th>Furnishings</th>
                                            <th>Financing</th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th>DOM</th>
                                            <th>Photo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($c_properties as $row): ?>
                                        <tr>
                                            <?php foreach ($row as $col): ?>
                                            <td><?= $col ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /.row comparables -->
        <?php endif; ?>

        <!-- Property Details Extended - Description, Brokerage Info, etc. -->
        <?php if ($details->propertyInfoAdditionalBrokerageDetails): ?>
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
        <?php if ($details->propertyInfoDetails): ?>
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

    </div><!-- /#content -->
</div><!-- /#main -->

<?php
// Inline JS data for map and comparables
$this->registerJs("
var propertyData = {
    property_id: " . (int)$details->property_id . ",
    lat: " . (float)$details->getlatitude . ",
    lon: " . (float)$details->getlongitude . ",
    address: " . json_encode($details->property_street) . ",
    price: " . (int)$details->property_price . ",
    shape: " . $shape . ",
    excluded_by_shape: " . $excluded_by_shape . ",
    comparebles: " . json_encode($comparebles_properties) . "
};
", \yii\web\View::POS_HEAD);
?>
