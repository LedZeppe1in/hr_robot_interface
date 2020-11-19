<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\QuestionProcessingStatus */

$this->title = 'Состояние обработки видео ответа на вопрос: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Состояния обработки видео ответов по вопросам', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="question-processing-status-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
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
            'video_interview_processing_status_id',
            'question_id',
            [
                'attribute' => 'question_id',
                'label' => 'Название файла с видео ответом на вопрос',
                'value' => $model->question_id ? $model->question->video_file_name : null,
            ],
            [
                'attribute' => 'status',
                'value' => $model->getStatus(),
            ],
            [
                'attribute' => 'ivan_video_analysis_runtime',
                'value' => $model->getIvanVideoAnalysisRuntime(),
            ],
            [
                'attribute' => 'andrey_video_analysis_runtime',
                'value' => $model->getAndreyVideoAnalysisRuntime(),
            ],
            [
                'attribute' => 'feature_detection_runtime',
                'value' => $model->getFeatureDetectionRuntime(),
            ],
            [
                'attribute' => 'feature_interpretation_runtime',
                'value' => $model->getFeatureInterpretationRuntime(),
            ],
        ],
    ]) ?>

</div>