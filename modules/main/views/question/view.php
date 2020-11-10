<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\Question */

$this->title = 'Вопрос видеоинтервью №' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Вопросы видеоинтервью', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="video-interview-question-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
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
            'test_question_id',
            [
                'attribute' => 'test_question_id',
                'label' => 'Текст вопроса опроса',
                'value' => $model->test_question_id ? $model->testQuestion->text : null,
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