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
/* @var $knowledgeBase app\modules\main\controllers\AnalysisResultController */
/* @var $facts app\modules\main\controllers\AnalysisResultController */

$this->title = ($model->videoInterview->landmark_file_name != '') ? $model->videoInterview->landmark_file_name :
    'не загружено';
$this->params['breadcrumbs'][] = ['label' => 'Итоговые результаты анализа видеоинтервью', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<!-- Подключение js-скрипта -->
<?php $this->registerJsFile('/js/Integration.js') ?>

<script type="text/javascript">
    // Переменная для базы знаний
    var knowledgeBase = '<?php echo $knowledgeBase;//str_replace(array("\r", "\n"), ' ', $knowledgeBase); ?>';
    // Массив для наборов фактов
    var facts = '<?php echo $facts; ?>';
</script>

<div class="analysis-result-view">

    <h1>Итоговые результаты для: <?= Html::encode($this->title) ?></h1>

    <p>
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
                'attribute' => 'video_interview_id',
                'label' => 'ID видеоинтервью',
                'value' => $model->videoInterview->id,
            ],
            [
                'attribute' => 'video_interview_id',
                'label' => 'Название файла видеоинтервью',
                'value' => $model->videoInterview->video_file_name,
            ],
            [
                'label' => 'Описание',
                'value' => ($model->description != '') ? $model->description : null,
                'format' => 'raw'
            ],
            [
                'label' => 'Файл с результатами определения признаков',
                'value' => ($model->detection_result_file_name != '') ? Html::a('скачать',
                    ['file-download', 'id' => $model->id], ['target' => '_blank']) : null,
                'format' => 'raw'
            ],
            [
                'label' => 'Файл с набором фактов',
                'value' => ($model->facts_file_name != '') ? Html::a('скачать',
                    ['facts-download', 'id' => $model->id], ['target' => '_blank']) : null,
                'format' => 'raw'
            ],
            [
                'label' => 'Файл с результатами интерпретации признаков',
                'value' => ($model->interpretation_result_file_name != '') ? Html::a('скачать',
                    '#', ['target' => '_blank']) : null,
                'format' => 'raw'
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
                'label' => 'Факты',
                'content' => $this->render('_facts', [
                    'facts' => json_decode($facts, true)
                ]),
            ],
            [
                'label' => 'База знаний',
                'content' => $this->render('_knowledge_base', [
                    'knowledgeBase' => $knowledgeBase
                ]),
            ],
            [
                'label' => 'Результаты интерпретации',
                'content' => $this->render('_interpretation_result'),
            ]
        ]
    ]); ?>
</div>