<?php

use app\modules\main\models\Landmark;
use yii\bootstrap\ButtonDropdown;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\Question */

$this->title = 'Видео на вопрос №' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Видео на вопросы', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="video-interview-question-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if ($model->video_file_name != '') {
            echo ButtonDropdown::widget([
                'label' => 'Сформировать цифровую маску',
                'dropdown' => [
                    'items' => [
                        ['label' => 'Модулем Ивана', 'url' => '/question/get-ivan-landmarks/' . $model->id],
                        ['label' => 'Модулем Андрея', 'url' => '/question/get-andrey-landmarks/' . $model->id],
                    ],
                ],
                'options' => ['class' => 'btn btn-success']
            ]);
        } ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите удалить этот вопрос?',
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
                'attribute' => 'test_question_id',
                'format' => 'raw',
                'value' => ($model->test_question_id !== null) ? Html::a($model->test_question_id,
                    ['test-question/view', 'id' => $model->test_question_id]) : null,
            ],
            [
                'attribute' => 'test_question_id',
                'label' => 'Текст вопроса',
                'value' => $model->test_question_id ? $model->testQuestion->text : null,
            ],
            [
                'attribute' => 'video_interview_id',
                'format' => 'raw',
                'value' => $model->video_interview_id ? Html::a($model->video_interview_id,
                    ['video-interview/view', 'id' => $model->video_interview_id]) : null,
            ],
            [
                'attribute' => 'video_interview_id',
                'label' => 'Название файла с полным видеоинтервью',
                'value' => $model->video_interview_id ? $model->videoInterview->video_file_name : null,
            ],
            [
                'label' => 'Описание',
                'value' => ($model->description != '') ? $model->description : null,
                'format' => 'raw'
            ],
            [
                'label' => 'Название файла видео ответа на вопрос',
                'value' => ($model->video_file_name != '') ? $model->video_file_name : null,
                'format' => 'raw'
            ],
            [
                'label' => 'Файл c видео ответом на вопрос',
                'value' => ($model->video_file_name != '') ? Html::a('скачать',
                    ['/question/video-file-download/' . $model->id], ['target' => '_blank']) : null,
                'format' => 'raw'
            ],
        ],
    ]) ?>

</div>