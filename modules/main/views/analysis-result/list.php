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
                    return $data->videoInterview->name;
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