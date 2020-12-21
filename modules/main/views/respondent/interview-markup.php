<?php

use yii\helpers\Html;

require_once('/var/www/hr-robot-default.com/public_html/Common/CommonData.php');

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\Respondent */
/* @var $userId app\modules\main\controllers\RespondentController */

$this->title = 'Разметка интервью для: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Интервью респондентов', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<!-- Подключение css-стилей -->
<?php $this->registerCssFile('/css/UI Themes Files/base/theme.css') ?>
<?php $this->registerCssFile('/css/jQueryUI.css') ?>
<?php $this->registerCssFile('/css/jQueryUIStructure.css') ?>
<?php $this->registerCssFile('/css/jQueryGrid.css') ?>

<!-- Подключение js-скриптов -->
<?php $this->registerJsFile('/js/jQueryUI.js') ?>
<?php $this->registerJsFile('/js/jquery.ui.datepicker-ru.js') ?>
<?php $this->registerJsFile('/js/grid.locale-ru.js') ?>
<?php $this->registerJsFile('/js/jQueryGrid.js') ?>

<script type="text/javascript">
    jQuery(document).ready(function() {
        var IDOfUser = '<?= $userId ?>';
        var CodeOfRespondentInterview = '<?= $model->name ?>';
        var AccessKey = '<?= TCommonData::InternalAccessKey() ?>';
        var RetrieveDataURL = 'https://84.201.129.65:8880/Main.php';
        VideoAnalysisLibrary.Initialize(jQuery('#VideoAnalysisUIControl'), IDOfUser, CodeOfRespondentInterview,
            AccessKey, RetrieveDataURL);
    });
</script>

<div class="interview-markup">

    <h1><?= Html::encode($this->title) ?></h1>

    <div id="VideoAnalysisUIControl" style="padding: 5px;"></div>

    <?php echo TConstructorOfVideoAnalysisPage::IncludeRequiredCode(); ?>

</div>