<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Итоговые результаты анализа';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="analysis-result-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'attribute'=>'landmark',
                'label' => 'ID видеоинтервью',
                'format' => 'raw',
                'value' => function($data) {
                    return Html::a($data->landmark->video_interview_id,
                        ['video-interview/view', 'id' => $data->landmark->video_interview_id]);
                },
            ],
            [
                'attribute'=>'landmark',
                'label' => 'ID видео на вопрос',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->landmark->question_id != '') ? Html::a($data->landmark->question_id,
                        ['question/view', 'id' => $data->landmark->question_id]) : null;
                },
            ],
            [
                'attribute'=>'landmark_id',
                'format' => 'raw',
                'value' => function($data) {
                    return Html::a($data->landmark_id, ['landmark/view', 'id' => $data->landmark_id]);
                },
            ],
            [
                'attribute'=>'landmark_id',
                'label' => 'Название файла с лицевыми точками',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->landmark->landmark_file_name;
                },
            ],
            [
                'attribute'=>'description',
                'label' => 'Описание',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->description != '') ? $data->description : null;
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['class' => 'action-column'],
                'template' => '{view} {update} {mask-editor} {detection-file-download} {facts-file-download} 
                    {interpretation-file-download} {delete}',
                'buttons' => [
                    'mask-editor' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-user',
                                'title' => 'Посмотреть в редакторе маски']);
                        $url = 'https://84.201.129.65:8080/HRRMaskEditor/MaskEditor.php?landmark_id='.
                            $model->landmark->id . '&detection_result_id=' . $model->id;
                        return Html::a($icon, $url);
                    },
                    'detection-file-download' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-floppy-save',
                                'title' => 'Скачать результаты определения признаков']);
                        $url = ['/analysis-result/detection-file-download/' . $model->id];
                        return Html::a($icon, $url);
                    },
                    'facts-file-download' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-level-up', 'title' => 'Скачать факты']);
                        $url = ['/analysis-result/facts-download/' . $model->id];
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