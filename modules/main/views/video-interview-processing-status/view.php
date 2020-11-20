<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\VideoInterviewProcessingStatus */

$this->title = 'Состояние обработки видеоинтервью №' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Состояния обработки видеоинтервью', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="video-interview-processing-status-view">

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
            [
                'attribute' => 'video_interview_id',
                'format' => 'raw',
                'value' => Html::a($model->video_interview_id,
                    ['video-interview/view', 'id' => $model->video_interview_id]),
            ],
            [
                'attribute' => 'video_interview_id',
                'label' => 'Название файла с полным видеоинтервью',
                'value' => $model->video_interview_id ? $model->videoInterview->video_file_name : null,
            ],
            [
                'attribute' => 'status',
                'value' => $model->getStatus(),
            ],
            [
                'attribute' => 'all_runtime',
                'value' => $model->all_runtime ? $model->getAllRuntime() : null,
            ],
            [
                'attribute' => 'emotion_interpretation_runtime',
                'value' => $model->emotion_interpretation_runtime ? $model->getEmotionInterpretationRuntime() : null,
            ],
        ],
    ]) ?>

</div>