<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\CPathCDN;
use app\models\UserOAuth;

/* @var $this yii\web\View */
/* @var $content string */

// Yii1: Yii::import('ext.hoauth.*'); require_once('models/UserOAuth.php'); require_once('HOAuthAction.php');
// In Yii2, classes are autoloaded via namespaces. Adjust the namespace/path as needed for your project.
// If UserOAuth and HOAuthAction are in the same module or extension, use the appropriate `use` statement.
// For now, we assume they are available via autoloading or have been migrated.

$configHOauth = UserOAuth::getConfig();

$guest = Yii::$app->user->isGuest ? 0 : 1;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en-us">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <!--<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">-->
        <meta http-equiv="Content-Language" content="en">
        <?= Html::csrfMetaTags() ?>
<?php $this->head() ?>
<?php if (isset(Yii::$app->seo)) { Yii::$app->seo->run(); } ?>
<?php /*/ ?>
        <title> <?php echo isset($this->title) ? $this->title : "Irradii Real Estate" ?> </title>
        <meta name="description" content="Real estate search just got a whole lot smarter! Irradii is your eye into a market full of valuable real estate opportunities all around you.">
        <meta name="author" content="">

<meta property="fb:app_id" content="<?php echo !empty($configHOauth['providers']['Facebook']['keys']['id']) ? $configHOauth['providers']['Facebook']['keys']['id'] : "" ?>">
<meta property="og:site_name" content="Irradii Real Estate">
<meta property="og:title" content="Irradii Real Estate">
<meta property="og:type" content="article">
<meta property="og:url" content="http://irradii.com/user/login">
<meta property="og:image" content="http://css.irradii.com/assets/img/demo/color_logo.png">
<meta property="og:locale" content="en_US">
<meta property="og:description" content="Real estate search just got a whole lot smarter! Irradii is your eye into a market full of valuable real estate opportunities all around you.">

<meta name="twitter:site:id" content="">
<meta name="twitter:card" content="summary">
<?php

        <!-- Use the correct meta names below for your web application
                 Ref: http://davidbcalhoun.com/2010/viewport-metatag 
                 
        <meta name="HandheldFriendly" content="True">
        <meta name="MobileOptimized" content="320">--> */?>

        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

        <!-- Basic Styles -->

<!--        --><?php //$this->registerCssFile('//maxcdn.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css'); ?>
        <?php $this->registerCssFile('//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css'); ?>

        <!-- SmartAdmin Styles : Please note (smartadmin-production.css) was created using LESS variables -->
        <!--    Old way    -->
<!--        --><?php //$this->registerCssFile(CPathCDN::baseurl( 'img' ) . '/css/smartadmin-production.css'); ?>
<!--        --><?php //$this->registerCssFile(CPathCDN::baseurl( 'img' ) . '/css/smartadmin-skins.css'); ?>
<!--        --><?php //$this->registerCssFile(CPathCDN::baseurl( 'img' ) . '/css/demo.css'); ?>
<!--        --><?php //$this->registerCssFile(CPathCDN::publish(Yii::$app->theme->basePath . '/css/styles-smartadmin.css', 'text/css')); ?>
<!--        --><?php //$this->registerCssFile(CPathCDN::publish(Yii::$app->theme->basePath . '/css/styles-smartadmin_1.css', 'text/css')); ?>

        <!--    New way    -->
<!--        --><?php //$this->registerCssFile(CPathCDN::gzipPublish(Yii::$app->theme->basePath . '/css/concat-style.min.css', 'text/css')); //included .css files are listed in Gruntfile.js ?>

        <!-- SmartAdmin RTL Support is under construction
                <link rel="stylesheet" type="text/css" media="screen" href="css/smartadmin-rtl.css"> -->




        <!-- FAVICONS -->
        <link rel="shortcut icon" href="<?php echo CPathCDN::baseurl( 'img' ); ?>/img/favicon/favicon.ico" type="image/x-icon">
        <link rel="icon" href="<?php echo CPathCDN::baseurl( 'img' ); ?>/img/favicon/favicon.ico" type="image/x-icon">


        <!-- GOOGLE FONT -->
        <?php $this->registerCssFile('//fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,300,400,700'); ?>
<!--        --><?php //$this->registerCssFile(CPathCDN::gzipPublish('//fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,300,400,700|Open+Sans+Condensed:300|Muli', 'text/css')); ?>
        <?php $this->registerCssFile('@web/css/concat-style.min.css'); ?>

    </head>
    <!--here-->
<body 
<?php if(isset($this->params['body_ID'])) echo 'id="'.$this->params['body_ID'].'"'; ?>
<?php if(isset($this->params['body_onload'])) echo ' onload="'.$this->params['body_onload'].'"'; ?>
class="">
        <?php $this->beginBody() ?>
        <!-- possible classes: minified, no-right-panel, fixed-ribbon, fixed-header, fixed-width-->
        <header id="header" class="<?php echo  (Yii::$app->user->isGuest)? 'nonauth': 'auth'; ?>">
                        <!--<span id="logo"></span>-->

            <div id="logo-group">

                <!-- PLACE YOUR LOGO HERE -->
                <span id="logo"> 
                    <img src="<?php echo CPathCDN::baseurl( 'img' ); ?>/img/logo/color_logo.png" alt="irradii"  class="pull-left " > 
                    <div class="pull-left logo-title" style=""><span class="text-success">i</span>rrad<span class="text-primary">i</span><span class="txt-color-red">i</span></div>
<?php /*/ ?>
                    <div class="pull-left text-muted logo-description" style="">Your eyes into the real estate market around you</div>
<?php /*/ ?>
                </span>
                <!-- END LOGO PLACEHOLDER -->
<?php if (!Yii::$app->user->isGuest): ?>
                    <!-- Note: The activity badge color changes when clicked and resets the number to 0
                    Suggestion: You may want to set a flag when this happens to tick off all checked messages / notifications -->
                    <?php /*<span id="activity" class="activity-dropdown"> <i class="fa fa-user"></i> <b class="badge"> 21 </b> </span> */ ?>

                    <!-- AJAX-DROPDOWN : control this dropdown height, look and feel from the LESS variable file -->
                    <div class="ajax-dropdown">

                        <!-- the ID links are fetched via AJAX to the ajax container "ajax-notifications" -->
                        <div class="btn-group btn-group-justified" data-toggle="buttons">
                            <label class="btn btn-default">
                                <input type="radio" name="activity" id="ajax/notify/mail.html">
                                Msgs (14) </label>
                            <label class="btn btn-default">
                                <input type="radio" name="activity" id="ajax/notify/notifications.html">
                                Alerts (3) </label>
                            <label class="btn btn-default">
                                <input type="radio" name="activity" id="ajax/notify/tasks.html">
                                Tasks (4) </label>
                        </div>

                        <!-- notification content -->
                        <div class="ajax-notifications custom-scroll">

                            <div class="alert alert-transparent">
                                <h4>Click a button to show messages here</h4>
                                This blank page message helps protect your privacy, or you can show the first message here automatically.
                            </div>

                            <i class="fa fa-lock fa-4x fa-border"></i>

                        </div>
                        <!-- end notification content -->

                        <!-- footer: refresh area -->
                        <span> Last updated on: 12/12/2013 9:43AM
                            <button type="button" data-loading-text="<i class='fa fa-refresh fa-spin'></i> Loading..." class="btn btn-xs btn-default pull-right">
                                <i class="fa fa-refresh"></i>
                            </button> </span>
                        <!-- end footer -->

                    </div>
<?php else : ?>
                    <?php /*<span id="activity" class="activity-dropdown"> <i class="fa fa-tasks"></i> <b class="badge"> 3 </b> </span> */?>

                    <!-- AJAX-DROPDOWN : control this dropdown height, look and feel from the LESS variable file -->
                    <div class="ajax-dropdown">

                        <!-- the ID links are fetched via AJAX to the ajax container "ajax-notifications" -->
                        <div class="btn-group btn-group-justified" data-toggle="buttons">
                            <label class="btn btn-default">
                                <input type="radio" name="activity" id="ajax/notify/mail.html">
                                Welcome </label>
                            <label class="btn btn-default">
                                <input type="radio" name="activity" id="ajax/notify/notifications.html">
                                Tools </label>
                            <label class="btn btn-default">
                                <input type="radio" name="activity" id="ajax/notify/tasks.html">
                                About </label>
                        </div>

                        <!-- notification content -->
                        <div class="ajax-notifications custom-scroll">

                            <div class="alert alert-transparent">
                                <h4>Welcome to irradii real estate search</h4>
                                Sign into a free account to access Level II and Level III information and analytics on the properties and markets that interest you most!
                                <br>
                                <br>	
                                Joining is free and signing up one takes a minute.

                                <br>
                                <br>

                                <ul class="demo-btns">
                                    <li>
                                        <a href="javascript:void(0);" class="btn btn-labeled btn-primary"> <span class="btn-label"><i class="glyphicon glyphicon-plus"></i></span>Create an Account </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="btn btn-labeled btn-success"> <span class="btn-label"><i class="glyphicon glyphicon-user"></i></span>Sign In </a>
                                    </li>
                                </ul>
                            </div>
                            <br>

                            <i class="fa fa-lock fa-4x fa-border"></i>

                        </div>
                        <!-- end notification content -->

                        <!-- footer: refresh area -->
                        <span> Last updated on: 12/12/2013 9:43AM
                            <button type="button" data-loading-text="<i class='fa fa-refresh fa-spin'></i> Loading..." class="btn btn-xs btn-default pull-right">
                                <i class="fa fa-refresh"></i>
                            </button> </span>
                        <!-- end footer -->

                    </div>

            <?php endif; ?>

                <!-- END AJAX-DROPDOWN -->
            </div><!-- /#logo-group -->

<?php if (!Yii::$app->user->isGuest): ?>
                <!-- projects dropdown -->
                <div id="project-context">

                    <span class="label">History:</span>
                    <span id="project-selector" class="popover-trigger-element dropdown-toggle" data-toggle="dropdown">Recent pages <i class="fa fa-angle-down"></i></span>

                    <!-- Suggestion: populate this list with fetch and push technique -->
                    <ul class="dropdown-menu">
                        <?php $session = Yii::$app->session;
                        //$session['recent_pages'] = array();
                        
                        if(isset($session['recent_pages']) && count($session['recent_pages'])>0):?>
                        <?php $sess_arr = array_unique($session['recent_pages']); ?>
                        <?php 
                        while (count($sess_arr)>10) {
                            array_shift($sess_arr);
//                            unset($sess_arr[0]);
//                            ksort($sess_arr);
                        }
                        $session['recent_pages'] = $sess_arr;
                        ?>
                        <?php  
                            foreach ($sess_arr as $page_value):?>
                               <?php  $sub_recent_page_arr = explode('@', $page_value);?>
                               <li>
                                    <a href="<?php echo $sub_recent_page_arr[1] ?>"><?php echo $sub_recent_page_arr[0]; ?></a>
                               </li>
                            <?php endforeach;?>
                        <?php endif; ?>
                        
                        <li class="divider"></li>
                        <li>
                            <a href="javascript:void(0);"><i class="fa fa-power-off"></i> Clear</a>
                        </li>
                    </ul>
                    <!-- end dropdown-menu-->

                </div>

            <?php if(\app\components\SiteHelper::forFullPaidMembersOnly(true) !== true){ ?>
            <div class="unlock_header_btn">
                <a href="<?php echo Yii::$app->params['linkToBuyingSubscr']?>"><i class="fa fa-lg fa-fw fa-unlock"></i>Full Access</a>
            </div>
            <?php } ?>

                <div class="clearfix"></div>
                <!-- end projects dropdown -->

                <!-- pulled right: nav area -->
                <!--<div class="header-right-container pull-right">-->
                <div class="header-right-container">

                    <!-- collapse menu button -->
                    <div id="hide-menu" class="btn-header pull-right">
                        <span> <a href="javascript:void(0);" title="Collapse Menu"><i class="fa fa-reorder"></i></a> </span>
                    </div>
                    <!-- end collapse menu -->

                    <!-- logout button -->
                    <?php if (!Yii::$app->user->isGuest): ?>
                        <div id="logout" class="btn-header transparent pull-right">
                            <span> <a href="<?php echo Url::to(['/login/logout']); ?>" title="Sign Out"><i class="fa fa-sign-out"></i></a> </span>
                        </div>
                    <?php endif; ?>

                    <!-- end logout button -->

                    <!-- search mobile button (this is hidden till mobile view port) -->
                    <div id="search-mobile" class="btn-header transparent pull-right">
                        <span> <a href="javascript:void(0)" title="Search"><i class="fa fa-search"></i></a> </span>
                    </div>
                    <!-- end search mobile button -->

                    <!-- input: search field -->
                    <form action="/property/search" method="POST" class="header-search pull-right" name="top-search-form">
                        <input type="hidden" name="<?php echo Yii::$app->request->csrfParam; ?>" value="<?php echo Yii::$app->request->csrfToken; ?>">
                        <input type="text" placeholder="Address, City, State or ZIP" id="search-fld" name="searchfld">
                        <input type="hidden" id="street_number_searchfld" name="street_number_searchfld" disabled="true">
                        <input type="hidden" id="route_searchfld" name="street_address_searchfld" disabled="true">
                        <input type="hidden" id="locality_searchfld" name="city_searchfld" disabled="true">
                        <input type="hidden" id="administrative_area_level_1_searchfld" name="state_searchfld" disabled="true">
                        <input type="hidden" id="postal_code_searchfld" name="zipcode_searchfld" disabled="true">
                        <input type="hidden" id="country_searchfld" name="country_searchfld" disabled="true">
                        <input type="hidden" id="sale_type_searchfld" name="sale_type_searchfld" value="For Sale">
                        <button type="submit" name="top-search-submit">
                            <i class="fa fa-search"></i>
                        </button>
                        <a href="javascript:void(0);" id="cancel-search-js" title="Cancel Search"><i class="fa fa-times"></i></a>
                    </form>
                    <!-- end input: search field -->

                    <!-- multiple lang dropdown : find all flags in the image folder -->
                    <ul class="header-dropdown-list hidden-xs">
                        <li>
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <i class="fa fa-lg fa-fw fa-home"></i> <span> Homes for Sale </span> <i class="fa fa-angle-down"></i> </a>
                            <ul class="dropdown-menu pull-right">
                                <li class="active">
                                    <a href="javascript:void(0);"><i class="fa fa-lg fa-fw fa-home"></i> Homes for Sale</a>
                                </li>
                                <?php /*<li>
                                    <a href="javascript:void(0);"><i class="fa fa-lg fa-fw fa-legal"></i> Property Records</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);"><i class="fa fa-lg fa-fw fa-user"></i> Real Estate Agents</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);"><i class="fa fa-lg fa-fw fa-wrench"></i> Resources</a>
                                </li> */ ?>
                            </ul>
                        </li>
                    </ul>
                    <!-- end multiple lang -->

                </div>
                <!-- end pulled right: nav area -->
<?php else: ?><!--if (!Yii::$app->user->isGuest)-->
                <!-- professionals dropdown -->
                <div id="project-context">

                    <span class="label">Tools:</span>
                    <span id="project-selector" class="popover-trigger-element dropdown-toggle" data-toggle="dropdown">For Professionals <i class="fa fa-angle-down"></i></span>

                    <!-- Suggestion: populate this list with fetch and push technique -->
                    <ul class="dropdown-menu">
                        <li>
                            <a href="http://irradii.com/post/28/Irradii.com+for+real+estate+investors">Real estate investors</a>
                        </li>
                        <li>
                            <a href="http://irradii.com/post/29/Irradii.com+for+real+estate+agents">Real estate agents</a>
                        </li>
                        <li>
                            <a href="http://irradii.com/post/30/Irradii.com+for+real+estate+brokers">Real estate brokers</a>
                        </li>
                        <li>
                            <a href="http://irradii.com/post/31/how+landlords+use+irradii.com">Landlords</a>
                        </li>
                        <li>
                            <a href="http://irradii.com/post/32/how+home+buyers+use+irradii.com">Home Buyers</a>
                        </li>
                        <li>
                            <a href="http://irradii.com/post/33/Lenders+and+Title+Companies+connect+on+irradii.com">Lenders and Title</a>
                        </li>
                        <li>
                            <a href="http://irradii.com/post/34/Insurance+Companies%2C+Contractors+and+Vendors+earn+business+on+irradii.com+">Insurance and Contractors</a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="http://irradii.com/blog"><i class="fa fa-plus"></i> MORE...</a>
                        </li>
                    </ul>
                    <!-- end dropdown-menu-->

                </div>
                <!-- end professionals dropdown -->

                <!-- history dropdown -->
                <div id="project-context">

                    <span class="label">Local:</span>
                    <span id="project-selector" class="popover-trigger-element dropdown-toggle" data-toggle="dropdown">Market Info <i class="fa fa-angle-down"></i></span>

                    <!-- Suggestion: populate this list with fetch and push technique -->
                    <ul class="dropdown-menu">
                        <li>
                            <a href="http://irradii.com/post/35/How+does+Irradii.com+work%3F">How does Irradii.com work?</a>
                        </li>
                        <li>
                            <a href="http://irradii.com/post/36/What+is+this+home+worth%3F">What's this home worth?</a>
                        </li>
                        <li>
                            <a href="http://irradii.com/post/37/True+Market+Value+Property+Reports">True Market Value Property Reports</a>
                        </li>
                        <li>
                            <a href="http://irradii.com/post/38/Foreclosure+and+Short+Sale+Listing+and+Sales+Data">Foreclosure Data</a>
                        </li>					
                        <li>
                            <a href="http://irradii.com/post/39/Local+Real+Estate+Market+Trends+and+Opportunities">Local Market Trends</a>
                        </li>
                        <li>
                            <a href="http://irradii.com/post/40/Detailed+Property+Reports+and+Local+Market+Comparison+Statistics">Property and Market Comparisons</a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="http://irradii.com/blog"><i class="fa fa-plus"></i> MORE...</a>
                        </li>
                    </ul>
                    <!-- end dropdown-menu-->

                </div>
                <!-- end history dropdown -->
    <?php if (isset($this->params['signin'])): ?>
                <div class="header-right-container">
                    <span id="login-header-space">
                        <span class="hidden-mobile need-an-account">
                            <?php
                            if(Yii::$app->controller->id == 'registration'){
                               echo 'Already have an account';
                            }
                            else{
                                echo 'Need an account';
                            }?>
                        </span>
                        <?php if (isset($this->params['signin'])): ?>
                            <?php echo $this->params['signin']; ?>
                        <?php endif; ?>
                    </span>
                            <!-- search mobile button (this is hidden till mobile view port) -->
                        <div id="search-mobile" class="btn-header transparent pull-right">
                            <span> <a href="javascript:void(0)" title="Search"><i class="fa fa-search"></i></a> </span>
                        </div>
                        <!-- end search mobile button -->

                        <!-- input: search field -->
                        <form action="/property/search" method="POST" class="header-search pull-right" name="top-search-form">
                            <input type="hidden" name="<?php echo Yii::$app->request->csrfParam; ?>" value="<?php echo Yii::$app->request->csrfToken; ?>">
                            <input type="text" placeholder="Address, City, State or ZIP" id="search-fld" name="searchfld">
                            <input type="hidden" id="street_number_searchfld" name="street_number_searchfld" disabled="true">
                            <input type="hidden" id="route_searchfld" name="street_address_searchfld" disabled="true">
                            <input type="hidden" id="locality_searchfld" name="city_searchfld" disabled="true">
                            <input type="hidden" id="administrative_area_level_1_searchfld" name="state_searchfld" disabled="true">
                            <input type="hidden" id="postal_code_searchfld" name="zipcode_searchfld" disabled="true">
                            <input type="hidden" id="country_searchfld" name="country_searchfld" disabled="true">
                            <button type="submit" name="top-search-submit">
                                <i class="fa fa-search"></i>
                            </button>
                            <a href="javascript:void(0);" id="cancel-search-js" title="Cancel Search"><i class="fa fa-times"></i></a>
                        </form>
                        <!-- end input: search field -->
                </div>
    <?php else: ?>
                    <!-- pulled right: nav area -->
                    <!--<div class="header-right-container pull-right">-->
                    <div class="header-right-container">

                        <!-- collapse menu button
                        <div id="hide-menu" class="btn-header pull-right">
                                <span> <a href="javascript:void(0);" title="Collapse Menu"><i class="fa fa-reorder"></i></a> </span>
                        </div>
                             end collapse menu -->

                        <!-- login button -->		
                        <div id="login-block" class="btn-header transparent pull-right">
                            <span> <a href="<?php echo Url::to(['user/login']) ?>" title="Log In"><i class="fa fa-sign-in"></i><span class="font-sm">&nbsp;Sign In&nbsp;</span></a> </span>
                        </div>

                        <!-- end login button -->

                        <!-- create account button -->
                        <div id="create-account-block" class="btn-header transparent pull-right">
                            <span> <a href="<?php echo Url::to(['user/registration']) ?>" title="Register"><i class="fa fa-plus"></i><span class="font-sm">&nbsp;Join&nbsp;</span></a> </span>
                        </div>								
                        <!-- end create account button -->				

                        <!-- search mobile button (this is hidden till mobile view port) -->
                        <div id="search-mobile" class="btn-header transparent pull-right">
                            <span> <a href="javascript:void(0)" title="Search"><i class="fa fa-search"></i></a> </span>
                        </div>
                        <!-- end search mobile button -->

                        <!-- input: search field -->
                        <form action="/property/search" method="POST" class="header-search pull-right" name="top-search-form">
                            <input type="hidden" name="<?php echo Yii::$app->request->csrfParam; ?>" value="<?php echo Yii::$app->request->csrfToken; ?>">
                            <input type="text" placeholder="Address, City, State or ZIP" id="search-fld" name="searchfld">
                            <input type="hidden" id="street_number_searchfld" name="street_number_searchfld" disabled="true">
                            <input type="hidden" id="route_searchfld" name="street_address_searchfld" disabled="true">
                            <input type="hidden" id="locality_searchfld" name="city_searchfld" disabled="true">
                            <input type="hidden" id="administrative_area_level_1_searchfld" name="state_searchfld" disabled="true">
                            <input type="hidden" id="postal_code_searchfld" name="zipcode_searchfld" disabled="true">
                            <input type="hidden" id="country_searchfld" name="country_searchfld" disabled="true">
                            <button type="submit" name="top-search-submit">
                                <i class="fa fa-search"></i>
                            </button>
                            <a href="javascript:void(0);" id="cancel-search-js" title="Cancel Search"><i class="fa fa-times"></i></a>
                        </form>
                        <!-- end input: search field -->

                        <!-- multiple lang dropdown : find all flags in the image folder -->
                        <ul class="header-dropdown-list hidden-xs">
                            <li>
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <i class="fa fa-lg fa-fw fa-home"></i> <span> Homes for Sale </span> <i class="fa fa-angle-down"></i> </a>
                                <ul class="dropdown-menu pull-right">
                                    <li class="active">
                                        <a href="javascript:void(0);"><i class="fa fa-lg fa-fw fa-home"></i> Homes for Sale</a>
                                    </li>
                                    <?php /*<li>
                                        <a href="javascript:void(0);"><i class="fa fa-lg fa-fw fa-legal"></i> Property Records</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);"><i class="fa fa-lg fa-fw fa-dollar"></i> Deals for Sale</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);"><i class="fa fa-lg fa-fw fa-user"></i> Real Estate Agents</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);"><i class="fa fa-lg fa-fw fa-wrench"></i> Resources</a>
                                    </li> */ ?>
                                </ul>
                            </li>
                        </ul>
                        <!-- end multiple lang -->

                    </div>
                    <!-- end pulled right: nav area -->
    <?php endif; ?>

<?php endif; ?><!--if (!Yii::$app->user->isGuest) else-->



            <div class="clearfix"></div>
        </header>
        <div class="clearfix"></div>
        <!-- END HEADER -->	
        
        <?php if (!Yii::$app->user->isGuest): ?>
            <?php echo $this->render('aside.php'); ?>
        <?php endif; ?>

<?php echo $content; ?>


        <!--================================================== -->	

        <!-- PACE LOADER - turn this on if you want ajax loading to show (caution: uses lots of memory on iDevices)
        <script data-pace-options='{ "restartOnRequestAfter": true }' src="js/plugin/pace/pace.min.js"></script>-->


<!--        --><?php //$this->registerJsFile('//underscorejs.org/underscore-min.js', ['position' => $this::POS_END]); ?>


<!--        --><?php //$this->registerCoreScript('jquery');

$this->registerJs("
    var jqueryOne = jQuery;
        ", $this::POS_END);  ?>

        <!-- JS TOUCH : include this plugin for mobile drag / drop touch events
        <script src="js/plugin/jquery-touch/jquery.ui.touch-punch.min.js"></script> -->

        <!-- BOOTSTRAP JS -->
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/bootstrap/bootstrap.min.js', ['position' => $this::POS_END]); ?>


        <!-- CUSTOM NOTIFICATION -->
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/notification/SmartNotification.min.js', ['position' => $this::POS_END]); ?>


        <!-- JARVIS WIDGETS -->
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/smartwidgets/jarvis.widget.min.js', ['position' => $this::POS_END]); ?>


        <!-- EASY PIE CHARTS -->
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/plugin/easy-pie-chart/jquery.easy-pie-chart.min.js', ['position' => $this::POS_END]); ?>


        <!-- SPARKLINES -->
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/plugin/sparkline/jquery.sparkline.min.js', ['position' => $this::POS_END]); ?>


        <!-- JQUERY VALIDATE -->
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/plugin/jquery-validate/jquery.validate.min.js', ['position' => $this::POS_END]); ?>


        <!-- JQUERY MASKED INPUT -->
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/plugin/masked-input/jquery.maskedinput.min.js', ['position' => $this::POS_END]); ?>


        <!-- JQUERY SELECT2 INPUT -->
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/plugin/select2/400/select2_custom.js', ['position' => $this::POS_END]); ?>
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/plugin/select2/select2.min.js', ['position' => $this::POS_END]); ?>


        <!-- JQUERY UI + Bootstrap Slider -->
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/plugin/bootstrap-slider/bootstrap-slider.min.js', ['position' => $this::POS_END]); ?>


        <!-- browser msie issue fix -->
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/plugin/msie-fix/jquery.mb.browser.min.js', ['position' => $this::POS_END]); ?>


        <!-- SmartClick: For mobile devices -->
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/plugin/smartclick/smartclick.js', ['position' => $this::POS_END]); ?>

        <!-- JQuery Form: For form managing -->
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/plugin/jquery-form/jquery-form.min.js', ['position' => $this::POS_END]); ?>

        <!--[if IE 7]>

                <h1>Your browser is out of date, please update your browser by going to www.microsoft.com/download</h1>

        <![endif]-->
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/demo.js', ['position' => $this::POS_END]); ?>

        <!-- MAIN APP JS FILE -->
        <?php // $this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/app.js', ['position' => $this::POS_END]); ?>

        <!-- PAGE RELATED PLUGIN(S) -->

        <!-- Flot Chart Plugin: Flot Engine, Flot Resizer, Flot Tooltip -->
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/plugin/flot/jquery.flot.cust.js', ['position' => $this::POS_END]); ?>
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/plugin/flot/jquery.flot.resize.js', ['position' => $this::POS_END]); ?>
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/plugin/flot/jquery.flot.tooltip.js', ['position' => $this::POS_END]); ?>

        <!-- Vector Maps Plugin: Vectormap engine, Vectormap language -->
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/plugin/vectormap/jquery-jvectormap-1.2.2.min.js', ['position' => $this::POS_END]); ?>
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/plugin/vectormap/jquery-jvectormap-world-mill-en.js', ['position' => $this::POS_END]); ?>

        <!-- Full Calendar -->
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . '/js/plugin/fullcalendar/jquery.fullcalendar.min.js', ['position' => $this::POS_END]); ?>
        <!-- PACE LOADER - turn this on if you want ajax loading to show (caution: uses lots of memory on iDevices)-->


        <!-- JS TOUCH : include this plugin for mobile drag / drop touch events
        <script src="js/plugin/jquery-touch/jquery.ui.touch-punch.min.js"></script> -->

        <!-- PAGE RELATED PLUGIN(S) -->
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . "/js/plugin/bootstrap-progressbar/bootstrap-progressbar.js", ['position' => $this::POS_END]); ?>
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . "/js/plugin/datatables/jquery.dataTables-cust.min.js", ['position' => $this::POS_END]); ?>
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . "/js/plugin/bootstrap-tags/bootstrap-tagsinput.min.js", ['position' => $this::POS_END]); ?>
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . "/js/plugin/datatables/ColReorder.min.js", ['position' => $this::POS_END]); ?>

        <?php // $this->registerJsFile(Yii::$app->view->theme->getBaseUrl() . '/js/concat-build.min.js', ['position' => $this::POS_END]); // load file from project folder ?>
<!--        --><?php //$this->registerJsFile(CPathCDN::gzipPublish(Yii::$app->view->theme->basePath . '/js/concat-build.min.js', 'text/javascript'), ['position' => $this::POS_END]); //included .js files are listed in Gruntfile.js ?>

        <?php
        // 1. Main JS Build (Includes Bootstrap etc.)
        $this->registerJsFile('@web/js/concat-build.min.js', [
            'position' => $this::POS_END,
            'depends' => [\yii\web\JqueryAsset::class, \yii\web\YiiAsset::class]
        ]);

        // 2. Google Maps - Updated for Yii2 and API Key requirement
        $this->registerJsFile('https://maps.googleapis.com/maps/api/js?key=' . Yii::$app->params['googleMapsKey'] . '&libraries=drawing,places', [
            'position' => $this::POS_END,
            'async' => true,
            'defer' => true
        ]);

        // 3. DataTables Sorting Plugins (Must load after jQuery)
        $this->registerJsFile("//cdn.datatables.net/plug-ins/be7019ee387/sorting/num-html.js", [
            'position' => $this::POS_END,
            'depends' => [\yii\web\JqueryAsset::class]
        ]);

        $this->registerJsFile("//cdn.datatables.net/plug-ins/be7019ee387/sorting/currency.js", [
            'position' => $this::POS_END,
            'depends' => [\yii\web\JqueryAsset::class]
        ]);

        $this->registerJsFile("//cdn.datatables.net/plug-ins/be7019ee387/sorting/natural.js", [
            'position' => $this::POS_END,
            'depends' => [\yii\web\JqueryAsset::class]
        ]);
        ?>


        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . "/js/plugin/datatables/FixedColumns.min.js", ['position' => $this::POS_END]); ?>
        <?php // do not uncomment $this->registerJsFile(CPathCDN::baseurl( 'js' ) . "/js/plugin/datatables/ColVis.min.js", ['position' => $this::POS_END]); ?>
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . "/js/plugin/datatables/ColVisNew.js", ['position' => $this::POS_END]); ?>
        <?php // do not uncomment $this->registerJsFile(CPathCDN::baseurl( 'js' ) . "/js/plugin/datatables/ColVis.js", ['position' => $this::POS_END]); ?>
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . "/js/plugin/datatables/ZeroClipboard.js", ['position' => $this::POS_END]); ?>
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . "/js/plugin/datatables/media/js/TableTools.min.js", ['position' => $this::POS_END]); ?>
        <?php //$this->registerJsFile(CPathCDN::baseurl( 'js' ) . "/js/plugin/datatables/DT_bootstrap.js", ['position' => $this::POS_END]); ?>


        <?php $this->registerJs("
                function setLastVisit(){
                    $.ajax({
                            url: '/property/online',
                            data: {
                                    guest: guest 
                            },
                            type: 'POST',
                            dataType: 'json',
                            cache: false,
                            success: function(data){/*console.log(data);*/},
                            error: function(data){/*console.log(data);*/}
                        });
                }
                
                var guest = " . $guest . ";
               
                if(guest === 1){
                    setLastVisit();
                    setIntervalUserOnline = setInterval(function(){
                        setLastVisit();
                    },1000*60*5);
                }
              ", $this::POS_END); ?>
        <?php
        $this->registerJs(" 
            //app.js_st_477 // #myprofile, #editmyprofile,
            $('#save, #share, #print,  #dashboard_menu, #alerts_menu, #calendar_menu,'+ 
               '#search_nearby_menu, #invoice_menu, #gallery_menu, #upload_photo_menu, '+
               '#upgrade_account_menu, #beacon_menu, #spot_light_menu, #halo_menu, '+
               '#wizard_menu, #ray_menu, #propery_comparison_menu, #market_lab_menu, '+
               '#wave_menu, #value_analitics_menu, #agents_vendors_menu, '+
               '#saved_agents_profiles_menu, #saved_vendor_profiles_menu, #my_properties_menu, #my_listings_menu, '+
               '#active_menu, #active_file_menu, #active_file2_menu, #archive_menu, #archive_file_menu, '+
               '#archive_file2_menu, #post_new_listing_menu, #saved_properties_menu, #third_level_menu, #third_level_file_menu, '+
               '#third_level_file2_menu, #auction_watch_menu, #beacon2_menu, #you_visited_menu, '+
               '#visited1_menu, #visited2_menu, #visited3_menu, #visited4_menu, #visited5_menu, #visited6_menu, #visited7_menu, '+
//               '#details_full_access, #details_analyze,'+
               '#market_data_menu, #saved_properties2_menu, #third_level2_menu, #third_level2_file_menu, #third_level2_file2_menu').click(function(e){
                e.preventDefault();
                $('#payAndGoModal').modal('show');;
            });
                 ", $this::POS_READY);
        $this->registerJs(" 
            $('#logo').click(function(e){
                e.preventDefault();
                window.location.href = '" . Yii::$app->request->baseUrl . "';
            });
                 ", $this::POS_READY);

$this->registerJs("
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-66028133-1', 'auto');
  ga('send', 'pageview');

", $this::POS_END);
        ?>
        <div id="payAndGoModal" role="dialog" tabindex="-1" class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button data-dismiss="modal" class="close closeModal" type="button">×</button><h1><?php echo Yii::t('app', "Attention!"); ?></h1>
                    </div>
                    <div class="modal-body">
                        <p>This account isn't authorized for Level II analytics</p>
                    </div>
                </div>
            </div>
        </div>

<!-- Modal -->
<div class="modal fade" id="modal_login" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
</div>
<!-- /.modal -->
<!-- Modal -->
<div class="modal fade" id="modal_signup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
</div>
<!-- /.modal -->

<!-- Modal -->
<div class="modal fade" id="myModalTerms" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
</div><!-- /.modal -->

        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>