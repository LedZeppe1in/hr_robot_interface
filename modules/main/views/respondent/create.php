<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\Respondent */

$this->title = 'Создать';
$this->params['breadcrumbs'][] = ['label' => 'Респонденты', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="respondent-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>