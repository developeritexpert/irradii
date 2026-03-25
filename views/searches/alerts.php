<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\models\SavedSearchCriteria;
use app\models\SavedSearch;
use app\helpers\SiteHelper;

$this->title = 'Searches/Alerts';
// Note: aside is already rendered by the irradii_main.php layout - no need to render it here
?>

<div id="main" role="main">

    <!-- RIBBON -->
    <div id="ribbon">

        <span class="ribbon-button-alignment"> <span id="refresh" class="btn btn-ribbon" data-title="refresh"  rel="tooltip" data-placement="bottom" data-original-title="<i class='text-warning fa fa-warning'></i> Warning! This will reset all of your personalized widget settings." data-html="true"><i class="fa fa-refresh"></i></span> </span>

        <!-- breadcrumb -->
        <ol class="breadcrumb">
            <li>
                Searches
            </li>
            <li>
                Alerts
            </li>
        </ol>
        <!-- end breadcrumb -->

        <!-- You can also add more buttons to the
        ribbon for further usability

        Example below:

        <span class="ribbon-button-alignment pull-right">
        <span id="search" class="btn btn-ribbon hidden-xs" data-title="search"><i class="fa-grid"></i> Change Grid</span>
        <span id="add" class="btn btn-ribbon hidden-xs" data-title="add"><i class="fa-plus"></i> Add</span>
        <span id="search" class="btn btn-ribbon" data-title="search"><i class="fa-search"></i> <span class="hidden-mobile">Search</span></span>
        </span> -->

    </div>
    <!-- END RIBBON -->

    <!-- MAIN CONTENT -->
    <div id="content">
        <div class="row">
            <div class="col-xs-12 col-sm-7 col-md-7 col-lg-4">
                <h1 class="page-title txt-color-blueDark">
                    <i class="fa fa-check-square-o fa-fw "></i>
                    Saved Searches
                </h1>
            </div>
            <div class="col-xs-12 col-sm-5 col-md-5 col-lg-8">
            </div>
        </div>

        <!-- widget grid -->
        <section id="widget-grid" class="">

        <!-- row -->
        <div class="row">

            <!-- NEW COL START -->
            <article class="col-sm-12">

                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget jarviswidget-color-blueDark" id="wid-id-0" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
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
                        <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                        <h2>Saved Searches and Email Alert Settings </h2>

                    </header>

                    <!-- widget div-->
                    <div>

                        <!-- widget edit box -->
                        <div class="jarviswidget-editbox">
                            <!-- This area used as dropdown edit box -->

                        </div>
                        <!-- end widget edit box -->

                        <!-- widget content -->


                        <div class="widget-body">

                            <!--
                                    <div class="widget-body-toolbar">

                                        <div class="row">

                                            <div class="col-sm-6">
                                                <button id="enable" class="btn btn btn-default">
                                                    EDIT
                                                </button>
                                            </div>

                                            <div class="col-sm-6 text-right">

                                                <div class="onoffswitch-container">
                                                    <span class="onoffswitch-title">Auto Open Next</span>
                                                    <span class="onoffswitch">
                                                        <input type="checkbox" class="onoffswitch-checkbox" id="autoopen">
                                                        <label class="onoffswitch-label" for="autoopen">
                                                            <span class="onoffswitch-inner" data-swchon-text="ON" data-swchoff-text="OFF"></span>
                                                            <span class="onoffswitch-switch"></span>
                                                        </label>
                                                    </span>


                                                </div>

                                                <div class="onoffswitch-container">
                                                    <span class="onoffswitch-title">Open Inline</span>
                                                    <span class="onoffswitch">
                                                        <input type="checkbox" class="onoffswitch-checkbox" id="inline">
                                                        <label class="onoffswitch-label" for="inline">
                                                            <span class="onoffswitch-inner" data-swchon-text="ON" data-swchoff-text="OFF"></span>
                                                            <span class="onoffswitch-switch"></span>
                                                        </label>
                                                    </span>
                                                </div>

                                            </div>

                                        </div>


                                    </div>

                            -->


                            <div class="table-wrapper">

                            <table id="user" class="table table-bordered table-striped" style="clear: both">
                                <thead>
                                <tr>
                                                                        <th style="width: 30px;"><i class="fa fa-reorder" style="visibility: hidden;"></i></th>

                                    <th>Search ID</th>
                                    <th>Search Now</th>
                                    <th>Search Name</th>
                                    <th>Orig. Date</th>
                                    <th>Expiry Date</th>
                                    <th>Search Criteria</th>
                                    <th>Email Alerts</th>
                                    <th>Linked Email Addresses</th>
                                    <!--<th>Edit</th>-->
                                    <th>Delete</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach($savedSearches as $keySavedSearch=>$savedSearch): ?>
                                    <?php
                                        $created_at = DateTime::createFromFormat('Y-m-d H:i:s', $savedSearch->created_at);
                                        $expiry_date = DateTime::createFromFormat('Y-m-d H:i:s', $savedSearch->expiry_date);
                                    ?>
                                    <tr data-id="<?php echo $savedSearch->id?>">
                                        <td><span class="sort-handle" style="cursor: move;"><i class="fa fa-reorder"></i></span></td>
                                        <td><?php echo $savedSearch->id?></td>
                                        <td>
                                            <form method="POST" action="<?php echo Url::to(['property/search'])?>">
                                                <?php foreach($savedSearch->savedSearchCriteria as $criteria):

                                                    $attr_value = @unserialize($criteria->attr_value);
                                                    ?>

                                                    <?php if(is_array($attr_value)): ?>
                                                    <?php foreach($attr_value as $value):?>
                                                        <input type="hidden" name="<?php echo $criteria->attr_name?>[]" value="<?php echo $value?>">
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <input type="hidden" name="<?php echo $criteria->attr_name?>" value="<?php echo $attr_value?>">
                                                <?php endif; ?>
                                                <?php endforeach; ?>


                                                <input type="hidden" name="save_search_title" value="<?php echo $savedSearch->name?>">
                                                <input type="hidden" name="searchOnLoad" value="1">
                                                <button type="submit" class="btn btn btn-success">
                                                    SEARCH
                                                </button>
                                            </form>

                                        </td>
                                        <td>
                                            <a href="javascript:void(0);"
                                               class="savedSearchName"
                                               data-type="text"
                                               data-pk=<?php echo $savedSearch->id?>
                                               data-name="name"
                                               data-url="<?php echo Url::to(['searches/editable'])?>"
                                               data-original-title="Enter a Search Name/ Title">
                                                <?php echo $savedSearch->name?>
                                            </a>
                                        </td>
                                        <td><?php echo $created_at->format('Y-m-d')?></td>
                                        <td>
                                            <a href="javascript:void(0);"
                                               class="savedSearchExpiryDate"
                                               data-type="date"
                                               data-viewformat="yyyy-mm-dd"
                                               data-pk=<?php echo $savedSearch->id?>
                                               data-name="expiry_date"
                                               data-url="<?php echo Url::to(['searches/editable'])?>"
                                               data-placement="right"
                                               data-original-title="When do you want the wealth to end?">

                                                <?php echo $expiry_date->format('Y-m-d')?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php
                                            $propertyTypeArr = array(
                                                "AK"=> 'Attached SFH',
                                                "HI"=>'Detached SFH',
                                                "CA1"=>'Low Rise',
                                                "OR"=>'High Rise',
                                                "TH"=>'Townhouse',
                                                "DP"=>'Duplex',
                                                "TP"=>'Triplex',
                                                "FP"=>'Fourplex',
                                                "AZ"=>'Mobile Home',
                                                "CO"=>'Manufactured Home',
                                                "AL"=>'Vacant Land',
                                            );
                                            $count = 0; $col1 = ''; $col2 = '';
                                            foreach($savedSearch->savedSearchCriteria as $criteria):

                                                $attr_value = @unserialize($criteria->attr_value);

                                                if(($criteria->attr_name == 'bed' || $criteria->attr_name == 'bath') && $attr_value==0) {
                                                    continue;
                                                }

                                                $label = SavedSearchCriteria::getLabel($criteria->attr_name);

                                                if($label == '' || strpos($label, 'Map Boundary' ) !== false) {
                                                    continue;
                                                }
                                                     $count++;
                                                     if($label == 'Property Type') {
                                                         $col =  $label . ' : ' .'<b>';
                                                         $tmpArr = array();
                                                         foreach ($attr_value as $key) {
                                                             if(isset($propertyTypeArr[$key])) {
                                                                $tmpArr[] = $propertyTypeArr[$key];
                                                             }
                                                         }
                                                         $col .= SiteHelper::toString( $tmpArr) . '</b>';
                                                     } else {
                                                        $col =  $label . ' : ' .'<b>'.SiteHelper::toString($attr_value).'</b>';
                                                     }
                                                     if ( $count > 1) {
                                                         $col2 .= '<p>' .$col . '</p>';
                                                     } else {
                                                         $col1 = $col;
                                                     }
                                             endforeach;
                                             if ( $col2 ) { ?>
        <div class=""> <?php echo $col1; ?>
                <a data-toggle="collapse" href="#collapseOne<?php echo $keySavedSearch;?>">
                    <b class="collapse-sign"><em class="fa fa-expand-o"></em></b>
                </a>
        </div>
        <div id="collapseOne<?php echo $keySavedSearch;?>" class="collapse">
                <?php echo $col2; ?>
        </div>
                                        <?php
                                             } else {
                                                 echo $col1;
                                             }
                                        ?>

                                        </td>

                                        <td>
                                            <a href="javascript:void(0);"
                                               class="savedSearchEmailFreq"
                                               data-type="select"
                                               data-pk=<?php echo $savedSearch->id?>
                                               data-name="email_alert_freq"
                                               data-value=<?php echo $savedSearch->email_alert_freq?>
                                               data-url="<?php echo Url::to(['searches/editable'])?>"
                                               data-original-title="Select frequency">

                                                <?php echo SavedSearch::getEmailFreqName($savedSearch->email_alert_freq)?>

                                            </a>
                                        </td>

                                        <td>
                                            <?php foreach($savedSearch->alertEmails as $alertEmailModel): ?>
                                                <p><a  href="javascript:void(0);"
                                                       class="savedSearchLinkedEmails"
                                                       data-type="email"
                                                       data-pk="<?php echo $alertEmailModel->id?>"
                                                       data-name="email"
                                                       data-saved_search_id=<?php echo $savedSearch->id?>
                                                       data-url="<?php echo Url::to(['searches/editable'])?>"
                                                       data-emptytext="Add"
                                                       data-original-title="Enter email">

                                                        <?php echo  $alertEmailModel->email ?>
                                                    </a>
                                                </p>
                                            <?php endforeach; ?>

                                            <p><a  href="javascript:void(0);"
                                                   class="savedSearchLinkedEmails"
                                                   style="color: #a90329;"
                                                   data-type="email"
                                                   data-pk="0"
                                                   data-name="email"
                                                   data-saved_search_id=<?php echo $savedSearch->id?>
                                                   data-url="<?php echo Url::to(['searches/editable'])?>"
                                                   data-emptytext="Add"
                                                   data-original-title="Enter email">Add</a>
                                            </p>
                                        </td>

                                        <!--
                                        <td>
                                            <button class="btn btn btn-primary">
                                                EDIT
                                            </button>
                                        </td>
                                        -->
                                        <td>
                                            <?php echo Html::a('DELETE',
                                                Url::to(['searches/delete']),
                                                ['data-id'=>$savedSearch->id,
                                                    'class'=>'dialog_delete_link btn btn-danger',
                                                ]
                                            )?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                </tbody>
                            </table>
                            </div>
                        </div>
                        <!-- end widget content -->

                    </div>
                    <!-- end widget div -->

                </div>
                <!-- end widget -->

            </article>
            <!-- END COL -->

        </div>

        <!-- end row -->

        <!-- START ROW -->

        <div class="row">

            <!-- NEW COL START -->
            <article class="col-sm-12 col-md-12 col-lg-12">

                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget" id="wid-id-2" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
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
                        <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                        <h2>Recent Search Results </h2>

                    </header>

                    <!-- widget div-->
                    <div>

                        <!-- widget edit box -->
                        <div class="jarviswidget-editbox">
                            <!-- This area used as dropdown edit box -->

                        </div>
                        <!-- end widget edit box -->

                        <?php foreach($partials as $partial): ?>
                            <?php echo  $partial ?>

                        <?php endforeach; ?>


                    </div>
                    <!-- end widget div -->

                </div>
                <!-- end widget -->

            </article>
            <!-- END COL -->

        </div>

        <!-- END ROW -->

        </section>
        <!-- end widget grid -->


    </div><!-- /.content -->
</div>
<!-- END MAIN PANEL -->

<!-- SHORTCUT AREA : With large tiles (activated via clicking user name tag)
Note: These tiles are completely responsive,
you can add as many as you like
-->
<div id="shortcut">
    <ul>
        <li>
            <a href="#inbox.html" class="jarvismetro-tile big-cubes bg-color-blue"> <span class="iconbox"> <i class="fa fa-envelope fa-4x"></i> <span>Mail <span class="label pull-right bg-color-darken">14</span></span> </span> </a>
        </li>
        <li>
            <a href="#calendar.html" class="jarvismetro-tile big-cubes bg-color-orangeDark"> <span class="iconbox"> <i class="fa fa-calendar fa-4x"></i> <span>Calendar</span> </span> </a>
        </li>
        <li>
            <a href="#gmap-xml.html" class="jarvismetro-tile big-cubes bg-color-purple"> <span class="iconbox"> <i class="fa fa-map-marker fa-4x"></i> <span>Maps</span> </span> </a>
        </li>
        <li>
            <a href="#invoice.html" class="jarvismetro-tile big-cubes bg-color-blueDark"> <span class="iconbox"> <i class="fa fa-book fa-4x"></i> <span>Invoice <span class="label pull-right bg-color-darken">99</span></span> </span> </a>
        </li>
        <li>
            <a href="#gallery.html" class="jarvismetro-tile big-cubes bg-color-greenLight"> <span class="iconbox"> <i class="fa fa-picture-o fa-4x"></i> <span>Gallery </span> </span> </a>
        </li>
        <li>
            <a href="javascript:void(0);" class="jarvismetro-tile big-cubes selected bg-color-pinkDark"> <span class="iconbox"> <i class="fa fa-user fa-4x"></i> <span>My Profile </span> </span> </a>
        </li>
    </ul>
</div>
<!-- END SHORTCUT AREA -->

<div id="dialog_deleteItem">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This item will be permanently deleted and cannot be recovered. Are you sure?</p>
</div>

<!--================================================== -->

<!-- PACE LOADER - turn this on if you want ajax loading to show (caution: uses lots of memory on iDevices)-->
<script data-pace-options='{ "restartOnRequestAfter": true }' src="<?php echo Yii::$app->request->baseUrl; ?>/js/plugin/pace/pace.min.js"></script>

<!--[if IE 7]>

<h1>Your browser is out of date, please update your browser by going to www.microsoft.com/download</h1>

<![endif]-->




<?php
// alerts.js registration moved to layout to ensure it loads after concat-build.min.js
?>


<script type="text/javascript">

// DO NOT REMOVE : GLOBAL FUNCTIONS!

$(document).ready(function() {

    // PAGE RELATED SCRIPTS

    // Spinners
    $("#spinner").spinner();
    $("#spinner-decimal").spinner({
        step: 0.01,
        numberFormat: "n"
    });

    $("#spinner-currency").spinner({
        min: 5,
        max: 2500,
        step: 25,
        start: 1000,
        numberFormat: "C"
    });

    //Maxlength

    $('input[maxlength]').maxlength({
        warningClass: "label label-success",
        limitReachedClass: "label label-important",
    });


    // START AND FINISH DATE
    $('#startdate').datepicker({
        dateFormat: 'dd.mm.yy',
        prevText: '<i class="fa fa-chevron-left"></i>',
        nextText: '<i class="fa fa-chevron-right"></i>',
        onSelect: function (selectedDate) {
            $('#finishdate').datepicker('option', 'minDate', selectedDate);
        }
    });
    $('#finishdate').datepicker({
        dateFormat: 'dd.mm.yy',
        prevText: '<i class="fa fa-chevron-left"></i>',
        nextText: '<i class="fa fa-chevron-right"></i>',
        onSelect: function (selectedDate) {
            $('#startdate').datepicker('option', 'maxDate', selectedDate);
        }
    });

    // Date Range Picker
    $("#from").datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        numberOfMonths: 3,
        prevText: '<i class="fa fa-chevron-left"></i>',
        nextText: '<i class="fa fa-chevron-right"></i>',
        onClose: function (selectedDate) {
            $("#to").datepicker("option", "maxDate", selectedDate);
        }

    });
    $("#to").datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        numberOfMonths: 3,
        prevText: '<i class="fa fa-chevron-left"></i>',
        nextText: '<i class="fa fa-chevron-right"></i>',
        onClose: function (selectedDate) {
            $("#from").datepicker("option", "minDate", selectedDate);
        }
    });

    /*
     * TIMEPICKER
     */

    $('#timepicker').timepicker();

    /*
     * CONVERT DIALOG TITLE TO HTML
     * REF: http://stackoverflow.com/questions/14488774/using-html-in-a-dialogs-title-in-jquery-ui-1-10
     */
    $.widget("ui.dialog", $.extend({}, $.ui.dialog.prototype, {
        _title : function(title) {
            if (!this.options.title) {
                title.html("&#160;");
            } else {
                title.html(this.options.title);
            }
        }
    }));


    /*
     * DIALOG SIMPLE
     */



    /*
     * DIALOG HEADER ICON
     */

    // Modal Link
    $('#modal_link').click(function() {
        $('#dialog-message').dialog('open');
        return false;
    });

    $("#dialog-message").dialog({
        autoOpen : false,
        modal : true,
        title : "<div class='widget-header'><h4><i class='icon-ok'></i> jQuery UI Dialog</h4></div>",
        buttons : [{
            html : "Cancel",
            "class" : "btn btn-default",
            click : function() {
                $(this).dialog("close");
            }
        }, {
            html : "<i class='fa fa-check'></i>&nbsp; OK",
            "class" : "btn btn-primary",
            click : function() {
                $(this).dialog("close");
            }
        }]

    });

    /*
     * Remove focus from buttons
     */
    $('.ui-dialog :button').blur();

    /*
     * Just Tabs
     */

    $('#tabs').tabs();

    /*
     *  Simple tabs adding and removing
     */

    $('#tabs2').tabs();

    // Dynamic tabs
    var tabTitle = $("#tab_title"), tabContent = $("#tab_content"), tabTemplate = "<li style='position:relative;'> <span class='air air-top-left delete-tab' style='top:7px; left:7px;'><button class='btn btn-xs font-xs btn-default hover-transparent'><i class='fa fa-times'></i></button></span></span><a href='#{href}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; #{label}</a></li>", tabCounter = 2;

    var tabs = $("#tabs2").tabs();

    // modal dialog init: custom buttons and a "close" callback reseting the form inside
    var dialog = $("#addtab").dialog({
        autoOpen : false,
        width : 600,
        resizable : false,
        modal : true,
        buttons : [{
            html : "<i class='fa fa-times'></i>&nbsp; Cancel",
            "class" : "btn btn-default",
            click : function() {
                $(this).dialog("close");

            }
        }, {

            html : "<i class='fa fa-plus'></i>&nbsp; Add",
            "class" : "btn btn-danger",
            click : function() {
                addTab();
                $(this).dialog("close");
            }
        }]
    });

    // addTab form: calls addTab function on submit and closes the dialog
    var form = dialog.find("form").submit(function(event) {
        addTab();
        dialog.dialog("close");
        event.preventDefault();
    });

    // actual addTab function: adds new tab using the input from the form above
    function addTab() {
        var label = tabTitle.val() || "Tab " + tabCounter, id = "tabs-" + tabCounter, li = $(tabTemplate.replace(/#\{href\}/g, "#" + id).replace(/#\{label\}/g, label)), tabContentHtml = tabContent.val() || "Tab " + tabCounter + " content.";

        tabs.find(".ui-tabs-nav").append(li);
        tabs.append("<div id='" + id + "'><p>" + tabContentHtml + "</p></div>");
        tabs.tabs("refresh");
        tabCounter++;

        // clear fields
        $("#tab_title").val("");
        $("#tab_content").val("");
    }

    // addTab button: just opens the dialog
    $("#add_tab").button().click(function() {
        dialog.dialog("open");
    });

    // close icon: removing the tab on click
    $("#tabs2").on("click", 'span.delete-tab', function() {

        var panelId = $(this).closest("li").remove().attr("aria-controls");
        $("#" + panelId).remove();
        tabs.tabs("refresh");
    });

    /*
     * JS SLIDER
     */

    $("#nouislider-1").noUiSlider({
        range: [2, 100],
        start: 55,
        handles: 1,
        connect: true,
    });

    $("#nouislider-2").noUiSlider({
        range: [0, 300],
        start: [55, 130],
        step: 60,
        handles: 2,
        connect: true
    });

    $("#nouislider-3").noUiSlider({
        range: [0, 1000],
        start: [264, 776],
        step: 1,
        connect: true,
        slide: function () {
            var values = $(this).val();
            $(".nouislider-value").text(values[0] + " - " + values[1]);
        }
    });

    $("#nouislider-4").noUiSlider({
        range: [0, 100],
        start: 50,
        handles: 1
    }).attr("disabled", "disabled");



    /*
     * ION SLIDER
     */

    $("#range-slider-1").ionRangeSlider({
        min: 0,
        max: 5000,
        from: 1000,
        to: 4000,
        type: 'double',
        step: 1,
        prefix: "$",
        prettify: false,
        hasGrid: true
    });

    $("#range-slider-2").ionRangeSlider();

    $("#range-slider-3").ionRangeSlider({
        min: 0,
        from: 2.3,
        max: 10,
        type: 'single',
        step: 0.1,
        postfix: " mm",
        prettify: false,
        hasGrid: true
    });

    $("#range-slider-4").ionRangeSlider({
        min: -50,
        max: 50,
        from: 5,
        to: 25,
        type: 'double',
        step: 1,
        postfix: "°",
        prettify: false,
        hasGrid: true
    });

    $("#range-slider-5").ionRangeSlider({
        min: 0,
        from: 0,
        max: 10,
        type: 'single',
        step: 0.1,
        postfix: " mm",
        prettify: false,
        hasGrid: true
    });



    /*
     * COLOR PICKER
     */

    $('#colorpicker-1').colorpicker();
    $('#colorpicker-2').colorpicker();



    /*
     * KNOB
     */

    $('.knob').knob({
        change: function (value) {
            //console.log("change : " + value);
        },
        release: function (value) {
            //console.log(this.$.attr('value'));
            //console.log("release : " + value);
        },
        cancel: function () {
            //console.log("cancel : ", this);
        }
    });


    /*
     * X-Ediable
     */




    (function (e) {
        "use strict";
        var t = function (e) {
            this.init("address", e, t.defaults)
        };
        e.fn.editableutils.inherit(t, e.fn.editabletypes.abstractinput);
        e.extend(t.prototype, {
            render: function () {
                this.$input = this.$tpl.find("input")
            },
            value2html: function (t, n) {
                if (!t) {
                    e(n).empty();
                    return
                }
                var r = e("<div>").text(t.city).html() + ", " + e("<div>").text(t.street).html() +
                    " st., bld. " + e("<div>").text(t.building).html();
                e(n).html(r)
            },
            html2value: function (e) {
                return null
            },
            value2str: function (e) {
                var t = "";
                if (e)
                    for (var n in e)
                        t = t + n + ":" + e[n] + ";";
                return t
            },
            str2value: function (e) {
                return e
            },
            value2input: function (e) {
                if (!e)
                    return;
                this.$input.filter('[name="city"]').val(e.city);
                this.$input.filter('[name="street"]').val(e.street);
                this.$input.filter('[name="building"]').val(e.building)
            },
            input2value: function () {
                return {
                    city: this.$input.filter('[name="city"]').val(),
                    street: this.$input.filter('[name="street"]').val(),
                    building: this.$input.filter('[name="building"]').val()
                }
            },
            activate: function () {
                this.$input.filter('[name="city"]').focus()
            },
            autosubmit: function () {
                this.$input.keydown(function (t) {
                    t.which === 13 && e(this).closest("form").submit()
                })
            }
        });
        t.defaults = e.extend({}, e.fn.editabletypes.abstractinput.defaults, {
            tpl: '<div class="editable-address"><label><span>City: </span><input type="text" name="city" class="input-small"></label></div><div class="editable-address"><label><span>Street: </span><input type="text" name="street" class="input-small"></label></div><div class="editable-address"><label><span>Building: </span><input type="text" name="building" class="input-mini"></label></div>',
            inputclass: ""
        });
        e.fn.editabletypes.address = t
    })(window.jQuery);

    //TODO: add this div to page
    function log(settings, response) {
        var s = [],
            str;
        s.push(settings.type.toUpperCase() + ' url = "' + settings.url + '"');
        for (var a in settings.data) {
            if (settings.data[a] && typeof settings.data[a] === 'object') {
                str = [];
                for (var j in settings.data[a]) {
                    str.push(j + ': "' + settings.data[a][j] + '"');
                }
                str = '{ ' + str.join(', ') + ' }';
            } else {
                str = '"' + settings.data[a] + '"';
            }
            s.push(a + ' = ' + str);
        }
        s.push('RESPONSE: status = ' + response.status);

        if (response.responseText) {
            if ($.isArray(response.responseText)) {
                s.push('[');
                $.each(response.responseText, function (i, v) {
                    s.push('{value: ' + v.value + ', text: "' + v.text + '"}');
                });
                s.push(']');
            } else {
                s.push($.trim(response.responseText));
            }
        }
        s.push('--------------------------------------\n');
        $('#console').val(s.join('\n') + $('#console').val());
    }

    /*
     * X-EDITABLES
     */

    $('#inline').on('change', function (e) {
        if ($(this).prop('checked')) {
            window.location.href = '?mode=inline#ajax/plugins.html';
        } else {
            window.location.href = '?#ajax/plugins.html';
        }
    });

    if (window.location.href.indexOf("?mode=inline") > -1) {
        $('#inline').prop('checked', true);
        $.fn.editable.defaults.mode = 'inline';
    } else {
        $('#inline').prop('checked', false);
        $.fn.editable.defaults.mode = 'popup';
    }

    //defaults
    $.fn.editable.defaults.url = '/post';
    //$.fn.editable.defaults.mode = 'inline'; use this to edit inline

    //enable / disable
    $('#enable').click(function () {
        $('#user .editable').editable('toggleDisabled');
    });

    //editables
    $('.savedSearchName').editable({
        success: function(response, newValue) {

            if(response && response.errors){
                //server-side validation error, response like {"errors": {"username": "username already exist"} }
                var errors = response.errors;
                var msg = '';

                if(errors && errors.responseText) { //ajax error, errors = xhr object
                    msg = errors.responseText;
                } else { //validation error (client-side or server-side)
                    $.each(errors, function(k, v) { msg += /*k+": "+*/v+"<br>"; });
                }

                $(this).addClass('editable-empty').html(msg).show();
                return false;
            }
        }
    });

    $('#firstname').editable({
        validate: function (value) {
            if ($.trim(value) == '')
                return 'This field is required';
        }
    });

    $('#sex').editable({
        prepend: "not selected",
        source: [{
            value: 1,
            text: 'Male'
        }, {
            value: 2,
            text: 'Female'
        }],
        display: function (value, sourceData) {
            var colors = {
                "": "gray",
                1: "green",
                2: "blue"
            }, elem = $.grep(sourceData, function (o) {
                return o.value == value;
            });

            if (elem.length) {
                $(this).text(elem[0].text).css("color", colors[value]);
            } else {
                $(this).empty();
            }
        }
    });

    $('#status').editable();

    $('.savedSearchEmailFreq').editable({
        showbuttons: false,
        source: <?php echo SavedSearch::getEmailFreqXEditableFormat()?>
    });

    $('.savedSearchExpiryDate').editable({
        datepicker: {
            todayBtn: 'linked'
        }
    });

    $('#dob').editable();

    $('#event').editable({
        placement: 'right',
        combodate: {
            firstItem: 'name'
        }
    });

    $('#meeting_start').editable({
        format: 'yyyy-mm-dd hh:ii',
        viewformat: 'dd/mm/yyyy hh:ii',
        validate: function (v) {
            if (v && v.getDate() == 10)
                return 'Day cant be 10!';
        },
        datetimepicker: {
            todayBtn: 'linked',
            weekStart: 1
        }
    });

    $('#comments').editable({
        showbuttons: 'bottom'
    });

    $('#note').editable();
    $('#pencil').click(function (e) {
        e.stopPropagation();
        e.preventDefault();
        $('#note').editable('toggle');
    });

    $('#state').editable({
        source: ["Alabama", "Alaska", "Arizona", "Arkansas", "California", "Colorado", "Connecticut",
            "Delaware", "Florida", "Georgia", "Hawaii", "Idaho", "Illinois", "Indiana", "Iowa", "Kansas",
            "Kentucky", "Louisiana", "Maine", "Maryland", "Massachusetts", "Michigan", "Minnesota",
            "Mississippi", "Missouri", "Montana", "Nebraska", "Nevada", "New Hampshire", "New Jersey",
            "New Mexico", "New York", "North Dakota", "North Carolina", "Ohio", "Oklahoma", "Oregon",
            "Pennsylvania", "Rhode Island", "South Carolina", "South Dakota", "Tennessee", "Texas",
            "Utah", "Vermont", "Virginia", "Washington", "West Virginia", "Wisconsin", "Wyoming"
        ]
    });

    $('#state2').editable({
        value: 'California',
        typeahead: {
            name: 'state',
            local: ["Alabama", "Alaska", "Arizona", "Arkansas", "California", "Colorado", "Connecticut",
                "Delaware", "Florida", "Georgia", "Hawaii", "Idaho", "Illinois", "Indiana", "Iowa",
                "Kansas", "Kentucky", "Louisiana", "Maine", "Maryland", "Massachusetts", "Michigan",
                "Minnesota", "Mississippi", "Missouri", "Montana", "Nebraska", "Nevada", "New Hampshire",
                "New Jersey", "New Mexico", "New York", "North Dakota", "North Carolina", "Ohio",
                "Oklahoma", "Oregon", "Pennsylvania", "Rhode Island", "South Carolina", "South Dakota",
                "Tennessee", "Texas", "Utah", "Vermont", "Virginia", "Washington", "West Virginia",
                "Wisconsin", "Wyoming"
            ]
        }
    });

    $('#fruits').editable({
        pk: 1,
        limit: 3,
        source: [{
            value: 1,
            text: 'banana'
        }, {
            value: 2,
            text: 'peach'
        }, {
            value: 3,
            text: 'apple'
        }, {
            value: 4,
            text: 'watermelon'
        }, {
            value: 5,
            text: 'orange'
        }]
    });



    var countries = [];
    $.each({
        "BD": "Bangladesh",
        "BE": "Belgium",
        "BF": "Burkina Faso",
        "BG": "Bulgaria",
        "BA": "Bosnia and Herzegovina",
        "BB": "Barbados",
        "WF": "Wallis and Futuna",
        "BL": "Saint Bartelemey",
        "BM": "Bermuda",
        "BN": "Brunei Darussalam",
        "BO": "Bolivia",
        "BH": "Bahrain",
        "BI": "Burundi",
        "BJ": "Benin",
        "BT": "Bhutan",
        "JM": "Jamaica",
        "BV": "Bouvet Island",
        "BW": "Botswana",
        "WS": "Samoa",
        "BR": "Brazil",
        "BS": "Bahamas",
        "JE": "Jersey",
        "BY": "Belarus",
        "O1": "Other Country",
        "LV": "Latvia",
        "RW": "Rwanda",
        "RS": "Serbia",
        "TL": "Timor-Leste",
        "RE": "Reunion",
        "LU": "Luxembourg",
        "TJ": "Tajikistan",
        "RO": "Romania",
        "PG": "Papua New Guinea",
        "GW": "Guinea-Bissau",
        "GU": "Guam",
        "GT": "Guatemala",
        "GS": "South Georgia and the South Sandwich Islands",
        "GR": "Greece",
        "GQ": "Equatorial Guinea",
        "GP": "Guadeloupe",
        "JP": "Japan",
        "GY": "Guyana",
        "GG": "Guernsey",
        "GF": "French Guiana",
        "GE": "Georgia",
        "GD": "Grenada",
        "GB": "United Kingdom",
        "GA": "Gabon",
        "SV": "El Salvador",
        "GN": "Guinea",
        "GM": "Gambia",
        "GL": "Greenland",
        "GI": "Gibraltar",
        "GH": "Ghana",
        "OM": "Oman",
        "TN": "Tunisia",
        "JO": "Jordan",
        "HR": "Croatia",
        "HT": "Haiti",
        "HU": "Hungary",
        "HK": "Hong Kong",
        "HN": "Honduras",
        "HM": "Heard Island and McDonald Islands",
        "VE": "Venezuela",
        "PR": "Puerto Rico",
        "PS": "Palestinian Territory",
        "PW": "Palau",
        "PT": "Portugal",
        "SJ": "Svalbard and Jan Mayen",
        "PY": "Paraguay",
        "IQ": "Iraq",
        "PA": "Panama",
        "PF": "French Polynesia",
        "BZ": "Belize",
        "PE": "Peru",
        "PK": "Pakistan",
        "PH": "Philippines",
        "PN": "Pitcairn",
        "TM": "Turkmenistan",
        "PL": "Poland",
        "PM": "Saint Pierre and Miquelon",
        "ZM": "Zambia",
        "EH": "Western Sahara",
        "RU": "Russian Federation",
        "EE": "Estonia",
        "EG": "Egypt",
        "TK": "Tokelau",
        "ZA": "South Africa",
        "EC": "Ecuador",
        "IT": "Italy",
        "VN": "Vietnam",
        "SB": "Solomon Islands",
        "EU": "Europe",
        "ET": "Ethiopia",
        "SO": "Somalia",
        "ZW": "Zimbabwe",
        "SA": "Saudi Arabia",
        "ES": "Spain",
        "ER": "Eritrea",
        "ME": "Montenegro",
        "MD": "Moldova, Republic of",
        "MG": "Madagascar",
        "MF": "Saint Martin",
        "MA": "Morocco",
        "MC": "Monaco",
        "UZ": "Uzbekistan",
        "MM": "Myanmar",
        "ML": "Mali",
        "MO": "Macao",
        "MN": "Mongolia",
        "MH": "Marshall Islands",
        "MK": "Macedonia",
        "MU": "Mauritius",
        "MT": "Malta",
        "MW": "Malawi",
        "MV": "Maldives",
        "MQ": "Martinique",
        "MP": "Northern Mariana Islands",
        "MS": "Montserrat",
        "MR": "Mauritania",
        "IM": "Isle of Man",
        "UG": "Uganda",
        "TZ": "Tanzania, United Republic of",
        "MY": "Malaysia",
        "MX": "Mexico",
        "IL": "Israel",
        "FR": "France",
        "IO": "British Indian Ocean Territory",
        "FX": "France, Metropolitan",
        "SH": "Saint Helena",
        "FI": "Finland",
        "FJ": "Fiji",
        "FK": "Falkland Islands (Malvinas)",
        "FM": "Micronesia, Federated States of",
        "FO": "Faroe Islands",
        "NI": "Nicaragua",
        "NL": "Netherlands",
        "NO": "Norway",
        "NA": "Namibia",
        "VU": "Vanuatu",
        "NC": "New Caledonia",
        "NE": "Niger",
        "NF": "Norfolk Island",
        "NG": "Nigeria",
        "NZ": "New Zealand",
        "NP": "Nepal",
        "NR": "Nauru",
        "NU": "Niue",
        "CK": "Cook Islands",
        "CI": "Cote d'Ivoire",
        "CH": "Switzerland",
        "CO": "Colombia",
        "CN": "China",
        "CM": "Cameroon",
        "CL": "Chile",
        "CC": "Cocos (Keeling) Islands",
        "CA": "Canada",
        "CG": "Congo",
        "CF": "Central African Republic",
        "CD": "Congo, The Democratic Republic of the",
        "CZ": "Czech Republic",
        "CY": "Cyprus",
        "CX": "Christmas Island",
        "CR": "Costa Rica",
        "CV": "Cape Verde",
        "CU": "Cuba",
        "SZ": "Swaziland",
        "SY": "Syrian Arab Republic",
        "KG": "Kyrgyzstan",
        "KE": "Kenya",
        "SR": "Suriname",
        "KI": "Kiribati",
        "KH": "Cambodia",
        "KN": "Saint Kitts and Nevis",
        "KM": "Comoros",
        "ST": "Sao Tome and Principe",
        "SK": "Slovakia",
        "KR": "Korea, Republic of",
        "SI": "Slovenia",
        "KP": "Korea, Democratic People's Republic of",
        "KW": "Kuwait",
        "SN": "Senegal",
        "SM": "San Marino",
        "SL": "Sierra Leone",
        "SC": "Seychelles",
        "KZ": "Kazakhstan",
        "KY": "Cayman Islands",
        "SG": "Singapore",
        "SE": "Sweden",
        "SD": "Sudan",
        "DO": "Dominican Republic",
        "DM": "Dominica",
        "DJ": "Djibouti",
        "DK": "Denmark",
        "VG": "Virgin Islands, British",
        "DE": "Germany",
        "YE": "Yemen",
        "DZ": "Algeria",
        "US": "United States",
        "UY": "Uruguay",
        "YT": "Mayotte",
        "UM": "United States Minor Outlying Islands",
        "LB": "Lebanon",
        "LC": "Saint Lucia",
        "LA": "Lao People's Democratic Republic",
        "TV": "Tuvalu",
        "TW": "Taiwan",
        "TT": "Trinidad and Tobago",
        "TR": "Turkey",
        "LK": "Sri Lanka",
        "LI": "Liechtenstein",
        "A1": "Anonymous Proxy",
        "TO": "Tonga",
        "LT": "Lithuania",
        "A2": "Satellite Provider",
        "LR": "Liberia",
        "LS": "Lesotho",
        "TH": "Thailand",
        "TF": "French Southern Territories",
        "TG": "Togo",
        "TD": "Chad",
        "TC": "Turks and Caicos Islands",
        "LY": "Libyan Arab Jamahiriya",
        "VA": "Holy See (Vatican City State)",
        "VC": "Saint Vincent and the Grenadines",
        "AE": "United Arab Emirates",
        "AD": "Andorra",
        "AG": "Antigua and Barbuda",
        "AF": "Afghanistan",
        "AI": "Anguilla",
        "VI": "Virgin Islands, U.S.",
        "IS": "Iceland",
        "IR": "Iran, Islamic Republic of",
        "AM": "Armenia",
        "AL": "Albania",
        "AO": "Angola",
        "AN": "Netherlands Antilles",
        "AQ": "Antarctica",
        "AP": "Asia/Pacific Region",
        "AS": "American Samoa",
        "AR": "Argentina",
        "AU": "Australia",
        "AT": "Austria",
        "AW": "Aruba",
        "IN": "India",
        "AX": "Aland Islands",
        "AZ": "Azerbaijan",
        "IE": "Ireland",
        "ID": "Indonesia",
        "UA": "Ukraine",
        "QA": "Qatar",
        "MZ": "Mozambique"
    }, function (k, v) {
        countries.push({
            id: k,
            text: v
        });
    });

    $('#country').editable({
        source: countries,
        select2: {
            width: 200
        }
    });

    $('.savedSearchCriteria').editable({
        url: '/post',
        value: {
            city: "Moscow",
            street: "Lenina",
            building: "12"
        },
        validate: function (value) {
            if (value.city == '')
                return 'city is required!';
        },
        display: function (value) {
            if (!value) {
                $(this).empty();
                return;
            }
            var html = '<b>' + $('<div>').text(value.city).html() + '</b>, ' + $('<div>').text(value.street)
                    .html() + ' st., bld. ' + $('<div>').text(value.building).html();
            $(this).html(html);
        }
    });

    $('#user .editable').on('hidden', function (e, reason) {
        if (reason === 'save' || reason === 'nochange') {
            var $next = $(this).closest('tr').next().find('.editable');
            if ($('#autoopen').is(':checked')) {
                setTimeout(function () {
                    $next.editable('show');
                }, 300);
            } else {
                $next.focus();
            }
        }
    });

})

</script>

<!-- Your GOOGLE ANALYTICS CODE Below -->
<script type="text/javascript">
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-XXXXXXXX-X']);
    _gaq.push(['_trackPageview']);

    (function() {
        var ga = document.createElement('script');
        ga.type = 'text/javascript';
        ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(ga, s);
    })();

</script>