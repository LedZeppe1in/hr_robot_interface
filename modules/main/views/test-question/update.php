<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\TestQuestion */

$this->title = 'Обновить вопрос №' . $model->id . ':';
$this->params['breadcrumbs'][] = ['label' => 'Вопросы', 'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => 'Вопрос №' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Обновить';
?>

<div class="test-question-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>