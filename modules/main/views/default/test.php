<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\GerchikovTestConclusion */

$this->title = 'Тестирование HR Robot';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="main-default-test">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="body-content">
        <p>
            <?= Html::a('Пройти собеседование',
                'https://84.201.129.65:8080/HRRMaskEditor/GenerateR1Test.php',
                ['class' => 'btn btn-success', 'style' => 'width: 180px;']) ?>
        </p>

        <?php $form = ActiveForm::begin(['action' => 'interview', 'method' => 'POST']); ?>
            <?= $form->field($model, 'accept_test')->hiddenInput(['value' => 1])
                ->label(false); ?>
            <?= $form->field($model, 'accept_level')->hiddenInput(['value' => 100])
                ->label(false); ?>
            <?= $form->field($model, 'instrumental_motivation')->hiddenInput(['value' => '1'])
                ->label(false); ?>
            <?= $form->field($model, 'professional_motivation')->hiddenInput(['value' => '2'])
                ->label(false); ?>
            <?= $form->field($model, 'patriot_motivation')->hiddenInput(['value' => '3'])
                ->label(false); ?>
            <?= $form->field($model, 'master_motivation')->hiddenInput(['value' => '3'])
                ->label(false); ?>
            <?= $form->field($model, 'avoid_motivation')->hiddenInput(['value' => '3'])
                ->label(false); ?>
            <?= $form->field($model, 'description')->hiddenInput(['value' => 'Автоматически созданная запись'])
                ->label(false); ?>
            <div class="form-group">
                <?= Html::submitButton('Пройти видеоинтервью',
                    ['class' => 'btn btn-primary', 'style' => 'width: 180px;']) ?>
            </div>
        <?php ActiveForm::end(); ?>

        <p>
            <?= Html::a('Анализ видеоинтервью', ['analysis'],
                ['class' => 'btn btn-primary', 'style' => 'width: 180px;']) ?>
        </p>
        <p>
            <?= Html::a('Записать видео', ['record'],
                ['class' => 'btn btn-primary', 'style' => 'width: 180px;']) ?>
        </p>
    </div>
</div>