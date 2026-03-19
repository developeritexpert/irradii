<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\RegistrationForm;

/* @var $this yii\web\View */
/* @var $model app\models\RegistrationForm */

$this->title = Yii::$app->name . ' - Registration';
$this->params['body_ID']     = 'login';
$this->params['body_onload'] = 'initialize()';
$this->params['signin']      = Html::a('Sign In', ['/user/login'], ['class' => 'btn btn-danger']);

?>

<div id="main" role="main">

    <!-- MAIN CONTENT -->
    <div id="content" class="container">
        <div class="row">

            <!-- LEFT PANEL -->
            <div class="col-xs-12 col-sm-12 col-md-7 col-lg-7 hidden-xs hidden-sm">
                <div class="row">
                    <div class="hero">
                        <div class="pull-left login-desc-box-ll col-xs-12 col-sm-12 col-md-6 col-lg-5">
                            <h1 class="txt-color-red login-header-big">Irradii Real Estate</h1>
                            <h4 class="paragraph-header">
                                Real estate search just got a whole lot smarter!
                                Irradii is your eye into a market full of valuable real estate opportunities all around you.
                            </h4>
                            <div class="login-app-icons">
                                <a href="<?= Url::to(['/property/search']) ?>" class="btn btn-danger btn-sm">Search Now</a>
                                <a href="<?= Url::to(['/blog/index']) ?>" class="btn btn-danger btn-sm">Learn more</a>
                            </div>
                        </div>
                        <div class="pull-right login-desc-box-ll col-xs-12 col-sm-12 col-md-6 col-lg-7" style="overflow: hidden;">
                            <img src="<?= CPathCDN::baseurl('img') ?>/img/demo/map_results.png"
                                 class="pull-right map_results_main_page" alt="">
                        </div>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                        <h5 class="about-heading">Find real estate opportunities all around you!</h5>
                        <p>
                            Our patent pending search technology crunches data on millions of
                            property records each night to filter and find the best market
                            value opportunities available each morning. Wake up each morning to a list of
                            properties available for sale today that are 20% - 50%+ below market value!
                        </p>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                        <h5 class="about-heading">Our Promise to You -</h5>
                        <p>
                            We are dedicated to providing the most accurate real estate values,
                            with tools to help you make stronger, faster and more educated real
                            estate decisions - an edge that can save or make you tens of thousands of dollars.
                        </p>
                    </div>
                </div>
            </div>
            <!-- END LEFT PANEL -->

            <!-- RIGHT PANEL: FORM -->
            <div class="col-xs-12 col-sm-12 col-md-5 col-lg-5">
                <div class="well no-padding">

                    <?php $form = ActiveForm::begin([
                        'id'      => 'registration-form-form',
                        'options' => ['class' => 'smart-form client-form'],
                        'enableAjaxValidation' => false,
                    ]); ?>

                    <header>Registration is FREE*</header>

                    <fieldset>

                        <!-- Email (username) -->
                        <section>
                            <label class="input<?= $model->hasErrors('username') ? ' state-error' : '' ?>">
                                <i class="icon-append fa fa-user"></i>
                                <?= $form->field($model, 'username')
                                    ->textInput(['maxlength' => 130, 'placeholder' => 'Email Address'])
                                    ->label(false) ?>
                                <b class="tooltip tooltip-bottom-right">Enter your email address</b>
                            </label>
                        </section>

                        <!-- Password -->
                        <section>
                            <label class="input<?= $model->hasErrors('password') ? ' state-error' : '' ?>">
                                <i class="icon-append fa fa-lock"></i>
                                <?= $form->field($model, 'password')
                                    ->passwordInput(['maxlength' => 128, 'placeholder' => 'Password'])
                                    ->label(false) ?>
                                <b class="tooltip tooltip-bottom-right">Don't forget your password</b>
                            </label>
                        </section>

                        <!-- Verify Password -->
                        <section>
                            <label class="input<?= $model->hasErrors('verifyPassword') ? ' state-error' : '' ?>">
                                <i class="icon-append fa fa-lock"></i>
                                <?= $form->field($model, 'verifyPassword')
                                    ->passwordInput(['maxlength' => 128, 'placeholder' => 'Confirm Password'])
                                    ->label(false) ?>
                                <b class="tooltip tooltip-bottom-right">Re-enter to confirm your password</b>
                            </label>
                        </section>

                    </fieldset>

                    <fieldset>

                        <!-- First Name + Last Name -->
                        <div class="row">
                            <section class="col col-6">
                                <label class="input<?= $model->hasErrors('firstName') ? ' state-error' : '' ?>">
                                    <?= $form->field($model, 'firstName')
                                        ->textInput(['maxlength' => 128, 'placeholder' => 'First Name'])
                                        ->label(false) ?>
                                </label>
                            </section>
                            <section class="col col-6">
                                <label class="input<?= $model->hasErrors('lastName') ? ' state-error' : '' ?>">
                                    <?= $form->field($model, 'lastName')
                                        ->textInput(['maxlength' => 128, 'placeholder' => 'Last Name'])
                                        ->label(false) ?>
                                </label>
                            </section>
                        </div>

                        <!-- Profession + City -->
                        <div class="row">
                            <section class="col col-6">
                                <label class="select<?= $model->hasErrors('professionRole') ? ' state-error' : '' ?>">
                                    <?= $form->field($model, 'professionRole')
                                        ->dropDownList(
                                            RegistrationForm::$professionList,
                                            ['prompt' => 'Profession']
                                        )
                                        ->label(false) ?>
                                    <i></i>
                                </label>
                            </section>
                            <section class="col col-6">
                                <label class="input">
                                    <i class="icon-append fa fa-globe"></i>
                                    <input name="autocomplete_city" class="form-control" id="autocomplete"
                                           placeholder="Enter your City"
                                           onfocus="geolocate()" type="text" autocomplete="off">
                                </label>
                                <!-- Hidden fields filled by Google Maps API -->
                                <?= $form->field($model, 'streetNumber')->hiddenInput(['id' => 'street_number'])->label(false) ?>
                                <?= $form->field($model, 'streetAddress')->hiddenInput(['id' => 'route'])->label(false) ?>
                                <?= $form->field($model, 'city')->hiddenInput(['id' => 'locality'])->label(false) ?>
                                <?= $form->field($model, 'state')->hiddenInput(['id' => 'administrative_area_level_1'])->label(false) ?>
                                <?= $form->field($model, 'country')->hiddenInput(['id' => 'country'])->label(false) ?>
                            </section>
                        </div>

                        <!-- Subscription + Terms -->
                        <section>
                            <label class="checkbox">
                                <input value="Yes" name="RegistrationForm[subscription]"
                                       id="RegistrationForm_subscription" type="checkbox"
                                    <?= ($model->subscription === 'Yes') ? 'checked="checked"' : '' ?>>
                                <i></i>I want to receive news and special offers
                            </label>

                            <label class="checkbox<?= $model->hasErrors('terms') ? ' state-error' : '' ?>">
                                <input value="1" name="RegistrationForm[terms]"
                                       id="RegistrationForm_terms" type="checkbox"
                                    <?= ($model->terms == '1') ? 'checked="checked"' : '' ?>>
                                <i></i>I agree with the
                                <a href="#" data-toggle="modal" data-target="#myModal">Terms and Conditions</a>
                                <?php if ($model->hasErrors('terms')): ?>
                                    <em class="help-inline"><?= Html::encode($model->getFirstError('terms')) ?></em>
                                <?php endif; ?>
                            </label>
                        </section>

                    </fieldset>

                    <footer>
                        <?= Html::submitButton('Register', ['class' => 'btn btn-primary']) ?>
                    </footer>

                    <div class="message" style="display:none;">
                        <i class="fa fa-check"></i>
                        <p style="padding: 5px 10px;">Thank you for your registration!</p>
                    </div>

                    <?php ActiveForm::end(); ?>

                </div><!-- /.well -->

                <p class="note text-center">
                    *Registration is always free to access our standard tools, register now and check them out.
                </p>

                <h5 class="text-center margin-top-10"> - Or register using - </h5>

                <?= \yii\authclient\widgets\AuthChoice::widget([
                    'baseAuthUrl' => ['user/auth']
                ]) ?>

            </div>
            <!-- END RIGHT PANEL -->

        </div>
    </div>

</div>


<!-- Terms & Conditions Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Terms &amp; Conditions</h4>
            </div>
            <div class="modal-body custom-scroll terms-body">
                <div id="left">
                    <h1>IRRADII TERMS &amp; CONDITIONS</h1>

                    <h2>Introduction</h2>
                    <p>These terms and conditions govern your use of this website; by using this website, you accept these
                        terms and conditions in full. If you disagree with these terms and conditions or any part of
                        these terms and conditions, you must not use this website.</p>

                    <h2>License to use website</h2>
                    <p>Unless otherwise stated, Irradii and/or its licensors own the intellectual property rights in the
                        website and material on the website. Subject to the license below, all these intellectual
                        property rights are reserved.</p>
                    <p>You must not: republish, sell, rent, sub-license, reproduce, duplicate, copy or otherwise exploit
                        material on this website for a commercial purpose.</p>

                    <h2>Acceptable use</h2>
                    <p>You must not use this website in any way that causes, or may cause, damage to the website or
                        impairment of the availability or accessibility of the website; or in any way which is unlawful,
                        illegal, fraudulent or harmful.</p>

                    <h2>No warranties</h2>
                    <p>This website is provided "as is" without any representations or warranties, express or implied.
                        Irradii makes no representations or warranties in relation to this website or the information
                        and materials provided on this website.</p>

                    <h2>Limitations of liability</h2>
                    <p>Irradii will not be liable to you in relation to the contents of, or use of, or otherwise in
                        connection with, this website for any indirect, special or consequential loss; or for any
                        business losses, loss of revenue, income, profits or anticipated savings.</p>

                    <h2>Law and jurisdiction</h2>
                    <p>These terms and conditions will be governed by and construed in accordance with applicable law,
                        and any disputes relating to these terms and conditions will be subject to the jurisdiction of
                        the courts.</p>
                </div>
                <br><br>
                <p><strong>By registering, you confirm that you have read and agree to these Terms and
                        Conditions.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="i-agree">
                    <i class="fa fa-check"></i> I Agree
                </button>
                <button type="button" class="btn btn-danger pull-left" id="print-terms">
                    <i class="fa fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div><!-- /.modal -->


<?php
// -----------------------------------------------------------------------
// JavaScript: Terms modal + jQuery validation + Google Maps autocomplete
// -----------------------------------------------------------------------
$this->registerJs("
    runAllForms();

    // 'I Agree' button inside Terms modal
    $('#i-agree').click(function() {
        var _this = $('#RegistrationForm_terms');
        if (!_this.prop('checked')) {
            _this.prop('checked', true);
        }
        $('#myModal').modal('hide');
    });

    // Print terms
    $('#print-terms').click(function() {
        window.print();
    });

    // jQuery form validation
    $(function() {
        $('#registration-form-form').validate({
            rules: {
                'RegistrationForm[username]':        { required: true, email: true },
                'RegistrationForm[password]':        { required: true, minlength: 4, maxlength: 128 },
                'RegistrationForm[verifyPassword]':  { required: true, minlength: 4, equalTo: '#registrationform-password' },
                'RegistrationForm[firstName]':       { required: true },
                'RegistrationForm[lastName]':        { required: true },
                'RegistrationForm[professionRole]':  { required: true },
                'RegistrationForm[terms]':           { required: true }
            },
            messages: {
                'RegistrationForm[username]':       { required: 'Please enter your email address', email: 'Please enter a valid email address' },
                'RegistrationForm[password]':       { required: 'Please enter a password', minlength: 'Password must be at least 4 characters' },
                'RegistrationForm[verifyPassword]': { required: 'Please confirm your password', equalTo: 'Passwords do not match' },
                'RegistrationForm[firstName]':      { required: 'Please enter your first name' },
                'RegistrationForm[lastName]':       { required: 'Please enter your last name' },
                'RegistrationForm[professionRole]': { required: 'Please select your profession' },
                'RegistrationForm[terms]':          { required: 'You must agree with the Terms and Conditions' }
            },
            errorPlacement: function(error, element) {
                error.insertAfter(element.parent());
            }
        });
    });
", \yii\web\View::POS_END);

// Flash message handler
if (Yii::$app->session->hasFlash('registration')) {
    $flashMsg = Yii::$app->session->getFlash('registration');
    $this->registerJs("
        var mess = " . json_encode($flashMsg) . ";
        $('.message').find('p').empty().html(mess);
        $('.message').css('display', 'block');
    ", \yii\web\View::POS_END);
}

// Google Maps autocomplete
$this->registerJs("
    var placeSearch, autocomplete;
    var componentForm = {
        street_number:                'short_name',
        route:                        'long_name',
        locality:                     'long_name',
        administrative_area_level_1:  'short_name',
        country:                      'long_name'
    };

    function initialize() {
        autocomplete = new google.maps.places.Autocomplete(
            document.getElementById('autocomplete'),
            { types: ['geocode'] }
        );
        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            fillInAddress();
        });
    }

    function fillInAddress() {
        var place = autocomplete.getPlace();
        for (var component in componentForm) {
            var el = document.getElementById(component);
            if (el) el.value = '';
        }
        for (var i = 0; i < place.address_components.length; i++) {
            var addressType = place.address_components[i].types[0];
            if (componentForm[addressType]) {
                var val = place.address_components[i][componentForm[addressType]];
                var el2 = document.getElementById(addressType);
                if (el2) el2.value = val;
            }
        }
    }

    function geolocate() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var geolocation = new google.maps.LatLng(
                    position.coords.latitude, position.coords.longitude
                );
                autocomplete.setBounds(new google.maps.LatLngBounds(geolocation, geolocation));
            });
        }
    }
", \yii\web\View::POS_END);
?>