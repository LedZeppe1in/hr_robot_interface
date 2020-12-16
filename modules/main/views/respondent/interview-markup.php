<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\Respondent */

$this->title = 'Разметка интервью для: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Интервью респондентов', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="interview-markup">

    <h1><?= Html::encode($this->title) ?></h1>

</div>