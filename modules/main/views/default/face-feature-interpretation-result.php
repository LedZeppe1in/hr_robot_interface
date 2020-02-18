<?php

/* @var $this yii\web\View */
/* @var $faceData app\modules\main\controllers\DefaultController */
/* @var $emotionDetector app\modules\main\controllers\DefaultController */

use yii\helpers\Html;

$this->title = 'Результаты';

$this->params['breadcrumbs'][] = ['label' => 'Интерпретация признаков', 'url' => ['face-feature-interpretation']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="main-default-face-feature-detection-result">
    <h1><?= Html::encode($this->title) ?></h1>
    <h4>Входной кадр с признаками:</h4>
    <div class="row">
        <div class="col-md-12">
            <?php
                echo '<pre>';
                print_r($faceData);
                echo '</pre>';
            ?>
        </div>
    </div>
    <h4>Интерпретация входного кадра с признаками:</h4>
    <div class="row">
        <div class="col-md-12">
            <div class="well">
                <?php echo '<b>Эмоция:</b> ' . $emotionDetector->name ?><br/>
                <?php echo '<b>Активированное правило:</b> ' . $emotionDetector->firedRule ?>
            </div>
        </div>
    </div>
</div>