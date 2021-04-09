<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Базы знаний';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="knowledge-base-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <h4><b>База знаний</b> — это формализованное представление знаний в виде модели продукций для решения различных
        задач, в частности, в контексте разрабатываемой системы — задачи определения аномалий в эмоциональном
        состоянии респондента.
    </h4>

    <p>
        <?= Html::a('Загрузить', ['upload'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'name',
            [
                'attribute'=>'knowledge_base_file_name',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->knowledge_base_file_name != '') ? $data->knowledge_base_file_name : null;
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
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>