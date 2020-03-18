<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Видеоинтервью';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="video-interview-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Загрузить', ['upload'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'name',
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
                'template' => '{view} {detection} {delete}',
                'buttons' => [
                    'detection' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-save-file', 'title' => 'Определение признаков']);
                        $url = ['/analysis-result/detection/' . $model->id];
                        return Html::a($icon, $url);
                    },
                ],
            ],
        ],
    ]); ?>

</div>