<?php

use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use yii\widgets\ActiveForm;
use app\modules\main\models\FeaturesDetectionModuleSettingForm;

/* @var $featuresDetectionModuleSettingForm app\modules\main\models\FeaturesDetectionModuleSettingForm */
?>

<!-- Модальное окно запуска модуля определения признаков (МОП) -->
<?php Modal::begin([
    'id' => 'facialFeaturesDetectionModalForm',
    'header' => '<h3>Настройки модуля определения признаков (МОП)</h3>',
]); ?>

    <?php $form = ActiveForm::begin([
        'id' => 'facial-features-detection-form',
        'method' => 'post',
        'action' => ['/analysis-result/detection'],
        'enableClientValidation' => true,
    ]); ?>

        <?= $form->field($featuresDetectionModuleSettingForm, 'invariantPointFlag')
            ->dropDownList(FeaturesDetectionModuleSettingForm::getInvariantPoints()) ?>

        <div class="row">
            <div class="col-lg-6">
                <?= $form->field($featuresDetectionModuleSettingForm, 'firstInvariantPoint')->textInput([
                    'value' => FeaturesDetectionModuleSettingForm::INVARIANT1_POINT1
                ]) ?>
            </div>
            <div class="col-lg-6">
                <?= $form->field($featuresDetectionModuleSettingForm, 'secondInvariantPoint')->textInput([
                    'value' => FeaturesDetectionModuleSettingForm::INVARIANT1_POINT2
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <?= $form->field($featuresDetectionModuleSettingForm, 'invariantRightLengthFirstPoint')
                    ->textInput(['value' => FeaturesDetectionModuleSettingForm::INVARIANT_LENGTH1_POINT1]) ?>
            </div>
            <div class="col-lg-6">
                <?= $form->field($featuresDetectionModuleSettingForm, 'invariantRightLengthSecondPoint')
                    ->textInput(['value' => FeaturesDetectionModuleSettingForm::INVARIANT_LENGTH1_POINT2]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <?= $form->field($featuresDetectionModuleSettingForm, 'invariantLeftLengthFirstPoint')
                    ->textInput(['value' => FeaturesDetectionModuleSettingForm::INVARIANT_LENGTH2_POINT1]) ?>
            </div>
            <div class="col-lg-6">
            <?= $form->field($featuresDetectionModuleSettingForm, 'invariantLeftLengthSecondPoint')
                ->textInput(['value' => FeaturesDetectionModuleSettingForm::INVARIANT_LENGTH2_POINT2]) ?>
            </div>
        </div>

        <?= Button::widget([
            'label' => 'Запуск МОП с выбранными параметрами',
            'options' => [
                'id' => 'facial-features-detection-button',
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