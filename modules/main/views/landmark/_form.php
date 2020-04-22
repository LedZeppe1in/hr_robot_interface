<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\main\models\VideoInterview;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\Landmark */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="landmark-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'video_interview_id')->dropDownList(VideoInterview::getVideoInterviews()) ?>

    <?= $form->field($model, 'landmarkFile')->fileInput() ?>

    <?= $form->field($model, 'description')->textarea(['maxlength' => true, 'rows'=>6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>