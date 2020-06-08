<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use janisto\timepicker\TimePicker;
use app\modules\main\models\Respondent;
use app\modules\main\models\VideoInterview;
use wbraganca\dynamicform\DynamicFormWidget;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\modules\main\models\VideoInterview */
/* @var $landmarkModels app\modules\main\models\Landmark */
/* @var $questions app\modules\main\controllers\DefaultController */
?>

<script type="text/javascript">
    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Изменение номера у заголовков элементов шаблонов правил
        let dynamicLandmarkFormWrapper = jQuery(".add_dynamic_landmark_form_wrapper");
        dynamicLandmarkFormWrapper.on("afterInsert", function(e, item) {
            jQuery(".add_dynamic_landmark_form_wrapper .panel-title-question").each(function(index) {
                jQuery(this).html("Вопрос №" + (index + 1) + ":");
                $("#landmark-" + (index + 1) + "-start_time").timepicker({
                    timeFormat: "HH:mm:ss:l",
                    showSecond: true,
                    showButtonPanel: false
                });
                $("#landmark-" + (index + 1) + "-finish_time").timepicker({
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
        // Обработка выбора значения в откидном списке с вопросами
        $(document).on("change", ".question-list", function() {
            // Формирование названия id для поля ввода текста вопроса
            let questionInputName = this.id.slice(0, this.id.indexOf("-question")) + "-questiontext";
            // Получение элемента поля ввода текста вопроса по id
            let element = document.getElementById(questionInputName);
            // Получение элемента метки для поля ввода текста вопроса
            let label = document.querySelector("label[for='" + questionInputName + "']");
            // Получение значения в откидном поле
            let questionId = this.value;
            // Если значение не задано, то раскрываем поле ввода текста вопроса
            if (questionId === '') {
                element.style.display = "";
                element.value = "";
                label.style.display = "";
            } else {
                element.style.display = "none";
                element.value = "hidden";
                label.style.display = "none";
            }
        });
    });
</script>

<div class="video-interview-form">

    <?php $form = ActiveForm::begin(['id' => 'get-landmarks-form']); ?>

    <?= $form->field($model, 'respondent_id')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'rotationParameter')->dropDownList(VideoInterview::getRotationTypes()) ?>

    <?= $form->field($model, 'mirroringParameter')->dropDownList(VideoInterview::getMirroringValues()) ?>

    <?php DynamicFormWidget::begin([
        'widgetContainer' => 'add_dynamic_landmark_form_wrapper', // only alphanumeric characters plus "_" [A-Za-z0-9_]
        'widgetBody' => '.container-items', // css class selector
        'widgetItem' => '.item', // css class
        'limit' => 99, // the maximum times, an element can be cloned (default 999)
        'min' => 1, // 0 or 1 (default 1)
        'insertButton' => '.add-item', // css class
        'deleteButton' => '.remove-item', // css class
        'model' => $landmarkModels[0],
        'formId' => 'get-landmarks-form',
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
                        <div class="landmark-questions">
                            <?= $form->field($landmarkModel, "[{$index}]question_id")->dropDownList($questions,
                                ['prompt' => 'Ввести новый вопрос...', 'class' => 'form-control question-list'])
                                    ->label('Вопрос') ?>
                        </div>
                        <div class="landmark-question">
                            <?= $form->field($landmarkModel, "[{$index}]questionText")
                                ->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="landmark-start-time">
                            <?= $form->field($landmarkModel, "[{$index}]start_time")
                                ->widget(TimePicker::className(), [
                                    'mode' => 'time',
                                    'clientOptions'=> [
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
                                    'clientOptions'=> [
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
        <?= Html::submitButton('Сформировать цифровую маску', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>