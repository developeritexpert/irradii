<?php
/* @var $this PageController */
/* @var $model LandingPage */

$session = Yii::app()->session;
$uri_page = '@' . Yii::app()->request->url;
$recent_pages = 'Search' . $uri_page;
if (!isset($session['recent_pages']) || count($session['recent_pages']) == 0) {
    $session['recent_pages'] = array($recent_pages);
} else {
    $sess_arr = $session['recent_pages'];
    $sess_arr[] = $recent_pages;
    $session['recent_pages'] = $sess_arr;
}
$cs = Yii::app()->clientScript;
$themePath = Yii::app()->theme->baseUrl;
$this->title = $model->title;
if (!Yii::app()->user->isGuest) {
    echo $this->renderPartial('//layouts/aside', array('profile' => $profile));
}
?>
    <!-- END NAVIGATION -->
    <!-- MAIN PANEL -->
    <div id="main" role="main">

        <!-- MAIN CONTENT -->
        <div id="content">

            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <h1 class="page-title txt-color-blueDark">
                        <?php echo $model->title; ?>
                    </h1>
                </div>
            </div>

            <!-- widget grid -->
            <section id="widget-grid" class="">
                <?php
                if (!empty($search_results)) {
                    echo $this->renderPartial('_searchResultBlock', array('search_results' => $search_results));
                }
                ?>

                <div class="row">
                    <article class="col-sm-8 col-md-8 col-lg-8">
                        <div class="well well-light">
                            <?php
                            if (!empty($model->postTop)) {
                                echo $this->renderPartial('_postTopBlock', array('data' => $model->postTop));
                            }
                            ?>
                        </div>
                    </article>
                    <article class="col-sm-4 col-md-4 col-lg-4">
                        <div class="well">
                            <button type="button" class="btn btn-primary btn-lg btn-block  no-wrap" id="add-search-button" data-id="<?php echo $model->id; ?>"
                                <?php if (Yii::app()->user->isGuest) : ?> data-toggle="modal" href="/user/login" data-target="#modal_login"<?php endif; ?> >
                                <?php if (Yii::app()->user->isGuest) : ?>
                                    Sign up and receive new listings alerts
                                <?php else : ?>
                                    Follow and receive new listings alerts
                                <?php endif; ?>
                            </button>
                        </div>
                    </article>
                    <article class="col-sm-4 col-md-4 col-lg-4">
                        <div class="well well-light" id="google-ads4" data-widget-colorbutton="false" data-widget-togglebutton="false" data-widget-deletebutton="false" data-widget-fullscreenbutton="false" data-widget-editbutton="false" role="widget">
                            <div role="content">
                                <!-- widget content -->
                                <?php
                                $this->widget('application.extensions.googleadsense.GoogleAdSense', array(
                                        'slot' => '9155258396',
                                        //        'style' => '',
                                        'format' => 'rectangle',
                                        'responsive' => true,
                                    )
                                );

                                ?>
                                <!-- end widget content -->
                            </div>
                        </div>
                    </article>
                    <article class="col-sm-4 col-md-4 col-lg-4">
                        <div class="well well-light" id="google-ads5" data-widget-colorbutton="false" data-widget-togglebutton="false" data-widget-deletebutton="false" data-widget-fullscreenbutton="false" data-widget-editbutton="false" role="widget">
                            <div role="content">
                                <!-- widget content -->
                                <?php
                                $this->widget('application.extensions.googleadsense.GoogleAdSense', array(
                                        'slot' => '3549250794',
                                        //        'style' => '',
                                        'format' => 'rectangle',
                                        'responsive' => true,
                                    )
                                );

                                ?>
                                <!-- end widget content -->
                            </div>
                        </div>
                    </article>
                    <article class="col-sm-4 col-md-4 col-lg-4">
                        <div class="well well-light" id="google-ads6" data-widget-colorbutton="false" data-widget-togglebutton="false" data-widget-deletebutton="false" data-widget-fullscreenbutton="false" data-widget-editbutton="false" role="widget">
                            <div role="content">
                                <!-- widget content -->
                                <?php
                                $this->widget('application.extensions.googleadsense.GoogleAdSense', array(
                                        'slot' => '8817181195',
                                        //        'style' => '',
                                        'format' => 'rectangle',
                                        'responsive' => true,
                                    )
                                );

                                ?>
                                <!-- end widget content -->
                            </div>
                        </div>
                    </article>
                </div>

                <div class="row">
                    <article class="col-sm-12 col-md-12 col-lg-12">
                        <?php
                        //                echo $this->renderPartial('_paypalBlock', array());
                        ?>
                    </article>
                </div>

                <div class="row">
                    <article class="col-sm-12 col-md-12 col-lg-12">
                        <div class="well well-light">
                            <?php
                            if (!empty($model->postBottom)) {
                                echo $this->renderPartial('_postBottomBlock', array('data' => $model->postBottom));
                            }
                            ?>
                        </div>
                    </article>
                </div>

                <div class="row">
                    <article class="col-sm-12 col-md-12 col-lg-12">
                        <?php
                        //                echo $this->renderPartial('_paypalBlock', array());
                        ?>
                    </article>
                </div>

            </section>

        </div>
    </div>

<?php
$cs->registerScript("addSearchVar", "
//    var jqueryOne = $.noConflict();
    var jqueryOne = jQuery;
    var isGuest = '" . Yii::app()->user->isGuest . "';
        ", CClientScript::POS_END);
if (!Yii::app()->user->isGuest) {
    $cs->registerScript("addSearchButton", "
			$('#add-search-button').click(function(e) {
				$.SmartMessageBox({
                                        sound: false,
					title : 'Get the daily email alerts',
					content : 'Get the daily email alerts?',
					buttons : '[No][Yes]'
				}, function(ButtonPressed) {
					if (ButtonPressed === 'Yes') {
		
                                            $(this).prop('disabled',true);
                                            clickAddSearchButton();
					}
					if (ButtonPressed === 'No') {

					}
		
				});
				e.preventDefault();
			})
//$('#modal-sign-up-button').on('click', function(){
//console.log('modal-sign-up-button click');
//    $('#modal_login').modal('hide');
//});
        ", CClientScript::POS_READY);
} else {

}
$cs->registerScript("addSearchFunction", "
            function clickAddSearchButton(){
            var this_id = $('#add-search-button').data('id');
                $.ajax({
                    url: '/landing/page/addsearch',
                    type: 'POST',
                    data: { id : this_id },
                    dataType: 'json',
                    cache: false,
                    success: function(data){
//                        $('#loader_img').hide();
//console.log(isGuest);
                        if(isGuest == '1') {
                            setTimeout(function(){window.location.reload();}, 4000);
                        }
                        if(data.success==true) {
                            (function($){
                            $.smallBox({
                                    title : 'Email alerts',
                                    content : '<i class=\"fa fa-clock-o\"></i> <i>Daily email alerts created</i>',
                                    color : '#659265',
                                    iconSmall : 'fa fa-check fa-2x fadeInRight animated',
                                    timeout : 4000
                            });
                            })(jqueryOne);
                        } else {
                            (function($){
                            $.smallBox({
                                    title : 'Email alerts',
                                    content : '<i class=\"fa fa-clock-o\"></i> <i>Daily email alerts already exist</i>',
                                    color : '#C46A69',
                                    iconSmall : 'fa fa-bell swing animated',
                                    timeout : 4000
                            });
                            })(jqueryOne);
                        }
//console.log(data);
                        $('#add-search-button').prop('disabled',false);
                    },
                    error:  function(xhr, str){
                        console.log('error: ' + xhr.responseCode);
                        $('#add-search-button').prop('disabled',false);
                    }
                });
            }

        ", CClientScript::POS_END);
