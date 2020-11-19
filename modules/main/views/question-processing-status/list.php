<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Состояния обработки видео ответов по вопросам';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="question-processing-status-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'video_interview_processing_status_id',
            'question_id',
            [
                'attribute'=>'status',
                'label' => 'Статус',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->status !== null) ? $data->getStatus() : null;
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