<?php

use yii\helpers\Html;
use yii\bootstrap\Tabs;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\AnalysisResult */
/* @var $interpretationResult app\modules\main\controllers\InterpretationResultController */
/* @var $ruleDescription app\modules\main\controllers\InterpretationResultController */
/* @var $factTemplateDescription app\modules\main\controllers\InterpretationResultController */

$this->title = $model->landmark->landmark_file_name;
$this->params['breadcrumbs'][] = ['label' => 'Результаты интерпретации признаков', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="interpretation-result-view">

    <h1>Результат для: <?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Обновить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
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
                'attribute' => 'landmark',
                'label' => 'ID видеоинтервью',
                'format' => 'raw',
                'value' => Html::a($model->landmark->video_interview_id,
                    ['video-interview/view', 'id' => $model->landmark->video_interview_id]),
            ],
            [
                'attribute' => 'landmark',
                'label' => 'ID видео на вопрос',
                'format' => 'raw',
                'value' => ($model->landmark->question_id != '') ? Html::a($model->landmark->question_id,
                    ['question/view', 'id' => $model->landmark->question_id]) : null,
            ],
            [
                'attribute' => 'landmark_id',
                'label' => 'ID цифровой маски',
                'format' => 'raw',
                'value' => Html::a($model->landmark_id, ['landmark/view', 'id' => $model->landmark_id]),
            ],
            [
                'attribute' => 'landmark_id',
                'label' => 'Название файла с лицевыми точками',
                'value' => $model->landmark->landmark_file_name,
            ],
            [
                'label' => 'Описание',
                'format' => 'raw',
                'value' => ($model->description != '') ? $model->description : null,
            ],
            [
                'label' => 'Файл с результатами интерпретации признаков',
                'format' => 'raw',
                'value' => ($model->interpretation_result_file_name != '') ? Html::a('скачать',
                    ['file-download', 'id' => $model->id], ['target' => '_blank']) : null,
            ],
        ],
    ]) ?>

    <?php echo Tabs::widget([
        'items' => [
            [
                'label' => 'Результаты интерпретации по кадрам',
                'content' => $this->render('_interpretation_results', [
                    'interpretationResult' => $interpretationResult
                ]),
            ],
            [
                'label' => 'Описание правил',
                'content' => $this->render('_rule_description', [
                    'ruleDescription' => $ruleDescription
                ]),
            ],
            [
                'label' => 'Описание шаблонов фактов',
                'content' => $this->render('_fact_template_description', [
                    'factTemplateDescription' => $factTemplateDescription
                ]),
            ]
        ]
    ]); ?>
</div>