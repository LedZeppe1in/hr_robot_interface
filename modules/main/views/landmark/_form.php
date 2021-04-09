<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use janisto\timepicker\TimePicker;
use app\modules\main\models\Landmark;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\modules\main\models\Landmark */
/* @var $questions app\modules\main\controllers\LandmarkController */
?>

<div class="landmark-form">

    <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'video_interview_id')->textInput() ?>

        <?= $form->field($model, 'landmarkFile')->fileInput() ?>

        <?= $form->field($model, 'type')->dropDownList(Landmark::getTypes()) ?>

        <?= $form->field($model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

        <?= $form->field($model, 'rotation')->dropDownList(Landmark::getRotationTypes()) ?>

        <?= $form->field($model, 'mirroring')->dropDownList(Landmark::getMirroringValues()) ?>

        <?= $form->field($model, 'testQuestionId')->textInput() ?>

        <?= $form->field($model, 'start_time')->widget(TimePicker::className(), ['mode' => 'time',
            'clientOptions'=> [
                'timeFormat' => 'HH:mm:ss:l',
                'showSecond' => true,
                'showButtonPanel' => false
            ],
        ])->label('Время начала нарезки'); ?>

        <?= $form->field($model, 'finish_time')->widget(TimePicker::className(), ['mode' => 'time',
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