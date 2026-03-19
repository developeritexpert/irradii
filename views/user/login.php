<?php
use app\components\CPathCDN;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\UserLogin */

$this->title = Yii::$app->name . ' - Sign In';
$this->params['signin']  = Html::a('Create account', ['/user/registration'], ['class' => 'btn btn-danger']);
$this->params['body_ID'] = 'login';

$title       = Yii::$app->request->get('title');
$act_content = Yii::$app->request->get('content');
?>

<div id="main" role="main">

    <div id="content" class="container">
        <div class="row">

            <!-- LEFT PANEL -->
            <div class="col-xs-12 col-sm-12 col-md-7 col-lg-8 hidden-xs hidden-sm">
                <div class="row">
                    <div class="hero">

                        <!-- ✅ ADDED LOGO IMAGE (same as old Yii1) -->
                        <img src="<?= CPathCDN::baseurl('img') ?>/img/demo/color_logo.png"
                             class="display-image eye-logo-login-page"
                             alt="logo">

                        <div class="pull-left login-desc-box-ll col-xs-12 col-sm-12 col-md-6 col-lg-5">
                            <h1 class="txt-color-red login-header-big">Irradii Real Estate</h1>
                            <h4 class="paragraph-header">
                                Real estate search just got a whole lot smarter!
                                Irradii is your eye into a market full of valuable real estate opportunities all around you.
                            </h4>
                            <div class="login-app-icons">
                                <a href="<?= Url::to(['/property/search']) ?>" class="btn btn-danger btn-sm">Search Now</a>
                                <a href="<?= Url::to(['/blog/index']) ?>" class="btn btn-danger btn-sm">Visit our Blog</a>
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
                            Our patent pending search technology crunches data on millions of property records each
                            night to filter and find the best market value opportunities available each morning.
                            Wake up each morning to a list of properties available for sale today that are
                            20% - 50%+ below market value!
                        </p>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                        <h5 class="about-heading">Our Promise to You -</h5>
                        <p>
                            We are dedicated to providing the most accurate real estate values, with tools to help you
                            make stronger, faster and more educated real estate decisions - an edge that can save or
                            make you tens of thousands of dollars.
                        </p>
                    </div>
                </div>
            </div>
            <!-- END LEFT PANEL -->

            <!-- RIGHT PANEL -->
            <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4">
                <div class="well no-padding">
                    <div class="form">
                        <?php $form = ActiveForm::begin([
                            'id'      => 'login-form',
                            'options' => ['class' => 'smart-form client-form'],
                            'enableAjaxValidation' => false,
                            'fieldConfig' => [
                                'options' => ['tag' => false],
                                'template' => '{input}{error}',
                                'errorOptions' => ['tag' => 'em', 'class' => 'invalid'],
                            ],
                        ]); ?>

                        <header>Sign In</header>

                        <fieldset>

                            <!-- Email -->
                            <section>
                                <label class="label">E-mail</label>
                                <label class="input<?= $model->hasErrors('username') ? ' state-error' : '' ?>">
                                    <i class="icon-append fa fa-user"></i>
                                    <?= $form->field($model, 'username')
                                        ->textInput(['maxlength' => 130, 'placeholder' => 'Email or Username']) ?>
                                    <b class="tooltip tooltip-top-right"><i class="fa fa-user txt-color-teal"></i> Please enter email address/username</b>
                                </label>
                            </section>

                            <!-- Password -->
                            <section>
                                <label class="label">Password</label>
                                <label class="input<?= $model->hasErrors('password') ? ' state-error' : '' ?>">
                                    <i class="icon-append fa fa-lock"></i>
                                    <?= $form->field($model, 'password')
                                        ->passwordInput(['maxlength' => 32, 'placeholder' => 'Password']) ?>
                                    <b class="tooltip tooltip-top-right"><i class="fa fa-lock txt-color-teal"></i> Enter your password</b>
                                </label>
                                <div class="note">
                                    <?= Html::a('Forgot password?', Url::to(['/user/recovery'])) ?>
                                </div>
                            </section>

                            <!-- Remember -->
                            <section>
                                <label class="checkbox">
                                    <?= $form->field($model, 'rememberMe')->checkbox(['checked' => 'checked'], false) ?>
                                    <i></i>Stay signed in
                                </label>
                            </section>

                        </fieldset>

                        <footer>
                            <?= Html::submitButton('Sign in', ['class' => 'btn btn-primary']) ?>
                        </footer>

                        <?php ActiveForm::end(); ?>

                    </div>
                </div>

                <h5 class="text-center"> - Or sign in using -</h5>

                <?= \yii\authclient\widgets\AuthChoice::widget([
                    'baseAuthUrl' => ['user/auth']
                ]) ?>

            </div>
            <!-- END RIGHT PANEL -->

        </div>
    </div>
</div>

<?php
$js = "
    runAllForms();

    $(function() {
        // Validation
        $('#login-form').validate({
            // Rules for form validation
            rules : {
                'LoginForm[username]' : {
                    required : true
                },
                'LoginForm[password]' : {
                    required : true,
                    minlength : 3,
                    maxlength : 20
                }
            },

            // Messages for form validation
            messages : {
                'LoginForm[username]' : {
                    required : 'Please enter your email address'
                },
                'LoginForm[password]' : {
                    required : 'Please enter your password'
                }
            },

            // Do not change code below
            errorPlacement : function(error, element) {
                error.insertAfter(element.parent());
            }
        });
    });
";

if ($title && $act_content) {
    $js .= "
        var title = " . json_encode($title) . ";
        var mess  = " . json_encode($act_content) . ";
        $.SmartMessageBox({
            title   : '<span class=\"txt-color-orangeDark\"><strong>' + title + '</strong></span>',
            content : mess,
            buttons : '[Ok]'
        }, function(ButtonPressed) {
            if (ButtonPressed === 'Ok') {
                $('#MsgBoxBack').addClass('animated fadeOutUp');
            }
        });
    ";
}
$this->registerJs($js, \yii\web\View::POS_END);
?>