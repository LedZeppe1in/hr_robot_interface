<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\main\models\VideoInterviewSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $videoProcessingModuleSettingForm app\modules\main\models\VideoProcessingModuleSettingForm */

$this->title = 'Видеоинтервью';
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

<div class="video-interview-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Загрузить', ['upload'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            [
                'attribute' => 'created_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            [
                'attribute'=>'video_file_name',
                'label' => 'Видеоинтервью',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->video_file_name != '') ? $data->video_file_name : null;
                },
            ],
            [
                'attribute'=>'description',
                'label' => 'Описание',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->description != '') ? $data->description : null;
                },
            ],
            [
                'attribute'=>'respondent_id',
                'label' => 'Код интервью',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->respondent->name;
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['class' => 'action-column'],
                'template' => '{view} {update} {get-ivan-landmarks} {get-recognized-speech} {get-andrey-landmarks} ' .
                    '{run-analysis} {run-features-detection} {run-features-interpretation} {delete} ' .
                    '{delete-all-analysis-results}',
                'buttons' => [
                    'get-ivan-landmarks' => function ($url, $model, $key) {
                        $icon = ($model->video_file_name != '') ? Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-file get-ivan-landmarks', 'id' => $model->id,
                                'title' => 'Сформировать цифровую маску модулем Ивана']) : false;
                        $url = '#';
                        return Html::a($icon, $url);
                    },
                    'get-recognized-speech' => function ($url, $model, $key) {
                        $icon = ($model->video_file_name != '') ? Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-comment', 'title' => 'Распознать речь в видео']) : false;
                        $url = ($model->video_file_name != '') ?
                            ['/video-interview/get-recognized-speech/' . $model->id] : false;
                        return Html::a($icon, $url);
                    },
                    'get-andrey-landmarks' => function ($url, $model, $key) {
                        $icon = ($model->video_file_name != '') ? Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-save-file get-andrey-landmarks', 'id' => $model->id,
                                'title' => 'Сформировать цифровую маску модулем Андрея']) : false;
                        $url = '#';
                        return Html::a($icon, $url);
                    },
                    'run-analysis' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '', ['class' => 'glyphicon glyphicon-play-circle',
                            'title' => 'Запуск анализа видеоинтервью по всем вопросам (МОВ Ивана + МОВ Андрея)']);
                        $url = ['/video-interview/run-analysis/' . $model->id];
                        return Html::a($icon, $url);
                    },
                    'run-features-detection' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '', ['class' => 'glyphicon glyphicon-cog',
                            'title' => 'Запуск МОП по всем сформированным цифровым маскам видеоинтервью']);
                        $url = ['/video-interview/run-features-detection/' . $model->id];
                        return Html::a($icon, $url);
                    },
                    'run-features-interpretation' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '', ['class' => 'glyphicon glyphicon-certificate',
                            'title' => 'Запуск МИП (первый + второй уровень) по всем результатам МОП']);
                        $url = ['/video-interview/run-features-interpretation/' . $model->id];
                        return Html::a($icon, $url);
                    },
                    'delete-all-analysis-results' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '', ['class' => 'glyphicon glyphicon-remove',
                            'title' => 'Удалить все результаты МОП и МИП для данного видеоинтервью']);
                        $url = ['/video-interview/delete-all-analysis-results/' . $model->id];
                        $options = [
                            'data' => [
                                'confirm' => 'Вы уверены, что хотите удалить все результаты МОП и МИП для данному видеоинтеврью?',
                                'method' => 'post',
                            ]
                        ];
                        return Html::a($icon, $url, $options);
                    },
                ],
            ],
        ],
    ]); ?>

</div>