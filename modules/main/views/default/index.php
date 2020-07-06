<?php

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\GerchikovTestConclusion */

$this->title = 'HR Robot';

use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="main-default-index">
    <div class="jumbotron">
        <h1>Добро пожаловать в HR Robot!</h1>
    </div>
    <div class="body-content">
        <h4><b>HR Robot</b> —  это веб-ориентированная интеллектуальная программная система поддержки
        принятия решений при отборе кандидатов на вакансии и проверке персонала на мотивацию (исследовании
            психологической обстановки в коллективе).
        </h4>
        <h4>Особенностью системы поддержки принятия решений <b>HR Robot</b> является использование результатов
            определения эмоционального состояния кандидата на вакансию (персонала) на основе <i>видеоинформации</i>,
            полученной с веб-камеры или камеры смартфона при проведении интервью (собеседования), а также результатов
            психологического тестирования.
        </h4><br />
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