<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\AnalysisResult */
/* @var $interpretationResult app\modules\main\controllers\InterpretationResultController */

$this->title = $model->landmark->landmark_file_name;
$this->params['breadcrumbs'][] = ['label' => 'Результаты интерпретации признаков', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="interpretation-result-view">

    <h1>Результат для: <?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите удалить этот элемент?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'created_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            [
                'attribute' => 'updated_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            [
                'attribute' => 'landmark_id',
                'label' => 'ID цифровой маски',
                'value' => $model->landmark->id,
            ],
            [
                'attribute' => 'landmark_id',
                'label' => 'Название файла с лицевыми точками',
                'value' => $model->landmark->landmark_file_name,
            ],
            [
                'label' => 'Описание',
                'value' => ($model->description != '') ? $model->description : null,
                'format' => 'raw'
            ],
            [
                'label' => 'Файл с результатами интерпретации признаков',
                'value' => ($model->interpretation_result_file_name != '') ? Html::a('скачать',
                    ['file-download', 'id' => $model->id], ['target' => '_blank']) : null,
                'format' => 'raw'
            ],
        ],
    ]) ?>

    <?php
        echo '<pre>';
        print_r($interpretationResult);
        echo '</pre>';
    ?>
</div>