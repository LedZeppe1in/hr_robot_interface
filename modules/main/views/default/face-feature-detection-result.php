<?php

/* @var $this yii\web\View */
/* @var $eyeFeatures app\modules\main\controllers\DefaultController */
/* @var $mouthFeatures app\modules\main\controllers\DefaultController */

use yii\helpers\Html;

$this->title = 'Результаты';

$this->params['breadcrumbs'][] = ['label' => 'Определение признаков', 'url' => ['face-feature-detection']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="main-default-face-feature-detection-result">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="container">
        <div class="row">
            <h3>Признаки для глаз:</h3>
            <div class="col-lg-12">
                <?php echo($eyeFeatures); ?>
            </div>
        </div>
        <div class="row">
            <h3>Признаки для рта:</h3>
            <div class="col-lg-12">
                <?php echo($mouthFeatures); ?>
            </div>
        </div>
    </div>
</div>