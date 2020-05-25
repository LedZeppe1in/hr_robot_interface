<?php

use janisto\timepicker\TimePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\main\models\Respondent;
use app\modules\main\models\VideoInterview;
use wbraganca\dynamicform\DynamicFormWidget;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\modules\main\models\VideoInterview */
/* @var $landmarkModels app\modules\main\models\Landmark */
?>

<script type="text/javascript">
    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Изменение номера у заголовков элементов шаблонов правил
        var dynamicLandmarkFormWrapper = jQuery(".add_dynamic_landmark_form_wrapper");
        dynamicLandmarkFormWrapper.on("afterInsert", function(e, item) {
            jQuery(".add_dynamic_landmark_form_wrapper .panel-title-question").each(function(index) {
                jQuery(this).html("Вопрос №" + (index + 1) + ":");
                $("#landmark-" + (index + 1) + "-start_time").timepicker({
                    timeFormat: "HH:mm:ss:l",
                    showSecond: true,
                    showButtonPanel: false
                });
            });
        });
        dynamicLandmarkFormWrapper.on("afterDelete", function(e) {
            jQuery(".add_dynamic_landmark_form_wrapper .panel-title-question").each(function(index) {
                jQuery(this).html("Вопрос №" + (index + 1) + ":")
            });
        });
    });
</script>

<div class="video-interview-form">

    <?php $form = ActiveForm::begin(['id' => 'video-interview-analysis-form']); ?>

    <?= $form->field($model, 'respondent_id')->dropDownList(Respondent::getRespondents())
        ->label('Респондент') ?>

    <?= $form->field($model, 'videoInterviewFile')->fileInput() ?>

    <?= $form->field($model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

    <?= $form->field($model, 'rotationParameter')->dropDownList(VideoInterview::getRotationTypes()) ?>

    <?= $form->field($model, 'mirroringParameter')->dropDownList(VideoInterview::getMirroringTypes()) ?>

    <?php DynamicFormWidget::begin([
        'widgetContainer' => 'add_dynamic_landmark_form_wrapper', // only alphanumeric characters plus "_" [A-Za-z0-9_]
        'widgetBody' => '.container-items', // css class selector
        'widgetItem' => '.item', // css class
        'limit' => 99, // the maximum times, an element can be cloned (default 999)
        'min' => 1, // 0 or 1 (default 1)
        'insertButton' => '.add-item', // css class
        'deleteButton' => '.remove-item', // css class
        'model' => $landmarkModels[0],
        'formId' => 'video-interview-analysis-form',
        'formFields' => [
            'questionText',
            'start_time',
            'finish_time'
        ],
    ]); ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <i class="fa fa-envelope"></i><b>Список вопросов</b>
            <button type="button" class="pull-right add-item btn btn-success btn-xs" id="add-new-question">
                <i class="glyphicon glyphicon-plus"></i> Добавить вопрос
            </button>
            <div class="clearfix"></div>
        </div>
        <div class="panel-body container-items"><!-- widgetContainer -->
            <?php foreach ($landmarkModels as $index => $landmarkModel): ?>
                <div class="item panel panel-default"><!-- widgetBody -->
                    <div class="panel-heading">
                        <span class="panel-title-question">Вопрос №<?= ($index + 1) ?>:</span>
                        <button type="button" class="pull-right remove-item btn btn-danger btn-xs">
                            <i class="glyphicon glyphicon-minus"></i>
                        </button>
                        <div class="clearfix"></div>
                    </div>
                    <div class="panel-body">
                        <div class="landmark-question">
                            <?= $form->field($landmarkModel, "[{$index}]questionText")
                                ->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="landmark-start-time">
                            <?= $form->field($landmarkModel, "[{$index}]start_time")
                                ->widget(TimePicker::className(), [
                                    'mode' => 'time',
                                    'clientOptions'=>[
                                        'timeFormat' => 'HH:mm:ss:l',
                                        'showSecond' => true,
                                        'showButtonPanel' => false
                                    ]
                                ]); ?>
                        </div>
                        <div class="landmark-finish-time">
                            <?= $form->field($landmarkModel, "[{$index}]finish_time")
                                ->widget(TimePicker::className(), [
                                    'mode' => 'time',
                                    'clientOptions'=>[
                                        'timeFormat' => 'HH:mm:ss:l',
                                        'showSecond' => true,
                                        'showButtonPanel' => false
                                    ]
                                ]); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php DynamicFormWidget::end(); ?>

    <div class="form-group">
        <?= Html::submitButton('Анализ', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>