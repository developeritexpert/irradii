<div class="row" id="search_map_block" style="display: block;">

                <!-- NEW WIDGET START -->
                <article class="col-sm-7 col-md-8 col-lg-8">

                    <!-- Widget ID (each widget will need unique ID)-->
                    <div class="jarviswidget jarviswidget-color-white" id="wid-id-0M2" data-widget-editbutton="false" data-widget-deletebutton="false" data-widget-colorbutton="false" data-widget-fullscreenbutton="false" data-widget-collapsed="false" data-widget-sortable="false">
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

                        </header>

                        <!-- widget div-->
                        <div>

                            <!-- widget edit box -->
                            <div class="jarviswidget-editbox">
                                <!-- This area used as dropdown edit box -->

                            </div>
                            <!-- end widget edit box -->

                            <!-- widget content -->
                            <div class="widget-body no-padding mobile-wrapper" style="height:750px">
                                <div id="map_canvas" class="google_maps2">
                                    &nbsp;
                                </div>

                            </div>
                            <!-- end widget content -->

                        </div>
                        <!-- end widget div -->

                    </div>
                    <!-- end widget -->

                </article>
                <!-- NEW WIDGET START -->
                <article class="col-sm-5 col-md-4 col-lg-4">

                    <!-- Widget ID (each widget will need unique ID)-->
                    <div class="jarviswidget jarviswidget-color-greenDark" id="wid-id-results12" data-widget-editbutton="false" data-widget-deletebutton="false" data-widget-colorbutton="false"  data-widget-fullscreenbutton="false" data-widget-collapsed="false"  data-widget-sortable="false">
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

                        <!-- widget div-->
                        <div>

                            <!-- widget edit box -->
                            <div class="jarviswidget-editbox">
                                <!-- This area used as dropdown edit box -->

                            </div>
                            <!-- end widget edit box -->

                            <!-- widget content -->
                            <div class="widget-body no-padding mobile-wrapper" style="height:750px; overflow-y: scroll;">

                                <table  class="table table-striped table-hover datatable_tabletools2">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Address</th>
                                            <th>Desc.</th>
                                        </tr>
                                    </thead>
                                    <tbody>


                                    </tbody>
                                </table>
                            </div>
                            <!-- end widget content -->

                        </div>
                        <!-- end widget div -->

                    </div>
                    <!-- end widget -->

                </article>
                <!-- end row -->

            </div>

<div id="loader_img">
    <img src="<?php echo CPathCDN::baseurl( 'img' ); ?>/img/ajax-loader.gif" alt="">
</div>
<div class="detail-pop-up">
    <button class="close" type="button">×</button>
    <a class="show-in-table" href="#">Show in table</a>
    <div class="row">
        <div class="col-sm-6">
            <div class="img-container">
                <div id="detail-pop-up-carousel" class="carousel fade">
                    <div class="carousel-inner" role="listbox">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="street"></div>
            <div class="city"></div>
            <div class="subdivision"></div>
            <div class="type"></div>
            <div class="metrics"></div>
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
</div>
<?php

Yii::app()->clientScript->registerScript("MapBoundaryVar", "
var mapBoundaries = [];
        ", CClientScript::POS_END);

$searchResultSmallQuery = $search_results ? json_encode($search_results) : '';

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
            var drawingManager;
            var selectedShape;
            var bounds2 = new google.maps.LatLngBounds();

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
//                console.log('this_form',this_form);
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
                shape.setEditable(true);
            }

            function deleteSelectedShape() {
                if (selectedShape) { selectedShape.setMap(null);   }
                coordinates_filter = '';
                selectedShape = null;
            }

            function initialize() {

                var mapOptions = {
                    zoom: 12,
                    minZoom: 2
                };
                map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);


               for (key in mapBoundaries) {
                    if (mapBoundaries.hasOwnProperty(key)) {

                        // clone objects
                        var shape = jQuery.extend(true, {}, mapBoundaries[key]);

                        shape.setMap(map);
                        selectedShape = shape;
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
                    }, function() {
                        handleNoGeolocation(map);
                    });
                } else {
                    // Browser doesn't support Geolocation
                    handleNoGeolocation(map);
                }

                bounds = new google.maps.LatLngBounds();
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



            var dTable;
            var markers = [];
            var latLon_arr = [];
            var search_results;
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
                var a_path = getPathImages();
                markers = [];
                for(var i = 0; i < latLon_arr.length; i++){
                    for(var key in latLon_arr[i]){

                        if( ( ( latLon_arr[i][key].lat == '0.000000' ) || ( latLon_arr[i][key].lat == '' )  ) &&
                            ( ( latLon_arr[i][key].lon == '0.000000' ) || ( latLon_arr[i][key].lon == '' ) ) ){
                            continue;
                        }
                        
                        position = new google.maps.LatLng(latLon_arr[i][key].lat, latLon_arr[i][key].lon);
                        if(latLon_arr[i][key].status != ''){
                            var status = latLon_arr[i][key].status.toLowerCase();
                        } else { var status = 'for sale'; }
                        
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
                            
                            case 'group':
                                console.log('NEED SET GROUP IMAGE');
                            break;
                        }
                        
                        marker = new google.maps.Marker({
                                position: position,
                                map: map,
                                icon: image,
                                property: latLon_arr[i][key].property_id,
                                title: latLon_arr[i][key].address
                                });
                                
                        marker.addListener('click', toggleDetailPopup);      
                        markers.push(marker);
                        bounds.extend(position);
                    }
                }

                setAllMap(null);
                setAllMap(map);
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
                var prop_id = marker.property,
                    rows$ = $('#DataTables_Table_0').find('tbody tr'),
                    map = '#map_canvas',
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
                            if ($(element).find('.property_info_row_map').data('property_id') === prop_id) {
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
                var targetProperty = row$.find('td .property_info_row_map'),
                    street = targetProperty.data('address'),
                    label$ = row$.find('.status-label .label').clone(),
                    price = row$.find('.price').clone();
                
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
       
                var minTop = 0,
                    minLeft = 0,
                    markerHeight = 40,
                    popup$ = $('.detail-pop-up'),
                    popupHeight = popup$.height(),
                    popupWidth = popup$.width(),
                    topRight = map.getProjection().fromLatLngToPoint(map.getBounds().getNorthEast()),
                    bottomLeft = map.getProjection().fromLatLngToPoint(map.getBounds().getSouthWest()),
                    scale = Math.pow(2, map.getZoom()),
                    worldPoint = map.getProjection().fromLatLngToPoint(marker.getPosition()),
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
                            null,
                            { 'sType': 'currency' },
                            { 'sType': 'natural' },
                            { 'sType': 'natural' },
                            null,
                     ],
                     'aaSorting': [[ 0, 'desc' ]],

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
                        'sDom': '<\'dt-top-row\'Tlf>r<\'dt-wrapper\'t><\'dt-row dt-bottom-row\'<\'row\'<\'col-sm-6\'i><\'col-sm-6 text-right\'p>>',
                        'oTableTools': {
                            'aButtons': ['copy', 'print', {
                                    'sExtends': 'collection',
                                    'sButtonText': 'Save <span class=\'caret\' />',
                                    'aButtons': ['csv', 'xls', 'pdf']
                                }],
                            'sSwfPath': '" . CPathCDN::baseurl( 'js' ) ."/js/plugin/datatables/media/swf/copy_csv_xls_pdf.swf'
                        },
                        'iDisplayLength':100,
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
                      'aoColumns': [
                           { 'bVisible': false, 'sType': 'natural' },
                           { 'sType': 'num-html' },
                           { 'bVisible': false,'sType': 'currency' }
                        ],
                        'aaSorting': [[ 0, 'desc' ]],
                        'oLanguage': {
                            'sEmptyTable': 'No data available in table'
                        },
                        'fnInitComplete': function(oSettings, json) {
                            $(this).closest('#dt_table_tools_wrapper').find('.DTTT.btn-group').addClass('table_tools_group').children('a.btn').each(function() {
                                $(this).addClass('btn-sm btn-default');
                            });
                        }
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
//                         map.setCenter(pos);
                         map.setCenter(pos);
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


            var myMap = google.maps.event.addDomListener(window, 'load', initialize);
            var searchOnLoad = " . ((isset($general_search_fields['searchOnLoad']) && $general_search_fields['searchOnLoad']=='1')? '1' :'0') . ";


", CClientScript::POS_END);
