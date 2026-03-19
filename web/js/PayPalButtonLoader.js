(function ($) {

    document.addEventListener("DOMContentLoaded", function () {
        var PayPalLoader = document.getElementsByClassName('startPayPalProcess');
        var cur_url = window.location.href;
        if(cur_url.indexOf('post/update') + 1){
            console.log('Editing');
            return;
        }


        if (PayPalLoader.length == 0) {
            return false;
        } else {
            for (var i = 0; i < PayPalLoader.length; i++) {
                loadButton(PayPalLoader[i]);
            }
        }

        function loadButton(elem) {
            var formBlock = $(elem),
                submitButton = formBlock.find('button'),
                formId = submitButton.attr('form'),
                paypalFormId = submitButton.data('paypal-form-id');
            $.ajax({
                url: '/user/subscription/getsubscriptionbutton',
                data: {
                    id:formId,
                    paypalFormId:paypalFormId
                },
                type: 'POST',
                dataType: 'json',
                cache: false,
                success: function(data){
                    if(data.data != null){
                        if(data.status == 'Trial used' && paypalFormId != 1){
                            submitButton.text('Trial Used, click to full access');
                            formBlock.show().append(data.data);
                        } else if(paypalFormId == 1 && data.status == "Guest"){
                            return;
                        } else if(data.status == 'Admin' || data.status == 'Membership'){
                            console.log('Your status is ' + data.status);
                            submitButton.text('Your status is ' + data.status);
                            formBlock.show().append(data.data);
                        }  else if (data.status == "Unsubscribed") {
                            formBlock.show().append(data.data);
                        } else {
                            submitButton.text('Sign in');
                            formBlock.show().append(data.data);
                        }
                    } else {
                        console.log(data.status)
                    }
                },
                error: function(data){
                    console.log(data);
                }
            });
        }
    });
})(jQuery);