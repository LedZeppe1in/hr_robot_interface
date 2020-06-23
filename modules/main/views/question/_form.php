<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use janisto\timepicker\TimePicker;
use app\modules\main\models\Question;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\Question */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="question-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'type')->dropDownList(Question::getTypes()) ?>

    <?= $form->field($model, 'text')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'audioFile')->fileInput() ?>

    <?= $form->field($model, 'time')->widget(TimePicker::className(), ['mode' => 'time',
        'clientOptions'=> [
            'timeFormat' => 'HH:mm:ss:l',
            'showSecond' => true,
            'showButtonPanel' => false
        ],
    ])->label('Время'); ?>

    <?= $form->field($model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>