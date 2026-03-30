<?php // test

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\components\CPathCDN;
use app\components\SiteHelper;
use yii\widgets\Breadcrumbs;

$session = Yii::$app->session;
$uri_page = '@' . Yii::$app->request->url; 
$recent_pages = 'User Profile' . $uri_page;

$recentPages = $session->get('recent_pages', []);
if (empty($recentPages)) {
    $session->set('recent_pages', [$recent_pages]);
} else {
    $recentPages[] = $recent_pages;
    $session->set('recent_pages', $recentPages);
}

$cs = $this;
$themePath = Yii::$app->request->baseUrl;
$this->context->layout = 'irradii_main'; // Updated for Yii2
$success_flag = '';
// $this->body_onload = 'onload="initialize()"'; // Handled via registerJs below
$this->params['breadcrumbs'] = array(
    'User Profile',
);
$this->title = 'My Profile'; 
//echo '<pre>',  print_r($profession_collection),'</pre>';die();
$office_logo = false;
foreach ($profession_collection as $collection_office_logo) {
    if($collection_office_logo){
        if (strtolower($collection_office_logo->office_logo ?? '') === 'yes') {
            $office_logo = true;
        }
    }
}
$office = false;
foreach ($profession_collection as $collection_office) {
    if($collection_office){
       if (strtolower($collection_office->office ?? '') === 'yes') {
           $office = true;
       }
    }
}

$website_url = false;
foreach ($profession_collection as $collection_website_url) {
    if($collection_website_url){
        if (strtolower($collection_website_url->website_url ?? '') === 'yes') {
            $website_url = true;
        }
    }
}
$phone_office = false;
foreach ($profession_collection as $collection_phone_office) {
    if($collection_phone_office){
        if (strtolower($collection_phone_office->phone_office ?? '') === 'yes') {
            $phone_office = true;
        }
    }
}
$phone_fax = false;
foreach ($profession_collection as $collection_phone_fax) {
    if($collection_phone_fax){
        if (strtolower($collection_phone_fax->phone_fax ?? '') === 'yes') {
            $phone_fax = true;
        }
    }
}
$upload_logo = false;
foreach ($profession_collection as $collection_upload_logo) {
    if($collection_upload_logo){
        if (strtolower($collection_upload_logo->upload_logo ?? '') === 'yes') {
            $upload_logo = true;
        }
    }
}
$about_me = false;
foreach ($profession_collection as $collection_about_me) {
    if($collection_about_me){
        if (strtolower($collection_about_me->about_me ?? '') === 'yes') {
            $about_me = true;
        }
    }
}
$tagline = false;
foreach ($profession_collection as $collection_tagline) {
    if($collection_tagline){
        if (strtolower($collection_tagline->tagline ?? '') === 'yes') {
            $tagline = true;
        }
    }
}
$years_of_experience_text = false;
foreach ($profession_collection as $collection_years_of_experience_text) {
    if($collection_years_of_experience_text){
        if (strtolower($collection_years_of_experience_text->years_of_experience_text ?? '') === 'yes') {
            $years_of_experience_text = true;
        }
    }
}
$area_expertise = false;
foreach ($profession_collection as $collection_area_expertise) {
    if($collection_area_expertise){
        if (strtolower($collection_area_expertise->area_expertise ?? '') === 'yes') {
            $area_expertise = true;
        }
    }
}
$area_expertise_text = false;
foreach ($profession_collection as $collection_area_expertise_text) {
    if($collection_area_expertise_text){
        if (strtolower($collection_area_expertise_text->area_expertise_text ?? '') === 'yes') {
            $area_expertise_text = true;
        }
    }
}

    // Professions logic consolidated into the view for a straight row design.
    ($anotherUserId !== null && SiteHelper::isAdmin())? $editMode = true : $editMode = false;
?>
<!-- HEADER -->


<!-- Left panel : Navigation area -->
<!-- Note: This width of the aside area can be adjusted through LESS variables -->
<!--aside-->
<?php if(!Yii::$app->user->isGuest){
    // In Yii2, renderPartial is typically replaced with render for partials
    echo $this->render('/layouts/aside', array('profile'=>$profile));
}?>
<!-- END NAVIGATION -->


<!-- MAIN PANEL -->
<div id="main" role="main">
    
    <?php if (Yii::$app->session->hasFlash('profileMessage')): ?>
        <div class="success1 user_profile">
            <?php
            $success_flag = 1;
            echo Yii::$app->session->getFlash('profileMessage');
            ?>
        </div>
    <?php endif; ?>
    
    <!-- RIBBON -->
    <div id="ribbon">

        <div class="ribbon-button-alignment"> 
            <div id="refresh" 
                  class="btn btn-ribbon" 
                  data-title="refresh"  
                  rel="tooltip" 
                  data-placement="bottom" 
                  data-original-title="<i class='text-warning fa fa-warning'></i> Warning! This will reset all of your personalized widget settings."
                  data-html="true">
                <i class="fa fa-refresh"></i>
            </div> 
        </div>

        <!-- breadcrumb -->
        <?php if (isset($this->params['breadcrumbs'])): ?>
            <?php
            echo Breadcrumbs::widget(array(
                'links' => $this->params['breadcrumbs'],
                //'separator' => "&nbsp;&nbsp;/&nbsp;&nbsp;", // breadcrumbs widget handles this differently
            ));
            ?><!-- breadcrumbs -->
        <?php endif ?>

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
                    <i class="fa fa-pencil-square-o fa-fw "></i> 
                    <?php echo ($anotherUserId === null)? "User Profile": "User Profile. Edit Mode "; ?>
                    <?php if(false){ ?>
                    <!--      #UNCOMMENT WHEN NEEDE      -->
<!--                    <span>> -->
<!--                        <span class="txt-color-greenDark"><b>95% COMPLETE</b></span>-->
<!--                    </span>-->
                    <?php } ?>
                </h1>
            </div>
            <?php if(false){ ?>
<!--      #UNCOMMENT WHEN NEEDE     -->
<?php             /*<div class="col-xs-12 col-sm-5 col-md-5 col-lg-8">
                <ul id="sparks" class="">
                    <li class="sparks-info">
                        <h5> My Profile Views <span class="txt-color-greenDark">1071</span></h5>
                        <div class="sparkline txt-color-greenDark hidden-mobile hidden-md hidden-sm">
                            1300, 1877, 2500, 2577, 2000, 2100, 3000, 2700, 3631, 2471, 2700, 3631, 2471
                        </div>
                    </li>
                    <li class="sparks-info">
                        <h5> Listings Visits <span class="txt-color-red"><i class="fa fa-arrow-circle-up" data-rel="bootstrap-tooltip" title="Increased"></i>&nbsp;45%</span></h5>
                        <div class="sparkline txt-color-red hidden-mobile hidden-md hidden-sm">
                            110,150,300,130,400,240,220,310,220,300, 270, 210
                        </div>
                    </li>
                    <li class="sparks-info">
                        <h5> Market Activity <span class="txt-color-greenDark"><i class="fa fa-shopping-cart"></i>&nbsp;2447</span></h5>
                        <div class="sparkline txt-color-greenDark hidden-mobile hidden-md hidden-sm">
                            110,150,300,130,400,240,220,310,220,300, 270, 210
                        </div>
                    </li>
                </ul>
            </div> */?>
            <?php } ?>
        </div>


        <?php if(SiteHelper::isAdmin() || SiteHelper::forFullPaidMembersOnly(1) !== 1){?>
        <div class="well" <?php echo (!$editMode)? '': 'style="background: #FFEACD; border: 1px solid #FFC77C; box-shadow: 0 2px 1px #DADADA;"' ?> >
            <div class="row">
                <?php
                if(SiteHelper::isAdmin()){
                    echo ($anotherUserId === null)? "You are admin": "Warning! You edit <strong>$model->username</strong> profile.";
                }elseif(SiteHelper::forFullPaidMembersOnly(1) !== 1){
                ?>
                <legend>Full Access Membership</legend>
                <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                    <?php if($subscr_form_data['subscriptions_left'] >= 1){

                        $sbmt = '<button class="btn btn-success btn-lg" type="submit"><i class="fa fa-paypal"></i> Subscribe</button>';

                        echo SiteHelper::buildPaymentForm($sbmt);

                         } else{ ?>
                        <button class="btn btn-default btn-lg" style="cursor: auto;">
                            <i class="fa fa-paypal"></i> Subscription will be available soon.
                        </button>
                    <?php }?>
                </div>

                    <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">
                        <p>For only $<?php echo $subscr_form_data['amount']; ?>.00 a month, get <strong>FULL ACCESS MEMBERSHIP</strong> that gives you the competitive advantage with our <strong>EXCLUSIVE</strong> search filters, library of analytics tools, time saving deal finding automation features, and full access to the complete live database of property listings. <strong>ACT NOW only <?php echo $subscr_form_data['subscriptions_left'] ?> membership<?php echo $subscr_form_data['subscriptions_left']==1?'':'s'; ?> left.</strong></p>
                    </div>

                    <?php /* <!-- Text for normal propose to subscribe  -->
                <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">
                    <p><strong>Exclusive Membership</strong> gives You the advantage with full access to the complete database of property listings, library of analytics tools, search filters, and automation features.</p>
                </div> */?>

                <?php } ?>
            </div>
        </div>
        <?php } ?>


        <!-- widget grid -->
        <section id="widget-grid" class="">

            <!-- row -->
            <div class="row">

                <!-- NEW WIDGET START -->
                <article class="col-sm-12 col-md-12 col-lg-12">


                    <!-- Widget ID (each widget will need unique ID)-->
                    <div class="jarviswidget" id="wid-id-2" data-widget-colorbutton="false" data-widget-editbutton="false">
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
                        <header class="user-profile-header">
                            <span class="widget-icon"> <i class="fa fa-eye"></i> </span>
                            <h2>My Information</h2>
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

                                <?php
                                $form = ActiveForm::begin(array(
                                    'id' => 'tbl-users-profiles-form',
                                    // Please note: When you enable ajax validation, make sure the corresponding
                                    // controller action is handling ajax validation correctly.
                                    // There is a call to performAjaxValidation() commented in generated controller code.
                                    // See class documentation of CActiveForm for details on this.
                                    'enableClientValidation' => false,
                                    'options' => array('enctype' => 'multipart/form-data'),
                                ));
                                ?>

                                <fieldset>
                                    <legend>Profile Photo</legend>

                                    <div class="form-group row">

                                        <label class="col-sm-4 control-label">
                                            <?php
                                        $cdnImages = Yii::$app->params['cdnImages'] ?? '';
                                        if(!empty($cdnImages)) {
                                            $filename = !empty($profile->upload_photo) ?
                                                    CPathCDN::baseurl( 'images' ) . '/images/avatars/50_50_' . $profile->upload_photo:
                                                    CPathCDN::baseurl( 'images' ) . '/images/avatars/male.png';
                                        } else {
                                            $filename = !empty($profile->upload_photo) ?
                                                    (file_exists(Yii::getAlias('@webroot')."/images/avatars/50_50_". $profile->upload_photo)?
                                                    CPathCDN::baseurl( 'images' ) . '/images/avatars/50_50_' . $profile->upload_photo:
                                                    CPathCDN::baseurl( 'images' ) . '/images/avatars/male.png') :
                                                    CPathCDN::baseurl( 'images' ) . '/images/avatars/male.png';
                                        }
                                            ?>
                                            <img src="<?php echo $filename; ?>" alt="me" class="online  <!--thumb-img-50-->" />

                                        </label>


                                        <div class="col-sm-8 col-md-8 col-lg-8">
                                            <div class="smart-form">
                                                <section>
                                                    <label class="label">Upload Profile Photo</label>
                                                    <div class="input input-file">
                                                        <span class="button">
                                                            <?php
                                                            echo Html::activeFileInput(
                                                                $model, 'avatar_image', array(
                                                                                            'id' => "file",
                                                                                            'onchange' => 'document.getElementById("avatar_picture_text_input").value = this.value'
                                                                                    ));
                                                            ?> Browse File
                                                        </span>
                                                        <input type="text" id="avatar_picture_text_input" placeholder="Upload a profile picture in .png, .jpg, or .img format" readonly="">
                                                    </div>
                                                </section>
                                            </div>
                                        </div>
                                    </div>

                                </fieldset>

                                <fieldset>
                                    <input name="authenticity_token" type="hidden">
                                    <div class="form-group<?php echo $model->hasErrors('username') ? ' has-error' : ' has-success'; ?>">
                                        <label>Email Address</label>
                                        <div class="smart-form" >
                                            <div class="input-group<?php echo $model->hasErrors('username') ? ' state-error' : ''; ?> col-md-12">
<!--                                                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>-->
                                                <label class="input">
                                                    <i class="icon-prepend fa fa-envelope-o"></i>

                                                    <?php echo $form->field($model, 'username', ['template' => '{input}', 'options' => ['tag' => false]])->input('email', ['class' => ''])->label(false); ?>
                                                </label>
<!--                                                <span class="input-group-addon"><i class="fa fa-check"></i></span>-->

                                            </div>
                                        <?php // echo $form->error($model, 'username'); // Handled by field() ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Password</label>
<!--                                            <input class="form-control" placeholder="Password" type="password" value="">-->
                                        <?php
                                        echo Html::button('Password', array('class' => 'form-control', 'id' => 'changePassword'));?>
                                    </div>
                                    <div class="form-group<?php echo $profile->hasErrors('first_name') ? ' state-error' : ''; ?>">
                                        <label>First Name</label>
<!--                                            <input class="form-control" placeholder="Text field" type="text">-->
                                                                                 <?php echo $form->field($profile, 'first_name', ['template' => '{input}', 'options' => ['tag' => false]])->textInput(['class' => 'form-control'])->label(false); ?>
                                         <?php echo Html::error($profile, 'first_name', ['class' => 'help-block text-danger']); ?>

                                    </div>
                                    <div class="form-group<?php echo $profile->hasErrors('last_name') ? ' state-error' : ''; ?>">
                                        <label>Last Name</label>
<!--                                            <input class="form-control" placeholder="Text field" type="text">-->
                                                                                 <?php echo $form->field($profile, 'last_name', ['template' => '{input}', 'options' => ['tag' => false]])->textInput(['class' => 'form-control'])->label(false); ?>
                                         <?php echo Html::error($profile, 'last_name', ['class' => 'help-block text-danger']); ?>

                                    </div>

                                    <div class="form-group<?php echo $profile->hasErrors('phone') ? ' has-error' : ' has-success'; ?>">
                                        <label>Phone Number</label>
                                        <div class="smart-form">
                                            <div class="input-group<?php echo $profile->hasErrors('phone') ? ' state-error' : ''; ?> col-md-12">
<!--                                                <span class="input-group-addon"><i class="fa fa-phone"></i></span>-->
                                                <label class="input">
                                                    <i class="icon-prepend fa fa-phone"></i>

                                                                                                         <?php echo $form->field($profile, 'phone', ['template' => '{input}', 'options' => ['tag' => false]])->textInput(array('data-mask'=>'(999) 999-9999', 'placeholder'=>'phone', 'class' => ''))->label(false); ?>

                                                </label>
<!--                                                <span class="input-group-addon"><i class="fa fa-check"></i></span>-->
                                            </div>
<!--                                            <span class="help-block">(XXX) XXX-XXXX</span>-->
                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset class="demo-switcher-1  col-sm-12 col-md-12 col-lg-12">
                                    <legend>My Profession</legend>
                                    <div class="form-group row">
                                        <label class="col-md-2 control-label">Check all that apply</label>

                                        <div class="col-md-10" style="padding-top: 5px;">
                                            <?php foreach ($all_profession as $pItem) : 
                                                $pChecked = '';
                                                foreach ($my_profession as $pAssigned) {
                                                    if ($pAssigned->itemname === $pItem->name) {
                                                        $pChecked = 'checked="checked"';
                                                        break;
                                                    }
                                                }
                                            ?>
                                                <label class="checkbox-inline" style="margin-left: 0; margin-right: 15px; margin-bottom: 5px;">
                                                    <input type="checkbox" name="User[professionsArray][]" value="<?= Html::encode($pItem->name); ?>" class="checkbox style-0" <?= $pChecked ?> >
                                                    <span style="text-transform: uppercase;"><?php echo $pItem->name; ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <?php /* $var = ' <!-- OLD VERSION OF PROFESSION --> <div class="form-group">
                                        <label class="col-md-2 control-label">Check all that apply</label>

                                        <div class="col-md-3">
                                            <?php for ($i = 0; $i < count($left); $i++) : ?>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" class="checkbox style-0" <?php echo $left_checked_attr[$i]; ?> >
                                                        <span><?php echo ucwords(strtolower($left[$i])); ?></span>
                                                    </label>
                                                </div>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="col-md-4">
                                            <?php for ($i = 0; $i < count($center); $i++) : ?>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" class="checkbox style-0" <?php echo $center_checked_attr[$i]; ?> >
                                                        <span><?php echo ucwords(strtolower($center[$i])); ?></span>
                                                    </label>
                                                </div>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="col-md-3">
                                                <?php for ($i = 0; $i < count($right); $i++) : ?>
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" class="checkbox style-0" <?php echo $right_checked_attr[$i]; ?> >
                                                        <span><?php echo ucwords(strtolower($right[$i])); ?></span>
                                                    </label>
                                                </div>
                                            <?php endfor; ?>
                                        </div>
                                    </div> ';*/ ?>

                                </fieldset>

                                    <?php if ($office_logo): ?>
                                    <fieldset>
                                        <legend>Company Logo</legend>
                                            <?php
                                            if(!empty($cdnImages)) {
                                                $office_logo_path = !empty($profile->office_logo) ? 
                                                        CPathCDN::baseurl( 'images' ) . '/images/office_logo/50_50_' . $profile->office_logo :
                                                    CPathCDN::baseurl( 'images' ) . '/images/avatars/male.png';
                                            } else {
                                                $office_logo_path = !empty($profile->office_logo) ? 
                                                    (file_exists(Yii::getAlias('@webroot')."/images/office_logo/50_50_". $profile->office_logo)?
                                                        CPathCDN::baseurl( 'images' ) . '/images/office_logo/50_50_' . $profile->office_logo :
                                                        CPathCDN::baseurl( 'images' ) . '/images/avatars/male.png'): 
                                                    CPathCDN::baseurl( 'images' ) . '/images/avatars/male.png';
                                            }
                                            ?>
                                        <div class="form-group row">
                                            <label class="col-sm-4 control-label">
                                                <img src="<?php echo $office_logo_path; ?>" alt="me" class="online <!--thumb-img-50-->" />
                                            </label>
                                            <div class="col-sm-8 col-md-8 col-lg-8">


                                                <div class="smart-form">
                                                    <section>
                                                        <label class="label">Upload Company Logo</label>
                                                        <div class="input input-file">
                                                        <span class="button">
                                                            <?php
                                                            echo Html::activeFileInput(
                                                                $model, 'company_logo', array(
                                                                'id' => "exampleInputFile12",
                                                                'onchange' => 'document.getElementById("company_picture_text_input").value = this.value'
                                                            ));
                                                            ?> Browse File
<!--                                                            <input type="file" id="file" name="file" onchange="this.parentNode.nextSibling.value = this.value">Browse-->
                                                        </span>
                                                            <input type="text" id="company_picture_text_input" placeholder="Upload a company logo in .png, .jpg, or .img format" readonly="">
                                                        </div>
                                                    </section>
                                                </div>


<!--                                            --><?php
//                                                echo $form->fileField(
//                                                    $model, 'company_logo', array('class' => "btn btn-default form-control", 'id' => "exampleInputFile12"));
//                                            ?>
<!--                                                <p class="help-block">-->
<!--                                                    Upload your own or pick from the drop down list.-->
<!--                                                </p>-->
                                            </div>
                                        </div>

                                    </fieldset>
                                    <?php endif; ?>


                                <div class="row">
                                <fieldset class="col-sm-12 col-md-6 col-lg-6">
                                    <legend>Connected with Us</legend>
                                    <div class="form-group">
                                        <!-- ext.hoauthwidgets.HConnectedNetworks Widget -->
                                    </div>
                                </fieldset>

                                <fieldset class="col-sm-12 col-md-6 col-lg-6">
                                    <legend>Connect with Us</legend>
                                    <div class="form-group">
                                        <!-- ext.hoauthwidgets.HOAuth Widget -->
                                    </div>
                                </fieldset>
                                </div>
                                
                                <fieldset>
                                        <?php $legend = $office ? 'Company Information' : 'My Information'; ?>
                                    <legend><?php echo $legend; ?></legend>
                                        <?php if ($office): ?>
                                        <div class="form-group<?php echo $profile->hasErrors('office') ? ' has-error' : ''; ?>">
                                            <label>Office/ Company Name</label>
    <!--                                            <input class="form-control" placeholder="Text field" type="text">-->
                                            <?php echo $form->field($profile, 'office')->label(false); ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($website_url): ?>
                                        <div class="form-group<?php echo $profile->hasErrors('website_url') ? ' has-error' : ' has-success'; ?> ">
                                            <label>Company Website</label>
                                            <div>
                                                <div class="input-group<?php echo $profile->hasErrors('website_url') ? ' state-error' : ''; ?>">
                                                    <span class="input-group-addon"><i class="fa fa-globe"></i></span>
    <!--                                                    <input class="form-control" type="text">-->
                                                                                                                 <?php echo $form->field($profile, 'website_url', ['template' => '{input}', 'options' => ['tag' => false]])->textInput(['class' => 'form-control'])->label(false); ?>

                                                    <span class="input-group-addon"><i class="fa fa-check"></i></span>

                                                </div>
                                                <span class="help-block">Enter a Valid Website Address</span>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($phone_office): ?>
                                        <div class="form-group<?php echo $profile->hasErrors('phone_office') ? ' has-error' : ' has-success'; ?>">
                                            <label>Office Phone Number</label>
                                            <div>
                                                <div class="input-group<?php echo $profile->hasErrors('phone_office') ? ' state-error' : ''; ?>">
                                                    <span class="input-group-addon"><i class="fa fa-phone"></i></span>
    <!--                                                    <input class="form-control" type="text">-->
                                                                                                                 <?php echo $form->field($profile, 'phone_office', ['template' => '{input}', 'options' => ['tag' => false]])->textInput(['class' => 'form-control'])->label(false); ?>

                                                    <span class="input-group-addon"><i class="fa fa-check"></i></span>
                                                </div>
                                                <span class="help-block">(XXX) XXX-XXXX</span>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($phone_fax): ?>
                                        <div class="form-group<?php echo $profile->hasErrors('phone_fax') ? ' has-error' : ' has-success'; ?>">
                                            <label>Fax Number</label>
                                            <div>
                                                <div class="input-group<?php echo $profile->hasErrors('phone_fax') ? ' state-error' : ''; ?>">
                                                    <span class="input-group-addon"><i class="fa fa-phone"></i></span>
    <!--                                                    <input class="form-control" type="text">-->
                                                                                                                 <?php echo $form->field($profile, 'phone_fax', ['template' => '{input}', 'options' => ['tag' => false]])->textInput(['class' => 'form-control'])->label(false); ?>

                                                    <span class="input-group-addon"><i class="fa fa-check"></i></span>
                                                </div>
                                                <span class="help-block">(XXX) XXX-XXXX</span>

                                            </div>
                                        </div>
                                        <?php endif; ?>
<!--                                    <div class="form-group">-->
<!--                                        <label>Address</label>-->
<!--                                        <input class="form-control" id="autocomplete" placeholder="Enter your address" onFocus="geolocate()" type="text">-->
<!--                                    </div>-->
                                       
                                    <div class="form-group<?php echo $model->hasErrors('street_number') ? ' has-error': '';?>">
                                        <label>Street Number</label>
<!--                                        <input class="form-control" id="street_number" disabled="true">-->
                                                                                 <?php echo $form->field($model, 'street_number', ['template' => '{input}', 'options' => ['tag' => false]])->textInput(array('id'=>"street_number", 'value'=>!empty($profile->street_number)?$profile->street_number:'', 'class' => 'form-control'))->label(false);?>
                                         <?php echo Html::error($model, 'street_number', ['class' => 'help-block text-danger']); ?>

                                    </div>

                                    <div class="form-group<?php echo $model->hasErrors('street_address') ? ' has-error': '';?>">

                                        <label>Street Address</label>
<!--                                        <input class="form-control" id="route" disabled="true">-->
                                                                                 <?php echo $form->field($model, 'street_address', ['template' => '{input}', 'options' => ['tag' => false]])->textInput(array('id'=>"route", 'value'=>!empty($profile->street_address)?$profile->street_address:'', 'class' => 'form-control'))->label(false); ?>
                                         <?php echo Html::error($model, 'street_address', ['class' => 'help-block text-danger']); ?>

                                    </div>

                                    <div class="form-group<?php echo $model->hasErrors('city') ? ' has-error': '';?>">
                                        <label>City</label>
<!--                                        <input class="form-control" id="locality" disabled="true">-->
                                                                                 <?php echo $form->field($model, 'city', ['template' => '{input}', 'options' => ['tag' => false]])->textInput(array('id'=>"locality", 'value'=>!empty($profile->city)?$profile->city:'', 'class' => 'form-control'))->label(false) ?>
                                         <?php echo Html::error($model, 'city', ['class' => 'help-block text-danger']); ?>

                                    </div> 

                                    <div class="form-group<?php echo $model->hasErrors('state') ? ' has-error': '';?>">
                                        <label>State</label>
<!--                                        <input class="form-control" id="administrative_area_level_1" disabled="true">-->
                                                                                 <?php echo $form->field($model, 'state', ['template' => '{input}', 'options' => ['tag' => false]])->textInput(array('id'=>"administrative_area_level_1", 'value'=>!empty($profile->state)?$profile->state:'', 'class' => 'form-control'))->label(false); ?>
                                         <?php echo Html::error($model, 'state', ['class' => 'help-block text-danger']); ?>

                                     </div>  

                                    <div class="form-group<?php echo $model->hasErrors('zipcode') ? ' has-error': '';?>">
                                        <label>Zip code</label>
<!--                                        <input class="form-control" id="postal_code" disabled="true">-->
                                        <?php 
                                        $zip_db = (!empty($profile->zipcode) && $profile->zipcode != 0) ? $profile->zipcode : ''; 
                                        echo $form->field($model, 'zipcode', ['template' => '{input}', 'options' => ['tag' => false]])->textInput(array('id'=>"postal_code", 'value'=>$zip_db, 'class' => 'form-control'))->label(false); ?>
                                         <?php echo Html::error($model, 'zipcode', ['class' => 'help-block text-danger']); ?>

                                    </div>
                                    <div class="form-group<?php echo $model->hasErrors('country') ? ' has-error': '';?>">
                                        <label>Country</label>
<!--                                        <input class="form-control" id="country" disabled="true">-->
                                                                                 <?php echo $form->field($model, 'country', ['template' => '{input}', 'options' => ['tag' => false]])->textInput(array('id'=>"country", 'value'=>!empty($profile->country)?$profile->country:'', 'class' => 'form-control'))->label(false); ?>
                                         <?php echo Html::error($model, 'country', ['class' => 'help-block text-danger']); ?>

                                   </div>
                                        
                                        
                                </fieldset>

                                <fieldset>
                                    <?php if ($about_me): ?>
                                        <legend>MY BIO</legend>
                                        <div class="form-group<?php echo $profile->hasErrors('about_me') ? ' has-error' : ''; ?>">
                                            <label>About Me:</label>
    <!--                                            <textarea class="form-control" placeholder="Tell us about yourself..." rows="3"></textarea>-->
                                        <?php echo $form->field($profile, 'about_me')->textarea(array('rows' => 3, 'placeholder' => 'Tell us about yourself...'))->label(false); ?>
                                        </div>
                                        <?php endif; ?>
                                    <?php if ($tagline): ?>
                                        <div class="form-group<?php echo $profile->hasErrors('tagline') ? ' has-error' : ''; ?>">
                                            <label>Tag Line:</label>
    <!--                                            <textarea class="form-control" placeholder="A short catch phrase to rope them in..." rows="2"></textarea>-->
                                        <?php echo $form->field($profile, 'tagline')->textarea(array('placeholder' => 'A short catch phrase to rope them in...', 'rows' => 2))->label(false); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($years_of_experience_text): ?>
                                        <div class="form-group<?php echo $profile->hasErrors('years_of_experience_text') ? ' has-error' : ''; ?>">
                                            <label>Years of Experience:</label>
    <!--                                            <textarea class="form-control" placeholder="How many years in the business..." rows="1"></textarea>-->
                                        <?php echo $form->field($profile, 'years_of_experience_text')->textarea(array('placeholder' => 'How many years in the business...', 'rows' => 1))->label(false); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($area_expertise): ?>
                                        <div class="form-group<?php echo $profile->hasErrors('area_expertise') ? ' has-error' : ''; ?>">
                                            <label>Areas I serve:</label>
    <!--                                            <textarea class="form-control" placeholder="The cities, subdivisions and neighborhoods that you focus on..." rows="2"></textarea>-->
                                        <?php echo $form->field($profile, 'area_expertise')->textarea(array('placeholder' => 'The cities, subdivisions and neighborhoods that you focus on...', 'rows' => 2))->label(false); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($area_expertise_text): ?>
                                        <div class="form-group<?php echo $profile->hasErrors('area_expertise_text') ? ' has-error' : ''; ?>">
                                            <label>Service Area Details:</label>
    <!--                                            <textarea class="form-control" placeholder="Tell us about your knowledge of the local area..." rows="3"></textarea>-->
                                        <?php echo $form->field($profile, 'area_expertise_text')->textarea(array('placeholder' => 'Tell us about your knowledge of the local area...', 'rows' => 3))->label(false); ?>
                                        </div>
                                    <?php endif; ?>
                                </fieldset>
                                <?php if ($upload_logo): ?>
                                    <fieldset>
                                        <legend>Badges and Certifications</legend>

                                        <div class="form-group row">
                                            <label class="col-sm-4 control-label">
                                            <?php
                                            if(!empty($cdnImages)) {
                                            $upload_logo_path = !empty($profile->upload_logo) ? 
                                                CPathCDN::baseurl( 'images' ) . '/images/bankers_office_logo/50_50_' . $profile->upload_logo :
                                                CPathCDN::baseurl( 'images' ) . '/images/avatars/male.png';
                                            } else {
                                            $upload_logo_path = !empty($profile->upload_logo) ? 
                                            (file_exists(Yii::getAlias('@webroot')."/../images/bankers_office_logo/50_50_". $profile->upload_logo) ?
                                                CPathCDN::baseurl( 'images' ) . '/images/bankers_office_logo/50_50_' . $profile->upload_logo :
                                                CPathCDN::baseurl( 'images' ) . '/images/avatars/male.png'): 
                                                CPathCDN::baseurl( 'images' ) . '/images/avatars/male.png';
                                            }
                                            ?>
                                                <img src="<?php echo $upload_logo_path; ?>" alt="me" class="online  <!--thumb-img-50-->" /></label>
                                            <div class="col-sm-8 col-md-8 col-lg-8">


                                                <div class="smart-form">
                                                    <section>
                                                        <label class="label">Upload Badge/Certification Logo</label>
                                                        <div class="input input-file">
                                                        <span class="button">
                                                            <?php
                                                            echo Html::activeFileInput(
                                                                $model, 'certifications', array(
                                                                'id' => "exampleInputFile13",
                                                                'onchange' => 'document.getElementById("certification_picture_text_input").value = this.value'
                                                            ));
                                                            ?> Browse File
<!--                                                            <input type="file" id="file" name="file" onchange="this.parentNode.nextSibling.value = this.value">Browse-->
                                                        </span>
                                                            <input type="text" id="certification_picture_text_input" placeholder="Upload a badge/certification picture in .png, .jpg, or .img format" readonly="">
                                                        </div>
                                                    </section>
                                                </div>



<!--                                                --><?php
//                                                echo $form->fileField(
//                                                        $model, 'certifications', array('class' => 'btn btn-default form-control', 'id' => 'exampleInputFile13')
//                                                );
//                                                ?>
<!--                                                <p class="help-block">-->
<!--                                                    Brag about yourself by adding your accomplishments here...-->
<!--                                                </p>-->
                                            </div>
                                        </div>

                                    </fieldset>
                                    <?php if(SiteHelper::forFullPaidMembersOnly(1) === 1 && !SiteHelper::isAdmin()){ ?>
                                        <fieldset>
                                            <div class="well">
                                                <legend>Full Access Membership</legend>
                                                <div class="row">
                                                    <div class="col-xs-12 col-md-10 col-sm-9">
                                                        <p>
                                                            <?php $vipDaysLeft = SiteHelper::dateDiff("-", $profile->membership_expire_date, date('Y-m-d')); ?>
                                                            <span class="txt-color-green"><i class="fa fa-check"></i></span> Your subscription is active. It will automatically renew in <strong> <?php echo $vipDaysLeft; ?> </strong> day<?php echo ($vipDaysLeft == 1)?'':'s'; ?>. Renewal date is <?php echo date('M-d-Y', strtotime($profile->membership_expire_date)) ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-xs-12 col-md-2 col-sm-3">
                                                        <a class="btn btn-warning col-sm-12" href="<?php echo Url::to(['/membership/membership/unsubscribe']) ?>"><i class="fa fa-times"></i> Unsubscribe</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                    <?php } ?>
                                <?php endif; ?>
                                <div class="form-actions">

                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fa fa-save"></i>
                                        Update
                                    </button>
                                </div>
                                <?php ActiveForm::end(); ?>

                            </div>
                            <!-- end widget content -->

                        </div>
                        <!-- end widget div -->

                    </div>
                    <!-- end widget -->				

                </article>
                <!-- WIDGET END -->

            </div>

            <!-- end row -->

        </section>
        <!-- end widget grid -->

    </div>
    <!-- END MAIN CONTENT -->

</div>
<!-- END MAIN PANEL -->


</div>
<!-- END MAIN PANEL -->

<div id="changePasswordModal" role="dialog" tabindex="-1" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button data-dismiss="modal" class="close closeModal" type="button">×</button>
                <h1>
                    <?php
                    if(!$editMode){
                        echo "Change password";
                    }else{
                        echo "You change password for $model->username";
                    }
                    ?>
                </h1>
            </div>
            <div class="modal-body">

                

                
                    <?php
                        $form = ActiveForm::begin(
                        array(
                            'id' => 'changepassword-form',
                            'action'=> Url::to(['/user/profile/changepassword']),
                            'enableClientValidation' => true,
                            'options' => array(),
                        )
                    );
                    ?>
                    <fieldset>
                        
                        <?php if(!$editMode){ ?>
                        <section>
                            <div class="form-group<?php echo $modelChangePassword->hasErrors('oldPassword') ? ' has-error' : ' has-success'; ?>">
                                <?php
                                echo Html::activeLabel($modelChangePassword, 'oldPassword', array('class'=>"label")); ?>
                                <?php echo $form->field($modelChangePassword, 'oldPassword')->passwordInput()->label(false); ?>
                            </div>
                        </section>
                        <?php
                        }else{
                            echo Html::activeHiddenInput($modelChangePassword, 'anotherUserId', array('value' => $anotherUserId));
                        } ?>
                        <section>
                           <div class="form-group<?php echo $modelChangePassword->hasErrors('password') ? ' has-error' : ' has-success'; ?>">
                                <?php echo Html::activeLabel($modelChangePassword, 'password', array('class'=>"label")); ?>
                                <?php echo $form->field($modelChangePassword, 'password')->passwordInput()->label(false); ?>
                                <p class="hint">
                                    <?php echo "Minimal password length 4 symbols."; ?>
                                </p>
                           </div>
                        </section>
                        <?php if(!$editMode){ ?>
                        <section>
                            <div class="form-group<?php echo $modelChangePassword->hasErrors('verifyPassword') ? ' has-error' : ' has-success'; ?>">
                                <?php echo Html::activeLabel($modelChangePassword, 'verifyPassword', array('class'=>"label")); ?>
                                <?php echo $form->field($modelChangePassword, 'verifyPassword')->passwordInput()->label(false); ?>
                            </div>     
                        </section>
                        <?php } ?>
                    </fieldset>
                <div class="modal-footer">
                    <?php echo Html::submitButton('Save Changes', array('class'=>"btn btn-primary", 'name'=>"savePasswordButton", 'id'=>"savePasswordButton")); ?>
                    &nbsp;
                    <button data-dismiss="modal" class="btn btn-default closeModal" name="yt2" type="button">Close</button>
                </div>
                    <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    // DO NOT REMOVE : GLOBAL FUNCTIONS!

    $(document).ready(function() {
        if (typeof jqueryOne !== 'undefined' && jqueryOne.fn.mask) {
            jqueryOne('[data-mask]').each(function() {

                var this_1 = jqueryOne(this);
                var mask = this_1.attr('data-mask') || 'error...', mask_placeholder = this_1.attr('data-mask-placeholder') || 'X';

                this_1.mask(mask, {
                    placeholder : mask_placeholder
                });
            });
        }
        // Removed validation logic as it's handled by Yii2 ActiveForm client validation
    });
</script>


<?php
$this->registerJs("pageSetUp();

			// PAGE RELATED SCRIPTS
		
			// class switcher for radio and checkbox
			$('input[name=\"demo-switcher-1\"]').change( function() {
				  //alert($(this).val())
				  var _this = $(this);
				  
				  var myNewClass = _this.attr('id');
				  
				  $('.demo-switcher-1 input[type=\"checkbox\"]').removeClass();
				  $('.demo-switcher-1 input[type=\"checkbox\"]').addClass(\"checkbox \"+ myNewClass);
				  
				  $('.demo-switcher-1 input[type=\"radio\"]').removeClass();
				  $('.demo-switcher-1 input[type=\"radio\"]').addClass(\"radiobox \"+ myNewClass);
			  
		})
//    
//    //SUBSCRIPTION ACTION
//    
//	$('#newsubscribe').submit(function(e){
//        e.preventDefault();
//        $.ajax({
//            url:'/membership/membership/newsubscribe',
//            type:'post',
//            data:$('#newsubscribe').serialize(),
//            success:function(data){
//                 window.open(data, '_blank');
//            }
//        });
//    });
", \yii\web\View::POS_END);

$this->registerJs("
                 
        var hasFlash = '" . $success_flag . "';
        if(hasFlash !== ''){
            $('.success1').animate({opacity:'hide'}, 2500);
            setTimeout(function(){
                $('.success1').addClass('hideblock');
            },2500);
            
        }
        
        $('#changePassword').click(function(){
            //$('.modal-body #changePasswordButton').addClass('hideblock');
            $('#changePasswordModal').modal('show');
        });
        
        $('.closeModal').click(function(){
            $('.error-message').empty();
        });               
        
        ", \yii\web\View::POS_END);

$this->registerJs(" 
            
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
                var search_fld = new google.maps.places.Autocomplete(document.getElementById('search-fld'),{ types: ['geocode'] });
                var place = search_fld.getPlace();
//                console.log(place);
                for (var component in componentFormSearchFld) {
//                    console.log(component);
                    var el = document.getElementById(component);
                    if (el) { el.value = ''; el.disabled = false; }
                }
                for (var i = 0; i < place.address_components.length; i++) {
//                    console.log(place.address_components[i].types[0]);
                    var addressType = place.address_components[i].types[0];
                    if (componentFormSearchFld[addressType+'_searchfld']) {
                        var val = place.address_components[i][componentFormSearchFld[addressType+'_searchfld']];
                        var el = document.getElementById(addressType+'_searchfld');
                        if (el) el.value = val;
                    }
                }
            }

            function initialize() {
                var autocompleteEl = document.getElementById('autocomplete');
                if (autocompleteEl) {
                    autocomplete = new google.maps.places.Autocomplete(autocompleteEl, { types: ['geocode'] });
                    google.maps.event.addListener(autocomplete, 'place_changed', function() { fillInAddress(); });
                }
                var searchFldEl = document.getElementById('search-fld');
                if (searchFldEl) {
                    var search_fld = new google.maps.places.Autocomplete(searchFldEl,{ types: ['geocode'] });
                    google.maps.event.addListener(search_fld, 'place_changed', function() { fillInAddressSearchFld(); });
                }
            }

            // [START region_fillform]
            
            function fillInAddress() {
              // Get the place details from the autocomplete object.
              var place = autocomplete.getPlace();

//              for (var component in componentForm) {
//                document.getElementById(component).value = '';
//                document.getElementById(component).disabled = false;
//              }

              // Get each component of the address from the place details
              // and fill the corresponding field on the form.
              
              for (var i = 0; i < place.address_components.length; i++) {
                var addressType = place.address_components[i].types[0];
                if (componentForm[addressType]) {
                  var val = place.address_components[i][componentForm[addressType]];
                  var el = document.getElementById(addressType);
                  if (el) el.value = val;
                }
              }
            }
            // [END region_fillform]

            // [START region_geolocation]
            // Bias the autocomplete object to the user's geographical location,
            // as supplied by the browser's 'navigator.geolocation' object.
            
            function geolocate() {
              if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                  var geolocation = new google.maps.LatLng(
                      position.coords.latitude, position.coords.longitude);
                  autocomplete.setBounds(new google.maps.LatLngBounds(geolocation,
                      geolocation));
                });
              }
            }
            // [END region_geolocation]
            ",  \yii\web\View::POS_END);

if(!isset($invites) || !$invites) {
?>
        <div id="invites-box" role="dialog" tabindex="-1" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button data-dismiss="modal" class="close closeModal" type="button">×</button>
                        <h1>Invite friends and colleagues to Irradii to search and find what they value most!</h1>
                    </div>
                    <div class="modal-body">
                        <!-- ext.hoauthwidgets.HOAuthInvite Widget -->
                    </div>
                </div>
            </div>
        </div>
<?php
$this->registerJs("
    $('#invites-box').modal('show');;
", \yii\web\View::POS_READY);


$this->registerJs(" 
$('#invites-box button.close').click(function(e){
e.preventDefault();
        $.ajax({
                url: '/user/profile/closeinvites',
                data: {
                        guest: 'guest' 
                },
                type: 'POST',
                dataType: 'json',
                cache: false,
                success: function(data){/*console.log(data);*/},
                error: function(data){/*console.log(data);*/}
            });
//$('#invites-box').modal('close');
});
  ", \yii\web\View::POS_READY);

} 
