<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\AnalysisResult */
/* @var $eyeFeatures app\modules\main\controllers\DetectionResultController */
/* @var $mouthFeatures app\modules\main\controllers\DetectionResultController */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Результаты определения признаков', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="analysis-result-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Скачать результаты определения признаков',
            ['file-download', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
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
                'attribute' => 'video_interview_id',
                'label' => 'Видеоинтервью',
                'value' => $model->videoInterview->name,
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
            ]
        ]
    ]); ?>
</div>