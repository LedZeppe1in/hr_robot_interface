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
/* @var $factTemplates app\modules\main\controllers\AnalysisResultController */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Результаты анализа', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<!-- Подключение js-скрипта -->
<?php $this->registerJsFile('/js/Integration.js') ?>

<script type="text/javascript">
    // Переменная для базы знаний
    var knowledgeBase = '<?php echo str_replace(array("\r", "\n"), ' ', $knowledgeBase); ?>';
    // Массив для наборов шаблонов фактов
    var factTemplates = '<?php echo $factTemplates; ?>';
</script>

<div class="analysis-result-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Скачать результаты определения признаков',
            ['detection-file-download', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Скачать шаблоны фактов',
            ['fact-templates-download', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Скачать результаты интерпретации признаков', '#', ['class' => 'btn btn-primary']) ?>
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
                'label' => 'Видеоинтервью',
                'value' => $model->videoInterview->video_file_name,
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
                'label' => 'Шаблоны фактов',
                'content' => $this->render('_fact_templates', [
                    'factTemplates' => json_decode($factTemplates, true)
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