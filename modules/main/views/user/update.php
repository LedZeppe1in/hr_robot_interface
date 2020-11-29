<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\User */

$this->title = 'Обновить профиль';
$this->params['breadcrumbs'][] = ['label' => 'Профиль', 'url' => ['profile', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Обновить профиль: ' . $model->username;
?>

<div class="user-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>