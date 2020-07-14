<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Итоговые заключения по видеоинтервью';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="final-video-interview-conclusion-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'conclusion:ntext',
            [
                'attribute'=>'finalResult',
                'label' => 'ID видеоинтервью',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->finalResult->video_interview_id;
                },
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>