<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Интервью респондентов';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="respondent-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Создать', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'name',
            'main_respondent_id',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>