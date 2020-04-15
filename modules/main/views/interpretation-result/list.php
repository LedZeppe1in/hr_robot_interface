<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Результаты интерпретации признаков';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="interpretation-result-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'attribute'=>'landmark_id',
                'label' => 'Цифровая маска',
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
                'template' => '{view} {interpretation-file-download} {delete}',
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