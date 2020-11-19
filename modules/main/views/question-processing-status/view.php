<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\QuestionProcessingStatus */
/* @var $moduleMessages app\modules\main\models\ModuleMessage */

$this->title = 'Состояние обработки видео ответа на вопрос: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Состояния обработки видео ответов по вопросам', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="question-processing-status-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
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
                'value' => $model->ivan_video_analysis_runtime ? $model->getIvanVideoAnalysisRuntime() : null,
            ],
            [
                'attribute' => 'andrey_video_analysis_runtime',
                'value' => $model->andrey_video_analysis_runtime ? $model->getAndreyVideoAnalysisRuntime() : null,
            ],
            [
                'attribute' => 'feature_detection_runtime',
                'value' => $model->feature_detection_runtime ? $model->getFeatureDetectionRuntime() : null,
            ],
            [
                'attribute' => 'feature_interpretation_runtime',
                'value' => $model->feature_interpretation_runtime ? $model->getFeatureInterpretationRuntime() : null,
            ],
        ],
    ]) ?>

    <?php if(!empty($moduleMessages)): ?>
        <h3>Сообщения:</h3>
    <?php endif; ?>

    <?php foreach ($moduleMessages as $moduleMessage): ?>
        <?= DetailView::widget([
            'model' => $moduleMessage,
            'attributes' => [
                'message',
                [
                    'attribute' => 'module_name',
                    'value' => $moduleMessage->getModuleName(),
                ],
            ],
        ]) ?>
    <?php endforeach; ?>
</div>