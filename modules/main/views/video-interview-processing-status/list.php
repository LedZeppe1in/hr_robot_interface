<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Состояния обработки видеоинтервью';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="video-interview-processing-status-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'attribute'=>'video_interview_id',
                'format' => 'raw',
                'value' => function($data) {
                    return Html::a($data->video_interview_id,
                        ['video-interview/view', 'id' => $data->video_interview_id]);
                },
            ],
            [
                'attribute'=>'status',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->status !== null) ? $data->getStatus() : null;
                },
            ],
            [
                'attribute'=>'all_runtime',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->all_runtime !== null) ? $data->getAllRuntime() : null;
                },
            ],
            [
                'attribute'=>'emotion_interpretation_runtime',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->emotion_interpretation_runtime !== null) ? $data->getEmotionInterpretationRuntime() :
                        null;
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['class' => 'action-column'],
                'template' => '{view} {delete}',
            ],
        ],
    ]); ?>

</div>