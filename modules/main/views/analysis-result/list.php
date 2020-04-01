<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Результаты анализа';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="analysis-result-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'attribute'=>'video_interview_id',
                'label' => 'Видеоинтервью',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->videoInterview->video_file_name;
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['class' => 'action-column'],
                'template' => '{view} {detection-file-download} {fact-templates-file-download} 
                    {interpretation-file-download} {delete}',
                'buttons' => [
                    'detection-file-download' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-floppy-save',
                                'title' => 'Скачать результаты определения признаков']);
                        $url = ['/analysis-result/detection-file-download/' . $model->id];
                        return Html::a($icon, $url);
                    },
                    'fact-templates-file-download' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-level-up',
                                'title' => 'Скачать шаблоны фактов']);
                        $url = ['/analysis-result/fact-templates-download/' . $model->id];
                        return Html::a($icon, $url);
                    },
                    'interpretation-file-download' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-export',
                                'title' => 'Скачать результаты интерпретации признаков']);
                        $url = '#';
                        return Html::a($icon, $url);
                    },
                ],
            ],
        ],
    ]); ?>

</div>