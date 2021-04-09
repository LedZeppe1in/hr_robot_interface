<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use janisto\timepicker\TimePicker;
use app\modules\main\models\TestQuestion;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\TestQuestion */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="test-question-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput() ?>

    <?= $form->field($model, 'type')->dropDownList(TestQuestion::getTypes()) ?>

    <?= $form->field($model, 'text')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'audioFile')->fileInput() ?>

    <?= $form->field($model, 'maximum_time')->widget(TimePicker::className(), ['mode' => 'time',
        'clientOptions'=> [
            'timeFormat' => 'HH:mm:ss:l',
            'showSecond' => true,
            'showButtonPanel' => false
        ],
    ])->label('Максимальное время на ответ'); ?>

    <?= $form->field($model, 'time')->widget(TimePicker::className(), ['mode' => 'time',
        'clientOptions'=> [
            'timeFormat' => 'HH:mm:ss:l',
            'showSecond' => true,
            'showButtonPanel' => false
        ],
    ])->label('Время вопроса'); ?>

    <?= $form->field($model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>