<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\main\models\MainRespondent;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\Respondent */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="respondent-form">

    <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'main_respondent_id')->dropDownList(MainRespondent::getMainRespondents()) ?>

        <div class="form-group">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
        </div>

    <?php ActiveForm::end(); ?>

</div>