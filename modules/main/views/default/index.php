<?php

use yii\helpers\Html;
use app\modules\main\models\User;
use yii\bootstrap\ButtonDropdown;

/* @var $this yii\web\View */
/* @var $surveys app\modules\main\models\Survey */

$this->title = 'HR Robot';
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
        <h4>На данный моменент, система находиться в тестовом режиме.
        </h4><br />
        <p>
            <?= Html::a('Пройти собеседование',
                'https://84.201.129.65:8080/HRRMaskEditor/GenerateR1Test.php',
                ['class' => 'btn btn-lg btn-success', 'style' => 'display:none']) ?>
            <?= (Yii::$app->user->isGuest || !Yii::$app->user->identity->role == User::ROLE_PSYCHOLOGIST) ?
                Html::a('Пройти видео-интервью', 'interview/31',
                    ['class' => 'btn btn-lg btn-success', 'style' => 'display:none']) : null ?>

            <?php
                $items = array();
                foreach ($surveys as $survey)
                    if ($survey->id != 36) {
                        $items[$survey->id]['label'] = $survey->name;
                        $items[$survey->id]['url'] = 'interview/' . $survey->id;
                    }
                if (Yii::$app->user->isGuest || !Yii::$app->user->identity->role == User::ROLE_PSYCHOLOGIST)
                    echo ButtonDropdown::widget([
                        'label' => 'Пройти видеоинтервью',
                        'dropdown' => [
                            'items' => $items,
                        ],
                        'options' => ['class' => 'btn btn-lg btn-success']
                    ]);
            ?>

            <?php
                $items = array();
                foreach ($surveys as $survey)
                    if ($survey->id != 31 && $survey->id != 36 && $survey->id != 37) {
                        $items[$survey->id]['label'] = $survey->name;
                        $items[$survey->id]['url'] = 'motivation-test/' . $survey->id;
                    }
                if (Yii::$app->user->isGuest || !Yii::$app->user->identity->role == User::ROLE_PSYCHOLOGIST)
                    echo ButtonDropdown::widget([
                        'label' => 'Пройти тест мотивации к труду',
                        'dropdown' => [
                            'items' => $items,
                        ],
                        'options' => ['class' => 'btn btn-lg btn-success']
                    ]);
            ?>
        </p>
    </div>
</div>