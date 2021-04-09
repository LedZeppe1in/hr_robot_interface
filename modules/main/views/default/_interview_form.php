<?php

use yii\widgets\ActiveForm;
use janisto\timepicker\TimePicker;
use app\modules\main\models\Landmark;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $videoInterviewModel app\modules\main\models\VideoInterview */
/* @var $landmarkModel app\modules\main\models\Landmark */
?>

<div class="interview" style="display: none">

    <?php $form = ActiveForm::begin(['id' => 'landmark-form']); ?>

        <?= $form->field($landmarkModel, 'video_interview_id')
            ->textInput(['value' => $videoInterviewModel->id]) ?>

        <?= $form->field($landmarkModel, 'mirroring')->dropDownList(Landmark::getMirroringValues()) ?>

        <?= $form->field($landmarkModel, "start_time")->widget(TimePicker::className(), ['mode' => 'time',
            'clientOptions'=> [
                'timeFormat' => 'HH:mm:ss:l',
                'showSecond' => true,
                'showButtonPanel' => false
            ]
        ]); ?>

        <?= $form->field($landmarkModel, "finish_time")->widget(TimePicker::className(), ['mode' => 'time',
            'clientOptions'=> [
                'timeFormat' => 'HH:mm:ss:l',
                'showSecond' => true,
                'showButtonPanel' => false
            ]
        ]); ?>

    <?php ActiveForm::end(); ?>

</div>