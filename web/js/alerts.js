
var PageSearchesAlertsHelper = {
    search_fld:null,
    componentFormSearchFld:{
        street_number_searchfld: 'short_name',
        route_searchfld: 'long_name',
        locality_searchfld: 'long_name',
        administrative_area_level_1_searchfld: 'short_name',
        country_searchfld: 'long_name',
        postal_code_searchfld: 'short_name'
    },

    fillInAddressSearchFld : function(){

        var place = this.search_fld.getPlace();

        for (var component in PageSearchesAlertsHelper.componentFormSearchFld) {

            if (!PageSearchesAlertsHelper.componentFormSearchFld.hasOwnProperty(component)) continue;

            document.getElementById(component).value = '';
            document.getElementById(component).disabled = false;
        }
        for (var i = 0; i < place.address_components.length; i++) {

            var addressType = place.address_components[i].types[0];
            if (PageSearchesAlertsHelper.componentFormSearchFld[addressType+'_searchfld']) {
                var val = place.address_components[i][PageSearchesAlertsHelper.componentFormSearchFld[addressType+'_searchfld']];
                document.getElementById(addressType+'_searchfld').value = val;
            }
        }
    },

    deleteSavedSearch:function(clickedLink){
        if(typeof clickedLink == 'undefined')
            return false;

        var href = $(clickedLink).attr('href');
        var data = {
            id:parseInt($(clickedLink).data('id'))
        };

        $.when( this.sendAjaxDeleteSavedSearch(href,data))
            .then(
                function( data, textStatus, jqXHR ) {
                    var $tr = $(clickedLink).closest('tr');
                    $tr.hide(400, function(){
                        $tr.remove();
                    });
                }
            );
    },

    sendAjaxDeleteSavedSearch:function(url, data){
        return jQuery.ajax(
            {'type':'POST',
                'data':data,
                'url':url
            });
    },

    editableInitEmails:function(el){
        $(el).editable({
            success: function(response, newValue) {

                if(response && response.new_id){

                    var cloneAddElement;
                    cloneAddElement = $(this).clone(false);
                    $(cloneAddElement).empty();
                    PageSearchesAlertsHelper.editableInitEmails(cloneAddElement);
                    $(this).closest('td').append($('<p>').append(cloneAddElement));

                    //set pk
                    $(this).editable('option', 'pk', response.new_id);

                    // remove unsaved class
                    $(this).removeClass('editable-unsaved');
                }else if(response && response.deleted_id){

                    $(this).closest('p').hide(200, function(){$(this).remove()});

                }else if(response && response.errors){


                    //server-side validation error, response like {"errors": {"username": "username already exist"} }
                    var errors = response.errors;
                    var msg = '';

                    if(errors && errors.responseText) { //ajax error, errors = xhr object
                        msg = errors.responseText;
                    } else { //validation error (client-side or server-side)
                        $.each(errors, function(k, v) { msg += /*k+": "+*/v+"<br>"; });
                    }

                    $(this).html(msg).show();

                    return false;

                }
            }
        });

        $(el).editable('option', 'params', {
            'saved_search_id' : parseInt($(el).data('saved_search_id'))
        });
    }
};

$( document ).ready(function() {

    $('.dropdown-toggle').dropdown();

    // This will override the function used when setting jQuery UI dialog titles, allowing it to contain HTML.
    // http://stackoverflow.com/questions/14488774/using-html-in-a-dialogs-title-in-jquery-ui-1-10
    $.widget("ui.dialog", $.extend({}, $.ui.dialog.prototype, {
        _title: function(title) {
            if (!this.options.title ) {
                title.html("&#160;");
            } else {
                title.html(this.options.title);
            }
        }
    }));

    PageSearchesAlertsHelper.search_fld = new google.maps.places.Autocomplete(document.getElementById('search-fld'),{ types: ['geocode'] });
    google.maps.event.addListener(PageSearchesAlertsHelper.search_fld, 'place_changed', function() { PageSearchesAlertsHelper.fillInAddressSearchFld(); });


    // Dialog click
    $('.dialog_delete_link').click(function() {
        $("#dialog_deleteItem").dialog( "option", { clickedElement: this } );
        $("#dialog_deleteItem").dialog('open');
        return false;
    });

    $('#dialog_deleteItem').dialog({
        autoOpen : false,
        width : 600,
        resizable : false,
        modal : true,
        title : "<div class='widget-header'><h4><i class='fa fa-warning'></i> Delete item?</h4></div>",
        buttons : [{
            html : "<i class='fa fa-trash-o'></i>&nbsp; Delete item",
            "class" : "btn btn-danger",
            click : function() {
                $(this).dialog("close");
                PageSearchesAlertsHelper.deleteSavedSearch($(this).dialog("option", 'clickedElement'));
            }
        }, {
            html : "<i class='fa fa-times'></i>&nbsp; Cancel",
            "class" : "btn btn-default",
            click : function() {
                $(this).dialog("close");
                //PageSearchesAlertsHelper.deleteSavedSearch(false, $(this));
            }
        }]
    });

    // EDITABLES
    $('.savedSearchLinkedEmails').each(function(){
        PageSearchesAlertsHelper.editableInitEmails(this);
    });


    /*$("table.table-recent-search-results").dataTable({
     "sDom": '<\'dt-top-row\'lf>r<\'dt-wrapper\'t><\'dt-row dt-bottom-row\'<\'row\'<\'col-sm-6\'i><\'col-sm-6 text-right\'p>>',
     "aaSorting": []
     });*/

});
