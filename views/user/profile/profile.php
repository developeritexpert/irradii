<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html as BsHtml;
use app\components\CPathCDN;
use app\components\SiteHelper;
use app\models\AuthItem;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $profile app\models\UserProfiles */
/* @var $all_profession app\models\AuthItem[] */
/* @var $my_profession app\models\UserProfessions[] */
/* @var $profession_collection app\models\ProfessionFieldCollection[] */
/* @var $modelChangePassword app\models\ChangePasswordForm */
/* @var $invites bool */
/* @var $anotherUserId int|null */
/* @var $subscr_form_data array */

$session = Yii::$app->session;
$uri_page = '@' . Yii::$app->request->url;
$recent_pages = 'User Profile' . $uri_page;

if (!isset($session['recent_pages']) || count($session['recent_pages']) == 0) {
    $session['recent_pages'] = array($recent_pages);
} else {
    $sess_arr = $session['recent_pages'];
    $sess_arr[] = $recent_pages;
    $session['recent_pages'] = $sess_arr;
}

$this->title = 'My Profile';
$this->params['breadcrumbs'][] = 'User Profile';
$this->params['body_onload'] = 'initialize()';

$office_logo = false;
foreach ($profession_collection as $collection_office_logo) {
    if ($collection_office_logo) {
        if (strtolower($collection_office_logo->office_logo) === 'yes') {
            $office_logo = true;
        }
    }
}
$office = false;
foreach ($profession_collection as $collection_office) {
    if ($collection_office) {
        if (strtolower($collection_office->office) === 'yes') {
            $office = true;
        }
    }
}

$website_url = false;
foreach ($profession_collection as $collection_website_url) {
    if ($collection_website_url) {
        if (strtolower($collection_website_url->website_url) === 'yes') {
            $website_url = true;
        }
    }
}
$phone_office = false;
foreach ($profession_collection as $collection_phone_office) {
    if ($collection_phone_office) {
        if (strtolower($collection_phone_office->phone_office) === 'yes') {
            $phone_office = true;
        }
    }
}
$phone_fax = false;
foreach ($profession_collection as $collection_phone_fax) {
    if ($collection_phone_fax) {
        if (strtolower($collection_phone_fax->phone_fax) === 'yes') {
            $phone_fax = true;
        }
    }
}
$upload_logo = false;
foreach ($profession_collection as $collection_upload_logo) {
    if ($collection_upload_logo) {
        if (strtolower($collection_upload_logo->upload_logo) === 'yes') {
            $upload_logo = true;
        }
    }
}
$about_me = false;
foreach ($profession_collection as $collection_about_me) {
    if ($collection_about_me) {
        if (strtolower($collection_about_me->about_me) === 'yes') {
            $about_me = true;
        }
    }
}
$tagline = false;
foreach ($profession_collection as $collection_tagline) {
    if ($collection_tagline) {
        if (strtolower($collection_tagline->tagline) === 'yes') {
            $tagline = true;
        }
    }
}
$years_of_experience_text = false;
foreach ($profession_collection as $collection_years_of_experience_text) {
    if ($collection_years_of_experience_text) {
        if (strtolower($collection_years_of_experience_text->years_of_experience_text) === 'yes') {
            $years_of_experience_text = true;
        }
    }
}
$area_expertise = false;
foreach ($profession_collection as $collection_area_expertise) {
    if ($collection_area_expertise) {
        if (strtolower($collection_area_expertise->area_expertise) === 'yes') {
            $area_expertise = true;
        }
    }
}
$area_expertise_text = false;
foreach ($profession_collection as $collection_area_expertise_text) {
    if ($collection_area_expertise_text) {
        if (strtolower($collection_area_expertise_text->area_expertise_text) === 'yes') {
            $area_expertise_text = true;
        }
    }
}

$editMode = ($anotherUserId !== null && SiteHelper::isAdmin());
$success_flag = Yii::$app->session->hasFlash('profileMessage') ? 1 : '';
?>

<!-- MAIN PANEL -->
<div id="main" role="main">

    <?php if (Yii::$app->session->hasFlash('profileMessage')): ?>
        <div class="alert alert-success user_profile">
            <?php echo Yii::$app->session->getFlash('profileMessage'); ?>
        </div>
    <?php endif; ?>

    <!-- RIBBON -->
    <div id="ribbon">
        <div class="ribbon-button-alignment">
            <div id="refresh" class="btn btn-ribbon" data-title="refresh">
                <i class="fa fa-refresh"></i>
            </div>
        </div>
        <!-- breadcrumb -->
        <?php echo \yii\widgets\Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            'options' => ['class' => 'breadcrumb'],
            'tag' => 'ol',
        ]); ?>
        <!-- end breadcrumb -->
    </div>
    <!-- END RIBBON -->

    <!-- MAIN CONTENT -->
    <div id="content">
        <div class="row">
            <div class="col-xs-12 col-sm-7 col-md-7 col-lg-4">
                <h1 class="page-title txt-color-blueDark">
                    <i class="fa fa-pencil-square-o fa-fw "></i>
                    <?php echo ($anotherUserId === null) ? "User Profile" : "User Profile. Edit Mode "; ?>
                </h1>
            </div>
        </div>

        <?php if (SiteHelper::isAdmin() || SiteHelper::forFullPaidMembersOnly(1) !== 1) { ?>
            <div class="well" <?php echo (!$editMode) ? '' : 'style="background: #FFEACD; border: 1px solid #FFC77C; box-shadow: 0 2px 1px #DADADA;"' ?>>
                <div class="row">
                    <?php
                    if (SiteHelper::isAdmin()) {
                        echo ($anotherUserId === null) ? "You are admin" : "Warning! You edit <strong>" . Html::encode($model->username) . "</strong> profile.";
                    } elseif (SiteHelper::forFullPaidMembersOnly(1) !== 1) {
                    ?>
                        <legend>Full Access Membership</legend>
                        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                            <?php if ($subscr_form_data['subscriptions_left'] >= 1) { ?>
                                <button class="btn btn-success btn-lg" type="submit"><i class="fa fa-paypal"></i> Subscribe</button>
                            <?php } else { ?>
                                <button class="btn btn-default btn-lg" style="cursor: auto;">
                                    <i class="fa fa-paypal"></i> Subscription will be available soon.
                                </button>
                            <?php } ?>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">
                            <p>For only $<?php echo $subscr_form_data['amount']; ?>.00 a month, get <strong>FULL ACCESS MEMBERSHIP</strong>. <strong>ACT NOW only <?php echo $subscr_form_data['subscriptions_left'] ?> membership<?php echo $subscr_form_data['subscriptions_left'] == 1 ? '' : 's'; ?> left.</strong></p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <!-- widget grid -->
        <section id="widget-grid" class="">
            <div class="row">
                <article class="col-sm-12 col-md-12 col-lg-12">
                    <div class="jarviswidget" id="wid-id-2">
                        <header class="user-profile-header">
                            <span class="widget-icon"> <i class="fa fa-eye"></i> </span>
                            <h2>My Information</h2>
                        </header>
                        <div>
                            <div class="widget-body">
                                <?php $form = ActiveForm::begin([
                                    'id' => 'tbl-users-profiles-form',
                                    'options' => ['enctype' => 'multipart/form-data'],
                                ]); ?>

                                <fieldset>
                                    <legend>Profile Photo</legend>
                                    <div class="form-group row">
                                        <label class="col-sm-4 control-label">
                                            <?php
                                            $filename = !empty($profile->upload_photo) ?
                                                CPathCDN::baseurl('images') . '/images/avatars/50_50_' . $profile->upload_photo :
                                                CPathCDN::baseurl('images') . '/images/avatars/male.png';
                                            ?>
                                            <img src="<?php echo $filename; ?>" alt="me" class="online" />
                                        </label>
                                        <div class="col-sm-8">
                                            <div class="smart-form">
                                                <section>
                                                    <label class="label">Upload Profile Photo</label>
                                                    <div class="input input-file">
                                                        <?php echo $form->field($profile, 'upload_photo')->fileInput(['id' => 'file', 'onchange' => 'document.getElementById("avatar_picture_text_input").value = this.value'])->label(false); ?>
                                                        <input type="text" id="avatar_picture_text_input" placeholder="Upload a profile picture" readonly="">
                                                    </div>
                                                </section>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset>
                                    <?php echo $form->field($model, 'username')->textInput(['placeholder' => 'Email Address'])->label('Email Address'); ?>
                                    
                                    <div class="form-group">
                                        <label>Password</label>
                                        <?php echo Html::button('Change Password', ['class' => 'btn btn-secondary form-control', 'id' => 'changePassword']); ?>
                                    </div>

                                    <?php echo $form->field($profile, 'first_name')->textInput(); ?>
                                    <?php echo $form->field($profile, 'last_name')->textInput(); ?>
                                    <?php echo $form->field($profile, 'phone')->textInput(['data-mask' => '(999) 999-9999']); ?>
                                </fieldset>

                                <fieldset>
                                    <legend>My Profession</legend>
                                    <?php 
                                    $professionList = ArrayHelper::map($all_profession, 'name', 'name');
                                    // In Yii2, professions should be handled via a relation or separate model.
                                    // Assuming a manual check for now if necessary.
                                    echo $form->field($model, 'id')->checkboxList($professionList)->label('Check all that apply');
                                    ?>
                                </fieldset>

                                <?php if ($office_logo): ?>
                                    <fieldset>
                                        <legend>Company Logo</legend>
                                        <div class="form-group row">
                                            <label class="col-sm-4 control-label">
                                                <?php
                                                $office_logo_path = !empty($profile->office_logo) ?
                                                    CPathCDN::baseurl('images') . '/images/office_logo/50_50_' . $profile->office_logo :
                                                    CPathCDN::baseurl('images') . '/images/avatars/male.png';
                                                ?>
                                                <img src="<?php echo $office_logo_path; ?>" alt="office logo" />
                                            </label>
                                            <div class="col-sm-8">
                                                <?php echo $form->field($profile, 'office_logo')->fileInput()->label('Upload Company Logo'); ?>
                                            </div>
                                        </div>
                                    </fieldset>
                                <?php endif; ?>

                                <fieldset>
                                    <legend><?php echo $office ? 'Company Information' : 'My Information'; ?></legend>
                                    <?php if ($office) echo $form->field($profile, 'office')->textInput(); ?>
                                    <?php if ($website_url) echo $form->field($profile, 'website_url')->textInput(); ?>
                                    <?php if ($phone_office) echo $form->field($profile, 'phone_office')->textInput(); ?>
                                    <?php if ($phone_fax) echo $form->field($profile, 'phone_fax')->textInput(); ?>
                                    
                                    <?php echo $form->field($profile, 'street_number')->textInput(['id' => 'street_number']); ?>
                                    <?php echo $form->field($profile, 'street_address')->textInput(['id' => 'route']); ?>
                                    <?php echo $form->field($profile, 'city')->textInput(['id' => 'locality']); ?>
                                    <?php echo $form->field($profile, 'state')->textInput(['id' => 'administrative_area_level_1']); ?>
                                    <?php echo $form->field($profile, 'zipcode')->textInput(['id' => 'postal_code']); ?>
                                    <?php echo $form->field($profile, 'country')->textInput(['id' => 'country']); ?>
                                </fieldset>

                                <fieldset>
                                    <?php if ($about_me) echo $form->field($profile, 'about_me')->textarea(['rows' => 3]); ?>
                                    <?php if ($tagline) echo $form->field($profile, 'tagline')->textarea(['rows' => 2]); ?>
                                    <?php if ($years_of_experience_text) echo $form->field($profile, 'years_of_experience_text')->textarea(['rows' => 1]); ?>
                                    <?php if ($area_expertise) echo $form->field($profile, 'area_expertise')->textarea(['rows' => 2]); ?>
                                    <?php if ($area_expertise_text) echo $form->field($profile, 'area_expertise_text')->textarea(['rows' => 3]); ?>
                                </fieldset>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fa fa-save"></i> Update
                                    </button>
                                </div>
                                <?php ActiveForm::end(); ?>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </div>
</div>

<div id="changePasswordModal" role="dialog" tabindex="-1" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php $form = ActiveForm::begin([
                    'id' => 'changepassword-form',
                    'action' => Url::to(['/user/profile/changepassword']),
                ]); ?>
                
                <?php if (!$editMode): ?>
                    <?php echo $form->field($modelChangePassword, 'oldPassword')->passwordInput(); ?>
                <?php else: ?>
                    <?php echo $form->field($modelChangePassword, 'anotherUserId')->hiddenInput(['value' => $anotherUserId])->label(false); ?>
                <?php endif; ?>
                
                <?php echo $form->field($modelChangePassword, 'password')->passwordInput(); ?>
                <?php echo $form->field($modelChangePassword, 'verifyPassword')->passwordInput(); ?>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
    $(document).ready(function() {
        if (jqueryOne.fn.mask) {
            jqueryOne('[data-mask]').each(function() {
                var this_1 = jqueryOne(this);
                var mask = this_1.attr('data-mask') || 'error...', mask_placeholder = this_1.attr('data-mask-placeholder') || 'X';
                this_1.mask(mask, {
                    placeholder : mask_placeholder
                });
            });
        }
        var \$checkoutForm = jqueryOne('#tbl-users-profiles-form').validate({
            rules : {
                'User[username]' : {
                    email : true
                },
            },
            messages : {
                'AdClientActivity[user_email]' : {
                    email : 'Please enter a VALID email address'
                },
            },
            errorPlacement : function(error, element) {
                error.insertAfter(element.parent());
            }
        });
    });

    $('#changePassword').click(function(){
        $('#changePasswordModal').modal('show');
    });

    var hasFlash = '$success_flag';
    if(hasFlash !== ''){
        $('.user_profile').fadeOut(2500);
    }
JS;

$zipCodeScripts = <<<JS
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
        for (var component in componentFormSearchFld) {
            document.getElementById(component).value = '';
            document.getElementById(component).disabled = false;
        }
        for (var i = 0; i < place.address_components.length; i++) {
            var addressType = place.address_components[i].types[0];
            if (componentFormSearchFld[addressType+'_searchfld']) {
                var val = place.address_components[i][componentFormSearchFld[addressType+'_searchfld']];
                document.getElementById(addressType+'_searchfld').value = val;
            }
        }
    }

    function initialize() {
        autocomplete = new google.maps.places.Autocomplete(document.getElementById('autocomplete'), { types: ['geocode'] });
        google.maps.event.addListener(autocomplete, 'place_changed', function() { fillInAddress(); });
        search_fld = new google.maps.places.Autocomplete(document.getElementById('search-fld'),{ types: ['geocode'] });
        google.maps.event.addListener(search_fld, 'place_changed', function() { fillInAddressSearchFld(); });
    }

    function fillInAddress() {
        var place = autocomplete.getPlace();
        for (var i = 0; i < place.address_components.length; i++) {
            var addressType = place.address_components[i].types[0];
            if (componentForm[addressType]) {
                var val = place.address_components[i][componentForm[addressType]];
                document.getElementById(addressType).value = val;
            }
        }
    }

    function geolocate() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var geolocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                autocomplete.setBounds(new google.maps.LatLngBounds(geolocation, geolocation));
            });
        }
    }
JS;

$this->registerJs($js, \yii\web\View::POS_END);
$this->registerJs($zipCodeScripts, \yii\web\View::POS_END);
?>
