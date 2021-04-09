<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\main\models\AnalysisResultSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Результаты интерпретации признаков';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="interpretation-result-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
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
                'attribute'=>'landmarkName',
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
                'template' => '{view} {update} {interpretation-file-download} {delete}',
                'buttons' => [
                    'interpretation-file-download' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-floppy-save',
                                'title' => 'Скачать результаты интерпретации признаков']);
                        $url = ['/interpretation-result/file-download/' . $model->id];
                        return Html::a($icon, $url);
                    },
                ],
            ],
        ],
    ]); ?>

</div>