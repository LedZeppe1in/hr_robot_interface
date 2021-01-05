<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\ButtonDropdown;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\Question */
/* @var $videoProcessingModuleSettingForm app\modules\main\models\VideoProcessingModuleSettingForm */

$this->title = 'Видео на вопрос №' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Видео на вопросы', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
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

<div class="video-interview-question-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if ($model->video_file_name != '') {
            echo ButtonDropdown::widget([
                'label' => 'Сформировать цифровую маску',
                'dropdown' => [
                    'items' => [
                        ['label' => 'Модулем Ивана', 'url' => '#', 'options' => ['class' => 'get-ivan-landmarks',
                            'id' => $model->id]],
                        ['label' => 'Модулем Андрея', 'url' => '#', 'options' => ['class' => 'get-andrey-landmarks',
                            'id' => $model->id]],
                    ],
                ],
                'options' => ['class' => 'btn btn-success']
            ]);
        } ?>
        <?= Html::a('Распознать речь в видео', ['get-recognized-speech', 'id' => $model->id],
            ['class' => 'btn btn-success']) ?>
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