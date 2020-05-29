<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use janisto\timepicker\TimePicker;
use app\modules\main\models\Landmark;
use app\modules\main\models\VideoInterview;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\modules\main\models\Landmark */
/* @var $questions app\modules\main\controllers\LandmarkController */
?>

<script type="text/javascript">
    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Получение откидного поля со списком вопросов
        let questionSelector = document.getElementById("landmark-question_id");
        // Получение поля ввода текста вопроса по id
        let questionInputField = document.getElementById("landmark-questiontext");
        // Получение метки для поля ввода текста вопроса
        let questionInputLabel = document.querySelector("label[for='landmark-questiontext']");
        // Если значение не задано, то раскрываем поле ввода текста вопроса
        if (questionSelector.value === '') {
            questionInputField.style.display = "";
            questionInputField.value = "";
            questionInputLabel.style.display = "";
        } else {
            questionInputField.style.display = "none";
            questionInputField.value = "hidden";
            questionInputLabel.style.display = "none";
        }
        // Обработка выбора значения в откидном списке с вопросами
        $(document).on("change", ".question-list", function() {
            // Получение значения в откидном поле
            let questionId = this.value;
            // Если значение не задано, то раскрываем поле ввода текста вопроса
            if (questionId === '') {
                questionInputField.style.display = "";
                questionInputField.value = "";
                questionInputLabel.style.display = "";
            } else {
                questionInputField.style.display = "none";
                questionInputField.value = "hidden";
                questionInputLabel.style.display = "none";
            }
        });
    });
</script>

<div class="landmark-form">

    <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'video_interview_id')->dropDownList(VideoInterview::getVideoInterviews())
            ->label('Видеоинтервью') ?>

        <?= $form->field($model, 'landmarkFile')->fileInput() ?>

        <?= $form->field($model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

        <?= $form->field($model, 'rotation')->dropDownList(Landmark::getRotationTypes()) ?>

        <?= $form->field($model, 'mirroring')->dropDownList(Landmark::getMirroringValues()) ?>

        <?= $form->field($model, 'question_id')->dropDownList($questions, ['prompt' => 'Ввести новый вопрос...',
            'class' => 'form-control question-list'])->label('Вопрос') ?>

        <?= $form->field($model, 'questionText')->textInput(['maxlength' => true]); ?>

        <?= $form->field($model, 'startTime')->widget(TimePicker::className(), ['mode' => 'time',
            'clientOptions'=> [
                'timeFormat' => 'HH:mm:ss:l',
                'showSecond' => true,
                'showButtonPanel' => false
            ],
        ])->label('Время начала нарезки'); ?>

        <?= $form->field($model, 'finishTime')->widget(TimePicker::className(), ['mode' => 'time',
            'clientOptions'=> [
                'timeFormat' => 'HH:mm:ss:l',
                'showSecond' => true,
                'showButtonPanel' => false
            ],
        ])->label('Время окончания нарезки'); ?>

        <div class="form-group">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
        </div>

    <?php ActiveForm::end(); ?>

</div>