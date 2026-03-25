<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\LandingPage */
/* @var $savedSearches app\models\SavedSearch[] */

$session = Yii::$app->session;
$uri_page = '@' . Yii::$app->request->url;
$recent_pages = 'Create Landing Page' . $uri_page;
if (!isset($session['recent_pages']) || count($session['recent_pages']) == 0) {
    $session['recent_pages'] = array($recent_pages);
} else {
    $sess_arr = $session['recent_pages'];
    $sess_arr[] = $recent_pages;
    $session['recent_pages'] = $sess_arr;
}
?>

<!-- Left panel : Navigation area -->
<?php if (!Yii::$app->user->isGuest): ?>
    <?= $this->render('/layouts/aside', ['profile' => $profile]) ?>
<?php endif; ?>
<!-- END NAVIGATION -->

<!-- MAIN PANEL -->
<div id="main" role="main">

    <div id="ribbon">
        <ol class="breadcrumb">
            <li>
                <a href="<?= Url::to(['/landing']) ?>">Landing</a>
            </li>
            <li>
                Create
            </li>
        </ol>
    </div>

    <?php if (Yii::$app->session->hasFlash('profileMessage')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('profileMessage') ?>
        </div>
    <?php endif; ?>

    <!-- MAIN CONTENT -->
    <div id="content">
        <div class="row">
            <div class="col-xs-12 col-sm-7 col-md-7 col-lg-4">
                <h1 class="page-title txt-color-blueDark">
                    Create Landing Page
                </h1>
            </div>
        </div>
        <div id="widget-grid">
            <?= Html::beginForm(['/landing/create'], 'post') ?>
            <div class="jarviswidget" id="wid-id-1" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                <header>
                    <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                    <h2>Saved searches </h2>
                </header>
                <div>
                    <div class="jarviswidget-editbox">
                        <!-- This area used as dropdown edit box -->
                    </div>
                    <div class="table-wrapper">
                        <table id="user" class="table table-bordered table-striped" style="clear: both">
                            <thead>
                            <tr>
                                <th>Search ID</th>
                                <th>Search Name</th>
                                <th>Orig. Date</th>
                                <th>Expiry Date</th>
                                <th>Linked Email Addresses</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $i = 0; foreach ($savedSearches as $savedSearch): ?>
                                <?php
                                $created_at = $savedSearch->created_at ? new DateTime($savedSearch->created_at) : new DateTime();
                                $expiry_date = $savedSearch->expiry_date ? new DateTime($savedSearch->expiry_date) : new DateTime();
                                ?>
                                <tr>
                                    <td><?= $savedSearch->id ?></td>
                                    <td>
                                        <a href="javascript:void(0);"
                                           class="savedSearchName"
                                           data-type="text"
                                           data-pk="<?= $savedSearch->id ?>"
                                           data-name="name"
                                           data-url="<?= Url::to(['/searches/editable']) ?>"
                                           data-original-title="Enter a Search Name/ Title">
                                             <?= Html::encode($savedSearch->name) ?>
                                         </a>
                                    </td>
                                    <td><?= $created_at->format('Y-m-d') ?></td>
                                    <td>
                                        <a href="javascript:void(0);"
                                           class="savedSearchExpiryDate"
                                           data-type="date"
                                           data-viewformat="yyyy-mm-dd"
                                           data-pk="<?= $savedSearch->id ?>"
                                           data-name="expiry_date"
                                           data-url="<?= Url::to(['/searches/editable']) ?>"
                                           data-placement="right"
                                           data-original-title="When do you want the wealth to end?">
                                             <?= $expiry_date->format('Y-m-d') ?>
                                         </a>
                                    </td>
                                    <td>
                                        <?php if (isset($savedSearch->alertEmails)): ?>
                                            <?php foreach ($savedSearch->alertEmails as $alertEmailModel): ?>
                                                <p>
                                                    <a  href="javascript:void(0);"
                                                       class="savedSearchLinkedEmails"
                                                       data-type="email"
                                                       data-pk="<?= $alertEmailModel->id ?>"
                                                       data-name="email"
                                                       data-saved_search_id="<?= $savedSearch->id ?>"
                                                       data-url="<?= Url::to(['/searches/editable']) ?>"
                                                       data-emptytext="Add"
                                                       data-original-title="Enter email">
                                                         <?= Html::encode($alertEmailModel->email) ?>
                                                     </a>
                                                </p>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        <p><a  href="javascript:void(0);"
                                               class="savedSearchLinkedEmails"
                                               data-type="email"
                                               data-pk="0"
                                               data-name="email"
                                               data-saved_search_id="<?= $savedSearch->id ?>"
                                               data-url="<?= Url::to(['/searches/editable']) ?>"
                                               data-emptytext="Add"
                                               data-original-title="Enter email">
                                            </a>
                                        </p>
                                    </td>
                                    <td>
                                        <input type="radio" name="LandingPage[search_id]" value="<?= $savedSearch->id ?>" <?= ($i == 0) ? 'checked="checked"' : '' ?> />
                                    </td>
                                </tr>
                            <?php $i++; endforeach; ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="jarviswidget" id="wid-id-2" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                <header>
                    <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                    <h2>Landing page content section</h2>
                </header>
                <div>
                    <div class="jarviswidget-editbox">
                        <!-- This area used as dropdown edit box -->
                    </div>
                    <label><h3>Landing title (for slug generation)</h3>
                        <input type="text" class="form-control" name="LandingPage[title]" />
                    </label>
                    <br>
                    <label for="landingText1"><h3>First content box</h3></label>
                    <textarea name="postContentPartOne" id="landingText1" cols="30" rows="10" class="form-control landingText"></textarea>
                    <label for="landingText2"><h3>Second content box</h3></label>
                    <textarea name="postContentPartTwo" id="landingText2" cols="30" rows="10" class="form-control landingText"></textarea>
                </div>
            </div>
            <div class="jarviswidget" id="wid-id-3" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-custombutton="false">
                <header>
                    <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                    <h2>Membership options</h2>
                </header>
                <div>
                    <div class="jarviswidget-editbox">
                        <!-- This area used as dropdown edit box -->
                    </div>
                    <div class="col-sm-4 col-md-4 col-lg-4 member-box">
                        <h3>First membership box</h3>
                        <div>
                            <label><p>Membership option title</p>
                                <input class="form-control" type="text" name="memberOptions[first][title]" />
                            </label>
                        </div>
                        <div>
                            <label><p>Box color</p>
                                <input class="form-control colorpicker" type="text" name="memberOptions[first][color]" />
                            </label>
                        </div>
                        <div>
                            <label><p>Price</p>
                                <input class="form-control" type="text" name="memberOptions[first][price]" />
                            </label>
                        </div>
                        <label for="featureList1"><h3>Feature list (separated by | symbol)</h3></label>
                        <br/>
                        <textarea class="form-control" name="memberOptions[first][featureList]" id="featureList1" cols="30" rows="10"></textarea>
                    </div>
                    <div class="col-sm-4 col-md-4 col-lg-4 member-box">
                        <h3>Second membership box</h3>
                        <div>
                            <label><p>Membership option title</p>
                                <input class="form-control" type="text" name="memberOptions[second][title]" />
                            </label>
                        </div>
                        <div>
                            <label><p>Box color</p>
                                <input class="form-control colorpicker" type="text" name="memberOptions[second][color]" />
                            </label>
                        </div>
                        <div>
                            <label><p>Price</p>
                                <input class="form-control" type="text" name="memberOptions[second][price]" />
                            </label>
                        </div>
                        <label for="featureList2"><h3>Feature list (separated by | symbol)</h3></label>
                        <br />
                        <textarea class="form-control" name="memberOptions[second][featureList]" id="featureList2" cols="30" rows="10"></textarea>
                    </div>
                    <div class="col-sm-4 col-md-4 col-lg-4 member-box">
                        <h3>Third membership box</h3>
                        <div>
                            <label><p>Membership option title</p>
                                <input class="form-control" type="text" name="memberOptions[third][title]" />
                            </label>
                        </div>
                        <div>
                            <label><p>Box color</p>
                                <input class="form-control colorpicker" type="text" name="memberOptions[third][color]" />
                            </label>
                        </div>
                        <div>
                            <label><p>Price</p>
                                <input class="form-control" type="text" name="memberOptions[third][price]" />
                            </label>
                        </div>
                        <label for="featureList3"><h3>Feature list (separated by | symbol)</h3></label>
                        <br/>
                        <textarea class="form-control" name="memberOptions[third][featureList]" id="featureList3" cols="30" rows="10"></textarea>
                    </div>
                </div>
            </div>
            <?= Html::submitButton('save', ['class' => 'btn btn-primary']) ?>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>