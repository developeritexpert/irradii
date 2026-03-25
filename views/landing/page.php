<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\CPathCDN;

/* @var $this yii\web\View */
/* @var $model app\models\LandingPage */
/* @var $membershipOptions app\models\MembershipOptions */

$session = Yii::$app->session;
$uri_page = '@' . Yii::$app->request->url;
$recent_pages = 'Search' . $uri_page; // Changed from 'Landing Pages' to 'Search' to match original context
if (!isset($session['recent_pages']) || count($session['recent_pages']) == 0) {
    $session['recent_pages'] = array($recent_pages);
} else {
    $sess_arr = $session['recent_pages'];
    $sess_arr[] = $recent_pages;
    $session['recent_pages'] = $sess_arr;
}

$baseUrl = Yii::$app->request->baseUrl;
$themePath = $baseUrl . '/themes/smartadmin'; // Typical conversion for this project

$this->title = 'Search'; // Moved this line up as per original context

// The original code had $property_type_array here. Keeping it as is.
$property_type_array = array('0' => 'Unknown',
                             '1' => 'Single Family Home',
                             '2' => 'Condo',
                             '3' => 'Townhouse',
                             '4' => 'Multi Family',
                             '5' => 'Land',
                             '6' => 'Mobile Home',
                             '7' => 'Manufactured Home',
                             '8' => 'Time Share',
                             '9' => 'Rental',
                             '16' => 'High Rise');

if (!Yii::$app->user->isGuest) {
    echo $this->render('/layouts/aside', ['profile' => $profile]);
}
if (Yii::$app->user->isGuest) {
    $guest = 0;
} else {
    $guest = 1;
}
//var_dump($membershipOptions);die();
?>
<style>
    .flex-grid {
        display: flex;
        justify-content: space-between;
    }
    .member-box-front {
        text-align: center;
    }
    .flex-grid .member-box-front {
        width: 25%;
        margin: 0 8%;
    }
    @media (max-width: 400px) {
        .flex-grid {
            display: block;
        }
        .flex-grid-thirds .member-box-front {
            width: 100%;
        }
    }
    .member-box-front > header {
        height: 150px;
        border-radius: 15px 15px 0 0;
    }
    .member-box-front > .price-block {
        background-color: #000;
        height: 50px;
    }
    .member-box-front > header h3{
        text-align: center;
        padding-top: 20%;
        color: #fff;
    }
    .member-box-front > .price-block h4{
        text-align: center;
        padding-top: 3%;
        color: #fff;
    }
    .member-box-front > .features {
        background-color: rgba(194, 207, 197, 0.6);
        min-height: 200px;
    }
    .member-box-front > .features p {
        padding: 10px 0;
    }
    .member-box-front > .features p:first-child{
        padding-top: 30px;
    }
    .member-box-front > .features p:last-child{
        padding-bottom: 0;
        margin-bottom: 0;
    }
    .member-box-front > .register-area {
        background-color: rgba(169, 176, 171, 0.6);
        min-height: 75px;
        border-radius: 0 0 15px 15px;
    }
    .member-box-front .register-members {
        padding: 15px;
        text-align: center;
        margin-top: 12px;
        width: 60%;
    }
    .member-box-front.first > header{
        background-color: <?php echo $membershipOptions['first_color']; ?>;
    }
    .member-box-front.second > header{
        background-color: <?php echo $membershipOptions['second_color']; ?>;
    }
    .member-box-front.third > header{
        background-color: <?php echo $membershipOptions['third_color']; ?>;
    }
</style>
<div id="main" role="main">
    <div id="content">
        <section id="widget-grid" class="">
            <div class="row">
                <?php echo $postTop->content; ?>
            </div>

            <div class="row" id="search_list_block">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    <div class="jarviswidget jarviswidget-color-white" id="wid-id-0M" data-widget-editbutton="false" data-widget-deletebutton="false" data-widget-colorbutton="false" data-widget-sortable="true">
                        <header>
                            <span class="widget-icon"> <i class="fa fa-map-marker"></i> </span>
                            <h2>Results Map</h2>
                            <div class="btn-group pull-right">
                                <button type="button" class="btn btn-default" id="search_list" title="List View">
                                    <i class="fa fa-list-ul"></i> List
                                </button>
                                <button type="button" class="btn btn-default" id="search_map" title="Map View">
                                    <i class="fa fa-globe"></i> Map
                                </button>
                                <!--                                <button type="button" class="btn btn-default" title="Photo Gallery">
                                                                    <i class="fa fa-th"></i> Gallery
                                                                </button>-->
                            </div>
                        </header>
                        <div>
                            <div class="jarviswidget-editbox"></div>
                            <div class="widget-body no-padding mobile-wrapper">
                                <div id="map_canvas" class="google_maps">
                                    &nbsp;
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="jarviswidget jarviswidget-color-greenDark" id="wid-id-results_table33" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false">
                        <!-- widget options:
                        usage: <div class="jarviswidget" id="wid-id-0" data-widget-editbutton="false">
                        data-widget-colorbutton="false"
                        data-widget-editbutton="false"
                        data-widget-togglebutton="false"
                        data-widget-deletebutton="false"
                        data-widget-fullscreenbutton="false"
                        data-widget-custombutton="false"
                        data-widget-collapsed="true"
                        data-widget-sortable="false" -->
                        <header>
                            <span class="widget-icon"> <i class="fa fa-table"></i> </span>
                            <h2><span class="count_search_result"></span> Search Results</h2>
                        </header>
                        <div>
                            <div class="jarviswidget-editbox"></div>
                            <div class="widget-body no-padding mobile-wrapper">
                                <div class="widget-body-toolbar"></div>
                                <table class="table table-striped table-hover datatable_tabletools">
                                    <thead>
                                        <tr>
                                            <th>Weight</th>
                                            <th>Value</th>
                                            <th>Address</th>
                                            <th>Status</th>
                                            <th>List Price</th>
                                            <th>Sq. Ft.</th>
                                            <th>Beds/Baths</th>
                                            <th>Public Remarks</th>
                                            <th>List Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <div class="row" id="search_map_block">
                <div class="col-sm-8 col-md-8 col-lg-8">
                    <div class="jarviswidget jarviswidget-color-white" id="wid-id-0M2" data-widget-editbutton="false" data-widget-deletebutton="false" data-widget-colorbutton="false" data-widget-sortable="true">
                        <!-- widget options:
                                usage: <div class="jarviswidget" id="wid-id-0" data-widget-editbutton="false">

                                data-widget-colorbutton="false"
                                data-widget-editbutton="false"
                                data-widget-togglebutton="false"
                                data-widget-deletebutton="false"
                                data-widget-fullscreenbutton="false"
                                data-widget-custombutton="false"
                                data-widget-collapsed="true"
                                data-widget-sortable="false"

                        -->
                        <header>
                            <span class="widget-icon"> <i class="fa fa-map-marker"></i> </span>
                            <h2>Results Map</h2>
                            <div class="btn-group pull-right">
                                <button type="button" class="btn btn-default" id="search_list" title="List View">
                                    <i class="fa fa-list-ul"></i> List
                                </button>
                                <button type="button" class="btn btn-default" id="search_map" title="Map View">
                                    <i class="fa fa-globe"></i> Map
                                </button>
                            </div>
                        </header>
                        <div>
                            <div class="jarviswidget-editbox">
                                <!-- This area used as dropdown edit box -->
                            </div>
                            <div class="widget-body no-padding mobile-wrapper" style="height:750px">
                                <div id="map_canvas2" class="google_maps2">
                                    &nbsp;
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4 col-md-4 col-lg-4">
                    <div class="jarviswidget jarviswidget-color-greenDark" id="wid-id-results12" data-widget-editbutton="false" data-widget-deletebutton="false" data-widget-colorbutton="false">
                        <!-- widget options:
                        usage: <div class="jarviswidget" id="wid-id-0" data-widget-editbutton="false">

                        data-widget-colorbutton="false"
                        data-widget-editbutton="false"
                        data-widget-togglebutton="false"
                        data-widget-deletebutton="false"
                        data-widget-fullscreenbutton="false"
                        data-widget-custombutton="false"
                        data-widget-collapsed="true"
                        data-widget-sortable="false"

                        -->
                        <header>
                            <span class="widget-icon"> <i class="fa fa-table"></i> </span>
                            <h2><span class="count_search_result"></span> Search Results</h2>
                        </header>
                        <div>
                            <div class="jarviswidget-editbox">
                                <!-- This area used as dropdown edit box -->
                            </div>
                            <div class="widget-body no-padding mobile-wrapper" style="height:750px; overflow-y: scroll;">
                                <div class="widget-body-toolbar">
                                    <select class="select2 sort-type" name="sorting_param">
                                        <option value="0" selected>Relevant</option>
                                        <option value="3">List Price</option>
                                        <option value="4">TMV</option>
                                        <option value="5">% Below Value</option>
                                        <option value="6">Estimated Equity</option>
                                        <option value="7">Full Address</option>
                                        <option value="8">Last Updated Date</option>
                                        <option value="9">Viewer Status</option>
                                    </select>
                                    <div style="clear:both"></div>
                                </div>
                                <table  class="table table-striped table-hover datatable_tabletools2">
                                    <thead>
                                    <tr>
                                        <th>Weight</th>
                                        <th>Sorting by: <span class="sort-type-desc">Relevant</span></th>
                                        <th>List Date</th>
                                        <th>List Price</th>
                                        <th>TMV</th>
                                        <th>% Below Value</th>
                                        <th>Estimated Equity</th>
                                        <th>Full Address</th>
                                        <th>Last Updated Date</th>
                                        <th>Viewer Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="row">
            <?php echo $postBottom->content; ?>
        </div>
        <div class="flex-grid">
            <div class="member-box-front first">
                <header>
                    <h3><?php echo $membershipOptions['first_title']; ?></h3>
                </header>
                <div class="price-block">
                    <h4><?php echo $membershipOptions['first_price']; ?></h4>
                </div>
                <div class="features">
                    <?php
                    $features = explode("|", $membershipOptions['first_text']);
                    foreach ($features as $feature) {
                        echo '<p>' . $feature . '</p>';
                    }
                    ?>
                </div>
                <div class="register-area">
                    <?php echo CHtml::link('REGISTER',
                        CController::createUrl('user/registration'),
                        array('mo'=>'first',
                              'class'=>'register-members btn btn-primary btn-default',
                        )
                    )?>
                </div>
            </div>
            <div class="member-box-front second">
                <header>
                    <h3><?php echo $membershipOptions['second_title']; ?></h3>
                </header>
                <div class="price-block">
                    <h4><?php echo $membershipOptions['second_price']; ?></h4>
                </div>
                <div class="features">
                    <?php
                    $features = explode("|", $membershipOptions['second_text']);
                    foreach ($features as $feature) {
                        echo '<p>' . $feature . '</p>';
                    }
                    ?>
                </div>
                <div class="register-area">
                    <?php echo CHtml::link('REGISTER',
                        CController::createUrl('user/registration'),
                        array('mo'=>'first',
                              'class'=>'register-members btn btn-primary btn-default',
                        )
                    )?>
                </div>
            </div>
            <div class="member-box-front third">
                <header>
                    <h3><?php echo $membershipOptions['third_title']; ?></h3>
                </header>
                <div class="price-block">
                    <h4><?php echo $membershipOptions['third_price']; ?></h4>
                </div>
                <div class="features">
                    <?php
                    $features = explode("|", $membershipOptions['third_text']);
                    foreach ($features as $feature) {
                        echo '<p>' . $feature . '</p>';
                    }
                    ?>
                </div>
                <div class="register-area">
                    <?php echo CHtml::link('REGISTER',
                        CController::createUrl('user/registration'),
                        array('mo'=>'first',
                              'class'=>'register-members btn btn-primary btn-default',
                        )
                    )?>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="loader_img">
    <img src="<?php echo CPathCDN::baseurl( 'img' ); ?>/img/ajax-loader.gif" alt="">
</div>

<?php

Yii::app()->clientScript->registerScript("MapBoundaryVar", "
var mapBoundaries = [];
        ", CClientScript::POS_END);

if(isset($general_search_fields['map_boundary'])){
    $mapBoundaryObject = $general_search_fields['map_boundary'];
    $mapBoundaryClass = get_class($general_search_fields['map_boundary']);

    switch($mapBoundaryClass){

        case 'MapBoundaryCircle': // https://developers.google.com/maps/documentation/javascript/examples/circle-simple
            Yii::app()->clientScript->registerScript("MapBoundaryCircle", "
                var circle = new google.maps.Circle({
                    strokeColor: '#000000',
                    strokeOpacity: 0.3,
                    strokeWeight: 2,
                    fillColor: '#000000',
                    fillOpacity: 0.2,
                    center: new google.maps.LatLng(".$mapBoundaryObject->getCenter()->getStringPresentation()."),
                    radius: ".$mapBoundaryObject->getRadius()."
                })
                mapBoundaries.push(circle);
                coordinates_filter = getCircleCoordinatesFilter(circle);
            ", CClientScript::POS_END);
            break;

        case 'MapBoundaryPolygon': // https://developers.google.com/maps/documentation/javascript/examples/polygon-arrays

            $points = $mapBoundaryObject->getPoints();

            Yii::app()->clientScript->registerScript("MapBoundaryPolygonCoords", "var polygonCoords = [];", CClientScript::POS_END);

            foreach($points as $key=>$point){
                Yii::app()->clientScript->registerScript("MapBoundaryPolygonCoord".$key, "polygonCoords.push(new google.maps.LatLng(".$point->getStringPresentation()."));", CClientScript::POS_END);
            }

            foreach($points as $key=>$point){
                Yii::app()->clientScript->registerScript("MapBoundaryPolygon", "
                    var polygon = new google.maps.Polygon({
                        paths: polygonCoords,
                        strokeColor: '#000000',
                        strokeOpacity: 0.3,
                        strokeWeight: 3,
                        fillColor: '#000000',
                        fillOpacity: 0.2
                    });
                    
                    mapBoundaries.push(polygon);
                    coordinates_filter = getPolygonCoordinatesFilter(polygon);
                ", CClientScript::POS_END);
            }
            break;

        case 'MapBoundaryRectangle':// https://developers.google.com/maps/documentation/javascript/examples/rectangle-simple
            Yii::app()->clientScript->registerScript("MapBoundaryRectangleBounds", "
                var rectangleBounds = new google.maps.LatLngBounds(
                      new google.maps.LatLng(".$mapBoundaryObject->getLeftTopPoint()->getStringPresentation()."),
                      new google.maps.LatLng(".$mapBoundaryObject->getRightBottomPoint()->getStringPresentation().")
                )
            ", CClientScript::POS_END);

            Yii::app()->clientScript->registerScript("MapBoundaryRectangle", "
                var rectangle = new google.maps.Rectangle({
                    strokeColor: '#000000',
                    strokeOpacity: 0.3,
                    strokeWeight: 2,
                    fillColor: '#000000',
                    fillOpacity: 0.2,
                    bounds: rectangleBounds
                });
                mapBoundaries.push(rectangle);
                
                coordinates_filter = getRectangleCoordinatesFilter(rectangle);
            ", CClientScript::POS_END);
            break;
    }// end switch
}

$searchResultSmallQuery = $search_results ? json_encode($search_results) : json_encode(array("count_result"=>0,"result"=>array(),"status"=>"failed"));

Yii::app()->clientScript->registerScript("main", "
            var dataSearchResultSmallQuery = " . $searchResultSmallQuery . ";

            var map;
            var mapReady = false;
            var geolocation;
            var drawingManager;
            var selectedShape;
            var coordinates_filter;
            var bounds = new google.maps.LatLngBounds();
            var marker_on_popup;
            var map2;
            var drawingManager2;
            var selectedShape2;
            var bounds2 = new google.maps.LatLngBounds();
            var isEquityDeal = 0;
            var search_results;
            
            
            function setAddressFields(){
                var a = $('#autocomplete').val();
                if(!a){
                    $('#street_number, #route, #locality, #administrative_area_level_1, #postal_code, #country').val('');
                }
            }

            function getCircleCoordinatesFilter(circle){
                var center = circle.getCenter();
                var lat = center.lat();
                var lon = center.lng();
                var radius = circle.getRadius();
                return '&geodistance_circle=' + 1 + '&latitude=' + lat + '&longitude=' + lon + '&radius=' + radius;
            }

            function getRectangleCoordinatesFilter(rectangle){
                var coordinates = rectangle.getBounds();
                var lat2 = coordinates.getNorthEast().lat();
                var lon2 = coordinates.getNorthEast().lng();
                var lat1 = coordinates.getSouthWest().lat();
                var lon1 = coordinates.getSouthWest().lng();

                return '&geodistance_rectangle=' + 1 + '&latitude1=' + lat1 + '&latitude2=' + lat2 + '&longitude1=' + lon1 + '&longitude2=' + lon2;
            }

            function getPolygonCoordinatesFilter(polygon){
                var filter = '';
                filter += '&geodistance_polygon=' + 1;
                polygon.getPaths().forEach(function(e){
                    e.getArray().forEach(function(el){
                        filter += '&latitude%5B%5D=' + el.lat() + '&longitude%5B%5D=' + el.lng();
                    });
                });

                return filter;
            }

            function saveDataForSearch() {
                data = $('#main_search_form').serialize();
                if (typeof(Storage) !== 'undefined') {
                    sessionStorage.form = data;
                    sessionStorage.address = $('.address').val();
                    sessionStorage.sale_type = $('.sale_type').val();
                    sessionStorage.property_type = $('.property_type').val();
                    sessionStorage.keywords = $('.keywords').val();
                    sessionStorage.min_price_sqft = $('.min_price_sqft').val();
                    sessionStorage.min_sqft = $('.min_sqft').val();
                    sessionStorage.min_year_built = $('.min_year_built').val();
                    sessionStorage.stories = $('.stories').val();
                    sessionStorage.max_price_sqft = $('.max_price_sqft').val();
                    sessionStorage.max_sqft = $('.max_sqft').val();
                    sessionStorage.max_year_built = $('.max_year_built').val();
                    sessionStorage.garage = $('.garage').val();
                    sessionStorage.min_price = $('.min_price').val();
                    sessionStorage.bed = $('.bed').val();
                    sessionStorage.min_lot_size = $('.min_lot_size').val();
                    sessionStorage.pool = $('.pool').val();
                    sessionStorage.max_price = $('.max_price').val();
                    sessionStorage.bath = $('.bath').val();
                    sessionStorage.max_lot_size = $('.max_lot_size').val();
                    sessionStorage.bmarket = $('.bmarket').val();
//                    data.forEach(function(el, index){
//                        sessionStorage.index = el;
//                        console.log(el, index);
//                    });
                }
                console.log(sessionStorage)
            }

            function clickSearchButton(){
                makeSearch();
            }


            function makeSearch(){
                setAddressFields();
                $('#loader_img').show();
                var this_form = '';
                this_form = $('#main_search_form').serialize();
                if(coordinates_filter){
                    this_form += coordinates_filter;
                }
                isEquityDeal = this_form.indexOf('Equity+Deals') + 1;

                setFiltersString();
                $.ajax({
                    url: '/property/search',
                    type: 'POST',
                    data: this_form,
                    dataType: 'json',
                    cache: false,
                    success: function(data){
                        $('#loader_img').hide();
                        dataSearchResultSmallQuery = data;
                        getSearchResult(data);
                        addResponsive();

                    },
                    error:  function(xhr, str){
//                        console.log('error: ' + xhr.responseCode);
                    }
                });
            }


            function setSelection(shape) {

                selectedShape = shape;
                selectedShape2 = shape;
                shape.setEditable(true);
            }

            function deleteSelectedShape() {
                if (selectedShape) { selectedShape.setMap(null);   }
                if (selectedShape2){ selectedShape2.setMap(null);  }
                coordinates_filter = '';
                selectedShape = null;
            }

            function initialize() {
                var mapOptions = {
                    zoom: 12,
                    minZoom: 2
                };
                map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
                var mapOptions2 = {
                    zoom: 12,
                    minZoom: 2
                };
                map2 = new google.maps.Map(document.getElementById('map_canvas2'), mapOptions2);


               for (key in mapBoundaries) {
                    if (mapBoundaries.hasOwnProperty(key)) {

                        // clone objects
                        var shape1 = jQuery.extend(true, {}, mapBoundaries[key]);
                        var shape2 = jQuery.extend(true, {}, mapBoundaries[key]);

                        shape1.setMap(map);
                        shape2.setMap(map2);

                        selectedShape = shape1;
                        selectedShape2 = shape2;


                    }
                }
                $('.detail-pop-up').on('mousewheel', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if(e.originalEvent.wheelDelta / 120 > 0) {
                        $('#detail-pop-up-carousel').carousel('prev');
                    } else {
                        $('#detail-pop-up-carousel').carousel('next');
                    }
                });

                var markersLoadedOnStartup = false;
                google.maps.event.addListener(map, 'idle', function(){

                    mapReady = true;
                    if(!markersLoadedOnStartup){
                        if((dataSearchResultSmallQuery.status == 'nothing') ||
                           (dataSearchResultSmallQuery.status == 'success') ){
                            getSearchResult(dataSearchResultSmallQuery);
                            setFiltersString();
                            addResponsive();
                       }
                       markersLoadedOnStartup = true;
                    }
                });
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        var pos = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                        map.setCenter(pos);
                        map2.setCenter(pos);
                    }, function() {
                        handleNoGeolocation(map);
                        handleNoGeolocation(map2);
                    });
                } else {
                    // Browser doesn't support Geolocation
                    handleNoGeolocation(map);
                    handleNoGeolocation(map2);
                }
                drawingManager = new google.maps.drawing.DrawingManager();
                drawingManager2 = new google.maps.drawing.DrawingManager();
                search_fld = new google.maps.places.Autocomplete(document.getElementById('search-fld'),{ types: ['geocode'] });
                google.maps.event.addListener(search_fld, 'place_changed', function() {
                  fillInAddressSearchFld();
                });
                autocomplete = new google.maps.places.Autocomplete(document.getElementById('autocomplete'),{ types: ['geocode'] });

                google.maps.event.addListener(autocomplete, 'place_changed', function() {
                  fillInAddress();
                });
                bounds = new google.maps.LatLngBounds();
                bounds2 = new google.maps.LatLngBounds();
            }



            function handleNoGeolocation(map) {
                var options = {
                  map: map,
                  position: new google.maps.LatLng(36.175, -115.1363889),
                };
                map.setCenter(options.position);
            }


            function setAllMap(map) {
                for (var i = 0; i < markers.length; i++) {
                    google.maps.event.addListener(markers[i], 'mouseover', function() {
                        var ttl = this.title;
                        $('.title-wraper-block').each(function() {
                            if ($.trim($(this).find('a').text()) == ttl) {
                                $(this).parent().parent().find('td').each(function() {
                                    $(this).css('border-bottom', '3px solid #60747C');
                                    $(this).css('border-top', '3px solid #60747C');
                                });
                            }
                        });
                    });
                    google.maps.event.addListener(markers[i], 'mouseout', function() {
                        var ttl = this.title;
                        $('.title-wraper-block').each(function() {
                            if ($.trim($(this).find('a').text()) == ttl) {
                                $(this).parent().parent().find('td').each(function() {
                                    $(this).css('border-bottom', '1px solid #dddddd');
                                    $(this).css('border-top', '1px solid #dddddd');
                                });
                            }
                        });
                    });
                    markers[i].setMap(map);
                }

            }



            function setAllMap2(map2) {

                for (var i = 0; i < markers2.length; i++) {
                    google.maps.event.addListener(markers2[i], 'mouseover', function() {
                        var ttl = this.title;
                        $('.title-wraper-block').each(function() {
                            if ($.trim($(this).find('a').text()) == ttl) {
                                $(this).parent().parent().find('td').each(function() {
                                    $(this).css('border-bottom', '3px solid #60747C');
                                    $(this).css('border-top', '3px solid #60747C');
                                });
                            }
                        });
                    });
                    google.maps.event.addListener(markers2[i], 'mouseout', function() {
                        var ttl = this.title;
                        $('.title-wraper-block').each(function() {
                            if ($.trim($(this).find('a').text()) == ttl) {
                                $(this).parent().parent().find('td').each(function() {
                                    $(this).css('border-bottom', '1px solid #dddddd');
                                    $(this).css('border-top', '1px solid #dddddd');
                                });
                            }
                        });
                    });
                    markers2[i].setMap(map2);
                }

            }



            var dTable;
            var dTable2;
            var markers = [];
            var markers2 = [];
            var latLon_arr = [];
            var search_map_results;

function getPathImages(){
    var a_path_cdn = '" . CPathCDN::baseurl( 'images' ) ."';
        if(a_path_cdn === '') {
            if (!window.location.origin) { // IE
                return window.location.protocol + '//' + window.location.hostname + (window.location.port ? ':' + window.location.port: '');
            } else {
                return window.location.origin ;
            }
        } else {
            return a_path_cdn;
        }
}
            function setMarkersArray(latLon_arr){
//                if (!window.location.origin) { // IE
//                    var a_path = window.location.protocol + '//' + window.location.hostname + (window.location.port ? ':' + window.location.port: '');
//                } else {
//                    var a_path = window.location.origin ;
//                }
                var a_path = getPathImages();
                markers = [];
                
                for(var i = 0; i < latLon_arr.length; i++){
                    for(var key in latLon_arr[i]){
                        
                        if( ( ( latLon_arr[i][key].lat == '0.000000' ) || ( latLon_arr[i][key].lat == '' )  ) &&
                            ( ( latLon_arr[i][key].lon == '0.000000' ) || ( latLon_arr[i][key].lon == '' ) ) ){
                            continue;
                        }
                        position = new google.maps.LatLng(latLon_arr[i][key].lat, latLon_arr[i][key].lon);
                        
                        var status = latLon_arr[i][key].status.toLowerCase();
                        
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
                        if(isEquityDeal !== 0 && status == 'active-exclusive right' || isEquityDeal !== 0 && status == 'active'){
                            image = new google.maps.MarkerImage(a_path + '/images/map-icons/green.png', null, null, new google.maps.Point(0, 34),new google.maps.Size(35, 40));
                        }
                        
                        
  
                        var marker = new google.maps.Marker({
                                position: position,
                                map: map,
                                icon: image,
                                property:latLon_arr[i][key].property_id,
                                title: latLon_arr[i][key].address
                        });
                        marker.addListener('click', toggleDetailPopup);        
                        markers.push(marker);
                        bounds.extend(position);
                    }
                }
                setAllMap(null);
                if(latLon_arr[0].length != 0){
                    setAllMap(map);
                } else {
                    setAllMap(map2);
                }
                propertyShowerInit(markers);
                if(!bounds.isEmpty()) {
                    map.fitBounds(bounds);
                }
            }
/*POPUP WORKER START*/
            $('.detail-pop-up .close').on('click', function() {
                hideDetailPopup();
            });
            
            function toggleDetailPopup() {
                var marker = this;
                if (marker && marker_on_popup && marker === marker_on_popup) {
                    hideDetailPopup();
                    return;
                }
                hideDetailPopup();
                showDetailPopup(marker);
                updatePopupPosition(marker);
            }
            
            function hideDetailPopup() {
                var searchMarkerPopup = $('.detail-pop-up'),
                    searchMarkerCarousel = searchMarkerPopup.find('#detail-pop-up-carousel');
        
                marker_on_popup = null;
        
                searchMarkerCarousel.find('.carousel-inner').text('');
                searchMarkerPopup
                    .find('.img-container')
                    .text('')
                    .append(searchMarkerCarousel);
        
                searchMarkerPopup.find('.price').text('');
                searchMarkerPopup.find('.label-container').text('');
                searchMarkerPopup.find('.street').text('');
                searchMarkerPopup.find('.exclude-btn').text('');
                searchMarkerPopup.find('.city').text('');
                searchMarkerPopup.find('.subdivision').text('');
                searchMarkerPopup.find('.type').text('');
                searchMarkerPopup.find('.metrics').text('');
                searchMarkerPopup.find('.popup-tmv-price').text('');
                searchMarkerPopup.find('.popup-tmv').text('');
        
                $('.detail-pop-up').css('display', 'none');

            }
            
            function showDetailPopup(marker) {
            
                if($('#search_map_block').is(':visible')){
                    var rows$ = $('#search_map_block').find('tbody tr');
                    var map = '#map_canvas2';
                } else {
                    var rows$ = $('#search_list_block').find('tbody tr');
                    var map = '#map_canvas';
                }

                var prop_id = marker.property,
                    is_main_prop = false,
                    row$,
                    firstPhoto$,
                    img$,
                    label$,
                    price,
                    street,
                    sqFoots,
                    acre,
                    beds,
                    baths,
                    exclude_btn = '';
        
                marker_on_popup = marker;
                
                if (typeof prop_id === 'undefined') {
                    is_main_prop = true;
                    rows$.each(function(index, element) {
                        if ($(element).find('.exclude_reinclude').length === 0) {
                            row$ = $(element);
                        }
                    });
                } else {
                    rows$.each(function(index, element) {
                            if ($(element).find('.exclude-reinclude').data('property_id') === prop_id) {
                                row$ = $(element);
                            } 
                    })
                }
                img$ = row$.find('img');
                firstPhoto$ = $('<div>').addClass('item active');
                firstPhoto$.append($('<img>', {
                            src: img$.attr('src'),
                            alt: img$.attr('alt')
                        }));
                        
                if($('#search_map_block').is(':visible')){
                
                    var targetProperty = row$.find('td .property_info_row_map'),
                    street = targetProperty.data('address'),
                    label$ = row$.find('.status-label .label').clone(),
                    price = row$.find('.price').clone();
                    
                } else {
                    label$ = row$.find('td').eq(2).clone();

                    price = row$.find('td').eq(3).html().split('<br>')[0];
                    street = row$.find('td').eq(1).html();
        
                    if (is_main_prop !== true) {
                        exclude_btn = row$.find('.exclude_reinclude').clone();
                        exclude_btn.on('click', function() {
                            hideDetailPopup();
                            row$.find('.exclude_reinclude').trigger('click');
                        });
        
                    $('.detail-pop-up')
                        .find('.exclude-btn')
                        .append('<span>Exclude from Comps&nbsp</span>');
                    }
                }
                хай
        
                $('.detail-pop-up')
                    .find('.carousel-inner')
                        .append(firstPhoto$)
                    .end()
                    .find('.label-container')
                        .append(label$)
                    .end()
                    .find('.price')
                        .html(price)
                    .end()
                    .find('.exclude-btn')
                        .append(exclude_btn)
                    .end()
                    .fadeIn()
                    .appendTo(map);
     
                $.ajax({
                    url: '/property/getcomppropertydetails',
                    data: {property_id: prop_id},
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    success: function(data){
                        $('#detail-pop-up-carousel').carousel('pause');
                        
                        $('.detail-pop-up')
                            .find('.link-container')
                                .attr('href',data['url'])
                            .end()
                            .find('.street')
                                .text(data['property_street'])
                            .end()
                            .find('.city')
                                .text(data['city'])
                            .end()
                            .find('.subdivision')
                                .text(data['subdivision'])
                            .end()
                            .find('.type')
                                .text(data['type'])
                            .end()
                            .find('.metrics')
                                .html(data['metrics'])
                            .end()
                            .find('.img-container')
                                .append(data['discont'])
                            .end()
                            .find('.img-container')
                                .attr('href',data['url'])
                            .end()
                            .find('.carousel-inner')
                                .append(data['carousel'])
                            .end();
                            
                            $('.detail-pop-up .show-in-table').attr('property_id', data['property_id']);
                         if(data['tmv'].length > 0){
                            $('.detail-pop-up')
                            .find('.popup-tmv')
                                .append('TMV:')
                            .end()
                            .find('.popup-tmv-price')
                                .append(data['tmv'])
                            .end();
                         }    
        
                        updatePopupPosition(marker_on_popup);
                    },
                    error: function(data){
//                            console.log('error: ',data);
                    }
                });
            }
            
            function updatePopupPosition(marker) {
                var target_map = $('#search_map_block').is(':visible') ? map2 : map;
                var minTop = 0,
                    minLeft = 0,
                    markerHeight = 40,
                    popup$ = $('.detail-pop-up'),
                    popupHeight = popup$.height(),
                    popupWidth = popup$.width(),
                    topRight = target_map.getProjection().fromLatLngToPoint(target_map.getBounds().getNorthEast()),
                    bottomLeft = target_map.getProjection().fromLatLngToPoint(target_map.getBounds().getSouthWest()),
                    scale = Math.pow(2, target_map.getZoom()),
                    worldPoint = target_map.getProjection().fromLatLngToPoint(marker.getPosition()),
                    markerCoords = new google.maps.Point((worldPoint.x - bottomLeft.x) * scale, (worldPoint.y - topRight.y) * scale),
                    top = (markerCoords['y'] < popupHeight + markerHeight) ? 0 : (markerCoords['y'] - popupHeight - markerHeight),
                    left = (markerCoords['x'] < popupWidth) ? 0 : (markerCoords['x'] - popupWidth);
                popup$.css({
                    top: top,
                    left: left
                });
            }
     
     
/*POPUP WORKER END*/
/*Binding properties MAP<->TABLE START*/

       
            $('.show-in-table').on('click',function(e){
                e.preventDefault();
                var id = $(this).attr('property_id');
        
                showPropertyInTable(id);
            });
            
           function showinmap(el){
                var id = $(el).attr('property_id');
                
                $.each(markers, function() {
                   if (this.property == id) {
                      this.setAnimation(google.maps.Animation.BOUNCE);
                   } else {
                      this.setAnimation(null);
                   }
                });
                scroll = $('#search_list_block').offset().top;
                $('html, body').animate(
                    {scrollTop: scroll},
                    1000,
                    'easeOutQuart'
                );
                return false;
            };
              function showPropertyInTable(id){
                if($('#search_map_block').is(':visible')){
                    var targetRow = $('#search_map_block')
                    .find('[data-property_id=\"'+id + '\"]')
                    .closest('tr'),
                    targetBody = targetRow.closest('.widget-body'),
                    scroll = targetRow.closest('tbody').scrollTop() + targetRow.position().top;   
                } else {
                $('#search_list_block').find('.table').addClass('blur');
                    var targetRow = $('#search_list_block')
                    .find('[data-property_id=\"'+id + '\"]')
                    .closest('tr'),
                    targetBody = $('html, body'),
                    scroll = targetRow.offset().top - $( window ).height()/2;
                    targetRow.addClass('show-row');
                    setTimeout(function(){
                        $('#search_list_block').find('.table').removeClass('blur');
                        targetRow.removeClass('show-row');
                    },3000);
                }
                targetBody.animate(
                    {scrollTop: scroll},
                    1000,
                    'easeOutQuart'
                );
            }
            function propertyShowerInit(map){
                var targetRow = $('#search_map_block tbody tr').find('.property_info_row_map').closest('tr');
                
                targetRow.hover(function(){
                    var id = $(this).find('.property_info_row_map').data('property_id');
                    $.each(markers, function() {
                        if (this.property == id) {
                            this.setAnimation(google.maps.Animation.BOUNCE);
                        } else {
                            this.setAnimation(null);
                        }
                    });
                    
                })
            }
/*Binding properties END*/

            function toggleEllapse(el){
            var block = $(el.closest('.public-remarks'))
                block.toggleClass('ellipsed');
                if(block.hasClass('ellipsed')){
                    $(el).text('Read more')
                } else {
                    $(el).text('Hide')
                }
            }
            function getDataCurrentPage(data){
                latLon_arr = [];
                latLon_arr[0] = [];
                latLon_arr[1] = [];
                data.find('.property_info_row').each(function(){
                    var arr = {};
                    arr.lat = $(this).data('lat');
                    arr.lon = $(this).data('lon');
                    arr.address = $(this).data('address');
                    arr.status = $(this).data('status');
                    arr.property_id = $(this).data('property_id');
                    latLon_arr[0].push(arr);
                    delete arr;
                });
                data.find('.property_info_row_map').each(function(){
                var arr2 = {};
                    arr2.lat = $(this).data('lat');
                    arr2.lon = $(this).data('lon');
                    arr2.address = $(this).data('address');
                    arr2.status = $(this).data('status');
                    arr2.property_id = $(this).data('property_id');
                    latLon_arr[1].push(arr2);
                    delete arr2;
                });

                setMarkersArray(latLon_arr);
            }

            function resetDataTableList(){
                setAllMap(null);
                dTable = $('.datatable_tabletools').dataTable().fnDestroy();
                dTable = $('.datatable_tabletools').dataTable({
                    'sDom' : '<\'dt-top-row\'Tlf>r<\'dt-wrapper\'t><\'dt-row dt-bottom-row\'<\'row\'<\'col-sm-6\'i><\'col-sm-6 text-right\'p>>',
                    'oTableTools' : {
                            'aButtons' : ['copy', 'print', {
                                    'sExtends' : 'collection',
                                    'sButtonText' : 'Save <span class=\"caret\" />',
                                    'aButtons' : ['csv', 'xls', 'pdf']
                            }],
                            'sSwfPath' : '" . CPathCDN::baseurl( 'js' ) ."/js/plugin/datatables/media/swf/copy_csv_xls_pdf.swf'
                    },
                    'iDisplayLength':100,
                    'aaData': search_results,
                    'bDeferRender': true,
                    'bAutoWidth': false,
                    'fnDrawCallback': function (oSettings) {
                        var current_page = this.fnPagingInfo().iPage+1;
                        setAllMap(null);
                        getDataCurrentPage($(this));

                        if(typeof oSettings.oFeatures.bAutoWidth != undefined && oSettings.oFeatures.bAutoWidth == false)
                        {
                            $(this).attr('style', 'width:100%;');
                        }
                     },
                     'aoColumns': [
                            { 'bVisible': false, 'sType': 'natural' },
                            { 'sType': 'num-html' },
                            { 'sType': 'natural' },
                            { 'sWidth': '10%', 'sType': 'natural' },
                            { 'sWidth': '10%', 'sType': 'formatted-num' },
                            { 'sWidth': '10%', 'sType': 'natural' },
                            { 'sWidth': '10%', 'sType': 'natural' },
                            { 'sType': 'natural' },
                            { 'sWidth': '10%'}
                     ],
                     
                     'aaSorting': [[ 3, 'desc' ]],

                    'fnInitComplete' : function(oSettings, json) {
                            $(this).closest('#dt_table_tools_wrapper').find('.DTTT.btn-group').addClass('table_tools_group').children('a.btn').each(function() {
                                    $(this).addClass('btn-sm btn-default');
                            });
                    }
                });

            }
            
            function resetDataTableMap(){
                setAllMap(null);
                dTable2 = $('.datatable_tabletools2').dataTable().fnDestroy();
                    dTable2 = $('.datatable_tabletools2').dataTable({
                        'iDisplayLength':100,
                        'sDom':'t',
                        'aaData': search_map_results,
                        'bDeferRender': true,
                        'bAutoWidth': false,
                        'fnDrawCallback': function (oSettings) {
                                        var current_page = this.fnPagingInfo().iPage+1;
                                        setAllMap(null);
                                        getDataCurrentPage($(this));
                                    if(typeof oSettings.oFeatures.bAutoWidth != undefined && oSettings.oFeatures.bAutoWidth == false)
                                    {
                                        $(this).attr('style', 'width:100%;');
                                    }
                        },
                        'bSort':false,
                        'aoColumns': [
                           { 'bVisible': false, 'sType': 'natural' },
                           { 'sType': 'num-html', 'ordering':true },
                           { 'bVisible': false, 'sType': 'natural'},
                           { 'bVisible': false, 'sType': 'natural'},
                           { 'bVisible': false, 'sType': 'natural'},
                           { 'bVisible': false, 'sType': 'natural'},
                           { 'bVisible': false, 'sType': 'natural'},
                           { 'bVisible': false, 'sType': 'natural'},
                           { 'bVisible': false, 'sType': 'natural'},
                           { 'bVisible': false, 'sType': 'natural'}
                        ],
                        'aaSorting': [[ 0, 'desc' ]],
                        'oLanguage': {
                            'sEmptyTable': 'No data available in table'
                        },
                    });
                $('#wid-id-results12 .sorting_disabled').addClass('sorting')
                $('#wid-id-results12 select.sort-type').on(\"select2-selecting\", function(e,el,t) {
                    var sortCur = dTable2.dataTable().fnSettings().aaSorting[1];
                    var sortT = $(e.target).children('[value=\"'+ e.val + '\"]').text();
                    $('#wid-id-results12 .sort-type-desc').text(sortT);
                    $('#wid-id-results12 .sorting').toggleClass('sorting_desc')
                    $('#wid-id-results12 .sorting').attr('sort-type', e.val)
                    dTable2.dataTable().fnSort([[e.val,'desc']]);
                });
                
                $('#wid-id-results12 .sorting').on(\"click\", function(e) {
                    var sortCol = dTable2.dataTable().fnSettings().aaSorting;
                    $.each(sortCol, function(k,v){
                        sortCol[k][1] = sortCol[k][1] == 'asc' ? 'desc' : 'asc';
                        if(sortCol[k][1] == 'asc'){
                            $('#wid-id-results12 .sorting').toggleClass('sorting_asc')
                            $('#wid-id-results12 .sorting').hasClass('sorting_desc') ? $('#wid-id-results12 .sorting').removeClass('sorting_desc') : false ;
                        } else {
                            $('#wid-id-results12 .sorting').toggleClass('sorting_desc');
                            $('#wid-id-results12 .sorting').hasClass('sorting_asc') ? $('#wid-id-results12 .sorting').removeClass('sorting_asc') : false ;
                        }
                    });
                    dTable2.dataTable().fnSort(sortCol);
                });
            }


            function waitMapReady(callbackWhenReady){
                setTimeout(
                    function () {
                        if (mapReady === true) {

                            if(typeof(callbackWhenReady) != 'undefined'){
                                callbackWhenReady();
                            }

                        } else {

                            //console.log('wait');
                            waitMapReady(callbackWhenReady);
                        }
                    }, 10); // wait 10 milisecond
            }

            function getSearchResult(data){

                waitMapReady(function(){
                    if(!bounds.isEmpty()) {
                        bounds = new google.maps.LatLngBounds(null);
                    }
                    if(!bounds2.isEmpty()) {
                        bounds2 = new google.maps.LatLngBounds(null)
                    }
                    search_results = '';
                    search_map_results = '';
                    $('.count_search_result').empty();
                    if(data.status.toLowerCase() == 'success' && data.count_result > 0){
                         var pos = new google.maps.LatLng(data.latlon[0], data.latlon[1]);
                         map.setCenter(pos);
                         map2.setCenter(pos);
                         $('.count_search_result').empty().html(data.count_result);
                         search_results = data.result;
                         search_map_results = data.res_map_layout;
                    } else { /*console.log(data);*/ }
                    if( $('#search_list_block').css('display') === 'block' ){
                        $('#wid-id-results_table33').fadeIn();
                        resetDataTableList();
                    }
                    if( $('#search_map_block').css('display') === 'block' ){
                        resetDataTableMap();
                    }
                });
            }

            function addResponsive(){
                $('.datatable_tabletools_wrapper .dt-wrapper').addClass('table-responsive');
            }


            function setFiltersString(){
                var filters = '';
                var filter_str = '';
                var min_price = '';
                var max_price = '';
                var min_sqft = '';
                var max_sqft ='';
                var min_year_built = '';
                var max_year_built = '';
                var min_lot_size = '';
                var max_lot_size = '';
                var filter_str_pref = '';
                filters = $('#main_search_form').serializeArray();
                $.each(filters, function(i, filter){
                    filter_str_pref = '';
                    filter_str_pref = filter_str.length > 0 ? ' / ' : '';
                    if ( (filter.name == 'address') && (filter.value.length != 0) ){
                        filter_str += filter_str_pref + filter.value;
                    }
                     if ( (filter.name == 'sale_type') && (filter.value.length != 0) && (filter.value != 0) ){
                        filter_str += filter_str_pref + filter.value;
                    }
                    if ( (filter.name == 'min_price') && (filter.value.length != 0) && (filter.value != 0) ){
                        min_price = '$' + filter.value;
                    }
                     if ( (filter.name == 'max_price') && (filter.value.length != 0) && (filter.value != 0) ){
                        max_price = '$' + filter.value;
                    }
                     if ( (filter.name == 'min_sqft') && (filter.value.length != 0) && (filter.value != 0) ){
                        min_sqft = filter.value;
                    }
                     if ( (filter.name == 'max_sqft') && (filter.value.length != 0) && (filter.value != 0) ){
                        max_sqft = filter.value;
                    }
                     if ( (filter.name == 'bed') && (filter.value.length != 0) && (filter.value != 0) ){
                        filter_str += filter_str_pref + filter.value + '+ Bed';
                    }
                     if ( (filter.name == 'bath') && (filter.value.length != 0) && (filter.value != 0) ){
                        filter_str += filter_str_pref + filter.value + '+ Bath' ;
                    }
                    if ( (filter.name == 'min_year_built') && (filter.value.length != 0) && (filter.value != 0) ){
                        var min_year_built_arr = '';
                        min_year_built_arr = filter.value.split(' ');
                        min_year_built = min_year_built_arr[1];
                    }
                    if ( (filter.name == 'max_year_built') && (filter.value.length != 0) && (filter.value != 0) ){
                        var max_year_built_arr = '';
                        max_year_built_arr = filter.value.split(' ')
                        max_year_built = max_year_built_arr[1];
                    }
                    if ( (filter.name == 'min_lot_size') && (filter.value.length != 0) && (filter.value != 0) ){
                        var min_lot_size_arr = '';
                        min_lot_size_arr = filter.value.split(' ');
                        min_lot_size = min_lot_size_arr[0];
                    }
                    if ( (filter.name == 'max_lot_size') && (filter.value.length != 0) && (filter.value != 0) ){
                        var max_lot_size_arr = '';
                        max_lot_size_arr = filter.value.split(' ');
                        max_lot_size = max_lot_size_arr[0];
                    }
                });
                if(min_price.length > 0 || max_price.length > 0){
                    filter_str += filter_str_pref + min_price + ' - ' + max_price;
                }
                if(min_sqft.length > 0 || max_sqft.length > 0){
                    filter_str += filter_str_pref + ' SqFt ' + min_sqft + ' - ' + max_sqft;
                }
                if(min_year_built.length > 0 || max_year_built.length > 0){
                    filter_str += filter_str_pref + ' Year ' + min_year_built + ' - ' + max_year_built;
                }
                if(min_lot_size.length > 0 || max_lot_size.length > 0){
                    filter_str += filter_str_pref +  min_lot_size + ' - ' + max_lot_size + ' Acre';
                }
                $('#filter-search-page').empty().html(filter_str);
            }

            function showAlert(text){
                $('.success2').empty().html(text);
                $('.success2').css('display','block');
                if($('.success2').css('display')=='block'){
                    $('.success2').fadeOut(5000);
                }
            }

            function deleteSearchResults(find_property){
                for(var i = 0; i < search_results.length; i++){
                    if(search_results[i][0].search(find_property) != -1){
                        search_results.splice(i,1);
                    }
                }
            }
            
            $('#search_list').click(function(){
                $('#search_list_block').show();
                $('#search_map_block').hide();
                
                google.maps.event.trigger(map, 'resize');
                if(search_results){
                    resetDataTableList();
                    $('#wid-id-results_table33').show();
                }       
                
            });
            
            $('#search_map').click(function(){
                $('#search_list_block').hide();
                $('#search_map_block').show();
                $('#wid-id-0M2').show();
                $('#wid-id-results12').show();
                google.maps.event.trigger(map2, 'resize');
                if(search_results){
                    resetDataTableMap();
                }
            });

            var myMap = google.maps.event.addDomListener(window, 'load', initialize);
            var searchOnLoad = " . ((isset($general_search_fields['searchOnLoad']) && $general_search_fields['searchOnLoad']=='1')? '1' :'0') . ";


", CClientScript::POS_END);




Yii::app()->clientScript->registerScript("successDestroyer", "if($('.success1').css('display')=='block'){
            $('.success1').fadeOut(5000);
        }", CClientScript::POS_END);


Yii::app()->clientScript->registerScript("searchScript", "   
       
var guest = " . $guest . ";
                   
        if(guest == 0){
            $('#main').css('margin-left', 0 );
            $('body #content').css('padding-top', '50px');
        }
        $('#save_search').on('focus',function(){
            if(guest == 0){
                 $('#alert_guest').fadeIn(200);
                 setTimeout(function(){ $('#save_search').trigger('blur'); },6000);
            }
        });
        $('#save_search_checkbox').on('click', function(){
            if($(this).prop('checked')==true){
                if(guest == 0){
                     $('#alert_guest').fadeIn(200);
                     setTimeout(function(){ $('#save_search_checkbox').prop('checked',false); },6000);
                }
            } 
        });

    ", CClientScript::POS_END);

Yii::app()->clientScript->registerScript(
    "sendAjaxRequest", " 
            var search_result;
            $('.rectangle').click(function() {                                                          //  RECTANGLE
                if($('#search_map_block').css('display') === 'block'){
                    if(selectedShape2){
                        selectedShape2.setMap(null);
                        coordinates_filter = '';
                    }

                    drawingManager2 = new google.maps.drawing.DrawingManager({
                        drawingMode: google.maps.drawing.OverlayType.RECTANGLE,
                        drawingControl: false,
                        drawingControlOptions: {
                            drawingModes: [
                                google.maps.drawing.OverlayType.RECTANGLE
                            ]
                        },
                        rectangleOptions: {
                            editable: true,
                            strokeWeight: 0
                        }
                    });
                    
                    drawingManager2.setMap(map2);
                    
                    google.maps.event.addListener(drawingManager2, 'rectanglecomplete', function(rectangle) {

                        coordinates_filter = getRectangleCoordinatesFilter(rectangle);
                        makeSearch();

                        drawingManager2.setDrawingMode(null);
                        google.maps.event.addListener(rectangle, 'click', function() {
                            setSelection(rectangle);
                        });
                        setSelection(rectangle);
                        google.maps.event.addListener(rectangle, 'bounds_changed', function(){

                            coordinates_filter = getRectangleCoordinatesFilter(rectangle);
                            makeSearch();
                        });
                    });
                }
                
                if($('#search_list_block').css('display') === 'block'){
                    if(selectedShape){
                        selectedShape.setMap(null);
                        coordinates_filter = '';
                    }

                    drawingManager = new google.maps.drawing.DrawingManager({
                        drawingMode: google.maps.drawing.OverlayType.RECTANGLE,
                        drawingControl: false,
                        drawingControlOptions: {
                            drawingModes: [
                                google.maps.drawing.OverlayType.RECTANGLE
                            ]
                        },
                        rectangleOptions: {
                            editable: true,
                            strokeWeight: 0
                        }
                    });
                    drawingManager.setMap(map);
                    google.maps.event.addListener(drawingManager, 'rectanglecomplete', function(rectangle) {

                        coordinates_filter = getRectangleCoordinatesFilter(rectangle);
                        makeSearch();

                        drawingManager.setDrawingMode(null);
                        google.maps.event.addListener(rectangle, 'click', function() {
                            setSelection(rectangle);
                        });
                        setSelection(rectangle);
                        google.maps.event.addListener(rectangle, 'bounds_changed', function(){

                            coordinates_filter = getRectangleCoordinatesFilter(rectangle);
                            makeSearch();
                        });
                    });
                }
            });
            


            $('.radius').click(function() {                                                             // RADIUS
                if( selectedShape ){
                    selectedShape.setMap(null);
                    coordinates_filter = '';
                }
                if( selectedShape2 ){
                    selectedShape2.setMap(null);
                    coordinates_filter = '';
                }

                drawingManager = new google.maps.drawing.DrawingManager({
                    drawingMode: google.maps.drawing.OverlayType.CIRCLE,
                    drawingControl: false,
                    drawingControlOptions: {
                        drawingModes: [
                            google.maps.drawing.OverlayType.CIRCLE
                        ]
                    },
                    circleOptions: {
                        strokeWeight: 0,
                        editable: true
                    }
                });
                drawingManager.setMap(map);
                drawingManager2 = new google.maps.drawing.DrawingManager({
                    drawingMode: google.maps.drawing.OverlayType.CIRCLE,
                    drawingControl: false,
                    drawingControlOptions: {
                        drawingModes: [
                            google.maps.drawing.OverlayType.CIRCLE
                        ]
                    },
                    circleOptions: {
                        strokeWeight: 0,
                        editable: true
                    }
                });
                drawingManager2.setMap(map2);
                
                google.maps.event.addListener(drawingManager, 'circlecomplete', function(circle) {

                        coordinates_filter = getCircleCoordinatesFilter(circle);
                        makeSearch();
                        drawingManager.setDrawingMode(null);

                    google.maps.event.addListener(circle, 'click', function() {
                        setSelection(circle);
                    });

                    setSelection(circle);
                    google.maps.event.addListener(circle, 'center_changed', function(){

                        coordinates_filter = getCircleCoordinatesFilter(circle);
                        makeSearch();
                    });
                    google.maps.event.addListener(circle, 'radius_changed', function(){

                        coordinates_filter = getCircleCoordinatesFilter(circle);
                        makeSearch();
                    });
                });
                google.maps.event.addListener(drawingManager2, 'circlecomplete', function(circle) {

                        coordinates_filter = getCircleCoordinatesFilter(circle);
                        makeSearch();
                    drawingManager2.setDrawingMode(null);
                    google.maps.event.addListener(circle, 'click', function() {
                        setSelection(circle);
                    });
                    setSelection(circle);
                    google.maps.event.addListener(circle, 'center_changed', function(){

                        coordinates_filter = getCircleCoordinatesFilter(circle);
                        makeSearch();
                    });
                    google.maps.event.addListener(circle, 'radius_changed', function(){

                        coordinates_filter = getCircleCoordinatesFilter(circle);
                        makeSearch();
                    });
                });
            });


            
            
            $('.freehand').click(function() {                                                           // POLYGON
                if( selectedShape ){
                    selectedShape.setMap(null);
                    coordinates_filter = '';
                }
                if( selectedShape2 ){
                    selectedShape2.setMap(null);
                    coordinates_filter = '';
                }

                drawingManager = new google.maps.drawing.DrawingManager({
                    drawingMode: google.maps.drawing.OverlayType.POLYGON,
                    drawingControl: false,
                    drawingControlOptions: {
                        drawingModes: [
                            google.maps.drawing.OverlayType.POLYGON
                        ]
                    },
                    poligonOptions: {
                        strokeWeight: 0,
                        editable: true
                    }
                });
                drawingManager.setMap(map);
                
                drawingManager2 = new google.maps.drawing.DrawingManager({
                    drawingMode: google.maps.drawing.OverlayType.POLYGON,
                    drawingControl: false,
                    drawingControlOptions: {
                        drawingModes: [
                            google.maps.drawing.OverlayType.POLYGON
                        ]
                    },
                    poligonOptions: {
                        strokeWeight: 0,
                        editable: true
                    }
                });
                drawingManager2.setMap(map2);
                
                google.maps.event.addListener(drawingManager, 'polygoncomplete', function(polygon) {

                    coordinates_filter = getPolygonCoordinatesFilter(polygon);
                    drawingManager.setDrawingMode(null);
                    setSelection(polygon);

                    google.maps.event.addListener(polygon, 'click', function() {
                        setSelection(polygon);
                    });

                    google.maps.event.addListener(polygon.getPath(), 'set_at', function() {
                        coordinates_filter = getPolygonCoordinatesFilter(polygon);
                        makeSearch();
                    });
                    google.maps.event.addListener(polygon.getPath(), 'insert_at', function() {
                        coordinates_filter = getPolygonCoordinatesFilter(polygon);
                        makeSearch();
                    });

                    makeSearch();

                });
                google.maps.event.addListener(drawingManager2, 'polygoncomplete', function(polygon) {
                    coordinates_filter = getPolygonCoordinatesFilter(polygon);
                    drawingManager.setDrawingMode(null);
                    google.maps.event.addListener(polygon, 'click', function() {
                        setSelection(polygon);
                    });
                    setSelection(polygon);
                    google.maps.event.addListener(polygon.getPath(), 'set_at', function() {
                        coordinates_filter = getPolygonCoordinatesFilter(polygon);
                    });
                    google.maps.event.addListener(polygon.getPath(), 'insert_at', function() {
                        coordinates_filter = getPolygonCoordinatesFilter(polygon);
                    });
                });
            });
            

            $('.delete-button').click(function() {
                deleteSelectedShape();
                
            });


            
            $('#keywords_block').on('blur', '.bootstrap-tagsinput input', function() {
                $('input.tagsinput').tagsinput('add', $(this).val());
                $(this).val('');
               
            });
            


            $('#search-button').click(function(){
                saveDataForSearch();
                clickSearchButton();
                return false;
            });




            $('.datatable_tabletools').on('click', 'button.delete-property', function(){
                var property = $(this).data('property_id');
                if(confirm('Are you sure you want to delete the row?')){
                    setAllMap(null);
                    deleteSearchResults(property);
                    
                    $.ajax({
                        url: '/property/deletefavorites',
                        type: 'POST',
                        data: { property: property },
                        dataType: 'json',
                        cache: false,
                        success: function(data){
                            if(data.length > 0){
                                showAlert(data);
                            }
                        },
                        error:  function(xhr, str){
//                            console.log('error: ' + xhr.responseCode);
                        }
                    });
                    $(this).closest('tr').remove();
                    var c_res = parseInt($('.count_search_result').text());
                    $('.count_search_result').empty().html(c_res-1);
                    for(var i = 0; i < latLon_arr.length; i++){
                        if(latLon_arr[i].property_id == property){ 
                            latLon_arr.splice(i,1);
                        };
                    }
                    resetDataTableList();
                }
            });

            $('.datatable_tabletools').on('click', 'button.favorite', function(){
                var property_id = $(this).data('property_id');
                $.ajax({
                    url: '/property/addfavorites',
                    type: 'POST',
                    data: { property: property_id },
                    dataType: 'json',
                    cache: false,
                    success: function(data){
                        showAlert(data);
                    },
                    error:  function(xhr, str){
//                        console.log('error: ' + xhr.responseCode);
                    }
                });
                
            });
            

            
            $(document).keypress(function(eventObject){
                var code = parseInt((eventObject.keyCode ? eventObject.keyCode : eventObject.which));
                if(code === 13){
                    makeSearch();
                    return false;
                }

            });
            



            if(searchOnLoad){
                makeSearch();
            }



// end sendAjaxRequest (POS_READY)


", CClientScript::POS_READY);

$cs->registerScriptFile(Yii::app()->theme->baseUrl . '/js/search.js', CClientScript::POS_END);

Yii::app()->clientScript->registerScript("Script", "
    
        var placeSearch, autocomplete;
        var componentForm = {
          street_number: 'short_name',
          route: 'long_name',
          locality: 'long_name',
          administrative_area_level_1: 'short_name',
          country: 'long_name',
          postal_code: 'short_name'
        };
        
         var componentFormSearchFld = {
          street_number_searchfld: 'short_name',
          route_searchfld: 'long_name',
          locality_searchfld: 'long_name',
          administrative_area_level_1_searchfld: 'short_name',
          country_searchfld: 'long_name',
          postal_code_searchfld: 'short_name'
        };
        
        function fillInAddressSearchFld() {
          var place = search_fld.getPlace();
//          console.log(place);
          for (var component in componentFormSearchFld) {
//            console.log(component);
            document.getElementById(component).value = '';
            document.getElementById(component).disabled = false;
          }
          for (var i = 0; i < place.address_components.length; i++) {
//            console.log(place.address_components[i].types[0]);
            var addressType = place.address_components[i].types[0];
            if (componentFormSearchFld[addressType+'_searchfld']) {
              var val = place.address_components[i][componentFormSearchFld[addressType+'_searchfld']];
              document.getElementById(addressType+'_searchfld').value = val;
            }
          }
        }
              

        function fillInAddress() {
          var place = autocomplete.getPlace();
          for (var component in componentForm) {
            document.getElementById(component).value = '';
            document.getElementById(component).disabled = false;
          }
          if ('address_components' in place) {
          for (var i = 0; i < place.address_components.length; i++) {
            var addressType = place.address_components[i].types[0];
            if (componentForm[addressType]) {
              var val = place.address_components[i][componentForm[addressType]];
              document.getElementById(addressType).value = val;
            }
          }
          }
        }

        
        function geolocate() {
          if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
              var geolocation = new google.maps.LatLng(
                  position.coords.latitude, position.coords.longitude);
                  
              autocomplete.setBounds(new google.maps.LatLngBounds(geolocation,
                  geolocation));
            });
          }
        }
        
        
    
    ", CClientScript::POS_END);

