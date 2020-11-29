<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\User */

$this->title = 'Профиль';
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="user-profile">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Обновить учетные данные', ['update', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Поменять пароль', ['change-password', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'username',
            [
                'attribute'=>'full_name',
                'value' => $model->full_name != '' ? $model->full_name : null,
            ],
            [
                'attribute'=>'email',
                'value' => $model->full_name != '' ? $model->email : null,
            ],
            [
                'attribute'=>'role',
                'value' => $model->getRoleName()
            ],
            [
                'attribute'=>'status',
                'value' => $model->getStatusName()
            ],
            [
                'attribute' => 'created_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            [
                'attribute' => 'updated_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
        ],
    ]) ?>

</div>