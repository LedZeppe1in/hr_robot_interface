<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Видеоинтервью';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="video-interview-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Загрузить', ['upload'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'attribute'=>'video_file_name',
                'label' => 'Видеоинтервью',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->video_file_name != '') ? $data->video_file_name : null;
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
                'attribute'=>'respondent_id',
                'label' => 'Респондент',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->respondent->name;
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