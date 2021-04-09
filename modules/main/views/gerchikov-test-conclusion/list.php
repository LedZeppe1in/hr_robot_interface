<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Итоговые заключения по тесту мотивации к труду';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="final-gerchikov-test-conclusion-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'attribute'=>'finalResult',
                'label' => 'ID видеоинтервью',
                'format' => 'raw',
                'value' => function($data) {
                    return Html::a($data->finalResult->video_interview_id,
                        ['video-interview/view', 'id' => $data->finalResult->video_interview_id]);
                },
            ],
            [
                'attribute'=>'accept_test',
                'label' => 'Решение о принятии',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->getAcceptTestDecisionValue();
                },
            ],
            'accept_level',
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['class' => 'action-column'],
                'template' => '{view} {delete}',
            ],
        ],
    ]); ?>

</div>