<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Вопросы видеоинтервью';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="video-interview-question-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'test_question_id',
            [
                'attribute' => 'test_question_id',
                'label' => 'Текст вопроса опроса',
                'value' => function($data) {
                    return ($data->test_question_id !== null) ? $data->testQuestion->text : null;
                },
            ],
            'video_interview_id',
            [
                'attribute' => 'video_interview_id',
                'label' => 'Название файла с полным видеоинтервью',
                'value' => function($data) {
                    return ($data->video_interview_id !== null) ? $data->videoInterview->video_file_name : null;
                },
            ],
            'video_file_name',
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['class' => 'action-column'],
                'template' => '{view} {video-file-download} {delete}',
                'buttons' => [
                    'video-file-download' => function ($url, $model, $key) {
                        $icon = ($model->video_file_name != '') ? Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-save-file',
                                'title' => 'Сформировать цифровую маску']) : false;
                        $url = ($model->video_file_name != '') ? ['/question/video-file-download/' . $model->id] :
                            false;
                        return Html::a($icon, $url);
                    },
                ],
            ],
        ],
    ]); ?>

</div>