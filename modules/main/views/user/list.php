<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Создать', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'username',
            [
                'attribute'=>'role',
                'value' => function($data) {
                    return ($data->role !== null) ? $data->getRoleName() : null;
                },
            ],
            [
                'attribute'=>'status',
                'value' => function($data) {
                    return ($data->status !== null) ? $data->getStatusName() : null;
                },
            ],
            [
                'attribute'=>'full_name',
                'value' => function($data) {
                    return ($data->full_name != '') ? $data->full_name : null;
                },
            ],
            [
                'attribute'=>'email',
                'value' => function($data) {
                    return ($data->email != '') ? $data->email : null;
                },
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>