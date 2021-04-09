<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\main\models\QuestionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $videoProcessingModuleSettingForm app\modules\main\models\VideoProcessingModuleSettingForm */

$this->title = 'Видео на вопросы';
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_modal_form_video_processing_ivan_module_setting', [
    'videoProcessingModuleSettingForm' => $videoProcessingModuleSettingForm
]); ?>

<?= $this->render('_modal_form_video_processing_andrey_module_setting', [
    'videoProcessingModuleSettingForm' => $videoProcessingModuleSettingForm
]); ?>

<script type="text/javascript">
    let actionName = "";
    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Обработка нажатия кнопки-иконки формирования цифровой маски модулем Ивана
        $(".get-ivan-landmarks").click(function(e) {
            // Форма параметров настроек запуска модуля обработки видео
            var form = document.getElementById("get-ivan-landmark-form");
            // Формирование названия URL-адреса для запроса
            if (actionName === "")
                actionName = form.action;
            form.action = actionName + "/" + this.id;
            // Открытие модального окна
            $("#formIvanLandmarkModalForm").modal("show");
        });
        // Обработка нажатия кнопки подтверждения формирования цифровой маски модулем Ивана
        $("#form-ivan-landmark-button").click(function(e) {
            // Скрывание модального окна
            $("#formIvanLandmarkModalForm").modal("hide");
        });

        // Обработка нажатия кнопки-иконки формирования цифровой маски модулем Андрея
        $(".get-andrey-landmarks").click(function(e) {
            // Форма параметров настроек запуска модуля обработки видео
            var form = document.getElementById("get-andrey-landmark-form");
            // Формирование названия URL-адреса для запроса
            if (actionName === "")
                actionName = form.action;
            form.action = actionName + "/" + this.id;
            // Открытие модального окна
            $("#formAndreyLandmarkModalForm").modal("show");
        });
        // Обработка нажатия кнопки подтверждения формирования цифровой маски модулем Андрея
        $("#form-andrey-landmark-button").click(function(e) {
            // Скрывание модального окна
            $("#formAndreyLandmarkModalForm").modal("hide");
        });
    });
</script>

<div class="video-interview-question-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            [
                'attribute'=>'video_interview_id',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->video_interview_id !== null) ? Html::a($data->video_interview_id,
                        ['video-interview/view', 'id' => $data->video_interview_id]) : null;
                },
            ],
            [
                'attribute'=>'test_question_id',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->test_question_id != '') ? Html::a($data->test_question_id,
                        ['test-question/view', 'id' => $data->test_question_id]) : null;
                },
            ],
            [
                'attribute' => 'testQuestionText',
                'label' => 'Текст вопроса',
                'value' => function($data) {
                    return ($data->test_question_id !== null) ? $data->testQuestion->text : null;
                },
            ],
            'video_file_name',
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['class' => 'action-column'],
                'template' => '{view} {get-ivan-landmarks} {get-recognized-speech} {get-andrey-landmarks} {delete}',
                'buttons' => [
                    'get-ivan-landmarks' => function ($url, $model, $key) {
                        $icon = ($model->video_file_name != '') ? Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-file get-ivan-landmarks', 'id' => $model->id,
                                'title' => 'Сформировать цифровую маску модулем Ивана']) : false;
                        $url = '#';
                        return Html::a($icon, $url);
                    },
                    'get-recognized-speech' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '', ['class' => 'glyphicon glyphicon-comment',
                            'title' => 'Распознать речь в видео']);
                        $url = ['/question/get-recognized-speech/' . $model->id];
                        return Html::a($icon, $url);
                    },
                    'get-andrey-landmarks' => function ($url, $model, $key) {
                        $icon = ($model->video_file_name != '') ? Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-save-file get-andrey-landmarks', 'id' => $model->id,
                                'title' => 'Сформировать цифровую маску модулем Андрея']) : false;
                        $url = '#';
                        return Html::a($icon, $url);
                    },
                ],
            ],
        ],
    ]); ?>

</div>