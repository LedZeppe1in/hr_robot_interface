<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\KnowledgeBase */

$this->title = 'Загрузка базы знаний';
$this->params['breadcrumbs'][] = ['label' => 'Базы знаний', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="knowledge-base-upload">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>