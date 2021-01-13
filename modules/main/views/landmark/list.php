<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\modules\main\models\Landmark;
use app\modules\main\models\FeaturesDetectionModuleSettingForm;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $featuresDetectionModuleSettingForm app\modules\main\models\FeaturesDetectionModuleSettingForm */

$this->title = 'Цифровые маски';
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_modal_form_features_detection_module_setting', [
    'featuresDetectionModuleSettingForm' => $featuresDetectionModuleSettingForm
]); ?>

<script type="text/javascript">
    let actionName = "";
    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        //
        var invariantRightLengthFirstPoint = document.getElementById("featuresdetectionmodulesettingform-invariantrightlengthfirstpoint");
        var invariantRightLengthSecondPoint = document.getElementById("featuresdetectionmodulesettingform-invariantrightlengthsecondpoint");
        var invariantLeftLengthFirstPoint = document.getElementById("featuresdetectionmodulesettingform-invariantleftlengthfirstpoint");
        var invariantLeftLengthSecondPoint = document.getElementById("featuresdetectionmodulesettingform-invariantleftlengthsecondpoint");
        // Обработка нажатия кнопки-иконки запуска МОП с параметрами
        $(".run-features-detection-with-parameters").click(function(e) {
            // Форма параметров настроек запуска МОП
            var form = document.getElementById("facial-features-detection-form");
            // Формирование названия URL-адреса для запроса
            if (actionName === "")
                actionName = form.action;
            form.action = actionName + "/" + this.id + "/3";
            // Открытие модального окна
            $("#facialFeaturesDetectionModalForm").modal("show");
        });
        // Обработка нажатия кнопки подтверждения запуска МОП с выбранными параметрами
        $("#facial-features-detection-button").click(function(e) {
            // Скрывание модального окна
            $("#facialFeaturesDetectionModalForm").modal("hide");
        });
        // Обработка выбора инвариантных точек в откидном списке
        $("#featuresdetectionmodulesettingform-invariantpointflag").change(function() {
            if (this.value === '0') {
                $("#featuresdetectionmodulesettingform-firstinvariantpoint").val(
                    "<?= FeaturesDetectionModuleSettingForm::INVARIANT1_POINT1 ?>"
                );
                $("#featuresdetectionmodulesettingform-secondinvariantpoint").val(
                    "<?= FeaturesDetectionModuleSettingForm::INVARIANT1_POINT2 ?>"
                );
            }
            if (this.value === '1') {
                $("#featuresdetectionmodulesettingform-firstinvariantpoint").val(
                    "<?= FeaturesDetectionModuleSettingForm::INVARIANT2_POINT1 ?>"
                );
                $("#featuresdetectionmodulesettingform-secondinvariantpoint").val(
                    "<?= FeaturesDetectionModuleSettingForm::INVARIANT2_POINT2 ?>"
                );
            }
        });
        // Обработка выбора использования длин в откидном списке
        $("#featuresdetectionmodulesettingform-uselength").change(function() {
            if (this.value === '0') {
                invariantRightLengthFirstPoint.style.display = "none";
                $("label[for='featuresdetectionmodulesettingform-invariantrightlengthfirstpoint']").hide();
                invariantRightLengthSecondPoint.style.display = "none";
                $("label[for='featuresdetectionmodulesettingform-invariantrightlengthsecondpoint']").hide();
                invariantLeftLengthFirstPoint.style.display = "none";
                $("label[for='featuresdetectionmodulesettingform-invariantleftlengthfirstpoint']").hide();
                invariantLeftLengthSecondPoint.style.display = "none";
                $("label[for='featuresdetectionmodulesettingform-invariantleftlengthsecondpoint']").hide();
            }
            if (this.value === '1') {
                invariantRightLengthFirstPoint.style.display = "block";
                $("label[for='featuresdetectionmodulesettingform-invariantrightlengthfirstpoint']").show();
                invariantRightLengthSecondPoint.style.display = "block";
                $("label[for='featuresdetectionmodulesettingform-invariantrightlengthsecondpoint']").show();
                invariantLeftLengthFirstPoint.style.display = "block";
                $("label[for='featuresdetectionmodulesettingform-invariantleftlengthfirstpoint']").show();
                invariantLeftLengthSecondPoint.style.display = "block";
                $("label[for='featuresdetectionmodulesettingform-invariantleftlengthsecondpoint']").show();
            }
        });
    });
</script>

<div class="landmark-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Загрузить', ['upload'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'attribute'=>'video_interview_id',
                'format' => 'raw',
                'value' => function($data) {
                    return Html::a($data->video_interview_id,
                        ['video-interview/view', 'id' => $data->video_interview_id]);
                },
            ],
            [
                'attribute'=>'question_id',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->question_id != '') ? Html::a($data->question_id,
                        ['question/view', 'id' => $data->question_id]) : null;
                },
            ],
            [
                'attribute'=>'landmark_file_name',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->landmark_file_name != '') ? $data->landmark_file_name : null;
                },
            ],
            [
                'attribute'=>'description',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->description != '') ? $data->description : null;
                },
            ],
            [
                'attribute'=>'type',
                'label' => 'Цифровая маска получена',
                'format' => 'raw',
                'value' => function($data) {
                    return ($data->type !== null) ? $data->getType() : null;
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'headerOptions' => ['class' => 'action-column'],
                'template' => '{view} {update} {mask-editor} {raw-detection} {norm-detection} {new-fdm} ' .
                    '{run-features-detection-with-parameters} {delete}',
                'buttons' => [
                    'mask-editor' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '',
                            ['class' => 'glyphicon glyphicon-user',
                                'title' => 'Посмотреть в редакторе маски']);
                        $url = 'https://84.201.129.65:8080/HRRMaskEditor/MaskEditor.php?landmark_id='. $model->id .
                            '&detection_result_id=none';
                        return Html::a($icon, $url);
                    },
                    'raw-detection' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '', ['class' => 'glyphicon glyphicon-file',
                            'title' => 'Определение признаков по сырым точкам']);
                        $url = ['/analysis-result/detection/' . $model->id . '/' . 0];
                        return ($model->landmark_file_name != '') ? Html::a($icon, $url) : null;
                    },
                    'norm-detection' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '', ['class' => 'glyphicon glyphicon-save-file',
                            'title' => 'Определение признаков по нормализованным точкам']);
                        $url = ['/analysis-result/detection/' . $model->id . '/' . 1];
                        return ($model->landmark_file_name != '' &&
                            $model->type != Landmark::TYPE_LANDMARK_ANDREW_MODULE) ? Html::a($icon, $url) : null;
                    },
                    'new-fdm' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '', ['class' => 'glyphicon glyphicon-circle-arrow-down',
                            'title' => 'Определение признаков по нормализованным точкам новый МОП']);
                        $url = ['/analysis-result/detection/' . $model->id . '/' . 2];
                        return ($model->landmark_file_name != '' &&
                            $model->type != Landmark::TYPE_LANDMARK_ANDREW_MODULE) ? Html::a($icon, $url) : null;
                    },
                    'run-features-detection-with-parameters' => function ($url, $model, $key) {
                        $icon = Html::tag('span', '', [
                            'class' => 'glyphicon glyphicon-wrench run-features-detection-with-parameters',
                            'id' => $model->id,
                            'title' => 'Запуск нового МОП с параметрами'
                        ]);
                        $url = '#';
                        return ($model->landmark_file_name != '' &&
                            $model->type != Landmark::TYPE_LANDMARK_ANDREW_MODULE) ? Html::a($icon, $url) : null;
                    },
                ],
            ],
        ],
    ]); ?>

</div>