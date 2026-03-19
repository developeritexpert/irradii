/**
 * Created by developer on 01.08.16.
 */
;(function ($) {
    $(document).ready(function () {
        var propertySlug = window.location.href.substr(window.location.href.lastIndexOf('/')+1);
        var targetTextarea = $('#user_property_note textarea');
        var savedValue = targetTextarea.val();
        $('.user-property-status li a').on('click',function (e) {
            var status = $(this).data('status');

            $.ajax({
                url: '/property/updateuserpropertystatus',
                data: {
                    user_property_status: status,
                    property_slug:propertySlug,
                    type:'status'
                },
                type: 'POST',
                dataType: 'json',
                cache: false,
                success: function(data){
                    if(data.status == 200){
                        toggleStatus(data.scheme);
                    }
                },
                error: function(data){
                    console.log('error: ',data);
                }
            });

            function toggleStatus(scheme) {
                var button = $('#status-button');
                var classList =$('#status-button').attr('class').split(/\s+/);
                var schemeClass = classList[classList.length - 1];

                button.html(status + ' <span class="caret"> </span>');
                button.removeClass(schemeClass);
                button.addClass(scheme);
            }
        })

        targetTextarea.on('blur',function () {
            saveNote();
        });
        setInterval(function () {
            if(targetTextarea.val() == savedValue){
                return;
            } else {
                saveNote();
            }
        },2000);

        function saveNote() {
            var thisTextarea = $('#user_property_note textarea');
            var userPropertyNote = thisTextarea.val();

            $.ajax({
                url: '/property/updateuserpropertystatus',
                data: {
                    user_property_note: userPropertyNote,
                    property_slug:propertySlug,
                    type:'note'
                },
                type: 'POST',
                dataType: 'json',
                cache: false,
                success: function(data){
                    if(data.status == 200){
                        savedValue = data.note;
                    }
                },
                error: function(data){
                    console.log('error: ',data);
                }
            });
        }
    });
})(jQuery);
