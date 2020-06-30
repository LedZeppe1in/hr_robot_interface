<?php

use yii\widgets\ActiveForm;
use janisto\timepicker\TimePicker;
use app\modules\main\models\Respondent;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $videoInterviewModel app\modules\main\models\VideoInterview */
/* @var $landmarkModels app\modules\main\models\Landmark */
/* @var $questionTexts app\modules\main\models\Question */
?>

<div class="interview-form" style="display: none">

    <?php $form = ActiveForm::begin(['id' => 'video-interview-form']); ?>

        <?= $form->field($videoInterviewModel, 'respondent_id')->dropDownList(Respondent::getRespondents())
            ->label('Респондент') ?>

        <?php foreach ($landmarkModels as $index => $landmarkModel): ?>
            <div class="item panel panel-default">
                <div class="panel-heading"><span class="panel-title-question">Вопрос №<?= ($index + 1) ?>:</span></div>
                <div class="panel-body">
                    <div class="landmark-question-id">
                        <?= $form->field($landmarkModel, "[{$index}]question_id")->dropDownList($questionTexts)
                            ->label('Вопрос') ?>
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

    <?php ActiveForm::end(); ?>

</div>