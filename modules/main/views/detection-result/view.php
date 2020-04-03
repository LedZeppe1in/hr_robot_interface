<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\AnalysisResult */
/* @var $eyeFeatures app\modules\main\controllers\DetectionResultController */
/* @var $mouthFeatures app\modules\main\controllers\DetectionResultController */
/* @var $browFeatures app\modules\main\controllers\DetectionResultController */
/* @var $eyebrowFeatures app\modules\main\controllers\DetectionResultController */
/* @var $noseFeatures app\modules\main\controllers\DetectionResultController */
/* @var $facts app\modules\main\controllers\DetectionResultController */

$this->title = $model->landmark->landmark_file_name;
$this->params['breadcrumbs'][] = ['label' => 'Результаты определения признаков', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="analysis-result-view">

    <h1>Результат для: <?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Интерпретировать признаки', '#', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите удалить этот элемент?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'created_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            [
                'attribute' => 'updated_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            [
                'attribute' => 'landmark_id',
                'label' => 'ID цифровой маски',
                'value' => $model->landmark->id,
            ],
            [
                'attribute' => 'landmark_id',
                'label' => 'Название файла с лицевыми точками',
                'value' => $model->landmark->landmark_file_name,
            ],
            [
                'label' => 'Описание',
                'value' => ($model->description != '') ? $model->description : null,
                'format' => 'raw'
            ],
            [
                'label' => 'Файл с результатами определения признаков',
                'value' => ($model->detection_result_file_name != '') ? Html::a('скачать',
                    ['file-download', 'id' => $model->id], ['target' => '_blank']) : null,
                'format' => 'raw'
            ],
            [
                'label' => 'Файл с набором фактов',
                'value' => ($model->facts_file_name != '') ? Html::a('скачать',
                    ['facts-download', 'id' => $model->id], ['target' => '_blank']) : null,
                'format' => 'raw'
            ],
        ],
    ]) ?>

    <?php echo Tabs::widget([
        'items' => [
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
            ],
            [
                'label' => 'Признаки для лба',
                'content' => $this->render('_brow_features', [
                    'browFeatures' => $browFeatures
                ]),
            ],
            [
                'label' => 'Признаки для бровей',
                'content' => $this->render('_eyebrow_features', [
                    'eyebrowFeatures' => $eyebrowFeatures
                ]),
            ],
            [
                'label' => 'Признаки для носа',
                'content' => $this->render('_nose_features', [
                    'noseFeatures' => $noseFeatures
                ]),
            ],
            [
                'label' => 'Факты',
                'content' => $this->render('_facts', [
                    'facts' => json_decode($facts, true)
                ]),
            ]
        ]
    ]); ?>
</div>