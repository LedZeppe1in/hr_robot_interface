<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Результаты определения признаков';
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
                'template' => '{view} {detection-file-download} {facts-file-download} {delete}',
                'buttons' => [
                    'detection-file-download' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-floppy-save',
                                'title' => 'Скачать результаты определения признаков']);
                        $url = ['/detection-result/file-download/' . $model->id];
                        return Html::a($icon, $url);
                    },
                    'facts-file-download' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-level-up', 'title' => 'Скачать факты']);
                        $url = ['/detection-result/facts-download/' . $model->id];
                        return Html::a($icon, $url);
                    },
                ],
            ],
        ],
    ]); ?>

</div>