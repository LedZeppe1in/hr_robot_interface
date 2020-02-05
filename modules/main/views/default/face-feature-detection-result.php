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
            <h3>Признаки для лба:</h3>
            <div class="col-lg-12">
                <div class="well">
                    <?= Html::a('Таблица признаков', 'http://84.201.129.65:9999/Preprocessor/Main.php') ?>
                </div>
            </div>
            <h3>Признаки для глаз:</h3>
            <div class="col-lg-12">
                <?php
                    echo '<pre>';
                    print_r($eyeFeatures);
                    echo '</pre>';
                ?>
            </div>
        </div>
        <div class="row">
            <h3>Признаки для рта:</h3>
            <div class="col-lg-12">
                <?php
                    echo '<pre>';
                    print_r($mouthFeatures);
                    echo '</pre>';
                ?>
            </div>
        </div>
    </div>
</div>