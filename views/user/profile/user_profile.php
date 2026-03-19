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

$office_logo = true;
$office = true;
$website_url = true;
$phone_office = true;
$phone_fax = true;
$upload_logo = true;
$about_me = true;
$tagline = true;
$years_of_experience_text = true;
$area_expertise = true;
$area_expertise_text = true;

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
    $('#changePassword').click(function(){
        $('#changePasswordModal').modal('show');
    });

    var hasFlash = '$success_flag';
    if(hasFlash !== ''){
        $('.user_profile').fadeOut(2500);
    }
JS;
$this->registerJs($js);
?>