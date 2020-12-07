<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\ButtonDropdown;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\VideoInterview */
/* @var $videoProcessingModuleSettingForm app\modules\main\models\VideoProcessingModuleSettingForm */

$this->title = ($model->video_file_name != '') ? $model->video_file_name : 'не загружено';
$this->params['breadcrumbs'][] = ['label' => 'Видеоинтервью', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?= $this->render('_modal_form_video_processing_module_setting', [
    'model' => $model,
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

<div class="video-interview-view">

    <h1>Видеоинтервью: <?= Html::encode($this->title) ?></h1>

    <p>
        <?php if ($model->video_file_name != '') {
            echo ButtonDropdown::widget([
                'label' => 'Сформировать цифровую маску',
                'dropdown' => [
                    'items' => [
                        ['label' => 'Модулем Ивана', 'url' => '#', 'options' => ['class' => 'get-ivan-landmarks',
                            'id' => $model->id]],
                        ['label' => 'Модулем Андрея', 'url' => '/video-interview/get-andrey-landmarks/' . $model->id],
                    ],
                ],
                'options' => ['class' => 'btn btn-success']
            ]);
        } ?>
        <?= Html::a('Обновить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
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
                'attribute' => 'respondent_id',
                'label' => 'Респондент',
                'value' => $model->respondent->name,
            ],
            [
                'label' => 'Описание',
                'value' => ($model->description != '') ? $model->description : null,
                'format' => 'raw'
            ],
            [
                'label' => 'Файл видеоинтервью',
                'value' => ($model->video_file_name != '') ? Html::a('скачать',
                    ['/video-interview/video-download/' . $model->id], ['target' => '_blank']) : null,
                'format' => 'raw'
            ]
        ],
    ]) ?>

</div>