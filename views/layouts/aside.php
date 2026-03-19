<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\CPathCDN;
use app\components\SiteHelper;

/* @var $this \yii\web\View */

$user = Yii::$app->user->identity;
$profile = $user ? $user->profile : null;
?>
<style>
    .member-box{
        box-shadow: rgba(0, 0, 0, 0.16) 0px 10px 36px 0px, rgba(0, 0, 0, 0.06) 0px 0px 0px 1px;
        text-align: center;
        padding-bottom: 15px;
    }
</style>
<aside id="left-panel">

    <!-- User info -->
    <div class="login-info">
        <span> 
            <?php 
            $user_first_name = ($profile && !empty($profile->first_name)) ? $profile->first_name : ($user ? $user->username : 'Guest'); 
            $user_last_name = ($profile && !empty($profile->last_name)) ? $profile->last_name : ''; 
            
            $cdnImages = Yii::$app->params['cdnImages'] ?? '';
            if(!empty($cdnImages)) {
                $filename = ($profile && !empty($profile->upload_photo)) ?
                        CPathCDN::baseurl( 'images' ) . '/images/avatars/50_50_' . $profile->upload_photo :
                        CPathCDN::baseurl( 'images' ) . '/images/avatars/male.png';
            } else {
                $filename = ($profile && !empty($profile->upload_photo)) ?
                        (file_exists(Yii::getAlias('@webroot') . "/images/avatars/50_50_" . $profile->upload_photo) ? 
                        CPathCDN::baseurl( 'images' ) . '/images/avatars/50_50_' . $profile->upload_photo :
                        CPathCDN::baseurl( 'images' ) . '/images/avatars/male.png') :
                        CPathCDN::baseurl( 'images' ) . '/images/avatars/male.png';
            }            
            ?>
            <img src="<?php echo $filename; ?>" alt="me" class="online" />

            <a href="javascript:void(0);" id="show-shortcut">
                <?php echo Html::encode($user_first_name . " " . $user_last_name); ?>
                <i class="fa fa-angle-down"></i>
            </a>  
        </span>
    </div>
    <!-- end user info -->

    <nav>
        <ul>
            <?php if(SiteHelper::forFullPaidMembersOnly(true) !== true): ?>
                <li>
                    <a href="<?php echo Yii::$app->params['linkToBuyingSubscr'] ?? '#';?>" class="unlock-link">
                        <i class="fa fa-lg fa-fw fa-unlock"></i>
                        <span class="menu-item-parent">Full Access</span>
                    </a>
                </li>
            <?php endif; ?>

            <li>
                <a id="myprofile" href="<?php echo Url::to(['user/profile'])?>">
                    <i class="fa fa-lg fa-fw fa-pencil-square-o"></i> 
                    <span class="menu-item-parent">My Profile</span>
                </a>
            </li>

            <li>
                <a href="<?php echo Url::to(['property/search']);?>">
                    <i class="fa fa-lg fa-fw fa-search"></i> 
                    <span class="menu-item-parent">Search</span>
                </a>
            </li>

            <li>
                <a id="searches_alerts_menu" href="<?php echo Url::to(['searches/alerts'])?>">
                    <i class="fa fa-lg fa-fw fa-bell"></i>
                    <span class="menu-item-parent">Searches / Alerts</span>
                </a>
            </li>

            <li>
                <a id="saved_alerts_menu" href="<?php echo Url::to(['saved/properties'])?>">
                    <i class="fa fa-lg fa-fw fa-heart"></i>
                    <span class="menu-item-parent">Saved Properties</span>
                </a>
            </li>

            <?php if(SiteHelper::isAdmin()): ?>
                <li>
                    <a id="admins_menu" href="#"><i class="fa fa-lg fa-fw fa-medkit"></i> <span class="menu-item-parent">Admins</span></a>
                    <ul>
                        <li>
                            <a id="adclient_menu" href="#"> Ad Clients </a>
                            <ul>
                                <li><a id="adclient_1_menu" href="<?php echo Url::to(['adclient/activity/admin'])?>"> Manage Ad Client Activities </a></li>
                                <li><a id="adclient_2_menu" href="<?php echo Url::to(['adclient/adclient/admin'])?>"> Manage Ad Clients </a></li>
                                <li><a id="adclient_3_menu" href="<?php echo Url::to(['adclient/adclient/create'])?>"> Create Ad Client </a></li>
                            </ul>
                        </li>
                        <li><a id="meta_tags_admins_menu" href="<?php echo Url::to(['yiiseo/seo'])?>">Meta tags</a></li>
                        <li><a id="statistic_admins_menu" href="<?php echo Url::to(['stat-info/index'])?>">Statistics</a></li>
                        <li><a id="history_admins_menu" href="<?php echo Url::to(['stat-info/history'])?>">Property History</a></li>
                        <li><a id="factors_admins_menu" href="<?php echo Url::to(['stat-info/factor'])?>">Factors</a></li>
                        <li>
                            <a id="blog_menu" href="#"> Blog </a>
                            <ul>
                                <li><a id="blog_1_menu" href="<?php echo Url::to(['blog/index'])?>"> Posts </a></li>
                                <li><a id="blog_2_menu" href="<?php echo Url::to(['blog/post/admin'])?>"> Manage Posts </a></li>
                                <li><a id="blog_3_menu" href="<?php echo Url::to(['blog/post/create'])?>"> Create Post </a></li>
                            </ul>
                        </li>
                        <li>
                            <a id="landing_menu" href="#"> Landing Pages </a>
                            <ul>
                                <li><a id="landing_1_menu" href="<?php echo Url::to(['landing/index'])?>"> Landing Pages </a></li>
                                <li><a id="landing_2_menu" href="<?php echo Url::to(['landing/create'])?>"> Create Landing Page </a></li>
                            </ul>
                        </li>
                        <li><a href="<?php echo Url::to(['membership/membership/search-membership'])?>">User Subscriptions</a></li>
                        <li><a href="<?php echo Url::to(['stat-info/uploadalertsmessages'])?>">Email Alerts Messages</a></li>
                    </ul>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <span class="minifyme" rel="tooltip" data-placement="right" data-original-title="Full Menu"> 
        <i class="fa fa-arrow-circle-left hit"></i> 
    </span>

</aside>
