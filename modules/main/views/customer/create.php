<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\Customer */

$this->title = 'Создать';
$this->params['breadcrumbs'][] = ['label' => 'Заказчики', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="customer-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>