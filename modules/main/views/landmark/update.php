<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\Landmark */
/* @var $questions app\modules\main\controllers\LandmarkController */

$this->title = ($model->landmark_file_name != '') ? 'Обновить: ' . $model->landmark_file_name : 'не загружено';
$this->params['breadcrumbs'][] = ['label' => 'Цифровые маски', 'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => ($model->landmark_file_name != '') ? $model->landmark_file_name :
    'не загружено', 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Обновить';
?>

<div class="landmark-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'questions' => $questions,
    ]) ?>

</div>