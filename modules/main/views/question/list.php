<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Видео на вопросы';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="video-interview-question-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'attribute'=>'test_question_id',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->test_question_id != '') ? Html::a($data->test_question_id,
                        ['test-question/view', 'id' => $data->test_question_id]) : null;
                },
            ],
            [
                'attribute' => 'test_question_id',
                'label' => 'Текст вопроса',
                'value' => function($data) {
                    return ($data->test_question_id !== null) ? $data->testQuestion->text : null;
                },
            ],
            [
                'attribute'=>'video_interview_id',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->video_interview_id !== null) ? Html::a($data->video_interview_id,
                        ['video-interview/view', 'id' => $data->video_interview_id]) : null;
                },
            ],
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
                'template' => '{view} {get-ivan-landmarks} {get-andrey-landmarks} {delete}',
                'buttons' => [
                    'get-ivan-landmarks' => function ($url, $model, $key) {
                        $icon = ($model->video_file_name != '') ? Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-file',
                                'title' => 'Сформировать цифровую маску модулем Ивана']) : false;
                        $url = ($model->video_file_name != '') ? ['/question/get-ivan-landmarks/' . $model->id] :
                            false;
                        return Html::a($icon, $url);
                    },
                    'get-andrey-landmarks' => function ($url, $model, $key) {
                        $icon = ($model->video_file_name != '') ? Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-save-file',
                                'title' => 'Сформировать цифровую маску модулем Андрея']) : false;
                        $url = ($model->video_file_name != '') ? ['/question/get-andrey-landmarks/' . $model->id] :
                            false;
                        return Html::a($icon, $url);
                    },
                ],
            ],
        ],
    ]); ?>

</div>