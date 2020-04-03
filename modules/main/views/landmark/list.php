<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Цифровые маски';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="landmark-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Загрузить', ['upload'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'attribute'=>'landmark_file_name',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->landmark_file_name != '') ? $data->landmark_file_name : null;
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
            'video_interview_id',
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['class' => 'action-column'],
                'template' => '{view} {detection} {delete}',
                'buttons' => [
                    'detection' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-save-file', 'title' => 'Определение признаков']);
                        $url = ['/analysis-result/detection/' . $model->id];
                        return ($model->landmark_file_name != '') ? Html::a($icon, $url) : null;
                    },
                ],
            ],
        ],
    ]); ?>

</div>