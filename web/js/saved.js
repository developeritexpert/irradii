;(function ($) {

    $(document).ready(function ($,table) {
        var table = $('#wid-id-saved-properties-table').find('table');
        var dataTable;
        var ths = table.find('th').toArray();
        var newArr = [];
        var excluded_statuses = [];
        var data = {};
        if(table.length != 0){
            getExcluded();
            getProperties(excluded_statuses);
        }

        function tableBuild (properties){
            if(dataTable){ dataTable.fnDestroy();}

            dataTable = table.dataTable({
                'sPaginationType' : 'bootstrap',
                'sDom': '<\'dt-top-row\'Clf>r<\'dt-wrapper\'t><\'dt-row dt-bottom-row\'<\'row\'<\'col-sm-6\'i><\'col-sm-6 text-right\'p>>',
                'aaData': properties,
                'bDeferRender': true,
                'iDisplayLength' : 25,
                'bAutoWidth': false,
                'stateSave': false,
                'bProcessing':true,
                'aoColumns': [
                    { 'bVisible' : true },	/*Value*/
                    { 'bVisible' : true },	/*Address*/
                    { 'bVisible' : true },	/*Status*/
                    { 'bVisible' : true },  /*List Price*/

                    { 'bVisible' : false }, /*Sale Price*/
                    { 'bVisible' : false }, /*Sale Type*/

                    { 'bVisible' : true },  /*Date*/

                    { 'bVisible' : false }, /*$/SqFt*/

                    { 'bVisible' : true },  /*Sq Ft*/
                    { 'bVisible' : true },	/*Bed*/
                    { 'bVisible' : true },	/*Bath*/

                    { 'bVisible' : false }, /*Garages*/
                    { 'bVisible' : false }, /*Lot*/
                    { 'bVisible' : false }, /*Yr Blt*/
                    { 'bVisible' : false }, /*Stores*/
                    { 'bVisible' : false }, /*Pool*/
                    { 'bVisible' : false }, /*Spa*/
                    { 'bVisible' : false }, /*Conditions*/

                    { 'bVisible' : false }, /*Hause Faces*/
                    { 'bVisible' : false }, /*Hause Views*/
                    { 'bVisible' : false }, /*Flooring*/
                    { 'bVisible' : false }, /*Furnishing*/
                    { 'bVisible' : false }, /*Financing*/

                    { 'bVisible' : false }, /*Foreclosure*/
                    { 'bVisible' : false }, /*Short Sale*/
                    { 'bVisible' : false }, /*Bank Owned*/
                    { 'bVisible' : false }, /*Original Price*/
                    { 'bVisible' : false } /*Days on Market*/
                ],
                'bScrollCollapse': false,
                // 'fnDrawCallback': function () {
                // },
                bStateSave: true,
                fnStateSave: function(oSettings, oData) {
                    localStorage.setItem('DataTables_' + this.fnSettings().sTableId, JSON.stringify(oData));
                },
                fnStateLoad: function (oSettings) {
                    return JSON.parse(localStorage.getItem('DataTables_' + this.fnSettings().sTableId));
                },
                'fnInitComplete' : function(oSettings, json) {
                    $('.ColVis_Button').addClass('btn btn-default btn-sm').html('Columns');
                }
            });
        }

        /* TABLE TOOLS */
        $('#datatable_tabletools').dataTable({
            'sDom' : '<\"dt-top-row\"Tlf>r<\"dt-wrapper\"t><\"dt-row dt-bottom-row\"><\"row\"<\"col-sm-6\"i><\"col-sm-6 text-right\"p>>',
            'oTableTools' : {
                'aButtons' : ['copy', 'print', {
                    'sExtends' : 'collection',
                    'sButtonText' : 'Save <span class=\"caret\" />',
                    'aButtons' : ['csv', 'xls', 'pdf']
                }],
                'sSwfPath' : '//js1.irradii.com/assets/js/plugin/datatables/media/swf/copy_csv_xls_pdf.swf'
            },
            'fnInitComplete' : function(oSettings, json) {
                $(this).closest('#dt_table_tools_wrapper').find('.DTTT.btn-group').addClass('table_tools_group').children('a.btn').each(function() {
                    $(this).addClass('btn-sm btn-default');
                });
            }
        });


        /* END TABLE TOOLS */
        $('.status_filter .select2').on('change', function() {
            getExcluded();
            getProperties(excluded_statuses);
        });

        function getProperties(excluded_statuses) {
            ths.forEach(function(item,i,arr){
                newArr.push(item.textContent);
            });
            data = {
                fields: newArr,
                user_id: 10,
                excluded_statuses:excluded_statuses
            }
            data[jqueryOne('meta[name=\"csrf-param\"]').attr('content')] = jqueryOne('meta[name=\"csrf-token\"]').attr('content');
            $.ajax({
                url: '/saved/get-saved-properties',
                data: data,
                type: 'POST',
                dataType: 'json',
                cache: false,
                success: function(data){
                    tableBuild(data);
                },
                error: function(data){
//         console.log('error: ',data);
                }
            });
        }
        function getExcluded() {
            var optionNotSelected = $('.select2').find('option').not(':selected'),
                excludedStatuses = [];
            optionNotSelected.each(function(index, element) {
                excludedStatuses.push($(element).val());
            });
            excluded_statuses = excludedStatuses;
        }
    });
    jQuery(function($) { $.extend({
        form: function(url, data, method) {
            if (method == null) method = 'POST';
            if (data == null) data = {};

            var form = $('<form>').attr({
                method: method,
                action: url
            }).css({
                display: 'none'
            });

            var addData = function(name, data) {
                if ($.isArray(data)) {
                    for (var i = 0; i < data.length; i++) {
                        var value = data[i];
                        addData(name + '[]', value);
                    }
                } else if (typeof data === 'object') {
                    for (var key in data) {
                        if (data.hasOwnProperty(key)) {
                            addData(name + '[' + key + ']', data[key]);
                        }
                    }
                } else if (data != null) {
                    form.append($('<input>').attr({
                        type: 'hidden',
                        name: String(name),
                        value: String(data)
                    }));
                }
            };

            for (var key in data) {
                if (data.hasOwnProperty(key)) {
                    addData(key, data[key]);
                }
            }

            return form.appendTo('body');
        }
    }); });
})(jQuery);