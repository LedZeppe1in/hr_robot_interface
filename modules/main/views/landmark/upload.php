<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\Landmark */
/* @var $questions app\modules\main\controllers\LandmarkController */

$this->title = 'Загрузка файла';
$this->params['breadcrumbs'][] = ['label' => 'Цифровые маски', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="landmark-upload">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'questions' => $questions,
    ]) ?>

</div>