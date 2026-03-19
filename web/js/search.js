
$(document).ready(function($) {

    /*
     * BASIC
     */
    $('#dt_basic').dataTable({
        "sPaginationType": "bootstrap_full"
    });
    /* END BASIC */

    /* Add the events etc before DataTables hides a column */
    $("#datatable_fixed_column thead input").keyup(function() {
        oTable.fnFilter(this.value, oTable.oApi._fnVisibleToColumnIndex(oTable.fnSettings(), $("thead input").index(this)));
    });
    $("#datatable_fixed_column thead input").each(function(i) {
        this.initVal = this.value;
    });
    $("#datatable_fixed_column thead input").focus(function() {
        if (this.className === "search_init") {
            this.className = "";
            this.value = "";
        }
    });
    $("#datatable_fixed_column thead input").blur(function(i) {
        if (this.value === "") {
            this.className = "search_init";
            this.value = this.initVal;
        }
    });
    var oTable = $('#datatable_fixed_column').dataTable({
        "sDom": "<'dt-top-row'><'dt-wrapper't><'dt-row dt-bottom-row'><'row'<'col-sm-6'i><'col-sm-6 text-right'p>>",
        //"sDom" : "t<'row dt-wrapper'<'col-sm-6'i><'dt-row dt-bottom-row'<'row'<'col-sm-6'i><'col-sm-6 text-right'>>",
        "oLanguage": {
            "sSearch": "Search all columns:"
        },
        "bSortCellsTop": true
    });
    /*
     * COL ORDER
     */
    $('.datatable_col_reorder').dataTable({
        "sPaginationType": "bootstrap",
        "sDom": "R<'dt-top-row'Clf>r<'dt-wrapper't><'dt-row dt-bottom-row'><'row'<'col-sm-6'i><'col-sm-6 text-right'p>>",
        "fnInitComplete": function(oSettings, json) {
            $('.ColVis_Button').addClass('btn btn-default btn-sm').html('Columns <i class="icon-arrow-down"></i>');
        }
    });
    /* END COL ORDER */

    /* TABLE TOOLS */





    /* END TABLE TOOLS */
    /*
     * Autostart Carousel
     */
    $('.carousel.slide').carousel({
        interval: 3000,
        cycle: true
    });
    $('.carousel.fade').carousel({
        interval: 3000,
        cycle: true
    });
    // Fill all progress bars with animation

    $('.progress-bar').progressbar({
        display_text: 'fill'
    });
    /*
     * Smart Notifications
     */
    $('#eg1').click(function(e) {
        $('#alert_guest').fadeOut(200);
        $.bigBox({
            title: "Save Your Search Results",
            content: "Enter a search name or keyword to save this search to your Searches/ Alerts tab!",
            color: "#C46A69",
            //timeout: 6000,
            icon: "fa fa-warning shake animated",
            number: "1",
            timeout: 6000
        });
        e.preventDefault();
    });

    $('#eg2').click(function(e) {

        $.bigBox({
            title: "Big Information box",
            content: "Lorem ipsum dolor sit amet, test consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam",
            color: "#3276B1",
            //timeout: 8000,
            icon: "fa fa-bell swing animated",
            number: "2"
        });
        e.preventDefault();
    });

    $('#eg3').click(function(e) {

        $.bigBox({
            title: "Shield is up and running!",
            content: "Lorem ipsum dolor sit amet, test consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam",
            color: "#C79121",
            //timeout: 8000,
            icon: "fa fa-shield fadeInLeft animated",
            number: "3"
        });
        e.preventDefault();
    });

    $('#eg4').click(function(e) {

        $.bigBox({
            title: "Success Message Example",
            content: "Lorem ipsum dolor sit amet, test consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam",
            color: "#739E73",
            //timeout: 8000,
            icon: "fa fa-check",
            number: "4"
        }, function() {
            closedthis();
        });
        e.preventDefault();
    });



    $('#eg5').click(function() {

        $.smallBox({
            title: "Ding Dong!",
            content: "Someone's at the door...shall one get it sir? <p class='text-align-right'><a href='javascript:void(0);' class='btn btn-primary btn-sm'>Yes</a> <a href='javascript:void(0);' class='btn btn-danger btn-sm'>No</a></p>",
            color: "#296191",
            //timeout: 8000,
            icon: "fa fa-bell swing animated"
        });
    });
    $('#eg6').click(function() {

        $.smallBox({
            title: "Big Information box",
            content: "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam",
            color: "#5384AF",
            //timeout: 8000,
            icon: "fa fa-bell"
        });
    });

    $('#eg7').click(function() {

        $.smallBox({
            title: "James Simmons liked your comment",
            content: "<i class='fa fa-clock-o'></i> <i>2 seconds ago...</i>",
            color: "#296191",
            iconSmall: "fa fa-thumbs-up bounce animated",
            timeout: 4000
        });
    });

    function closedthis() {
        $.smallBox({
            title: "Great! You just closed that last alert!",
            content: "This message will be gone in 5 seconds!",
            color: "#739E73",
            iconSmall: "fa fa-cloud",
            timeout: 5000
        });
    }

    /*
     * SmartAlerts
     */
// With Callback
    $("#smart-mod-eg1").click(function(e) {
        $.SmartMessageBox({
            title: "Smart Alert!",
            content: "This is a confirmation box. Can be programmed for button callback",
            buttons: '[No][Yes]'
        }, function(ButtonPressed) {
            if (ButtonPressed === "Yes") {

                $.smallBox({
                    title: "Callback function",
                    content: "<i class='fa fa-clock-o'></i> <i>You pressed Yes...</i>",
                    color: "#659265",
                    iconSmall: "fa fa-check fa-2x fadeInRight animated",
                    timeout: 4000
                });
            }
            if (ButtonPressed === "No") {
                $.smallBox({
                    title: "Callback function",
                    content: "<i class='fa fa-clock-o'></i> <i>You pressed No...</i>",
                    color: "#C46A69",
                    iconSmall: "fa fa-times fa-2x fadeInRight animated",
                    timeout: 4000
                });
            }

        });
        e.preventDefault();
    });
    // With Input
    $("#smart-mod-eg2").click(function(e) {

        $.SmartMessageBox({
            title: "Smart Alert: Input",
            content: "Please enter your user name",
            buttons: "[Accept]",
            input: "text",
            placeholder: "Enter your user name"
        }, function(ButtonPress, Value) {
            alert(ButtonPress + " " + Value);
        });
        e.preventDefault();
    });
    // With Buttons
    $("#smart-mod-eg3").click(function(e) {

        $.SmartMessageBox({
            title: "Smart Notification: Buttons",
            content: "Lots of buttons to go...",
            buttons: '[Need?][You][Do][Buttons][Many][How]'
        });
        e.preventDefault();
    });
    // With Select
    $("#smart-mod-eg4").click(function(e) {

        $.SmartMessageBox({
            title: "Smart Alert: Select",
            content: "You can even create a group of options.",
            buttons: "[Done]",
            input: "select",
            options: "[Costa Rica][United States][Autralia][Spain]"
        }, function(ButtonPress, Value) {
            alert(ButtonPress + " " + Value);
        });
        e.preventDefault();
    });
    // With Login
    $("#smart-mod-eg5").click(function(e) {

        $.SmartMessageBox({
            title: "Login form",
            content: "Please enter your user name",
            buttons: "[Cancel][Accept]",
            input: "text",
            placeholder: "Enter your user name"
        }, function(ButtonPress, Value) {
            if (ButtonPress === "Cancel") {
                alert("Why did you cancel that? :(");
                return 0;
            }

            Value1 = Value.toUpperCase();
            ValueOriginal = Value;
            $.SmartMessageBox({
                title: "Hey! <strong>" + Value1 + ",</strong>",
                content: "And now please provide your password:",
                buttons: "[Login]",
                input: "password",
                placeholder: "Password"
            }, function(ButtonPress, Value) {
                alert("Username: " + ValueOriginal + " and your password is: " + Value);
            });
        });
        e.preventDefault();
    });
    // PAGE RELATED SCRIPTS

    var colorful_style = [{
        "featureType": "landscape",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "transit",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi.park",
        "elementType": "labels",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi.park",
        "elementType": "geometry.fill",
        "stylers": [{
            "color": "#d3d3d3"
        }, {
            "visibility": "on"
        }]
    }, {
        "featureType": "road",
        "elementType": "geometry.stroke",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "landscape",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#b1bc39"
        }]
    }, {
        "featureType": "landscape.man_made",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#ebad02"
        }]
    }, {
        "featureType": "water",
        "elementType": "geometry.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#416d9f"
        }]
    }, {
        "featureType": "road",
        "elementType": "labels.text.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#000000"
        }]
    }, {
        "featureType": "road",
        "elementType": "labels.text.stroke",
        "stylers": [{
            "visibility": "off"
        }, {
            "color": "#ffffff"
        }]
    }, {
        "featureType": "administrative",
        "elementType": "labels.text.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#000000"
        }]
    }, {
        "featureType": "road",
        "elementType": "geometry.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#ffffff"
        }]
    }, {
        "featureType": "road",
        "elementType": "labels.icon",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "water",
        "elementType": "labels",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi",
        "elementType": "geometry.fill",
        "stylers": [{
            "color": "#ebad02"
        }]
    }, {
        "featureType": "poi.park",
        "elementType": "geometry.fill",
        "stylers": [{
            "color": "#8ca83c"
        }]
    }];
    // Grey Scale
    var greyscale_style = [{
        "featureType": "road.highway",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "landscape",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "transit",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi.park",
        "stylers": [{
            "visibility": "on"
        }]
    }, {
        "featureType": "poi.park",
        "elementType": "labels",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi.park",
        "elementType": "geometry.fill",
        "stylers": [{
            "color": "#d3d3d3"
        }, {
            "visibility": "on"
        }]
    }, {
        "featureType": "poi.medical",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi.medical",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "road",
        "elementType": "geometry.stroke",
        "stylers": [{
            "color": "#cccccc"
        }]
    }, {
        "featureType": "water",
        "elementType": "geometry.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#cecece"
        }]
    }, {
        "featureType": "road.local",
        "elementType": "labels.text.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#808080"
        }]
    }, {
        "featureType": "administrative",
        "elementType": "labels.text.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#808080"
        }]
    }, {
        "featureType": "road",
        "elementType": "geometry.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#fdfdfd"
        }]
    }, {
        "featureType": "road",
        "elementType": "labels.icon",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "water",
        "elementType": "labels",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi",
        "elementType": "geometry.fill",
        "stylers": [{
            "color": "#d2d2d2"
        }]
    }];
    // Black & White
    var monochrome_style = [{
        "featureType": "road.highway",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "landscape",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "transit",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi.park",
        "elementType": "labels",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi.park",
        "elementType": "geometry.fill",
        "stylers": [{
            "color": "#d3d3d3"
        }, {
            "visibility": "on"
        }]
    }, {
        "featureType": "road",
        "elementType": "geometry.stroke",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "landscape",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#ffffff"
        }]
    }, {
        "featureType": "water",
        "elementType": "geometry.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#cecece"
        }]
    }, {
        "featureType": "road",
        "elementType": "labels.text.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#000000"
        }]
    }, {
        "featureType": "road",
        "elementType": "labels.text.stroke",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#ffffff"
        }]
    }, {
        "featureType": "administrative",
        "elementType": "labels.text.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#000000"
        }]
    }, {
        "featureType": "road",
        "elementType": "geometry.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#000000"
        }]
    }, {
        "featureType": "road",
        "elementType": "labels.icon",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "water",
        "elementType": "labels",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi",
        "elementType": "geometry.fill",
        "stylers": [{
            "visibility": "off"
        }]
    }];
    // Retro
    var metro_style = [{
        "featureType": "transit",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi.park",
        "elementType": "geometry.fill",
        "stylers": [{
            "color": "#d3d3d3"
        }, {
            "visibility": "on"
        }]
    }, {
        "featureType": "road",
        "elementType": "geometry.stroke",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "landscape",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#eee8ce"
        }]
    }, {
        "featureType": "water",
        "elementType": "geometry.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#b8cec9"
        }]
    }, {
        "featureType": "road",
        "elementType": "labels.text.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#000000"
        }]
    }, {
        "featureType": "road",
        "elementType": "labels.text.stroke",
        "stylers": [{
            "visibility": "off"
        }, {
            "color": "#ffffff"
        }]
    }, {
        "featureType": "administrative",
        "elementType": "labels.text.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#000000"
        }]
    }, {
        "featureType": "road",
        "elementType": "geometry.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#ffffff"
        }]
    }, {
        "featureType": "road",
        "elementType": "geometry.stroke",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "road",
        "elementType": "labels.icon",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "water",
        "elementType": "labels",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi",
        "elementType": "geometry.fill",
        "stylers": [{
            "color": "#d3cdab"
        }]
    }, {
        "featureType": "poi.park",
        "elementType": "geometry.fill",
        "stylers": [{
            "color": "#ced09d"
        }]
    }, {
        "featureType": "poi",
        "elementType": "labels",
        "stylers": [{
            "visibility": "off"
        }]
    }];
    // Night
    var nightvision_style = [{
        "featureType": "landscape",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "transit",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi.park",
        "elementType": "labels",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi.park",
        "elementType": "geometry.fill",
        "stylers": [{
            "color": "#d3d3d3"
        }, {
            "visibility": "on"
        }]
    }, {
        "featureType": "road",
        "elementType": "geometry.stroke",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "landscape",
        "stylers": [{
            "visibility": "on"
        }, {
            "hue": "#0008ff"
        }, {
            "lightness": -75
        }, {
            "saturation": 10
        }]
    }, {
        "elementType": "geometry.stroke",
        "stylers": [{
            "color": "#1f1d45"
        }]
    }, {
        "featureType": "landscape.natural",
        "stylers": [{
            "color": "#1f1d45"
        }]
    }, {
        "featureType": "water",
        "elementType": "geometry.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#01001f"
        }]
    }, {
        "elementType": "labels.text.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#e7e8ec"
        }]
    }, {
        "elementType": "labels.text.stroke",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#151348"
        }]
    }, {
        "featureType": "administrative",
        "elementType": "labels.text.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#f7fdd9"
        }]
    }, {
        "featureType": "administrative",
        "elementType": "labels.text.stroke",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#01001f"
        }]
    }, {
        "featureType": "road",
        "elementType": "geometry.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#316694"
        }]
    }, {
        "featureType": "road",
        "elementType": "labels.icon",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "water",
        "elementType": "labels",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi",
        "elementType": "geometry.fill",
        "stylers": [{
            "color": "#1a153d"
        }]

    }];
    // Night Light
    var nightvision_highlight_style = [{
        "elementType": "geometry",
        "stylers": [{
            "visibility": "on"
        }, {
            "hue": "#232a57"
        }]
    }, {
        "featureType": "road.highway",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "landscape",
        "elementType": "geometry.fill",
        "stylers": [{
            "hue": "#0033ff"
        }, {
            "saturation": 13
        }, {
            "lightness": -77
        }]
    }, {
        "featureType": "landscape",
        "elementType": "geometry.stroke",
        "stylers": [{
            "color": "#4657ab"
        }]
    }, {
        "featureType": "transit",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "road",
        "elementType": "geometry.stroke",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "water",
        "elementType": "geometry.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#0d0a1f"
        }]
    }, {
        "elementType": "labels.text.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#d2cfe3"
        }]
    }, {
        "elementType": "labels.text.stroke",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#0d0a1f"
        }]
    }, {
        "featureType": "administrative",
        "elementType": "labels.text.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#ffffff"
        }]
    }, {
        "featureType": "administrative",
        "elementType": "labels.text.stroke",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#0d0a1f"
        }]
    }, {
        "featureType": "road",
        "elementType": "geometry.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#ff9910"
        }]
    }, {
        "featureType": "road.local",
        "elementType": "geometry.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#4657ab"
        }]
    }, {
        "featureType": "road",
        "elementType": "labels.icon",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "water",
        "elementType": "labels",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi",
        "elementType": "geometry.fill",
        "stylers": [{
            "color": "#232a57"
        }]
    }, {
        "featureType": "poi.park",
        "elementType": "geometry.fill",
        "stylers": [{
            "color": "#232a57"
        }]
    }, {
        "featureType": "poi",
        "elementType": "labels",
        "stylers": [{
            "visibility": "off"
        }]
    }];
    // Papiro
    var old_paper_style = [{
        "elementType": "geometry",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#f2e48c"
        }]
    }, {
        "featureType": "road.highway",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "transit",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi.park",
        "elementType": "labels",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi.park",
        "elementType": "geometry.fill",
        "stylers": [{
            "color": "#d3d3d3"
        }, {
            "visibility": "on"
        }]
    }, {
        "featureType": "road",
        "elementType": "geometry.stroke",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "landscape",
        "elementType": "geometry.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#f2e48c"
        }]
    }, {
        "featureType": "landscape",
        "elementType": "geometry.stroke",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#592c00"
        }]
    }, {
        "featureType": "water",
        "elementType": "geometry.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#a77637"
        }]
    }, {
        "elementType": "labels.text.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#592c00"
        }]
    }, {
        "elementType": "labels.text.stroke",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#f2e48c"
        }]
    }, {
        "featureType": "administrative",
        "elementType": "labels.text.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#592c00"
        }]
    }, {
        "featureType": "administrative",
        "elementType": "labels.text.stroke",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#f2e48c"
        }]
    }, {
        "featureType": "road",
        "elementType": "geometry.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#a5630f"
        }]
    }, {
        "featureType": "road.highway",
        "elementType": "geometry.fill",
        "stylers": [{
            "visibility": "on"
        }, {
            "color": "#592c00"
        }]
    }, {
        "featureType": "road",
        "elementType": "labels.icon",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "water",
        "elementType": "labels",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi",
        "elementType": "geometry.fill",
        "stylers": [{
            "visibility": "off"
        }]
    }, {
        "featureType": "poi",
        "elementType": "labels",
        "stylers": [{
            "visibility": "off"
        }]
    }];
    // One color - Change the hue value for your desired color
    var mono_color_style = [{
        "stylers": [{
            "hue": "#ff00aa"
        }, {
            "saturation": 1
        }, {
            "lightness": 1
        }]
    }];
    /*
     * Google Maps Initialize
     */


    function xmlLoadMap() {

        var centerLatLng = new google.maps.LatLng(data_lat, data_lng),
            mapOptions = {
                zoom: zoom_level,
                center: centerLatLng,
                //disableDefaultUI: true,
                //mapTypeId : google.maps.MapTypeId.ROADMAP
                mapTypeControlOptions: {
                    mapTypeIds: [google.maps.MapTypeId.TERRAIN, 'colorful_style', 'greyscale_style',
                        'monochrome_style', 'metro_style', 'nightvision_style', 'nightvision_highlight_style',
                        'old_paper_style', 'mono_color_style'
                    ]
                }
            },
            bounds = new google.maps.LatLngBounds(),
            infowindow = new google.maps.InfoWindow(),
            map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
        map.mapTypes.set('colorful_style', colorfulStyleMap);
//                map.mapTypes.set('greyscale_style', greyStyleMap);
//                map.mapTypes.set('monochrome_style', monoChromeStyleMap);
//                map.mapTypes.set('metro_style', metroStyleMap);
//                map.mapTypes.set('nightvision_style', nightvisionStyleMap);
//                map.mapTypes.set('nightvision_highlight_style', nvisionhstyleMap);
//                map.mapTypes.set('old_paper_style', oPaperStyleMap);
//                map.mapTypes.set('mono_color_style', monoColorStyleMap);

        map.setMapTypeId(google.maps.MapTypeId.TERRAIN);
        //map.setMapTypeId('colorful_style');

        /*

         $.get(xml_src, function(data) {

         // create the Bounds object
         var bounds = new google.maps.LatLngBounds();

         $(data).find("marker").each(function(){

         var eachMarker = jQuery(this),
         // grab the address from XML
         theAddress = eachMarker.find("address").text(),
         mygc = new google.maps.Geocoder(theAddress);

         mygc.geocode({'address' : theAddress}, function(results, status) {

         var mLat = results[0].geometry.location.lat(),
         Long = results[0].geometry.location.lng(),

         marker = new google.maps.Marker({
         position : new google.maps.LatLng(mLat, Long),
         map : map,
         icon : ('img/' + eachMarker.find("icons").text() + '.png'),
         scrollwheel : false,
         streetViewControl : true,
         title : eachMarker.find("name").text()
         }),

         link = "link";

         google.maps.event.addListener(marker, 'click', function() {// click
         // Setting the content of the InfoWindow
         var contentString = '<div id="info-map" style="width:300px; height:85px; padding:0px;"><div>' + '<div style="display:inline-block; width:86px; verticle-align:top; float:left;"><img src=' + eachMarker.find("image").text() + ' class="thumbnail" style="width:80%; verticle-align:top;" /></div>' + '<div style="display:inline-block; width:200px; float:left;"><h6>' + eachMarker.find("name").text() + '</h6><b>' + eachMarker.find("address").text() + '</b><br/>' + '<p><a href="' + eachMarker.find("link").text() + '" class="btn btn-mini pull-right"><i class="fa fa-map-marker"></i>More Info</a></p>' + '</div></div></div>';
         infowindow.setContent(contentString);
         infowindow.open(map, marker);

         google.maps.event.addListener(map, 'click', function() {
         infowindow.close()
         })
         });

         });// end geocode

         });// end find marker loop

         });	// end get data

         */
    } // end xmlLoadMap
    // grey
    function generate_map_1() {

        var mapOptions = {
            center: new google.maps.LatLng(36.175, -115.1363889),
            zoom: 12
        };
        map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
        // Setup skin for the map
        map.mapTypes.set('greyscale_style', greyStyleMap);
        map.setMapTypeId('greyscale_style');
        /* map.mapTypes.set('colorful_style', colorfulStyleMap);
         map.mapTypes.set('greyscale_style', greyStyleMap);
         map.mapTypes.set('monochrome_style', monoChromeStyleMap);
         map.mapTypes.set('metro_style', metroStyleMap);
         map.mapTypes.set('nightvision_style', nightvisionStyleMap);
         map.mapTypes.set('nightvision_highlight_style', nvisionhstyleMap);
         map.mapTypes.set('old_paper_style', oPaperStyleMap);
         map.mapTypes.set('mono_color_style', monoColorStyleMap);
         */

    }

    // colorful
    function generate_map_2() {

        var mapOptions = {
            center: new google.maps.LatLng(geolocation.k, geolocation.A),
            zoom: 12,
            minZoom: 2
        };
        map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
        // Setup skin for the map
        //map.mapTypes.set('colorful_style', colorfulStyleMap);
        //map.setMapTypeId('colorful_style');

    }

    // colorful
    function generate_map_3() {

        var mapOptions = {
            center: new google.maps.LatLng(36.175, -115.1363889),
            zoom: 12
        };
        map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
        // Setup skin for the map
        map.mapTypes.set('nightvision_style', nvisionhstyleMap);
        map.setMapTypeId('nightvision_style');
    }

    // Night Light
    function generate_map_4() {

        var mapOptions = {
            center: new google.maps.LatLng(36.175, -115.1363889),
            zoom: 12
        };
        map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
        // Setup skin for the map
        map.mapTypes.set('nightvision_highlight_style', nvisionhstyleMap);
        map.setMapTypeId('nightvision_highlight_style');
    }

    // Paper Style
    function generate_map_5() {

        var mapOptions = {
            center: new google.maps.LatLng(36.175, -115.1363889),
            zoom: 12
        };
        map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
        // Setup skin for the map
        map.mapTypes.set('old_paper_style', oPaperStyleMap);
        map.setMapTypeId('old_paper_style');
        /*
         map.mapTypes.set('monochrome_style', monoChromeStyleMap);
         map.mapTypes.set('metro_style', metroStyleMap);
         map.mapTypes.set('mono_color_style', monoColorStyleMap);
         */

    }

    // One Color Style
    function generate_map_6() {

        var mapOptions = {
            center: new google.maps.LatLng(36.175, -115.1363889),
            zoom: 12
        };
        map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
        // Setup skin for the map
        map.mapTypes.set('mono_color_style', monoColorStyleMap);
        map.setMapTypeId('mono_color_style');
    }

    // Monochrome Style
    function generate_map_8() {

        var mapOptions = {
            center: new google.maps.LatLng(36.175, -115.1363889),
            zoom: 12
        };
        map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
        // Setup skin for the map
        map.mapTypes.set('monochrome_style', monoChromeStyleMap);
        map.setMapTypeId('monochrome_style');
    }

    // Metro Style
    function generate_map_7() {

        var mapOptions = {
            center: new google.maps.LatLng(36.175, -115.1363889),
            zoom: 12
        };
        map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
        // Setup skin for the map
        map.mapTypes.set('metro_style', metroStyleMap);
        map.setMapTypeId('metro_style');
    }




});