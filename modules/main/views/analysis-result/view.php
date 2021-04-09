<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\main\models\AnalysisResult */
/* @var $eyeFeatures app\modules\main\controllers\AnalysisResultController */
/* @var $mouthFeatures app\modules\main\controllers\AnalysisResultController */
/* @var $browFeatures app\modules\main\controllers\AnalysisResultController */
/* @var $eyebrowFeatures app\modules\main\controllers\AnalysisResultController */
/* @var $noseFeatures app\modules\main\controllers\AnalysisResultController */
/* @var $chinFeatures app\modules\main\controllers\AnalysisResultController */
/* @var $knowledgeBaseModel app\modules\main\models\KnowledgeBase */
/* @var $knowledgeBase app\modules\main\controllers\AnalysisResultController */
/* @var $facts app\modules\main\controllers\AnalysisResultController */
/* @var $interpretationResult app\modules\main\controllers\AnalysisResultController */

$this->title = $model->landmark->landmark_file_name;
$this->params['breadcrumbs'][] = ['label' => 'Итоговые результаты анализа', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<!-- Подключение css-стилей -->
<?php $this->registerCssFile('/css/theme.css') ?>
<?php $this->registerCssFile('/css/jQueryUI.css') ?>
<?php $this->registerCssFile('/css/jQueryUIStructure.css') ?>
<?php $this->registerCssFile('/css/jQueryGrid.css') ?>

<!-- Подключение js-скриптов -->
<?php $this->registerJsFile('/js/jQueryUI.js') ?>
<?php $this->registerJsFile('/js/jquery.ui.datepicker-ru.js') ?>
<?php $this->registerJsFile('/js/grid.locale-ru.js') ?>
<?php $this->registerJsFile('/js/jQueryGrid.js') ?>
<?php $this->registerJsFile('https://84.201.129.65:9999/Drools/Integration.js') ?>

<script type="text/javascript">
    // Номер результата анализа (для набора фактов)
    var facts = '<?php echo $model->id; ?>';
    // Номер результата анализа
    var IDOfRecord = '<?php echo $model->id; ?>';
    // Номер базы знаний
    var knowledgeBase = '<?php echo $knowledgeBaseModel->id; ?>';
</script>

<div class="analysis-result-view">

    <h1>Итоговые результаты для: <?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Посмотреть в редакторе маски',
            'https://84.201.129.65:8080/HRRMaskEditor/MaskEditor.php?landmark_id='. $model->landmark->id .
                '&detection_result_id=' . $model->id, ['class' => 'btn btn-primary']) ?>
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
                'attribute' => 'landmark',
                'label' => 'ID видеоинтервью',
                'format' => 'raw',
                'value' => Html::a($model->landmark->video_interview_id,
                    ['video-interview/view', 'id' => $model->landmark->video_interview_id]),
            ],
            [
                'attribute' => 'landmark',
                'label' => 'ID видео на вопрос',
                'format' => 'raw',
                'value' => ($model->landmark->question_id != '') ? Html::a($model->landmark->question_id,
                    ['question/view', 'id' => $model->landmark->question_id]) : null,
            ],
            [
                'attribute' => 'landmark_id',
                'label' => 'ID цифровой маски',
                'format' => 'raw',
                'value' => Html::a($model->landmark_id, ['landmark/view', 'id' => $model->landmark_id]),
            ],
            [
                'attribute' => 'landmark_id',
                'label' => 'Название файла с лицевыми точками',
                'value' => $model->landmark->landmark_file_name,
            ],
            [
                'label' => 'Описание',
                'format' => 'raw',
                'value' => ($model->description != '') ? $model->description : null,
            ],
            [
                'label' => 'Файл с результатами определения признаков',
                'format' => 'raw',
                'value' => ($model->detection_result_file_name != '') ? Html::a('скачать',
                    ['detection-file-download', 'id' => $model->id], ['target' => '_blank']) : null,
            ],
            [
                'label' => 'Файл с набором фактов',
                'format' => 'raw',
                'value' => ($model->facts_file_name != '') ? Html::a('скачать',
                    ['facts-download', 'id' => $model->id], ['target' => '_blank']) : null,
            ],
            [
                'label' => 'Файл с результатами интерпретации признаков',
                'format' => 'raw',
                'value' => ($model->interpretation_result_file_name != '') ? Html::a('скачать',
                    ['interpretation-file-download', 'id' => $model->id], ['target' => '_blank']) : null,
            ],
        ],
    ]) ?>

    <?php echo Tabs::widget([
        'items' => [
            [
                'label' => 'Признаки для глаз',
                'content' => $this->render('_eye_features', [
                    'eyeFeatures' => $eyeFeatures
                ]),
            ],
            [
                'label' => 'Признаки для рта',
                'content' => $this->render('_mouth_features', [
                    'mouthFeatures' => $mouthFeatures
                ]),
            ],
            [
                'label' => 'Признаки для лба',
                'content' => $this->render('_brow_features', [
                    'browFeatures' => $browFeatures
                ]),
            ],
            [
                'label' => 'Признаки для бровей',
                'content' => $this->render('_eyebrow_features', [
                    'eyebrowFeatures' => $eyebrowFeatures
                ]),
            ],
            [
                'label' => 'Признаки для носа',
                'content' => $this->render('_nose_features', [
                    'noseFeatures' => $noseFeatures
                ]),
            ],
            [
                'label' => 'Признаки для подбородка',
                'content' => $this->render('_chin_features', [
                    'chinFeatures' => $chinFeatures
                ]),
            ],
            [
                'label' => 'Факты',
                'content' => $this->render('_facts', [
                    'facts' => json_decode($facts, true)
                ]),
            ],
            [
                'label' => 'База знаний',
                'content' => $this->render('_knowledge_base', [
                    'knowledgeBaseModel' => $knowledgeBaseModel,
                    'knowledgeBase' => $knowledgeBase
                ]),
            ],
            [
                'label' => 'Результаты интерпретации',
                'content' => $this->render('_interpretation_result', [
                    'interpretationResult' => $interpretationResult
                ]),
            ]
        ]
    ]); ?>
</div>