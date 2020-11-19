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
            'video_interview_id',
            [
                'attribute'=>'status',
                'label' => 'Статус',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->status !== null) ? $data->getStatus() : null;
                },
            ],
            [
                'attribute'=>'all_runtime',
                'label' => 'Время выполнения анализа видео-интервью',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->all_runtime !== null) ? $data->getAllRuntime() : null;
                },
            ],
            [
                'attribute'=>'emotion_interpretation_runtime',
                'label' => 'Время формирования итогового заключения',
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