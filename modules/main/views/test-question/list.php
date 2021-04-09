<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\main\models\TestQuestion;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\main\models\TestQuestionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Вопросы';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="test-question-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Создать', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
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
                'filter' => TestQuestion::getTypes(),
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