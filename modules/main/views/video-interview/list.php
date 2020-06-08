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
                'template' => '{view} {update} {get-landmarks} {delete}',
                'buttons' => [
                    'get-landmarks' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-save-file',
                                'title' => 'Сформировать цифровую маску']);
                        $url = ['/video-interview/get-landmarks/' . $model->id];
                        return Html::a($icon, $url);
                    },
                ],
            ],
        ],
    ]); ?>

</div>