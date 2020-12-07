<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\ButtonDropdown;

/* @var $this yii\web\View */
/* @var $surveys app\modules\main\models\Survey */

$this->title = 'Тестирование HR Robot';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="main-default-test">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="body-content">
        <p>
            <?= Html::a('Пройти собеседование',
                'https://84.201.129.65:8080/HRRMaskEditor/GenerateR1Test.php',
                ['class' => 'btn btn-success', 'style' => 'width: 192px;']) ?>
        </p>

        <p>
            <?php
                $items = array();
                foreach ($surveys as $survey) {
                    $items[$survey->id]['label'] = $survey->name;
                    $items[$survey->id]['url'] = 'interview/' . $survey->id;
                }
                echo ButtonDropdown::widget([
                    'label' => 'Пройти видеоинтервью',
                    'dropdown' => [
                        'items' => $items,
                    ],
                    'options' => ['class' => 'btn btn-primary', 'style' => 'width: 192px;']
                ]);
            ?>
        </p>

        <p>
            <?= Html::a('Анализ видеоинтервью', ['analysis'],
                ['class' => 'btn btn-primary', 'style' => 'width: 192px;']) ?>
        </p>
        <p>
            <?= Html::a('Записать видео', ['record'],
                ['class' => 'btn btn-primary', 'style' => 'width: 192px;']) ?>
        </p>
    </div>
</div>