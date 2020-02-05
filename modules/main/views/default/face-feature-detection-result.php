<?php

/* @var $this yii\web\View */
/* @var $eyeFeatures app\modules\main\controllers\DefaultController */
/* @var $mouthFeatures app\modules\main\controllers\DefaultController */

use yii\helpers\Html;
use yii\bootstrap\Tabs;

$this->title = 'Результаты';

$this->params['breadcrumbs'][] = ['label' => 'Определение признаков', 'url' => ['face-feature-detection']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="main-default-face-feature-detection-result">
    <h1><?= Html::encode($this->title) ?></h1>
    <div class="container">
        <?php echo Tabs::widget([
            'items' => [
                [
                    'label' => 'Признаки для лба',
                    'content' => $this->render('_forehead_features'),
                    'active' => true
                ],
                [
                    'label' => 'Признаки для глаз',
                    'content' => $this->render('_eye_features', [
                        'eyeFeatures' => $eyeFeatures
                    ]),
                ],
                [
                    'label' => 'Признаки для рта',
                    'content' => $this->render('_mouth_features', [
                        'mouthFeatures' => $mouthFeatures
                    ]),
                ]
            ]
        ]); ?>
    </div>
</div>