<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\AdvancedLandmark */

$this->title = 'Загрузка файла';
$this->params['breadcrumbs'][] = ['label' => 'Модифицированные файлы с лицевыми точками', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="advanced-landmark-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>