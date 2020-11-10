<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Вопросы опроса';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="test-question-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Создать', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'name',
            'text',
            [
                'attribute'=>'type',
                'label' => 'Тип',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->type !== null) ? $data->getType() : null;
                },
            ],
            [
                'attribute'=>'time',
                'label' => 'Время',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->time !== null) ? $data->getTime() : null;
                },
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>