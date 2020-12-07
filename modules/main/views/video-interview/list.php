<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $videoProcessingModuleSettingForm app\modules\main\models\VideoProcessingModuleSettingForm */

$this->title = 'Видеоинтервью';
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_modal_form_video_processing_module_setting', [
    'videoProcessingModuleSettingForm' => $videoProcessingModuleSettingForm
]); ?>

<script type="text/javascript">
    let actionName = "";
    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Обработка нажатия кнопки-иконки формирования цифровой маски модулем Ивана
        $(".get-ivan-landmarks").click(function(e) {
            // Форма параметров настроек запуска модуля обработки видео
            var form = document.getElementById("get-landmark-form");
            // Формирование названия URL-адреса для запроса
            if (actionName === "")
                actionName = form.action;
            form.action = actionName + "/" + this.id;
            // Открытие модального окна
            $("#formLandmarkModalForm").modal("show");
        });
        // Обработка нажатия кнопки подтверждения формирования цифровой маски модулем Ивана
        $("#form-landmark-button").click(function(e) {
            // Скрывание модального окна
            $("#formLandmarkModalForm").modal("hide");
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
        'columns' => [
            'id',
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
                'label' => 'Респондент',
                'format' => 'raw',
                'value' => function($data) {
                    return $data->respondent->name;
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['class' => 'action-column'],
                'template' => '{view} {update} {get-ivan-landmarks} {get-andrey-landmarks} {delete}',
                'buttons' => [
                    'get-ivan-landmarks' => function ($url, $model, $key) {
                        $icon = ($model->video_file_name != '') ? Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-file get-ivan-landmarks', 'id' => $model->id,
                                'title' => 'Сформировать цифровую маску модулем Ивана']) : false;
                        $url = '#';
                        return Html::a($icon, $url);
                    },
                    'get-andrey-landmarks' => function ($url, $model, $key) {
                        $icon = ($model->video_file_name != '') ? Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-save-file',
                                'title' => 'Сформировать цифровую маску модулем Андрея']) : false;
                        $url = ($model->video_file_name != '') ?
                            ['/video-interview/get-andrey-landmarks/' . $model->id] : false;
                        return Html::a($icon, $url);
                    },
                ],
            ],
        ],
    ]); ?>

</div>