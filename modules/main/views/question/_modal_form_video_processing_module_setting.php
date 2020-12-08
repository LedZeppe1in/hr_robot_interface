<?php

use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use yii\widgets\ActiveForm;
use app\modules\main\models\VideoProcessingModuleSettingForm;

/* @var $videoProcessingModuleSettingForm app\modules\main\models\VideoProcessingModuleSettingForm */
?>

<!-- Модальное окно запуска модуля обработки видео (Иван) -->
<?php Modal::begin([
    'id' => 'formLandmarkModalForm',
    'header' => '<h3>Настройки модуля обработки видео (Иван)</h3>',
]); ?>

    <?php $form = ActiveForm::begin([
        'id' => 'get-landmark-form',
        'method' => 'post',
        'action' => ['/question/get-ivan-landmarks'],
        'enableClientValidation' => true,
    ]); ?>

        <?= $form->errorSummary($videoProcessingModuleSettingForm); ?>

        <?= $form->field($videoProcessingModuleSettingForm, 'rotateMode')
            ->dropDownList(VideoProcessingModuleSettingForm::getRotateModes()) ?>

        <?= $form->field($videoProcessingModuleSettingForm, 'enableAutoRotate')
            ->dropDownList(VideoProcessingModuleSettingForm::getAutoRotates()) ?>

        <?= $form->field($videoProcessingModuleSettingForm, 'mirroring')
            ->dropDownList(VideoProcessingModuleSettingForm::getMirroringModes()) ?>

        <?= $form->field($videoProcessingModuleSettingForm, 'alignMode')
            ->dropDownList(VideoProcessingModuleSettingForm::getAlignModes()) ?>

        <?= $form->field($videoProcessingModuleSettingForm, 'landmarkMode')
            ->dropDownList(VideoProcessingModuleSettingForm::getLandmarkModes()) ?>

        <?= $form->field($videoProcessingModuleSettingForm, 'videoProcessingParameter')
            ->dropDownList(VideoProcessingModuleSettingForm::getParameterValues()) ?>

        <?= $form->field($videoProcessingModuleSettingForm, 'enableSecondScript')
            ->dropDownList(VideoProcessingModuleSettingForm::getSecondScriptFlags()) ?>

        <?= Button::widget([
            'label' => 'Сформировать цифровую маску',
            'options' => [
                'id' => 'form-landmark-button',
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