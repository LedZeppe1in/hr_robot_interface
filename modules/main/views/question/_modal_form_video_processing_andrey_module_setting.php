<?php

use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use yii\widgets\ActiveForm;
use app\modules\main\models\VideoProcessingModuleSettingForm;

/* @var $videoProcessingModuleSettingForm app\modules\main\models\VideoProcessingModuleSettingForm */
?>

<!-- Модальное окно запуска модуля обработки видео (Андрей) -->
<?php Modal::begin([
    'id' => 'formAndreyLandmarkModalForm',
    'header' => '<h3>Настройки модуля обработки видео (Андрей)</h3>',
]); ?>

    <?php $form = ActiveForm::begin([
        'id' => 'get-andrey-landmark-form',
        'method' => 'post',
        'action' => ['/video-interview/get-andrey-landmarks'],
        'enableClientValidation' => true,
    ]); ?>

        <?= $form->field($videoProcessingModuleSettingForm, 'rotateMode')
            ->dropDownList(VideoProcessingModuleSettingForm::getRotateModes()) ?>

        <?= Button::widget([
            'label' => 'Сформировать цифровую маску',
            'options' => [
                'id' => 'form-andrey-landmark-button',
                'class' => 'btn-success',
                'style' => 'margin:5px'
            ]
        ]); ?>

        <?= Button::widget([
            'label' => 'Отмена',
            'options' => [
                'class' => 'btn-danger',
                'style' => 'margin:5px',
                'data-dismiss'=>'modal'
            ]
        ]); ?>

    <?php ActiveForm::end(); ?>

<?php Modal::end(); ?>