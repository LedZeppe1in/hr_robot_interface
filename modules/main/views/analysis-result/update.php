<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\AnalysisResult */

$this->title = 'Обновить описание для результатов анализа №' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Итоговые результаты анализа', 'url' => ['list']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Обновить';
?>
<div class="analysis-result-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>